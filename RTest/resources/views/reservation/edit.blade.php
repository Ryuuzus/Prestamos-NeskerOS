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
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulario apuntando a update -->
            <form method="POST" action="{{ route('reservations.update', $reservation->id) }}">
                @csrf
                @method('PUT')
                
                <!-- Aquí inyectamos los mismos campos, pero ya vendrán llenos -->
                @include('reservation.form')

                <div class="mt-4" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-submit">Guardar Reservación</button>
                    <a href="{{ route('reservations.index') }}" class="btn-cancel">Cancelar</a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection