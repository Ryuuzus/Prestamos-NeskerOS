@extends('layouts.app')

@section('content')
<div class="container mt-4">
    
    <!-- Alertas -->
    @if(session('success'))
        <div class="custom-alert">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="custom-alert" style="background-color: #fee2e2; color: #dc2626; border-color: #fecaca;">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-wrapper">
        
        <!-- Encabezado -->
        <div class="table-header">
            <h4>Listado de Reservaciones</h4>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                
                <!-- Buscador -->
                <form action="{{ route('reservations.index') }}" method="GET" style="display: flex; margin: 0;">
                    <input type="text" name="search" class="custom-input" placeholder="Search..." value="{{ request('search') }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none; padding-top: 8px; padding-bottom: 8px; max-width: 250px;">
                    <button type="submit" class="btn-submit" style="border-top-left-radius: 0; border-bottom-left-radius: 0; padding: 8px 16px;">
                        Buscar
                    </button>
                </form>

                <!-- Botón Crear -->
                <a href="{{ route('reservations.create') }}" class="btn-create" style="padding: 8px 24px;">
                    Create
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Usuario</th>
                        <th>Aula</th>
                        <th>Dispositivo</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                        <tr>
                            <td>{{ $reservation->id }}</td>
                            <td>{{ $reservation->user->name ?? 'N/A' }}</td>
                            <td>{{ $reservation->classroom ? $reservation->classroom->classroom : 'Ninguna' }}</td>
                            <td>{{ $reservation->device ? $reservation->device->device_name : 'Ninguno' }}</td>
                            <td>{{ $reservation->start_time }}</td>
                            <td>{{ $reservation->end_time }}</td>
                            <td>
                                <!-- Badges de colores para el estado -->
                                <span class="badge 
                                    {{ $reservation->status == 'pending' ? 'bg-warning text-dark' : '' }}
                                    {{ $reservation->status == 'approved' ? 'bg-success' : '' }}
                                    {{ $reservation->status == 'rejected' ? 'bg-danger' : '' }}
                                    {{ $reservation->status == 'completed' ? 'bg-info text-dark' : '' }}">
                                    {{ strtoupper($reservation->status) }}
                                </span>
                            </td>
                            <td>
                                <!-- Botones de acción -->
                                <div class="action-btns">
                                    @if($reservation->status === 'pending')
                                        <a href="{{ route('reservations.edit', $reservation->id) }}" class="btn-action btn-edit">Edit</a>
                                        
                                        <form action="{{ route('reservations.destroy', $reservation->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas cancelar esta solicitud?');" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete">Delete</button>
                                        </form>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.85rem; font-weight: 500;">
                                            @if($reservation->status === 'rejected')
                                                <span style="color: #dc2626; display: block; max-width: 200px; white-space: normal;">
                                                    <b>Motivo:</b> {{ $reservation->rejection_reason ?? 'No especificado' }}
                                                </span>
                                            @elseif($reservation->status === 'completed')
                                                Entregado / Completado
                                            @else
                                                Sin acciones
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color: #64748b;">
                                No se encontraron reservaciones.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-center mt-3">
        {!! $reservations->links() !!}
    </div>
</div>
@endsection