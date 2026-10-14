<?php

namespace App\Http\Controllers;

use App\Models\User;
<<<<<<< HEAD
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

/**
 * Controlador de Autenticación con Google (GoogleAuthController)
 * 
 * Gestiona el flujo de autenticación OAuth 2.0 mediante Google Socialite.
 * Incluye la redirección al proveedor, la recepción del callback con la
 * creación/actualización de usuarios, verificación implícita de correo
 * y el inicio de sesión del usuario en la plataforma.
=======
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
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
 */
class GoogleAuthController extends Controller
{
    /**
<<<<<<< HEAD
     * Redirige al usuario a la página de autenticación de Google.
     */
    public function redirect(){
=======
     * Redirige al usuario a la página de autenticación segura de Google.
     */
    public function redirect()
    {
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        return Socialite::driver('google')->redirect();
    }

    /**
<<<<<<< HEAD
     * Procesa la respuesta (callback) enviada por Google tras la autenticación.
     * Crea o actualiza el registro del usuario, verifica su correo e inicia la sesión.
     */
    public function callback(){
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
    
        // Redirección del usuario según su rol dentro del sistema
        if ($user->is_admin == 1) {
            return redirect()->route('reservations.index');
        }

        return redirect()->route('reservations.index');
        }
=======
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
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
}