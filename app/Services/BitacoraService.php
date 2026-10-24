<?php

namespace App\Services;

use App\Models\Bitacora;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class BitacoraService
{
    public static function registrar(string $accion, string $modulo, ?string $detalle = null, ?int $usuarioId = null): void
    {
        try {
            $uid = $usuarioId ?? Auth::id();
            $ip = Request::ip() ?? '127.0.0.1';

            Bitacora::create([
                'usuario_id' => $uid,
                'accion' => strtoupper($accion),
                'modulo' => $modulo,
                'detalle' => $detalle,
                'ip_origen' => $ip,
                'fecha_hora' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail silently to avoid breaking main transaction if logging hits an error
            \Log::error('Error registrando en bitácora: ' . $e->getMessage());
        }
    }
}
