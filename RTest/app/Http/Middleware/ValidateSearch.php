<?php

namespace App\Http\Middleware;

use App\Rules\CatalogRules;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSearch
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && $request->query->has('search')) {
            $search = $request->query('search');

            if ($search !== null && (! is_string($search) || mb_strlen($search) > CatalogRules::SEARCH_MAX)) {
                $message = 'La búsqueda debe ser texto de máximo '.CatalogRules::SEARCH_MAX.' caracteres.';
                $request->query->remove('search');

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message, 'errors' => ['search' => [$message]]], 422);
                }

                // Do not flash or reflect the rejected, potentially very large input.
                return redirect($request->url())->withErrors(['search' => $message], 'search');
            }

            $request->query->set('search', trim($search ?? ''));
        }

        return $next($request);
    }
}
