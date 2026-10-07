{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista del formulario de registro de nuevos usuarios (User Registration View).
    Maneja la captura de datos (Nombre, Email, Contraseña y Confirmación), desplegando una
    lista de errores de validación si la petición falla al enviar a la ruta 'register'.
--================================================================================================== --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet"> 
    @vite(['resources/css/auth/register.css'])
</head>
<body>
    {{-- Contenedor principal de registro --}}
    <div class="register-container">
        <div class="register-card">
            <h2 class="register-title">Crear Cuenta</h2>
            
            {{-- Despliegue de errores de validación del formulario --}}
            @if ($errors->any())
                <div class="register-errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulario para crear una nueva cuenta --}}
            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="register-form-group">
                    <label class="register-label">Nombre Completo</label>
                    <input type="text" name="name" class="register-input" value="{{ old('name') }}" required>
                </div>
                <div class="register-form-group">
                    <label class="register-label">Correo Electrónico</label>
                    <input type="email" name="email" class="register-input" value="{{ old('email') }}" required>
                </div>
                <div class="register-form-group">
                    <label class="register-label">Contraseña</label>
                    <input type="password" name="password" class="register-input" required>
                </div>
                <div class="register-form-group">
                    <label class="register-label">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="register-input" required>
                </div>
                <button type="submit" class="register-btn">Registrarse</button>
            </form>

            {{-- Enlace para usuarios ya registrados --}}
            <div class="register-links">
                <a href="{{ route('login') }}">¿Ya tienes cuenta? Inicia sesión</a>
            </div>

        </div>
    </div>
</body>
</html>