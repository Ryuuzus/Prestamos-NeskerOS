{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista para el registro de una nueva reservación (Create Reservation View).
    Despliega el contenedor de formulario con manejo de errores de validación e inyecta la plantilla
    reutilizable `reservation.form` para procesar la creación vía POST hacia 'reservations.store'.
--================================================================================================== --}}

@extends('layouts.app')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- Contenido principal de la vista --}}
@section('content')
<div class="container mt-4">
    <div class="card">
        
        {{-- Encabezado de la tarjeta --}}
        <div class="card-header">
            <h4>Crear reservación</h4>
        </div>
        
        <div class="card-body">
            
            {{-- Despliegue de errores de validación --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulario para la creación de reservación --}}
            <form method="POST" action="{{ route('reservations.store') }}">
                @csrf
                
                {{-- Inclusión del formulario de campos reutilizable --}}
                @include('reservation.form')

                {{-- Botones de acción del formulario --}}
                <div class="mt-4" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit">Guardar Reservación</button>
                    <a href="{{ route('reservations.index') }}" class="btn-cancel">Cancelar</a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection