<<<<<<< HEAD
@extends('layouts.app')

=======
{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista basada en Bootstrap para la solicitud de restablecimiento de contraseña (Email Reset Link).
    Permite al usuario ingresar su correo electrónico para recibir un enlace seguro de recuperación
    procesado por la ruta 'password.email'.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Contenido principal de la vista basada en Bootstrap --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
<<<<<<< HEAD
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
=======
                
                {{-- Encabezado de la tarjeta --}}
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                    {{-- Alerta de estado tras enviar exitosamente el correo --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

<<<<<<< HEAD
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

=======
                    {{-- Formulario de envío de enlace de restablecimiento --}}
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        {{-- Campo: Correo electrónico --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

<<<<<<< HEAD
=======
                        {{-- Botón para procesar el envío del correo --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
<<<<<<< HEAD
=======

>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
            </div>
        </div>
    </div>
</div>
<<<<<<< HEAD
@endsection
=======
@endsection
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
