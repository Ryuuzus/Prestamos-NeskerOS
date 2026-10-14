<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica si está logueado y si su columna is_admin es igual a 1
        if (Auth::check() && Auth::user()->is_admin == 1) {
            return $next($request);
        }

        // Si no es admin, lo regresamos a sus reservaciones con un error
        return redirect()->route('reservations.index')
            ->with('error', 'Acceso denegado. No tienes permisos de administrador.');
    }
}