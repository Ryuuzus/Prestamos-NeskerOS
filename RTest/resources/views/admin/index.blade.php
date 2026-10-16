{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista principal del Panel de Administrador para la gestión de Solicitudes/Reservas (Reservations Index).
    Muestra el listado de reservas en una tabla interactiva con soporte para búsqueda por ID y paginación.
    Permite a los administradores gestionar el flujo de estados (aprobar, rechazar con motivo mediante JS 
    o finalizar solicitudes) y despliega alertas del sistema según el estado de las operaciones.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Contenido principal de la vista --}}
@section('content')
<div class="container mt-4">
    
    {{-- Notificaciones flash de éxito y error --}}
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
        
        {{-- Encabezado con título de panel y formulario de búsqueda rápida por ID --}}
        <div class="table-header">
            <h4>Panel de Administrador: Solicitudes</h4>
            
            <form action="{{ route('reservations.index') }}" method="GET" style="display: flex; margin: 0;">
                <input type="text" name="search" class="custom-input" placeholder="Buscar por ID..." value="{{ request('search') }}" 
                       style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: none; padding-top: 8px; padding-bottom: 8px; max-width: 250px;">
                <button type="submit" class="btn-submit" 
                        style="border-top-left-radius: 0; border-bottom-left-radius: 0; padding: 8px 16px;">
                    Buscar
                </button>
            </form>
        </div>

        {{-- Tabla de listado de solicitudes de reserva --}}
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>USUARIO</th>
                        <th>AULA / DISPOSITIVO</th>
                        <th>INICIO</th>
                        <th>FIN</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reservations as $reservation)
                        <tr>
                            <td>{{ $reservation->id }}</td>
                            <td>{{ $reservation->user->name ?? 'Desconocido' }}</td>
                            
                            {{-- Detalle del recurso reservado (Aula, Dispositivo o ambos) --}}
                            <td>
                                @if($reservation->classroom) Aula: {{ $reservation->classroom->classroom }} <br> @endif
                                @if($reservation->device) Disp: {{ $reservation->device->name }} @endif
                            </td>
                            
                            <td>{{ $reservation->start_time }}</td>
                            <td>{{ $reservation->end_time }}</td>
                            
                            {{-- Insignia estilizada según el estado de la reserva --}}
                            <td>
                                <span class="badge 
                                    {{ $reservation->status == 'pending' ? 'bg-warning text-dark' : '' }}
                                    {{ $reservation->status == 'approved' ? 'bg-success' : '' }}
                                    {{ $reservation->status == 'rejected' ? 'bg-danger' : '' }}
                                    {{ $reservation->status == 'completed' ? 'bg-info text-dark' : '' }}">
                                    {{ strtoupper($reservation->status) }}
                                </span>
                            </td>
                            
                            {{-- Acciones contextuales según el estado actual de la reserva --}}
                            <td>
                                <div class="action-btns">
                                    @if($reservation->status === 'pending')
                                        {{-- Botón Aprobar --}}
                                        <form action="{{ route('reservations.approve', $reservation->id) }}" method="POST" style="margin: 0;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn-action btn-edit">Aprobar</button>
                                        </form>
                                        
                                        {{-- Botón Rechazar (Detona la función JS para capturar el motivo) --}}
                                        <form action="{{ route('reservations.reject', $reservation->id) }}" method="POST" style="margin: 0;" onsubmit="return pedirMotivo(this);">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="rejection_reason" class="reason-input">
                                            <button type="submit" class="btn-action btn-delete">Rechazar</button>
                                        </form>
                                    @elseif($reservation->status === 'approved')
                                        {{-- Botón Entregar / Completar devolución --}}
                                        <form action="{{ route('reservations.complete', $reservation->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('¿Confirmar que el equipo/aula ha sido devuelto y finalizar solicitud?');">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn-action btn-show" style="background-color: #0284c7; color:white;">Finalizar</button>
                                        </form>
                                    @else
                                        {{-- Información de historial para reservas cerradas o rechazadas --}}
                                        <span style="color: #94a3b8; font-size: 0.85rem; font-weight: 500;">
                                            @if($reservation->status === 'rejected')
                                                Motivo: {{ $reservation->rejection_reason ?? 'N/A' }}
                                            @else
                                                Completada
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        {{-- Estado vacío cuando no existen registros en la consulta --}}
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #64748b;">
                                No hay solicitudes registradas en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Enlaces de paginación --}}
    <div class="d-flex justify-content-center mt-3">
        {!! $reservations->links() !!}
    </div>
</div>

{{-- Función JS para solicitar obligatoriamente el motivo al rechazar una solicitud --}}
<script>
function pedirMotivo(formulario) {
    let motivo = prompt('Por favor, ingresa el motivo del rechazo (obligatorio):');
    
    if (motivo === null || motivo.trim() === '') {
        alert('Debes ingresar un motivo para poder rechazar la solicitud.');
        return false; // Detiene el envío del formulario
    }
    
    // Inyecta el texto introducido en el campo oculto antes de enviar
    formulario.querySelector('.reason-input').value = motivo;
    return true; 
}
</script>
@endsection