<?php

namespace App\Services;

use App\Exceptions\ReservationException;
use App\Models\Building;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function __construct(private AvailabilityService $availability) {}

    public function approve(Reservation $reservation): void
    {
        $this->locked($reservation, function (Reservation $r) {
            $this->require($r->status === 'pending', 'Solo se pueden aprobar solicitudes pendientes.');
            $this->require($r->start_at->gt(now()), 'El inicio ya pasó. La solicitud debe cerrarse como vencida.');
            $this->require($r->classroom_id || $r->device_id, 'La solicitud ya no tiene recursos disponibles.');
            if ($r->classroom_id) {
                $classroom = $r->classroom;
                $this->require($classroom && $classroom->status !== 'maintenance' && $classroom->building?->status !== 'maintenance', 'El aula o su edificio está en mantenimiento.');
                $this->require(! $this->availability->classroomConflict($classroom, $r->start_at, $r->end_at, $r->id), 'El aula ya tiene una reservación aprobada en ese horario.');
            }
            if ($r->device_id) {
                $device = $r->device;
                $this->require($device && $device->status !== 'maintenance', 'El equipo está en mantenimiento.');
                $available = $this->availability->deviceAvailableUnits($device, $r->start_at, $r->end_at, $r->id);
                $this->require($r->device_quantity <= $available, "Solo hay {$available} unidad(es) disponibles en ese horario.");
            }
            $r->update(['status' => 'approved']);
            $this->record($r, 'approved');
        });
    }

    public function reject(Reservation $reservation, string $reason): void
    {
        $this->locked($reservation, function (Reservation $r) use ($reason) {
            $this->require($r->status === 'pending', 'Solo se pueden rechazar solicitudes pendientes.');
            $this->reason($reason);
            $r->update(['status' => 'rejected', 'rejection_reason' => $reason, 'closed_at' => now()]);
            $this->record($r, 'rejected', $reason);
        });
    }

    public function updatePending(Reservation $reservation, array $data): void
    {
        $this->locked($reservation, function (Reservation $r) use ($data) {
            $this->require($r->status === 'pending', 'La solicitud ya fue procesada y no puede editarse.');
            $r->update($data);
        });
    }

    public function cancel(Reservation $reservation, string $reason): void
    {
        $this->locked($reservation, function (Reservation $r) use ($reason) {
            $this->reason($reason);
            $this->require($r->canCancel(), 'No se puede cancelar: ya hay entregas registradas o pendientes de verificar. Solicita al personal registrar las devoluciones.');
            $r->update(['status' => 'cancelled', 'closed_at' => now(), 'closure_reason' => $reason]);
            $this->record($r, 'cancelled', $reason);
        });
    }

    /** Every handover is explicit; approving or reaching the start time never issues resources. */
    public function operate(Reservation $reservation, array $data): void
    {
        $this->locked($reservation, function (Reservation $r) use ($data) {
            $this->require($r->status === 'approved', 'Esta reservación ya no admite movimientos de préstamo.');
            $action = $data['action'];
            if (in_array($action, ['issue_key', 'issue_devices'], true)) {
                $this->require(! $r->hasUnverifiedCustody(), 'Primero verifica las entregas anteriores.');
                $this->require($r->start_at->lte(now()) && $r->end_at->gt(now()), 'La recogida solo se registra dentro del horario aprobado.');
                $this->issue($r, $action === 'issue_key' ? 'key' : 'devices', now());
                $this->record($r, $action, quantity: $action === 'issue_devices' ? $r->device_quantity : null);
            } elseif ($action === 'return_key') {
                $this->require(! $r->key_custody_unverified && $r->key_issued_at && ! $r->key_returned_at, 'No hay una llave entregada pendiente de devolución.');
                $r->update(['key_returned_at' => now()]);
                $this->record($r, $action);
            } elseif ($action === 'return_devices') {
                $quantity = (int) ($data['quantity'] ?? 0);
                $this->require(! $r->devices_custody_unverified && $r->devices_issued_at && $quantity > 0 && $quantity <= $r->device_quantity - $r->devices_returned, 'La cantidad debe estar entre 1 y los equipos pendientes de devolución.');
                $returned = $r->devices_returned + $quantity;
                $r->update(['devices_returned' => $returned, 'devices_returned_at' => $returned === $r->device_quantity ? now() : null]);
                $this->record($r, $action, quantity: $quantity);
            } elseif ($action === 'release' || $action === 'no_show') {
                $reason = $data['reason'] ?? '';
                $this->reason($reason);
                $this->require(! $r->hasUnverifiedCustody(), 'Primero verifica las entregas anteriores.');
                if ($action === 'no_show') {
                    $this->require(! $r->hasIssuedResources() && $r->start_at->lte(now()), 'Solo puedes registrar falta de recogida después del inicio y si no hubo entregas.');
                }
                $this->require($this->releaseUncollected($r), 'No quedan recursos sin recoger que se puedan liberar.');
                $r->closure_reason = $reason;
                $r->save();
                $this->record($r, $action, $reason);
                if (! $r->hasIssuedResources()) {
                    $r->update(['status' => $action === 'no_show' ? 'no_show' : 'cancelled', 'closed_at' => now()]);
                }
            } elseif (in_array($action, ['verify_key', 'verify_devices'], true)) {
                $resource = $action === 'verify_key' ? 'key' : 'devices';
                $this->require((bool) $r->{$resource.'_custody_unverified'}, 'Este recurso ya fue verificado.');
                $reason = $data['reason'] ?? '';
                $this->reason($reason);
                $issued = ($data['custody'] ?? '') === 'issued';
                if ($issued) {
                    $at = Carbon::parse($data['occurred_at']);
                    $this->require($at->gte($r->start_at) && $at->lte(now()), 'La entrega anterior debe estar entre el inicio de la reservación y el momento actual.');
                    $this->issue($r, $resource, $at, historical: true);
                }
                $r->update([$resource.'_custody_unverified' => false]);
                $this->record($r, $action, ($issued ? 'Sí se entregó. ' : 'No se entregó. ').$reason, occurredAt: $issued ? $at : now());
            } else {
                throw new ReservationException('Movimiento de préstamo no válido.');
            }
            $this->finish($r);
        });
    }

    /** Retains the legacy route but prevents an ambiguous, bulk physical return. */
    public function complete(Reservation $reservation): void
    {
        throw new ReservationException('Registra la devolución de la llave y de cada cantidad de equipos desde Gestionar préstamo.');
    }

    public function reconcileDue(): int
    {
        $changed = 0;
        Reservation::query()->whereIn('status', ['pending', 'approved'])->started()
            ->chunkById(100, function ($rows) use (&$changed) {
                foreach ($rows as $row) {
                    $this->locked($row, function (Reservation $r) use (&$changed) {
                        if ($r->status === 'pending' && $r->start_at->lte(now())) {
                            $reason = 'La solicitud llegó a su hora de inicio sin aprobación.';
                            $r->update(['status' => 'expired', 'closed_at' => now(), 'closure_reason' => $reason]);
                            $this->record($r, 'expired', $reason, system: true);
                            $changed++;
                        } elseif ($r->status === 'approved' && $r->end_at->lte(now()) && ! $r->hasUnverifiedCustody()) {
                            $released = $this->releaseUncollected($r);
                            if ($released) {
                                $this->record($r, 'release', 'Terminó el horario sin recoger estos recursos.', system: true);
                                $changed++;
                            }
                            if (! $r->hasIssuedResources()) {
                                $r->update(['status' => 'no_show', 'closed_at' => now(), 'closure_reason' => 'Terminó el horario sin recoger la llave ni los equipos solicitados.']);
                                $this->record($r, 'no_show', $r->closure_reason, system: true);
                            } else {
                                $this->finish($r, system: true);
                            }
                        }
                    });
                }
            });

        return $changed;
    }

    /** Called inside the maintenance transaction. Issued resources remain outstanding. */
    public function affectedByMaintenance(Reservation $r, string $resource, string $reason): void
    {
        if (! in_array($r->status, ['pending', 'approved'], true)) {
            return;
        }
        $this->record($r, 'maintenance', $reason);
        $r->closure_reason = $reason;
        if (! $r->hasIssuedResources() && ! $r->hasUnverifiedCustody()) {
            $r->fill(['status' => 'cancelled', 'closed_at' => now()]);
        } elseif (! $r->{$resource.'_issued_at'} && ! $r->{$resource.'_custody_unverified'}) {
            $r->{$resource.'_released_at'} = now();
        }
        $r->save();
        $this->finish($r);
    }

    private function issue(Reservation $r, string $resource, Carbon $at, bool $historical = false): void
    {
        $this->require(! $r->{$resource.'_issued_at'} && ! $r->{$resource.'_released_at'}, 'Este recurso ya fue entregado o liberado.');
        if ($resource === 'key') {
            $classroom = $r->classroom;
            $this->require($classroom !== null, 'Esta reservación no tiene aula.');
            if (! $historical) {
                $this->require($classroom->status !== 'maintenance' && $classroom->building?->status !== 'maintenance', 'El aula o el edificio está en mantenimiento.');
            }
            if (! $historical) {
                $this->require(! Reservation::keyOutstanding()->where('classroom_id', $r->classroom_id)->whereKeyNot($r->id)->exists(), 'La llave sigue prestada o su custodia está por verificar en otra reservación.');
            }
        } else {
            $device = $r->device;
            $this->require($device !== null, 'Esta reservación no tiene equipos.');
            if (! $historical) {
                $this->require($device->status !== 'maintenance', 'El equipo está en mantenimiento.');
            }
            if (! $historical) {
                $outstanding = Reservation::devicesOutstanding()->where('device_id', $r->device_id)->whereKeyNot($r->id)
                    ->selectRaw('COALESCE(SUM(device_quantity - devices_returned), 0) as total')->value('total');
                $this->require($r->device_quantity <= $device->quantity - $outstanding, 'Faltan equipos por devolver o verificar de otra reservación; no hay unidades físicas suficientes.');
            }
        }
        $r->update([$resource.'_issued_at' => $at]);
    }

    private function releaseUncollected(Reservation $r): bool
    {
        $changed = false;
        foreach (['key' => 'classroom_id', 'devices' => 'device_id'] as $resource => $id) {
            if ($r->$id && ! $r->{$resource.'_issued_at'} && ! $r->{$resource.'_released_at'} && ! $r->{$resource.'_custody_unverified'}) {
                $r->{$resource.'_released_at'} = now();
                $changed = true;
            }
        }
        $r->save();

        return $changed;
    }

    private function finish(Reservation $r, bool $system = false): void
    {
        if ($r->status !== 'approved' || $r->hasOutstandingResources() || ! $r->hasIssuedResources()) {
            return;
        }
        $keyDone = ! $r->classroom_id || $r->key_returned_at || $r->key_released_at;
        $devicesDone = ! $r->device_id || $r->devices_returned_at || $r->devices_released_at;
        if ($keyDone && $devicesDone) {
            $lastReturn = collect([$r->key_returned_at, $r->devices_returned_at])->filter()->sort()->last();
            $r->update(['status' => 'completed', 'actual_return_time' => $lastReturn, 'closed_at' => now()]);
            $this->record($r, 'completed', system: $system);
        }
    }

    private function locked(Reservation $reservation, Closure $action): void
    {
        DB::transaction(function () use ($reservation, $action) {
            $r = Reservation::lockForUpdate()->findOrFail($reservation->id);
            if ($r->classroom_id) {
                $classroom = Classroom::lockForUpdate()->find($r->classroom_id);
                if ($classroom) {
                    Building::whereKey($classroom->buildings_id)->lockForUpdate()->first();
                }
            }
            if ($r->device_id) {
                Device::whereKey($r->device_id)->lockForUpdate()->first();
            }
            $action($r);
        }, 5);
    }

    private function record(Reservation $r, string $action, ?string $reason = null, ?int $quantity = null, ?Carbon $occurredAt = null, bool $system = false): void
    {
        $r->events()->create(['action' => $action, 'reason' => $reason, 'quantity' => $quantity,
            'actor_id' => $system ? null : auth()->id(), 'occurred_at' => $occurredAt ?? now()]);
    }

    private function reason(string $reason): void
    {
        $this->require(trim($reason) !== '' && mb_strlen($reason) <= 500, 'Escribe un motivo de hasta 500 caracteres.');
    }

    private function require(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new ReservationException($message);
        }
    }
}
