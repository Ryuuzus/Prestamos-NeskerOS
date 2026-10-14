<<<<<<< HEAD
@extends('layouts.app')

=======
{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista basada en Bootstrap para establecer una nueva contraseña (Reset Password Form).
    Recibe el token de verificación, correo e inputs para definir y confirmar la nueva credencial
    enviando la información a la ruta 'password.update'.
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
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

=======
                
                {{-- Encabezado de la tarjeta --}}
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                    {{-- Formulario para actualización de contraseña con token --}}
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        {{-- Token de restablecimiento oculto --}}
                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Campo: Correo electrónico --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

<<<<<<< HEAD
=======
                        {{-- Campo: Nueva contraseña --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

<<<<<<< HEAD
=======
                        {{-- Campo: Confirmar nueva contraseña --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

<<<<<<< HEAD
=======
                        {{-- Botón para guardar cambios --}}
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
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
