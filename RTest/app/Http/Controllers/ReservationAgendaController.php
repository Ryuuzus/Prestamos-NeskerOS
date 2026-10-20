<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

/**
 * Agenda compartida de recursos prestados/reservados.
 * No muestra nombres, correos ni identificadores de los solicitantes.
 * Considera solamente reservas aprobadas y completadas; las pendientes
 * y rechazadas no representan compromisos confirmados de recursos.
 */
class ReservationAgendaController extends Controller
{
    public function index(Request $request): View
    {
        $input = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'view' => ['nullable', Rule::in(['week', 'month', 'day'])],
            'type' => ['nullable', Rule::in(['all', 'classroom', 'device'])],
        ]);

        $viewMode = $input['view'] ?? 'week';
        $type = $input['type'] ?? 'all';
        $selectedDate = isset($input['date'])
            ? Carbon::createFromFormat('!Y-m-d', $input['date'])
            : today();
        $selectedDate->startOfDay();

        // Límites semiabiertos [inicio, fin), para incluir reservas que
        // atraviesan medianoche y excluir las que terminan justo al inicio.
        if ($viewMode === 'day') {
            $from = $selectedDate->copy();
            $to = $from->copy()->addDay();
            $prevDate = $selectedDate->copy()->subDay()->toDateString();
            $nextDate = $selectedDate->copy()->addDay()->toDateString();
            $periodTitle = ucfirst($selectedDate->copy()->locale('es')->translatedFormat('l d')) . ' de ' . $selectedDate->copy()->locale('es')->translatedFormat('F Y');
        } elseif ($viewMode === 'month') {
            $from = $selectedDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $to = $selectedDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)->addDay()->startOfDay();
            $prevDate = $selectedDate->copy()->subMonthNoOverflow()->toDateString();
            $nextDate = $selectedDate->copy()->addMonthNoOverflow()->toDateString();
            $periodTitle = ucfirst($selectedDate->copy()->locale('es')->translatedFormat('F Y'));
        } else {
            $from = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
            $to = $from->copy()->addWeek();
            $prevDate = $selectedDate->copy()->subWeek()->toDateString();
            $nextDate = $selectedDate->copy()->addWeek()->toDateString();
            $periodTitle = $from->format('d/m/Y') . ' – ' . $to->copy()->subDay()->format('d/m/Y');
        }

        $query = Reservation::query()
            ->with(['classroom.building', 'device'])
            ->whereIn('status', [Reservation::STATUS_APPROVED, Reservation::STATUS_COMPLETED])
            // Las fronteras del período son medianoche. Consultar columnas DATE/TIME
            // por separado evita expresiones SQL específicas de MySQL y permite índices.
            ->where('start_date', '<', $to->toDateString())
            ->where(function ($q) use ($from) {
                $q->where('end_date', '>', $from->toDateString())
                    ->orWhere(function ($sameDay) use ($from) {
                        $sameDay->where('end_date', $from->toDateString())
                            ->where('end_time', '>', '00:00:00');
                    });
            })
            ->when($type === 'classroom', fn ($q) => $q->whereNotNull('classroom_id'))
            ->when($type === 'device', fn ($q) => $q->whereNotNull('device_id'))
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->orderBy('id');

        // Evita renderizar miles de bloques sin informar al usuario.
        $reservations = $query->limit(1001)->get();
        $truncated = $reservations->count() > 1000;
        if ($truncated) {
            $reservations = $reservations->take(1000);
        }

        $eventsByDay = [];
        $stats = ['reservations' => $reservations->count(), 'classrooms' => 0, 'devices' => 0];
        $now = now();

        foreach ($reservations as $reservation) {
            $start = $reservation->start_at;
            $end = $reservation->end_at;
            if (! $start || ! $end || $end->lte($start)) {
                continue;
            }

            $resources = [];
            if ($type !== 'device' && $reservation->classroom) {
                $resources[] = [
                    'kind' => 'classroom',
                    'title' => $reservation->classroom->classroom,
                    'detail' => $reservation->classroom->building?->name
                        ? $reservation->classroom->building->name . ' · Piso ' . $reservation->classroom->floor
                        : 'Aula · Piso ' . $reservation->classroom->floor,
                    'count' => null,
                ];
                $stats['classrooms']++;
            }
            if ($type !== 'classroom' && $reservation->device) {
                $resources[] = [
                    'kind' => 'device',
                    'title' => $reservation->device->name,
                    'detail' => 'Equipo · ' . (int) $reservation->device_quantity . ' unidad(es)',
                    'count' => (int) $reservation->device_quantity,
                ];
                $stats['devices']++;
            }

            $phase = $reservation->status === Reservation::STATUS_COMPLETED
                ? 'completed'
                : ($start->gt($now) ? 'upcoming' : ($end->lte($now) ? 'overdue' : 'active'));

            // Una reserva que dura varios días figura en CADA día afectado.
            $firstDay = $start->gt($from) ? $start->copy()->startOfDay() : $from->copy();
            $lastInstant = $end->lt($to) ? $end : $to;
            for ($day = $firstDay; $day->lt($lastInstant); $day->addDay()) {
                $key = $day->toDateString();
                $tomorrow = $day->copy()->addDay();
                $dayStart = $start->gt($day) ? $start : $day;
                $dayEnd = $end->lt($tomorrow) ? $end : $tomorrow;
                if ($dayStart->gte($dayEnd)) {
                    continue;
                }
                $timeText = $dayStart->format('H:i') . ' – '
                    . ($dayEnd->equalTo($tomorrow) ? '24:00' : $dayEnd->format('H:i'));

                foreach ($resources as $resource) {
                    $eventsByDay[$key][] = $resource + [
                        'time' => $timeText,
                        'startAt' => $start->format('d/m/Y H:i'),
                        'endAt' => $end->format('d/m/Y H:i'),
                        'phase' => $phase,
                        'sort' => $dayStart->timestamp,
                    ];
                }
            }
        }

        // Orden estable: hora y, en caso de empate, nombre del recurso.
        foreach ($eventsByDay as &$events) {
            usort($events, fn (array $a, array $b) => $a['sort'] <=> $b['sort'] ?: strcmp($a['title'], $b['title']));
        }
        unset($events);

        $days = [];
        for ($day = $from->copy(); $day->lt($to); $day->addDay()) {
            $days[] = $day->copy();
        }

        return view('reservation.agenda', compact(
            'viewMode', 'type', 'selectedDate', 'periodTitle', 'prevDate', 'nextDate',
            'days', 'eventsByDay', 'stats', 'truncated'
        ));
    }
}
