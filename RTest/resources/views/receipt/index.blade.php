{{-- Sección personal de recibos de préstamos. No lista reservaciones ajenas. --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="table-wrapper">
                <div class="table-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="m-0">Mis recibos de préstamo</h4>
                        <p class="text-muted small mb-0 mt-2">Aquí encontrarás comprobantes de tus reservaciones aprobadas o completadas.</p>
                    </div>
                    <a href="{{ route('reservations.index') }}" class="btn-cancel text-nowrap">Mis reservaciones</a>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Aula</th>
                                <th>Dispositivo</th>
                                <th>Inicio</th>
                                <th>Fin programado</th>
                                <th>Estado</th>
                                <th>Recibo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reservations as $reservation)
                                <tr>
                                    <td>{{ $reservation->id }}</td>
                                    <td>
                                        {{ $reservation->classroom?->classroom ?? '—' }}
                                        @if($reservation->classroom?->building)
                                            <small class="d-block text-muted">{{ $reservation->classroom->building->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $reservation->device ? $reservation->device->name . ' × ' . $reservation->device_quantity : '—' }}</td>
                                    <td>{{ $reservation->start_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $reservation->end_at?->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span @class([
                                            'badge',
                                            'bg-success' => $reservation->status === 'approved',
                                            'bg-info text-dark' => $reservation->status === 'completed',
                                        ])>
                                            {{ $reservation->status === 'approved' ? 'APROBADA' : 'COMPLETADA' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a class="btn-action btn-show" href="{{ route('pdf.ver', $reservation) }}" target="_blank" rel="noopener">Ver</a>
                                            <a class="btn-action btn-edit" href="{{ route('pdf.exportar', $reservation) }}">Descargar</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Todavía no tienes recibos. Aparecerán aquí cuando se apruebe una reservación.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-end mt-4">{{ $reservations->links() }}</div>
        </div>
    </div>
</div>
@endsection
