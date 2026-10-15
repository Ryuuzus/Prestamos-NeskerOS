<?php

namespace App\Services;

use App\Exceptions\ReservationException;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Ciclo de vida de una reservación y su efecto sobre el estado del aula / dispositivo.
 *
 *  pending   -> approved  : el recurso pasa a "occupied" (si no está en mantenimiento y no hay cruce).
 *  pending   -> rejected  : el recurso no cambia.
 *  approved  -> completed : el recurso vuelve a "available" SOLO si no le quedan otras reservaciones
 *                           aprobadas; si le quedan, sigue "occupied".
 *  eliminar una aprobada  : misma sincronización que al completar.
 *
 * Un recurso en "maintenance" nunca se cambia aquí. Todo corre en una transacción y con las filas
 * del recurso bloqueadas, para que dos aprobaciones simultáneas no se crucen.
 * ==================================================================================================
 */
class ReservationService
{
    /**
     * Aprueba una reservación pendiente.
     *
     * @throws ReservationException
     */
    public function approve(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $reservation = Reservation::lockForUpdate()->findOrFail($reservation->id);

            if ($reservation->status !== Reservation::STATUS_PENDING) {
                throw new ReservationException('Solo se pueden aprobar reservaciones pendientes.');
            }

            if ($reservation->end_at->isPast()) {
                throw new ReservationException('La reservación ya venció y no se puede aprobar. Recházala indicando el motivo.');
            }

            foreach ($this->resourcesOf($reservation) as [$resource, $foreignKey, $label, $name]) {
                if ($resource->status === 'maintenance') {
                    throw new ReservationException("{$label} «{$name}» está en mantenimiento; no se puede aprobar la reservación.");
                }

                $conflict = Reservation::query()
                    ->approved()
                    ->where($foreignKey, $resource->getKey())
                    ->where('id', '<>', $reservation->id)
                    ->overlapping($reservation->start_at, $reservation->end_at)
                    ->first();

                if ($conflict) {
                    throw new ReservationException(
                        "{$label} «{$name}» ya tiene la reservación aprobada #{$conflict->id} que se cruza con este horario."
                    );
                }
            }

            $reservation->update(['status' => Reservation::STATUS_APPROVED]);

            $this->syncResources($reservation);
        });
    }

    /**
     * Rechaza una reservación pendiente. No modifica el estado de ningún recurso.
     *
     * @throws ReservationException
     */
    public function reject(Reservation $reservation, string $reason): void
    {
        if ($reservation->status !== Reservation::STATUS_PENDING) {
            throw new ReservationException('Solo se pueden rechazar reservaciones pendientes.');
        }

        $reservation->update([
            'status'           => Reservation::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Marca una reservación aprobada como entregada/devuelta y libera el recurso.
     *
     * @throws ReservationException
     */
    public function complete(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $reservation = Reservation::lockForUpdate()->findOrFail($reservation->id);

            if ($reservation->status !== Reservation::STATUS_APPROVED) {
                throw new ReservationException('Solo se pueden completar reservaciones aprobadas.');
            }

            $reservation->update([
                'status'             => Reservation::STATUS_COMPLETED,
                'actual_return_time' => now(),
            ]);

            $this->syncResources($reservation);
        });
    }

    /**
     * Elimina una reservación. Un usuario normal solo puede eliminar las pendientes.
     *
     * @throws ReservationException
     */
    public function delete(Reservation $reservation, bool $isAdmin): void
    {
        DB::transaction(function () use ($reservation, $isAdmin) {
            $reservation = Reservation::lockForUpdate()->findOrFail($reservation->id);

            if (! $isAdmin && $reservation->status !== Reservation::STATUS_PENDING) {
                throw new ReservationException('Solo puedes cancelar solicitudes pendientes.');
            }

            $wasApproved = $reservation->status === Reservation::STATUS_APPROVED;

            $reservation->delete();

            if ($wasApproved) {
                $this->syncResources($reservation);
            }
        });
    }

    /**
     * Recalcula el estado de los recursos de la reservación:
     * "occupied" si les queda al menos una reservación aprobada; si no, "available".
     * Los recursos en mantenimiento no se tocan.
     */
    private function syncResources(Reservation $reservation): void
    {
        foreach ($this->resourcesOf($reservation) as [$resource, $foreignKey]) {
            if ($resource->status === 'maintenance') {
                continue;
            }

            $hasApproved = Reservation::query()
                ->approved()
                ->where($foreignKey, $resource->getKey())
                ->exists();

            $resource->update(['status' => $hasApproved ? 'occupied' : 'available']);
        }
    }

    /**
     * Aula y/o dispositivo de la reservación, con la fila bloqueada para escritura.
     *
     * @return array<int, array{0: \Illuminate\Database\Eloquent\Model, 1: string, 2: string, 3: string}>
     */
    private function resourcesOf(Reservation $reservation): array
    {
        $resources = [];

        if ($reservation->classroom_id) {
            $classroom = Classroom::lockForUpdate()->find($reservation->classroom_id);

            if ($classroom) {
                $resources[] = [$classroom, 'classroom_id', 'El aula', $classroom->classroom];
            }
        }

        if ($reservation->device_id) {
            $device = Device::lockForUpdate()->find($reservation->device_id);

            if ($device) {
                $resources[] = [$device, 'device_id', 'El dispositivo', $device->name];
            }
        }

        return $resources;
    }
}