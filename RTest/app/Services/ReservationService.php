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
 * Ciclo de vida de una reservación.
 *
 *  pending   -> approved  : se valida que el aula/dispositivo no esté en mantenimiento y que haya
 *                           disponibilidad en el horario (aula libre, o unidades suficientes).
 *  pending   -> rejected  : se registra el motivo.
 *  approved  -> completed : se registra la entrega/devolución (actual_return_time).
 *  eliminar               : un usuario normal solo puede eliminar las pendientes.
 *
 * Este servicio NO modifica el estado del aula ni del dispositivo: la ocupación se calcula con las
 * reservaciones aprobadas (ver AvailabilityService). Todo corre en una transacción y las filas del
 * recurso se bloquean al aprobar, para que dos aprobaciones simultáneas no se crucen.
 * ==================================================================================================
 */
class ReservationService
{
    public function __construct(private AvailabilityService $availability)
    {
    }

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

            // Se bloquean las filas del recurso mientras se valida la disponibilidad.
            $classroom = $reservation->classroom_id ? Classroom::lockForUpdate()->find($reservation->classroom_id) : null;
            $device    = $reservation->device_id ? Device::lockForUpdate()->find($reservation->device_id) : null;

            if ($classroom) {
                if ($classroom->status === 'maintenance') {
                    throw new ReservationException("El aula «{$classroom->classroom}» está en mantenimiento; no se puede aprobar la reservación.");
                }

                $conflict = $this->availability->classroomConflict(
                    $classroom,
                    $reservation->start_at,
                    $reservation->end_at,
                    $reservation->id,
                );

                if ($conflict) {
                    throw new ReservationException(
                        "El aula «{$classroom->classroom}» ya tiene la reservación aprobada #{$conflict->id} que se cruza con este horario."
                    );
                }
            }

            if ($device) {
                if ($device->status === 'maintenance') {
                    throw new ReservationException("El dispositivo «{$device->name}» está en mantenimiento; no se puede aprobar la reservación.");
                }

                $available = $this->availability->deviceAvailableUnits(
                    $device,
                    $reservation->start_at,
                    $reservation->end_at,
                    $reservation->id,
                );

                if ((int) $reservation->device_quantity > $available) {
                    throw new ReservationException(
                        "El dispositivo «{$device->name}» solo tiene {$available} unidad(es) disponible(s) en ese horario y la reservación pide {$reservation->device_quantity}."
                    );
                }
            }

            $reservation->update(['status' => Reservation::STATUS_APPROVED]);
        });
    }

    /**
     * Rechaza una reservación pendiente.
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
     * Marca una reservación aprobada como entregada/devuelta. Al dejar de estar "aprobada",
     * el aula o las unidades vuelven a contar como disponibles automáticamente.
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

            $reservation->delete();
        });
    }
}
