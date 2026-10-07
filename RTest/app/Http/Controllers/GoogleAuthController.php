<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Autenticación con Google (GoogleAuthController).
 * 
 * Gestiona el flujo de autenticación OAuth 2.0 mediante Google Socialite.
 * Incluye la redirección al proveedor, la recepción del callback con la
 * creación/actualización de usuarios, verificación implícita de correo
 * e inicio de sesión en la plataforma.
 * ==================================================================================================
 */
class GoogleAuthController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación segura de Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Procesa la respuesta (callback) enviada por Google tras la autenticación.
     * Crea o actualiza el registro del usuario, verifica su correo e inicia la sesión.
     */
    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();
    
        // Busca al usuario por su ID de Google o registra/actualiza sus datos
        $user = User::updateOrCreate([
            'google_id' => $googleUser->id,
        ], [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_token' => $googleUser->token,
            'google_refresh_token' => $googleUser->refreshToken,
        ]);

        // Autentica al usuario en el sistema
        Auth::login($user);

        // Marca la dirección de correo como verificada si aún no lo está
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
    
        // Redirección del usuario a la vista correspondiente
        if ($user->is_admin == 1) {
            return redirect()->route('reservations.index');
        }

        return redirect()->route('reservations.index');
    }
}