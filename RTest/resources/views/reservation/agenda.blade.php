{{--
    Agenda de préstamos, lectura para user/moderator/admin.
    Se muestran recursos y fechas; nunca se exponen los datos del solicitante.
    Los préstamos con aula y dispositivo aparecen como dos recursos separados.
--}}
@extends('layouts.app')

@push('styles')
    @vite(['resources/css/agenda.css'])
@endpush

@section('content')
@php
    $typeLabels = ['all' => 'Todos los recursos', 'classroom' => 'Solo aulas', 'device' => 'Solo dispositivos'];
    $phaseLabels = [
        'upcoming' => 'Programado',
        'active' => 'En curso',
        'overdue' => 'Devolución pendiente',
        'completed' => 'Completado',
    ];
    $agendaLink = fn ($date, $view = null, $kind = null) => route('agenda.index', [
        'date' => $date,
        'view' => $view ?? $viewMode,
        'type' => $kind ?? $type,
    ]);
    $today = today()->toDateString();
@endphp

<div class="container-fluid px-3 px-md-4 py-4 agenda-wrap">
    <div class="agenda-head">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa fa-calendar-alt me-2" aria-hidden="true"></i>Agenda de préstamos</h3>
            <p class="agenda-subtitle">Consulta cuándo están reservadas las aulas y las unidades de dispositivos. Visible para todos los roles.</p>
        </div>
        <div class="agenda-controls" aria-label="Cambiar vista de agenda">
            <a href="{{ $agendaLink($selectedDate->toDateString(), 'day') }}" class="agenda-tool {{ $viewMode === 'day' ? 'is-selected' : '' }}" {{ $viewMode === 'day' ? 'aria-current=page' : '' }}>Día</a>
            <a href="{{ $agendaLink($selectedDate->toDateString(), 'week') }}" class="agenda-tool {{ $viewMode === 'week' ? 'is-selected' : '' }}" {{ $viewMode === 'week' ? 'aria-current=page' : '' }}>Semana</a>
            <a href="{{ $agendaLink($selectedDate->toDateString(), 'month') }}" class="agenda-tool {{ $viewMode === 'month' ? 'is-selected' : '' }}" {{ $viewMode === 'month' ? 'aria-current=page' : '' }}>Mes</a>
        </div>
    </div>

    <form class="agenda-filters" method="GET" action="{{ route('agenda.index') }}">
        <input type="hidden" name="view" value="{{ $viewMode }}">
        <label for="agenda-date" class="small fw-semibold text-secondary">Ir a la fecha</label>
        <input type="date" id="agenda-date" name="date" value="{{ $selectedDate->toDateString() }}" class="agenda-field">
        <label for="agenda-type" class="small fw-semibold text-secondary">Mostrar</label>
        <select id="agenda-type" class="agenda-field" name="type">
            @foreach($typeLabels as $key => $label)
                <option value="{{ $key }}" @selected($type === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="agenda-tool is-selected"><i class="fa fa-filter" aria-hidden="true"></i> Aplicar</button>
        <a class="agenda-tool" href="{{ $agendaLink($today) }}"><i class="fa fa-calendar-day" aria-hidden="true"></i> Hoy</a>
    </form>

    <div class="agenda-stats">
        <div class="agenda-stat"><span class="agenda-stat-label">Reservaciones</span><span class="agenda-stat-number">{{ $stats['reservations'] }}</span></div>
        <div class="agenda-stat"><span class="agenda-stat-label">Aulas</span><span class="agenda-stat-number">{{ $stats['classrooms'] }}</span></div>
        <div class="agenda-stat"><span class="agenda-stat-label">Dispositivos</span><span class="agenda-stat-number">{{ $stats['devices'] }}</span></div>
    </div>

    <div class="agenda-legend" aria-label="Leyenda de la agenda">
        <span><i class="agenda-dot upcoming"></i>Programado</span>
        <span><i class="agenda-dot active"></i>En curso</span>
        <span><i class="agenda-dot overdue"></i>Devolución pendiente</span>
        <span><i class="agenda-dot completed"></i>Completado</span>
        <span><i class="fa fa-square text-primary" aria-hidden="true"></i> Aula</span>
        <span><i class="fa fa-square text-info" aria-hidden="true"></i> Dispositivo</span>
    </div>

    @if($truncated)
        <div class="agenda-notice" role="status">
            Hay más de 1000 reservaciones en este período. Se muestra una parte de la agenda; selecciona un día o filtra por tipo para ver el resto.
        </div>
    @endif

    <div class="agenda-period">
        <h5>{{ $periodTitle }}</h5>
        <div class="agenda-controls">
            <a class="agenda-tool" href="{{ $agendaLink($prevDate) }}" aria-label="Período anterior"><i class="fa fa-chevron-left" aria-hidden="true"></i> Anterior</a>
            <a class="agenda-tool" href="{{ $agendaLink($nextDate) }}" aria-label="Período siguiente">Siguiente <i class="fa fa-chevron-right" aria-hidden="true"></i></a>
        </div>
    </div>

    @if($viewMode === 'month')
        <div class="agenda-month-scroller" role="region" aria-label="Calendario mensual; desplázate horizontalmente si es necesario" tabindex="0">
            <div class="agenda-month">
                @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $weekday)
                    <div class="agenda-weekday">{{ $weekday }}</div>
                @endforeach
                @foreach($days as $day)
                    @php
                        $key = $day->toDateString();
                        $dayEvents = $eventsByDay[$key] ?? [];
                        $otherMonth = $day->month !== $selectedDate->month || $day->year !== $selectedDate->year;
                    @endphp
                    <div class="agenda-cell {{ $otherMonth ? 'is-other-month' : '' }} {{ $key === $today ? 'is-today' : '' }}">
                        <a class="agenda-cell-date" href="{{ $agendaLink($key, 'day') }}" aria-label="Consultar {{ $day->format('d/m/Y') }}">{{ $day->day }}</a>
                        <div class="agenda-cell-events">
                            @foreach(array_slice($dayEvents, 0, 3) as $event)
                                <div class="agenda-mini {{ $event['kind'] === 'device' ? 'kind-device' : '' }}" title="{{ $event['title'] }} · {{ $event['time'] }} · {{ $phaseLabels[$event['phase']] }}">
                                    {{ $event['time'] }} {{ $event['title'] }}
                                </div>
                            @endforeach
                            @if(count($dayEvents) > 3)
                                <a class="agenda-more" href="{{ $agendaLink($key, 'day') }}">+ {{ count($dayEvents) - 3 }} más · ver día</a>
                            @elseif(count($dayEvents) > 0)
                                <a class="agenda-more" href="{{ $agendaLink($key, 'day') }}">Ver día</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="agenda-day-list">
            @foreach($days as $day)
                @php
                    $key = $day->toDateString();
                    $dayEvents = $eventsByDay[$key] ?? [];
                @endphp
                <section class="agenda-day" aria-label="Préstamos del {{ $day->format('d/m/Y') }}">
                    <header class="agenda-day-header {{ $key === $today ? 'is-today' : '' }}">
                        <a href="{{ $agendaLink($key, 'day') }}">
                            {{ ucfirst($day->copy()->locale('es')->translatedFormat('D')) }} {{ $day->format('d/m') }}
                            @if($key === $today)<span class="badge bg-primary ms-1">Hoy</span>@endif
                        </a>
                        <span class="agenda-counter">{{ count($dayEvents) }} recurso(s)</span>
                    </header>
                    <div class="agenda-items">
                        @forelse($dayEvents as $event)
                            <article class="agenda-event {{ $event['kind'] === 'device' ? 'kind-device' : '' }}"
                                title="Desde {{ $event['startAt'] }} hasta {{ $event['endAt'] }}">
                                <div class="agenda-item-top">
                                    <span class="agenda-time"><i class="fa fa-clock me-1" aria-hidden="true"></i>{{ $event['time'] }}</span>
                                    <span class="agenda-phase phase-{{ $event['phase'] }}">{{ $phaseLabels[$event['phase']] }}</span>
                                </div>
                                <div class="agenda-title">
                                    <i class="fa {{ $event['kind'] === 'classroom' ? 'fa-door-open' : 'fa-laptop' }} me-1" aria-hidden="true"></i>
                                    {{ $event['title'] }}
                                </div>
                                <div class="agenda-detail">{{ $event['detail'] }}</div>
                                @if($viewMode === 'day')
                                    <div class="agenda-detail mt-1">Préstamo: {{ $event['startAt'] }} → {{ $event['endAt'] }}</div>
                                @endif
                            </article>
                        @empty
                            <div class="agenda-empty"><i class="fa fa-calendar-check me-1" aria-hidden="true"></i>Sin préstamos registrados para este día</div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    <p class="small text-muted mt-3 mb-0">
        La agenda incluye reservaciones <strong>aprobadas y completadas</strong> que coinciden con el período seleccionado.
        Las solicitudes pendientes o rechazadas no aparecen. Los horarios indican el intervalo reservado, no una confirmación física de entrega.
    </p>
</div>
@endsection
