<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use Carbon\Carbon;
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
     * Intervalos [inicio, fin) en los que la combinación pedida NO se puede reservar:
     * aula ocupada, o dispositivo sin unidades suficientes para la cantidad solicitada.
     * El formulario los usa para deshabilitar las horas que no están disponibles.
     *
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    public function blockedIntervals(
        ?Classroom $classroom,
        ?Device $device,
        int $quantity,
        CarbonInterface $from,
        CarbonInterface $to,
    ): array {
        $intervals = [];

        if ($classroom) {
            if ($classroom->status === 'maintenance') {
                $intervals[] = [Carbon::instance($from), Carbon::instance($to)];
            } else {
                Reservation::query()
                    ->approved()
                    ->where('classroom_id', $classroom->getKey())
                    ->overlapping($from, $to)
                    ->get()
                    ->each(function (Reservation $reservation) use (&$intervals) {
                        $intervals[] = [$reservation->start_at, $reservation->end_at];
                    });
            }
        }

        if ($device) {
            $intervals = array_merge($intervals, $this->deviceBlockedIntervals($device, $quantity, $from, $to));
        }

        return $this->mergeIntervals($intervals);
    }

    /**
     * Momentos en los que el dispositivo NO tiene unidades libres para $quantity:
     * mientras el uso simultáneo supere `quantity total - quantity solicitada`.
     *
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    private function deviceBlockedIntervals(Device $device, int $quantity, CarbonInterface $from, CarbonInterface $to): array
    {
        // En mantenimiento, o pide más unidades de las que existen: nada es reservable.
        if ($device->status === 'maintenance' || $quantity > (int) $device->quantity) {
            return [[Carbon::instance($from), Carbon::instance($to)]];
        }

        $limit = (int) $device->quantity - $quantity; // uso máximo que todavía deja lugar a la solicitud

        $reservations = Reservation::query()
            ->approved()
            ->where('device_id', $device->getKey())
            ->overlapping($from, $to)
            ->get();

        $events = [];

        foreach ($reservations as $reservation) {
            $units = max(1, (int) $reservation->device_quantity);

            $events[] = [$reservation->start_at->getTimestamp(), 1, $units];
            $events[] = [$reservation->end_at->getTimestamp(), 0, -$units];
        }

        // A la misma hora, el final va antes que el inicio (reservaciones consecutivas no se cruzan).
        usort($events, fn ($a, $b) => $a[0] <=> $b[0] ?: $a[1] <=> $b[1]);

        $timezone    = config('app.timezone');
        $result      = [];
        $current     = 0;
        $blockedFrom = null;

        foreach ($events as [$moment, , $delta]) {
            $current += $delta;

            if ($current > $limit && $blockedFrom === null) {
                $blockedFrom = $moment;
            } elseif ($current <= $limit && $blockedFrom !== null) {
                $result[]    = [Carbon::createFromTimestamp($blockedFrom, $timezone), Carbon::createFromTimestamp($moment, $timezone)];
                $blockedFrom = null;
            }
        }

        return $result;
    }

    /**
     * Une los intervalos que se traslapan o se tocan.
     *
     * @param  array<int, array{0: Carbon, 1: Carbon}>  $intervals
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    private function mergeIntervals(array $intervals): array
    {
        usort($intervals, fn ($a, $b) => $a[0]->getTimestamp() <=> $b[0]->getTimestamp());

        $merged = [];

        foreach ($intervals as [$start, $end]) {
            $last = count($merged) - 1;

            if ($last >= 0 && $start->getTimestamp() <= $merged[$last][1]->getTimestamp()) {
                if ($end->getTimestamp() > $merged[$last][1]->getTimestamp()) {
                    $merged[$last][1] = $end;
                }
            } else {
                $merged[] = [$start, $end];
            }
        }

        return $merged;
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
