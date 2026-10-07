{{-- ==================================================================================================
    DESCRIPCIÓN GENERAL:
    Vista para la definición de una nueva contraseña (Reset Password Form).
    Recibe el token de seguridad firmado mediante la ruta y permite actualizar la credencial
    de acceso enviando los datos del correo y la nueva contraseña a 'password.update'.
--================================================================================================== --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña</title>
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet"> 
    @vite(['resources/css/auth/reset_password.css'])
</head>
<body>
    {{-- Contenedor del formulario de restablecimiento --}}
    <div class="reset-container">
        <div class="reset-card">
            <h2 class="reset-title">Nueva Contraseña</h2>
            
            {{-- Visualización de errores de validación --}}
            @if ($errors->any())
                <div class="reset-errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulario para actualizar la contraseña con token encriptado --}}
            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" value="{{ $request->route('token') }}" name="token">
                
                <div class="reset-form-group">
                    <label class="reset-label">Correo Electrónico</label>
                    <input type="email" name="email" class="reset-input" value="{{ $request->email }}" required readonly>
                </div>
                <div class="reset-form-group">
                    <label class="reset-label">Nueva Contraseña</label>
                    <input type="password" name="password" class="reset-input" required>
                </div>
                <div class="reset-form-group">
                    <label class="reset-label">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" class="reset-input" required>
                </div>
                <button type="submit" class="reset-btn">Guardar Contraseña</button>
            </form>

        </div>
    </div>
</body>
</html>