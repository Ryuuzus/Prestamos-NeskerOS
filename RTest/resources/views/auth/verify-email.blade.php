<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Correo</title>
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet"> 
    @vite(['resources/css/auth/verify_email.css'])
</head>
<body>
    <div class="verify-container">
        <div class="verify-card">
            
            <!-- Icono de correo opcional para darle mejor vista -->
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 15px;">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>

            <h2 class="verify-title">Verifica tu correo</h2>
            
            <p class="verify-text">
                ¡Gracias por registrarte! Antes de comenzar, por favor verifica tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar.
            </p>

            <!-- Alerta de éxito cuando se reenvía el correo -->
            @if (session('status') == 'verification-link-sent')
                <div class="verify-alert">
                    Se ha enviado un nuevo enlace de verificación a la dirección de correo que proporcionaste durante el registro.
                </div>
            @endif

            <!-- Formulario para reenviar el correo de verificación -->
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify-btn">Reenviar correo de verificación</button>
            </form>

            <!-- Botón para cerrar sesión por si el usuario ingresó un correo incorrecto y quiere salir -->
            <div class="verify-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="verify-logout-btn">Cerrar Sesión</button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>