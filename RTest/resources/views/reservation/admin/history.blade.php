{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Historial de reservaciones (Admin Reservations History View).
    Muestra SOLO las reservaciones ya finalizadas: completadas (entregadas/devueltas) y rechazadas.
    Es de solo lectura. Se puede filtrar por estado y buscar por usuario, aula o dispositivo.
    Para las reservaciones que requieren acción (pendientes y aprobadas) ver
    reservation/admin/index.blade.php.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Contenido principal de la vista --}}
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            <div class="table-wrapper">

                {{-- Encabezado: título, filtros y regreso a las reservaciones activas --}}
                <div class="table-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h4 class="m-0">Historial de reservaciones</h4>

                    <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-md-end flex-wrap">

                        <form action="{{ route('reservations.history') }}" method="GET" class="m-0 d-flex gap-2" style="max-width: 520px; width: 100%;">
                            <select name="status" class="form-select custom-input" style="max-width: 170px;" onchange="this.form.submit()">
                                <option value="">Todas</option>
                                <option value="completed" {{ $filter === 'completed' ? 'selected' : '' }}>Completadas</option>
                                <option value="rejected" {{ $filter === 'rejected' ? 'selected' : '' }}>Rechazadas</option>
                            </select>

                            <div class="input-group">
                                <input type="text" name="search" class="form-control custom-input"
                                       placeholder="Usuario, aula o dispositivo..." value="{{ request('search') }}"
                                       style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <button type="submit" class="btn-submit"
                                        style="border-top-left-radius: 0; border-bottom-left-radius: 0; margin: 0; padding: 8px 16px;">
                                    Buscar
                                </button>
                            </div>
                        </form>

                        @if(request('search') || $filter)
                            <a href="{{ route('reservations.history') }}" class="btn btn-light"
                               style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 14px;" title="Limpiar filtros">
                                <i class="fa fa-times text-danger"></i>
                            </a>
                        @endif

                        <a href="{{ route('admin.reservations.index') }}" class="btn-create text-nowrap">
                            Reservaciones activas
                        </a>
                    </div>
                </div>

                {{-- Tabla del historial (solo lectura) --}}
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th class="building-id-column">No</th>
                                <th>Usuario</th>
                                <th>Aula</th>
                                <th>Dispositivo</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th class="building-status-column">Estado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reservations as $reservation)
                                <tr>
                                    <td class="building-id-column"><span class="text-muted fw-bold">{{ $reservation->id }}</span></td>

                                    <td class="building-name-cell fw-semibold">{{ $reservation->user->name ?? 'N/A' }}</td>

                                    <td class="building-name-cell">
                                        @if($reservation->classroom)
                                            {{ $reservation->classroom->classroom }}
                                            <small class="text-muted d-block">{{ $reservation->classroom->building->name ?? 'Sin edificio' }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td class="building-name-cell">
                                        @if($reservation->device)
                                            {{ $reservation->device->name }}
                                            <small class="text-muted d-block">{{ $reservation->device->serial_number }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td>{{ $reservation->start_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $reservation->end_at?->format('d/m/Y H:i') }}</td>

                                    <td class="building-status-column">
                                        <span @class([
                                            'badge',
                                            'bg-info text-dark' => $reservation->status === 'completed',
                                            'bg-danger'         => $reservation->status === 'rejected',
                                        ])>
                                            {{ $reservation->status === 'completed' ? 'COMPLETADA' : 'RECHAZADA' }}
                                        </span>
                                    </td>

                                    {{-- Motivo del rechazo, o fecha de devolución (y si fue con retraso) --}}
                                    <td class="building-name-cell">
                                        @if($reservation->status === 'rejected')
                                            <span class="text-danger small d-block">
                                                <b>Motivo:</b> {{ $reservation->rejection_reason ?? 'No especificado' }}
                                            </span>
                                        @elseif($reservation->actual_return_time)
                                            <span class="small d-block">
                                                Devuelto el {{ $reservation->actual_return_time->format('d/m/Y H:i') }}
                                            </span>

                                            @if($reservation->end_at && $reservation->actual_return_time->gt($reservation->end_at))
                                                <span class="small d-block text-danger fw-semibold">Con retraso</span>
                                            @endif
                                        @else
                                            <span class="small text-muted">Sin fecha de devolución registrada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No hay reservaciones en el historial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación conservando filtros y búsqueda --}}
            <div class="d-flex justify-content-end mt-4">
                {!! $reservations->links() !!}
            </div>

        </div>
    </div>
</div>
@endsection
