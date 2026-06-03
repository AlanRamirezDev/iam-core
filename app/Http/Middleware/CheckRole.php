<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Verificamos si hay un usuario autenticado por el token JWT
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        // 2. Comparamos la colección de roles del usuario contra los roles exigidos en la ruta
        $hasAccess = $user->roles->whereIn('name', $roles)->isNotEmpty();

        if (!$hasAccess) {
            return response()->json([
                'error' => 'Acceso denegado. Privilegios insuficientes para esta acción.'
            ], 403);
        }

        return $next($request);
    }
}