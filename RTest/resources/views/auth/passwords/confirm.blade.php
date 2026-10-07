{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista de confirmación de contraseña (Confirm Password View).
    Solicita al usuario reingresar su contraseña para validar su identidad antes de acceder
    a zonas protegidas o realizar acciones sensibles, enviando la petición a 'password.confirm'.
--================================================================================================== --}}

@extends('layouts.app')

{{-- Contenido principal de la vista basada en Bootstrap --}}
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                
                {{-- Encabezado de la tarjeta --}}
                <div class="card-header">{{ __('Confirm Password') }}</div>

                <div class="card-body">
                    {{-- Indicación para el usuario --}}
                    {{ __('Please confirm your password before continuing.') }}

                    {{-- Formulario para la confirmación de contraseña --}}
                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        {{-- Campo: Contraseña actual --}}
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        {{-- Botón de envío y enlace auxiliar de contraseña olvidada --}}
                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Confirm Password') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection