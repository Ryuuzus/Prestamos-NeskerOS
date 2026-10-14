{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista de solicitud para la recuperación de contraseña (Forgot Password).
    Proporciona la interfaz para que el usuario ingrese su correo electrónico registrado y envíe
    un enlace de restablecimiento a través de la ruta 'password.email'.
--================================================================================================== --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet"> 
    @vite(['resources/css/auth/forgot_password.css'])
</head>
<body>
    {{-- Contenedor principal de la tarjeta de recuperación --}}
    <div class="forgot-container">
        <div class="forgot-card">
            
            {{-- Título y descripción instructiva --}}
            <h2 class="forgot-title">Recuperar Acceso</h2>
            <p class="forgot-subtitle">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
            
            {{-- Formulario para envío del correo de recuperación --}}
            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="forgot-form-group">
                    <label class="forgot-label">Correo Electrónico</label>
                    <input type="email" name="email" class="forgot-input" required>
                </div>
                <button type="submit" class="forgot-btn">Enviar Enlace</button>
            </form>

            {{-- Enlace de retorno al inicio de sesión --}}
            <div class="forgot-links">
                <a href="{{ route('login') }}">Volver al Login</a>
            </div>

        </div>
    </div>
</body>
</html>