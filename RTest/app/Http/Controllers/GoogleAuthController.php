<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Controlador de Autenticación con Google (GoogleAuthController).
 * 
 * Gestiona la autenticación mediante Google OAuth 2.0.
 * Maneja la vinculación segura con cuentas locales existentes por correo
 * para evitar colisiones de clave única en la base de datos.
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
     * Procesa la respuesta de Google. Vincula o crea la cuenta del usuario,
     * verifica su correo electrónico e inicia sesión.
     */
    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        // Busca al usuario primero por id de google, o por su dirección de correo si ya existía
        $user = User::where('google_id', $googleUser->id)
                    ->orWhere('email', $googleUser->email)
                    ->first();

        if ($user) {
            $user->update([
                'google_id'            => $googleUser->id,
                'google_token'         => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);
        } else {
            $user = User::create([
                'name'                 => $googleUser->name,
                'email'                => $googleUser->email,
                'google_id'            => $googleUser->id,
                'google_token'         => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);
        }

        // Autentica al usuario en la sesión activa
        Auth::login($user);

        // Verifica el correo si aún no lo estaba
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('reservations.index');
    }
}