<?php

namespace App\Services;

use App\Exceptions\ReservationException;
use App\Models\Device;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Calcula la disponibilidad del inventario por intervalo sin modificar devices.quantity.
 * Cada reservación aprobada representa una unidad del catálogo.
 */
class DeviceAvailabilityService
{
    /** Unidades que no están prestadas ahora. Una aprobación iniciada sigue prestada
     * hasta que el administrador la marque como completada/devuelta. */
    public function availableQuantityNow(Device $device): int
    {
        if ($device->status === 'maintenance') {
            return 0;
        }

        $now = now();
        $loanedNow = $this->approvedReservations($device)
            ->filter(fn (Reservation $reservation) => $this->startAt($reservation)->lte($now))
            ->count();

        return max(0, (int) $device->quantity - $loanedNow);
    }

    /**
     * Cantidad de unidades disponibles durante todo el intervalo solicitado.
     * El resultado resta el máximo número de reservaciones aprobadas que coinciden en un mismo
     * instante dentro del intervalo pedido, no el total de reservaciones que se cruzan con él.
     */
    public function availableForPeriod(
        Device $device,
        Carbon $requestedStart,
        Carbon $requestedEnd,
        ?int $ignoreReservationId = null
    ): int {
        if ($device->status === 'maintenance' || $requestedEnd->lte($requestedStart)) {
            return 0;
        }

        $reservations = $this->approvedReservations($device)
            ->reject(fn (Reservation $reservation) => $ignoreReservationId !== null
                && (int) $reservation->getKey() === $ignoreReservationId);

        $peak = $this->peakConcurrent(
            $reservations,
            $requestedStart,
            $requestedEnd,
            true
        );

        return max(0, (int) $device->quantity - $peak);
    }

    /**
     * Verifica la disponibilidad antes de aprobar una reservación.
     * Llamar dentro de una transacción; el servicio de reservaciones bloquea previamente la fila
     * del dispositivo para serializar las aprobaciones que compiten por la última unidad.
     */
    public function assertAvailableForPeriod(
        Device $device,
        Carbon $requestedStart,
        Carbon $requestedEnd,
        ?int $ignoreReservationId = null,
        bool $lockDevice = false
    ): Device {
        if ($lockDevice) {
            $device = Device::query()->whereKey($device->getKey())->lockForUpdate()->firstOrFail();
        }

        if ($device->status === 'maintenance') {
            throw new ReservationException(
                "El dispositivo «{$device->name}» está en mantenimiento y no se puede prestar."
            );
        }

        $available = $this->availableForPeriod(
            $device,
            $requestedStart,
            $requestedEnd,
            $ignoreReservationId
        );

        if ($available < 1) {
            throw new ReservationException(
                "No hay unidades disponibles de «{$device->name}» para el periodo seleccionado. "
                . "Existencia total: {$device->quantity}. Las unidades ya están comprometidas en ese horario."
            );
        }

        return $device;
    }

    /**
     * Número mínimo de unidades que debe conservarse para cubrir las reservaciones aprobadas
     * actuales y futuras. Un préstamo iniciado que no se ha marcado como devuelto sigue consumiendo
     * una unidad; sus fechas futuras comprometidas también se respetan.
     */
    public function minimumRequiredQuantity(Device $device): int
    {
        $now = now();
        $events = [];

        foreach ($this->approvedReservations($device) as $reservation) {
            $start = $this->startAt($reservation);
            $end = $this->endAt($reservation);

            // El inventario ya está físicamente prestado. Si el horario previsto terminó y la
            // reservación sigue aprobada, se considera atrasada hasta registrar la devolución.
            if ($start->lte($now)) {
                $start = $now->copy();
                if ($end->lte($now)) {
                    $end = $now->copy()->addYears(50);
                }
            }

            if ($end->lte($start)) {
                continue;
            }

            $events[] = ['time' => $start->getTimestamp(), 'delta' => 1];
            $events[] = ['time' => $end->getTimestamp(), 'delta' => -1];
        }

        return $this->peakFromEvents($events);
    }

    /** @return Collection<int, Reservation> */
    private function approvedReservations(Device $device): Collection
    {
        return Reservation::query()
            ->approved()
            ->where('device_id', $device->getKey())
            ->get(['id', 'device_id', 'start_date', 'start_time', 'end_date', 'end_time']);
    }

    /**
     * Calcula el pico de concurrencia. Cuando una reservación iniciada ya venció y sigue approved,
     * se extiende hasta el final del intervalo consultado: aún no existe confirmación de devolución.
     *
     * @param Collection<int, Reservation> $reservations
     */
    private function peakConcurrent(
        Collection $reservations,
        ?Carbon $rangeStart,
        ?Carbon $rangeEnd,
        bool $extendOverdueLoans
    ): int {
        $events = [];
        $now = now();

        foreach ($reservations as $reservation) {
            $start = $this->startAt($reservation);
            $end = $this->endAt($reservation);

            if ($extendOverdueLoans && $start->lte($now) && $end->lte($now) && $rangeEnd !== null) {
                $end = $rangeEnd->copy();
            }

            // Descarta intervalos que no cruzan el rango consultado. Esto es esencial: una reserva
            // del lunes no debe consumir capacidad de una reserva del martes si los horarios no se cruzan.
            if ($rangeStart !== null && $end->lte($rangeStart)) {
                continue;
            }
            if ($rangeEnd !== null && $start->gte($rangeEnd)) {
                continue;
            }

            if ($rangeStart !== null && $start->lt($rangeStart)) {
                $start = $rangeStart->copy();
            }
            if ($rangeEnd !== null && $end->gt($rangeEnd)) {
                $end = $rangeEnd->copy();
            }

            if ($end->lte($start)) {
                continue;
            }

            $events[] = ['time' => $start->getTimestamp(), 'delta' => 1];
            $events[] = ['time' => $end->getTimestamp(), 'delta' => -1];
        }

        return $this->peakFromEvents($events);
    }

    /** @param array<int, array{time:int, delta:int}> $events */
    private function peakFromEvents(array $events): int
    {
        // Los intervalos son semiabiertos [inicio, fin): una reservación que termina a las 10:00
        // libera su unidad para otra que empieza exactamente a las 10:00.
        usort($events, static function (array $a, array $b): int {
            return ($a['time'] <=> $b['time']) ?: ($a['delta'] <=> $b['delta']);
        });

        $current = 0;
        $peak = 0;
        foreach ($events as $event) {
            $current += $event['delta'];
            $peak = max($peak, $current);
        }

        return $peak;
    }

    private function startAt(Reservation $reservation): Carbon
    {
        $value = $reservation->start_at;
        return $value instanceof Carbon ? $value->copy() : Carbon::parse($value);
    }

    private function endAt(Reservation $reservation): Carbon
    {
        $value = $reservation->end_at;
        return $value instanceof Carbon ? $value->copy() : Carbon::parse($value);
    }
}
