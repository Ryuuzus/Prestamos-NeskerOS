<<<<<<< HEAD
@extends('layouts.app')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Crear reservación</h4>
        </div>
        <div class="card-body">
            
            <!-- Mostrar errores de validación si existen -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
=======
{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista para el registro de una nueva reservación (Create Reservation View).
    Despliega el contenedor de formulario con manejo de errores de validación e inyecta la plantilla
    reutilizable `reservation.form` para procesar la creación vía POST hacia 'reservations.store'.
--================================================================================================== --}}

@extends('layouts.app')

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
                    <ul class="mb-0">
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

<<<<<<< HEAD
            <!-- Formulario apuntando a store -->
            <form method="POST" action="{{ route('reservations.store') }}">
                @csrf
                
                <!-- Aquí inyectamos los campos -->
                @include('reservation.form')

=======
            {{-- Formulario para la creación de reservación --}}
            <form method="POST" action="{{ route('reservations.store') }}">
                @csrf
                
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