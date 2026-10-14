<<<<<<< HEAD
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Editar Reservación #{{ $reservation->id }}</h4>
        </div>
        <div class="card-body">

            <!-- Mostrar errores de validación si existen -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
=======
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
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

<<<<<<< HEAD
            <!-- Formulario apuntando a update -->
=======
            {{-- Formulario para actualizar la reservación --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
            <form method="POST" action="{{ route('reservations.update', $reservation->id) }}">
                @csrf
                @method('PUT')
                
<<<<<<< HEAD
                <!-- Aquí inyectamos los mismos campos, pero ya vendrán llenos -->
                @include('reservation.form')

=======
                {{-- Inclusión del formulario de campos reutilizable --}}
                @include('reservation.form')

                {{-- Botones de acción del formulario --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                <div class="mt-4" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit">Guardar Reservación</button>
                    <a href="{{ route('reservations.index') }}" class="btn-cancel">Cancelar</a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection