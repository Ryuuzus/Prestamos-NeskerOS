@extends('layouts.app')
@section('content')
<div class="app-content app-content-narrow reservation-page">
    <div class="reservation-navigation">
        <a href="{{ route('reservations.index') }}" class="reservation-text-link"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Mis reservaciones</a>
        @include('reservation.guide-link')
    </div>
    <div class="reservation-page-heading"><h1 class="app-page-title">Nueva reservación</h1></div>
    @include('reservation.alerts')
    <form method="POST" action="{{ route('reservations.store') }}" class="reservation-form-panel">
        @csrf
        @include('reservation.form')
        <div class="reservation-form-actions">
            <button type="submit" class="btn-submit">Enviar solicitud <i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i></button>
            <a href="{{ route('reservations.index') }}" class="btn-cancel">Cancelar</a>
        </div>
    </form>
</div>
@endsection
