<?php

namespace App\Http\Controllers;

use App\Models\UsuarioModel;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Ingresa tu usuario o correo electrónico.',
            'password.required' => 'Ingresa tu contraseña.',
        ]);

        $loginInput = trim($request->login);

        $usuario = UsuarioModel::with('persona', 'rol')
            ->where(function ($q) use ($loginInput) {
                $q->where('username', $loginInput)
                  ->orWhere('email', $loginInput);
            })
            ->first();

        if (!$usuario) {
            BitacoraService::registrar('INTENTO_FALLIDO', 'Seguridad', "Intento con usuario inexistente: {$loginInput}");
            return back()->withErrors(['login' => 'Usuario o contraseña incorrectos.']);
        }

        if (!$usuario->estado) {
            BitacoraService::registrar('ACCESO_DENEGADO', 'Seguridad', "Intento en cuenta inactiva: {$usuario->username}", $usuario->id);
            return back()->withErrors(['login' => 'Esta cuenta se encuentra desactivada. Contacta al administrador.']);
        }

        // Verificar si la cuenta está bloqueada temporalmente
        if ($usuario->isBloqueado()) {
            $minutosRestantes = max(1, (int) ceil(now()->diffInSeconds($usuario->bloqueado_hasta) / 60));
            BitacoraService::registrar('ACCESO_BLOQUEADO', 'Seguridad', "Intento durante bloqueo temporal: {$usuario->username}", $usuario->id);
            return back()->withErrors([
                'login' => "Cuenta bloqueada por seguridad. Podrás intentar nuevamente en {$minutosRestantes} minuto(s).",
            ]);
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $usuario->password)) {
            $usuario->increment('intentos_fallidos');
            $restantes = 5 - $usuario->intentos_fallidos;

            if ($restantes <= 0) {
                $usuario->bloqueado_hasta = now()->addMinutes(15);
                $usuario->save();

                BitacoraService::registrar('BLOQUEO_SEGURIDAD', 'Seguridad', "Cuenta bloqueada por 15 minutos tras 5 fallos consecutivos: {$usuario->username}", $usuario->id);

                return back()->withErrors([
                    'login' => 'Has superado el límite de 5 intentos fallidos. Tu cuenta ha sido bloqueada temporalmente por 15 minutos.',
                ]);
            }

            $usuario->save();
            BitacoraService::registrar('PASSWORD_INCORRECTO', 'Seguridad', "Fallo de contraseña ({$usuario->intentos_fallidos}/5): {$usuario->username}", $usuario->id);

            return back()->withErrors([
                'password' => "Contraseña incorrecta. Te quedan {$restantes} intento(s) antes del bloqueo.",
            ]);
        }

        // Autenticación exitosa: reiniciar contadores
        $usuario->intentos_fallidos = 0;
        $usuario->bloqueado_hasta = null;
        $usuario->save();

        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();

        BitacoraService::registrar('INICIO_SESION', 'Seguridad', "Inicio de sesión exitoso. Rol: {$usuario->rol->nombre}", $usuario->id);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            BitacoraService::registrar('CIERRE_SESION', 'Seguridad', 'Cierre de sesión voluntario', $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
