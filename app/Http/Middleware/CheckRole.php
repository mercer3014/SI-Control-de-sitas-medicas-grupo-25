<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->estado) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['login' => 'Tu cuenta se encuentra inactiva.']);
        }

        if (empty($roles)) {
            return $next($request);
        }

        $userRole = strtolower($user->rol->nombre ?? '');
        $allowed = array_map('strtolower', $roles);

        if (!in_array($userRole, $allowed, true)) {
            abort(403, 'No tienes permisos para acceder a este módulo.');
        }

        return $next($request);
    }
}
