<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'rol' => $user->rol ? [
                        'id' => $user->rol->id,
                        'nombre' => $user->rol->nombre,
                    ] : null,
                    'persona' => $user->persona ? [
                        'id' => $user->persona->id,
                        'ci' => $user->persona->ci,
                        'nombres' => $user->persona->nombres,
                        'apellidos' => $user->persona->apellidos,
                        'nombre_completo' => $user->persona->nombre_completo,
                        'telefono' => $user->persona->telefono,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
