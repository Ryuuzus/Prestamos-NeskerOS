{{-- Mensajes globales de autenticación: éxito, información, errores de proceso y validación. --}}
@php
    $authStatus = session('status');
    $authStatusMessages = [
        'verification-link-sent' => 'Se envió un nuevo enlace de verificación a tu correo electrónico.',
        'passwords.sent' => 'Si existe una cuenta asociada a ese correo, recibirás un enlace para restablecer la contraseña.',
        'passwords.reset' => 'Tu contraseña se actualizó correctamente. Ya puedes iniciar sesión.',
        'passwords.throttled' => 'Has realizado demasiados intentos. Espera un momento antes de volver a intentarlo.',
    ];

    $authStatusText = $authStatus
        ? ($authStatusMessages[$authStatus] ?? __($authStatus))
        : null;
@endphp

@if ($authStatusText)
    <div role="status" aria-live="polite" style="padding:12px 15px; margin:0 0 16px; border:1px solid #bbf7d0; border-radius:8px; background:#f0fdf4; color:#166534; font-size:14px; line-height:1.5;">
        <strong>Listo:</strong> {{ $authStatusText }}
    </div>
@endif

@if (session('success'))
    <div role="status" aria-live="polite" style="padding:12px 15px; margin:0 0 16px; border:1px solid #bbf7d0; border-radius:8px; background:#f0fdf4; color:#166534; font-size:14px; line-height:1.5;">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div role="alert" aria-live="assertive" style="padding:12px 15px; margin:0 0 16px; border:1px solid #fecaca; border-radius:8px; background:#fef2f2; color:#991b1b; font-size:14px; line-height:1.5;">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" aria-live="assertive" style="padding:12px 15px; margin:0 0 16px; border:1px solid #fecaca; border-radius:8px; background:#fef2f2; color:#991b1b; font-size:14px; line-height:1.5;">
        <strong>No se pudo completar la solicitud.</strong>
        <p style="margin:5px 0 0;">Revisa la siguiente información y vuelve a intentarlo:</p>
        <ul style="margin:6px 0 0; padding-left:20px;">
            @foreach ($errors->all() as $authError)
                <li>{{ $authError }}</li>
            @endforeach
        </ul>
    </div>
@endif
