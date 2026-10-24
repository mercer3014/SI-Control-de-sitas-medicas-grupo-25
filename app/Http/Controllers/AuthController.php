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
        $inputKey = $request->has('email') ? 'email' : 'login';

        $request->validate([
            $inputKey => 'required|string',
            'password' => 'required|string',
        ], [
            "{$inputKey}.required" => 'Ingresa tu correo electrónico.',
            'password.required' => 'Ingresa tu contraseña.',
        ]);

        $emailInput = strtolower(trim($request->input($inputKey)));

        $usuario = UsuarioModel::with('persona', 'rol')
            ->where(function ($q) use ($emailInput) {
                $q->where('email', $emailInput)
                  ->orWhere('username', $emailInput);
            })
            ->first();

        if (!$usuario) {
            BitacoraService::registrar('INTENTO_FALLIDO', 'Seguridad', "Intento fallido con cuenta: {$emailInput}");
            return back()->withErrors([$inputKey => 'Correo electrónico o contraseña incorrectos.']);
        }

        if (!$usuario->estado) {
            BitacoraService::registrar('ACCESO_DENEGADO', 'Seguridad', "Intento en cuenta desactivada: {$usuario->username}", $usuario->id);
            return back()->withErrors([$inputKey => 'Esta cuenta se encuentra desactivada.']);
        }

        // Verificar bloqueo (temporal vigente o permanente por nivel 3)
        if ($usuario->isBloqueado()) {
            if ($usuario->esBloqueoPermanente()) {
                BitacoraService::registrar('ACCESO_BLOQUEADO', 'Seguridad', "Intento en cuenta con bloqueo permanente: {$usuario->username}", $usuario->id);
                return back()->withErrors([
                    $inputKey => 'Tu cuenta fue bloqueada permanentemente por seguridad. Contacta al administrador o restablece tu contraseña.',
                ]);
            }

            $minutosRestantes = $usuario->minutosBloqueoRestantes();
            BitacoraService::registrar('ACCESO_BLOQUEADO', 'Seguridad', "Intento durante bloqueo (nivel {$usuario->nivel_bloqueo}): {$usuario->username}", $usuario->id);
            return back()->withErrors([
                $inputKey => "Cuenta bloqueada por seguridad. Podrás intentar nuevamente en {$minutosRestantes} minuto(s).",
            ]);
        }

        // Si el bloqueo temporal ya expiró, solo se limpia la ventana de tiempo;
        // el contador de intentos se mantiene hasta un inicio de sesión exitoso.
        if ($usuario->bloqueado_hasta && now()->greaterThanOrEqualTo($usuario->bloqueado_hasta)) {
            $usuario->bloqueado_hasta = null;
            $usuario->intentos_fallidos = 0;
            $usuario->save();
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $usuario->password)) {
            $quedoBloqueada = $usuario->registrarIntentoFallido();

            if ($quedoBloqueada) {
                $duracion = UsuarioModel::DURACIONES_BLOQUEO[$usuario->nivel_bloqueo] ?? null;

                $detalle = $duracion
                    ? "Cuenta bloqueada por {$duracion} minutos (nivel {$usuario->nivel_bloqueo}) tras " . UsuarioModel::MAX_INTENTOS . " fallos consecutivos: {$usuario->username}"
                    : "Cuenta bloqueada permanentemente (nivel 3) tras reincidencia de fallos: {$usuario->username}";

                BitacoraService::registrar('BLOQUEO_SEGURIDAD', 'Seguridad', $detalle, $usuario->id);

                $mensaje = $duracion
                    ? "Has superado el límite de " . UsuarioModel::MAX_INTENTOS . " intentos fallidos. Tu cuenta ha sido bloqueada por {$duracion} minutos (nivel de bloqueo {$usuario->nivel_bloqueo})."
                    : 'Tu cuenta ha sido bloqueada permanentemente por reiterados intentos fallidos. Contacta al administrador.';

                return back()->withErrors([$inputKey => $mensaje]);
            }

            $restantes = $usuario->intentosRestantes();
            BitacoraService::registrar('PASSWORD_INCORRECTO', 'Seguridad', "Fallo de contraseña ({$usuario->intentos_fallidos}/" . UsuarioModel::MAX_INTENTOS . "): {$usuario->username}", $usuario->id);

            return back()->withErrors([
                $inputKey => "Contraseña incorrecta. Te quedan {$restantes} intento(s) antes del bloqueo.",
            ]);
        }

        // Autenticación exitosa: limpiar contadores, registrar acceso y sesión única
        $usuario->registrarAccesoExitoso();

        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->put('session_token', $usuario->session_token);

        BitacoraService::registrar('INICIO_SESION', 'Seguridad', "Inicio de sesión exitoso. Rol: {$usuario->rol->nombre}", $usuario->id);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->revocarSesion();
            BitacoraService::registrar('CIERRE_SESION', 'Seguridad', 'Cierre de sesión voluntario', $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
