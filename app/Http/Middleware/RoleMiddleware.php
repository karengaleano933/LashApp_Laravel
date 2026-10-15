<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRoleName = strtolower($user->role?->nombre ?? '');
        $requiredRole = strtolower($role);

        // Si es rol admin, coincide con 'admin' o 'administrador' o role_id == 1
        if (in_array($requiredRole, ['admin', 'administrador'])) {
            if ($user->role_id === 1 || in_array($userRoleName, ['admin', 'administrador'])) {
                return $next($request);
            }
        } elseif ($userRoleName === $requiredRole || (string)$user->role_id === (string)$role) {
            return $next($request);
        }

        abort(403, 'No tienes permisos para acceder a este módulo.');
    }
}
