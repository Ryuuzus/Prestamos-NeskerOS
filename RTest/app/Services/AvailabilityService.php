<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Fuente única de disponibilidad de aulas y dispositivos.
 *
 * La ocupación NO se guarda en el aula ni en el dispositivo: se calcula con las reservaciones
 * APROBADAS que todavía no se han completado.
 *
 *  - Aula: está libre en un horario si ninguna reservación aprobada se cruza con él.
 *  - Dispositivo: tiene `quantity` unidades. En un horario hay disponibles
 *    `quantity - máximo de unidades usadas a la vez` por las reservaciones aprobadas que se cruzan.
 *    Con 10 unidades, prestar 1 deja 9 disponibles, y esas 9 siguen reservables.
 *
 * Las reservaciones PENDIENTES no bloquean nada; al aprobar se vuelve a validar.
 * Dos reservaciones que se tocan en el borde (una termina cuando empieza la otra) no se cruzan.
 * ==================================================================================================
 */
class AvailabilityService
{
    /**
     * Primera reservación aprobada que se cruza con [$start, $end) para el aula, o null si está libre.
     */
    public function classroomConflict(
        Classroom $classroom,
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $ignoreReservationId = null,
    ): ?Reservation {
        return Reservation::query()
            ->approved()
            ->where('classroom_id', $classroom->getKey())
            ->when($ignoreReservationId, fn ($query) => $query->where('id', '<>', $ignoreReservationId))
            ->overlapping($start, $end)
            ->first();
    }

    /**
     * Unidades del dispositivo que siguen disponibles durante TODO el horario [$start, $end).
     */
    public function deviceAvailableUnits(
        Device $device,
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $ignoreReservationId = null,
    ): int {
        $overlapping = Reservation::query()
            ->approved()
            ->where('device_id', $device->getKey())
            ->when($ignoreReservationId, fn ($query) => $query->where('id', '<>', $ignoreReservationId))
            ->overlapping($start, $end)
            ->get();

        return max(0, (int) $device->quantity - $this->peakUsage($overlapping));
    }

    /**
     * Unidades en uso ahora mismo: reservaciones aprobadas que ya iniciaron y no se han completado
     * (una devolución atrasada sigue reteniendo sus unidades).
     */
    public function deviceUnitsInUseNow(Device $device): int
    {
        return (int) Reservation::query()
            ->approved()
            ->started()
            ->where('device_id', $device->getKey())
            ->sum('device_quantity');
    }

    /**
     * Máximo de unidades comprometidas a la vez por TODAS las reservaciones aprobadas sin completar
     * (incluidas las futuras). Sirve para no permitir bajar la cantidad del dispositivo por debajo.
     */
    public function devicePeakCommitted(Device $device): int
    {
        return $this->peakUsage(
            Reservation::query()->approved()->where('device_id', $device->getKey())->get()
        );
    }

    /**
     * ¿El aula tiene reservaciones aprobadas sin completar (pasadas por devolver, en curso o futuras)?
     */
    public function classroomHasApprovedReservations(Classroom $classroom): bool
    {
        return Reservation::query()->approved()->where('classroom_id', $classroom->getKey())->exists();
    }

    /**
     * Máximo de unidades usadas simultáneamente por un conjunto de reservaciones.
     * Se recorren los inicios y finales en orden; si coinciden a la misma hora, el final va primero
     * para que dos reservaciones consecutivas no cuenten como simultáneas.
     */
    public function peakUsage(Collection $reservations): int
    {
        $events = [];

        foreach ($reservations as $reservation) {
            $quantity = max(1, (int) $reservation->device_quantity);

            // [momento, tipo (0 = fin, 1 = inicio), cambio]
            $events[] = [$reservation->start_at->getTimestamp(), 1, $quantity];
            $events[] = [$reservation->end_at->getTimestamp(), 0, -$quantity];
        }

        usort($events, fn ($a, $b) => $a[0] <=> $b[0] ?: $a[1] <=> $b[1]);

        $current = 0;
        $peak    = 0;

        foreach ($events as [, , $delta]) {
            $current += $delta;
            $peak = max($peak, $current);
        }

        return $peak;
    }
}
