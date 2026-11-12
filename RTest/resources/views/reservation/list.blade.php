@php
    $isOwn = $listMode === 'own';
    $isHistory = $listMode === 'history';
    $listRoute = $isOwn ? 'reservations.index' : ($isHistory ? 'reservations.history' : 'admin.reservations.index');
    $listTitle = $isOwn ? 'Mis reservaciones' : ($isHistory ? 'Historial de reservaciones' : 'Reservaciones activas');
@endphp
<div class="app-content reservation-page">
    <div class="reservation-page-heading">
        <div>
            <h1 class="app-page-title">{{ $listTitle }}</h1>
            @if(! $isOwn && ! $isHistory)
                <div class="reservation-counts">
                    <span><b>{{ $pendingCount }}</b> {{ $pendingCount === 1 ? 'pendiente' : 'pendientes' }}</span>
                    <span><b>{{ $approvedCount }}</b> {{ $approvedCount === 1 ? 'aprobada' : 'aprobadas' }}</span>
                </div>
            @endif
        </div>
        @include('reservation.guide-link')
    </div>
    @include('reservation.alerts')

    <div class="table-wrapper">
        <div class="reservation-toolbar">
            <form action="{{ route($listRoute) }}" method="GET" class="reservation-toolbar-search">
                @if($isHistory)
                    <label class="visually-hidden" for="reservation-status-filter">Filtrar por estado</label>
                    <select id="reservation-status-filter" name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        @foreach(['completed' => 'Completadas', 'rejected' => 'Rechazadas', 'cancelled' => 'Canceladas', 'expired' => 'Vencidas', 'no_show' => 'Sin recoger'] as $value => $label)
                            <option value="{{ $value }}" @selected($filter === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
                <div class="input-group">
                    <label class="visually-hidden" for="reservation-search">Buscar reservaciones</label>
                    <input type="search" id="reservation-search" name="search" class="form-control"
                           maxlength="{{ \App\Rules\CatalogRules::SEARCH_MAX }}" title="Máximo 100 caracteres"
                           placeholder="{{ $isOwn ? 'Buscar aula o equipo…' : 'Buscar persona, aula o equipo…' }}" value="{{ request('search') }}">
                    <button class="btn-submit" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
                </div>
                @if(request()->filled('search') || ($isHistory && $filter))
                    <a href="{{ route($listRoute) }}" class="reservation-text-link">Limpiar</a>
                @endif
            </form>
            @if($isOwn)
                <a href="{{ route('reservations.create') }}" class="btn-create"><i class="fa-solid fa-plus" aria-hidden="true"></i>Nueva reservación</a>
            @elseif($isHistory)
                <a href="{{ route('admin.reservations.index') }}" class="btn-cancel">Ver activas <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i></a>
            @else
                <a href="{{ route('reservations.history') }}" class="btn-cancel"><i class="fa-solid fa-clock-rotate-left me-2" aria-hidden="true"></i>Historial <span class="reservation-count">{{ $historyCount }}</span></a>
            @endif
        </div>

        <div class="table-responsive reservation-table-wrap">
            <table class="custom-table reservation-table" role="table">
                <thead>
                    <tr role="row">
                        <th scope="col" class="reservation-col-id">Folio</th>
                        @unless($isOwn)<th scope="col">Solicitante</th>@endunless
                        <th scope="col">Recursos</th>
                        <th scope="col">Horario</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="reservation-col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $reservation)
                        <tr role="row">
                            <td class="reservation-cell-id" role="cell"><span class="reservation-folio">#{{ $reservation->id }}</span></td>
                            @unless($isOwn)<td class="reservation-cell-owner" role="cell"><span class="reservation-person">{{ $reservation->user?->name ?? 'Cuenta eliminada' }}</span></td>@endunless
                            <td class="reservation-cell-resources" role="cell">
                                @if($reservation->classroom)
                                    <div class="reservation-resource-line"><i class="fa-solid fa-door-open" aria-hidden="true"></i><span>{{ $reservation->classroom->classroom }}</span></div>
                                @endif
                                @if($reservation->device)
                                    <div class="reservation-resource-line"><i class="fa-solid fa-laptop" aria-hidden="true"></i><span>{{ $reservation->device->name }} <b class="reservation-quantity">× {{ $reservation->device_quantity }}</b></span></div>
                                @endif
                                @if(! $reservation->classroom && ! $reservation->device)<span class="text-muted">Recurso no disponible</span>@endif
                            </td>
                            <td class="reservation-cell-schedule" role="cell">
                                <div class="reservation-date"><i class="fa-regular fa-calendar" aria-hidden="true"></i><time datetime="{{ $reservation->start_at?->toIso8601String() }}">{{ $reservation->start_at?->format('d/m/Y · H:i') }}</time></div>
                                <small class="reservation-until">hasta {{ $reservation->end_at?->format('d/m/Y · H:i') }}</small>
                            </td>
                            <td class="reservation-cell-status" role="cell">
                                @include('reservation.status')
                                @include('reservation.custody-summary')
                            </td>
                            <td class="reservation-cell-actions" role="cell">
                                <div class="action-btns">
                                    @if($isOwn)
                                        @if($reservation->status === 'pending')
                                            <a href="{{ route('reservations.edit', $reservation) }}" class="btn-action btn-edit"><i class="fa-solid fa-pen" aria-hidden="true"></i>Editar</a>
                                        @endif
                                        @if($reservation->canCancel())
                                            <details class="reservation-cancel">
                                                <summary class="reservation-text-link">Cancelar</summary>
                                                <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" class="reservation-inline-form">
                                                    @csrf @method('DELETE')
                                                    <label for="cancel-{{ $reservation->id }}" class="form-label">Motivo de cancelación</label>
                                                    <textarea id="cancel-{{ $reservation->id }}" name="reason" maxlength="500" rows="2" class="form-control mb-2" required></textarea>
                                                    <button class="btn-action btn-delete">Confirmar cancelación</button>
                                                </form>
                                            </details>
                                        @endif
                                        @if(! $reservation->canCancel())<span class="text-muted" aria-label="Sin acciones disponibles">—</span>@endif
                                    @elseif($isHistory)
                                        <a href="{{ route('reservations.operations', $reservation) }}" class="btn-action btn-show">Ver movimientos <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                                    @elseif($reservation->status === 'pending')
                                        <form action="{{ route('reservations.approve', $reservation) }}" method="POST" class="m-0">
                                            @csrf @method('PATCH')
                                            <button class="btn-action btn-edit"><i class="fa-solid fa-check" aria-hidden="true"></i>Aprobar</button>
                                        </form>
                                        <button type="button" class="btn-action reservation-reject" data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                data-action="{{ route('reservations.reject', $reservation) }}" data-label="#{{ $reservation->id }}">Rechazar</button>
                                    @else
                                        <a href="{{ route('reservations.operations', $reservation) }}" class="btn-action btn-show">Gestionar préstamo <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isOwn ? 5 : 6 }}" class="reservation-empty">
                            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                            <p>{{ request()->filled('search') ? 'No hay coincidencias con tu búsqueda.' : ($isHistory ? 'Todavía no hay reservaciones en el historial.' : 'No hay reservaciones para mostrar.') }}</p>
                            @if($isOwn)<a href="{{ route('reservations.create') }}" class="reservation-text-link">Crear una reservación</a>@endif
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3">{{ $reservations->links() }}</div>
</div>
@if(! $isOwn && ! $isHistory)
    @include('reservation.admin.reject-modal')
@endif
