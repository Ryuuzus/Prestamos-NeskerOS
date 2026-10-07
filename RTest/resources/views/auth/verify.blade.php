{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista secundaria basada en Bootstrap para la verificación de dirección de correo electrónico.
    Solicita al usuario revisar su bandeja de entrada e incluye la opción para solicitar
    un nuevo enlace de verificación a través de la ruta 'verification.resend'.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Contenido principal de la vista basada en Bootstrap --}}
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    {{-- Alerta tras un reenvío exitoso del enlace --}}
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    {{-- Mensaje informativo y formulario dinámico para solicitar otro enlace --}}
                    {{ __('Before proceeding, please check your email for a verification link.') }}
                    {{ __('If you did not receive the email') }},
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection