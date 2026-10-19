{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Dashboard de administración de reservaciones (Admin Reservations Index View).
    Muestra SOLO las reservaciones que requieren atención: pendientes (por aprobar o rechazar) y
    aprobadas (por entregar/devolver). El historial (completadas y rechazadas) vive en
    reservation/admin/history.blade.php, al que se llega con el botón "Historial".

    Acciones:
      - Pendiente: Approve (PATCH reservations.approve) o Reject con motivo (PATCH reservations.reject).
      - Aprobada : Complete (PATCH reservations.complete) al entregar/devolver el recurso.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Contenido principal de la vista --}}
@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            {{-- Notificaciones de la sesión --}}
            @if(session('success'))
                <div class="custom-alert shadow-sm">
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="custom-alert custom-alert-error shadow-sm">
                    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="validation-summary" role="alert">
                    <strong>No se pudo completar la acción:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="table-wrapper">

                {{-- Encabezado: título con contadores, buscador y acceso al historial --}}
                <div class="table-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="m-0">Reservaciones activas</h4>
                        <div class="mt-2">
                            <span class="badge bg-warning text-dark">{{ $pendingCount }} pendiente(s)</span>
                            <span class="badge bg-success">{{ $approvedCount }} aprobada(s)</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-md-end">

                        <form action="{{ route('admin.reservations.index') }}" method="GET" class="m-0 d-flex" style="max-width: 300px; width: 100%;">
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

                        @if(request('search'))
                            <a href="{{ route('admin.reservations.index') }}" class="btn btn-light"
                               style="border-radius: 8px; border: 1px solid #e2e8f0; padding: 8px 14px;" title="Limpiar búsqueda">
                                <i class="fa fa-times text-danger"></i>
                            </a>
                        @endif

                        <a href="{{ route('reservations.history') }}" class="btn-cancel text-nowrap">
                            Historial ({{ $historyCount }})
                        </a>
                    </div>
                </div>

                {{-- Tabla de reservaciones activas --}}
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
                                <th class="building-actions-column">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reservations as $reservation)
                                @php
                                    // Momento de la reservación aprobada: próxima, en curso o con devolución atrasada.
                                    $phase = null;

                                    if ($reservation->status === 'approved' && $reservation->start_at && $reservation->end_at) {
                                        $now = now();

                                        $phase = $now->lt($reservation->start_at)
                                            ? ['Próxima', '#64748b']
                                            : ($now->lte($reservation->end_at)
                                                ? ['En curso', '#16a34a']
                                                : ['Devolución atrasada', '#dc2626']);
                                    }
                                @endphp
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
                                            'bg-warning text-dark' => $reservation->status === 'pending',
                                            'bg-success'           => $reservation->status === 'approved',
                                        ])>
                                            {{ $reservation->status === 'pending' ? 'PENDIENTE' : 'APROBADA' }}
                                        </span>

                                        @if($phase)
                                            <small class="d-block fw-semibold mt-1" style="color: {{ $phase[1] }};">{{ $phase[0] }}</small>
                                        @endif
                                    </td>

                                    <td class="building-actions-column">
                                        <div class="action-btns">
                                            @if($reservation->status === 'pending')
                                                {{-- Aprobar --}}
                                                <form action="{{ route('reservations.approve', $reservation->id) }}" method="POST" class="m-0">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-action btn-edit">Approve</button>
                                                </form>

                                                {{-- Rechazar: abre el modal para capturar el motivo --}}
                                                <button type="button" class="btn-action btn-delete"
                                                        data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                        data-action="{{ route('reservations.reject', $reservation->id) }}"
                                                        data-label="#{{ $reservation->id }}">
                                                    Reject
                                                </button>
                                            @else
                                                {{-- Marcar como entregado / devuelto --}}
                                                <form action="{{ route('reservations.complete', $reservation->id) }}" method="POST" class="m-0"
                                                      onsubmit="return confirm('¿Marcar esta reservación como entregada/devuelta?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-action btn-show">Complete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No hay reservaciones pendientes ni aprobadas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación conservando la búsqueda --}}
            <div class="d-flex justify-content-end mt-4">
                {!! $reservations->links() !!}
            </div>

        </div>
    </div>
</div>

{{-- Modal único para rechazar: el JS le asigna la URL de la reservación elegida --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content building-modal-content">

            <div class="modal-header building-modal-header">
                <h5 class="modal-title fw-bold" id="rejectModalLabel">
                    Rechazar reservación <span data-reservation-label></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="modal-body building-modal-body">
                    <label for="rejection_reason" class="custom-label">Motivo del rechazo</label>
                    <textarea name="rejection_reason" id="rejection_reason" class="form-control custom-input"
                              rows="3" maxlength="255" required
                              placeholder="Explica por qué se rechaza la solicitud"></textarea>
                </div>

                <div class="modal-footer building-modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-submit">Reject</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('rejectModal');

    // Al abrir el modal, apunta el formulario a la reservación del botón que lo abrió.
    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        modal.querySelector('form').action = button.getAttribute('data-action');
        modal.querySelector('[data-reservation-label]').textContent = button.getAttribute('data-label');
        modal.querySelector('textarea').value = '';
    });
});
</script>
@endsection
