{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista de edición para reservaciones existentes (Edit Reservation View).
    Carga los datos actuales del modelo mediante la inclusión de `reservation.form` y permite
    actualizar la solicitud enviando la petición vía PUT/PATCH hacia 'reservations.update'.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Contenido principal de la vista --}}
@section('content')
<div class="container mt-4">
    <div class="card">
        
        {{-- Encabezado de la tarjeta con identificador de la reservación --}}
        <div class="card-header">
            <h4>Editar Reservación #{{ $reservation->id }}</h4>
        </div>
        
        <div class="card-body">

            {{-- Despliegue de errores de validación --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulario para actualizar la reservación --}}
            <form method="POST" action="{{ route('reservations.update', $reservation->id) }}">
                @csrf
                @method('PUT')
                
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