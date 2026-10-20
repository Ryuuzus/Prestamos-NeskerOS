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
        // Verifica rol vigente (role es la autoridad, is_admin es legado).
        if (Auth::check() && Auth::user()->hasRole(\App\Models\User::ROLE_ADMIN)) {
            return $next($request);
        }

        // Si no es admin, lo regresamos a sus reservaciones con un error
        abort(403, 'Acceso denegado. No tienes permisos de administrador.');
    }
}