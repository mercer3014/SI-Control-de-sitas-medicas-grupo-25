<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class UsuarioModel extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    public const MAX_INTENTOS = 5;

    /**
     * Minutos de bloqueo por nivel. El nivel 3 es bloqueo permanente
     * (solo se revierte por administrador o restablecimiento de contraseña).
     */
    public const DURACIONES_BLOQUEO = [
        1 => 5,
        2 => 30,
        3 => null, // permanente
    ];

    protected $fillable = [
        'persona_id',
        'rol_id',
        'username',
        'email',
        'password',
        'intentos_fallidos',
        'nivel_bloqueo',
        'bloqueado_hasta',
        'fecha_bloqueo',
        'estado',
        'primer_login',
        'fecha_ult_login',
        'session_token',
        'codigo_recuperacion',
        'codigo_recuperacion_expira',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'session_token',
        'codigo_recuperacion',
    ];

    protected $casts = [
        'bloqueado_hasta' => 'datetime',
        'fecha_bloqueo' => 'datetime',
        'fecha_ult_login' => 'datetime',
        'codigo_recuperacion_expira' => 'datetime',
        'estado' => 'boolean',
        'primer_login' => 'boolean',
        'password' => 'hashed',
    ];

    public function persona()
    {
        return $this->belongsTo(PersonaModel::class, 'persona_id');
    }

    public function rol()
    {
        return $this->belongsTo(RolModel::class, 'rol_id');
    }

    public function bitacoras()
    {
        return $this->hasMany(BitacoraModel::class, 'usuario_id');
    }

    public function hasRole(string $nombre): bool
    {
        return $this->rol && strtolower($this->rol->nombre) === strtolower($nombre);
    }

    /**
     * La cuenta está bloqueada si:
     *  - alcanzó el nivel 3 (bloqueo permanente), o
     *  - tiene un bloqueo temporal vigente.
     */
    public function isBloqueado(): bool
    {
        if ((int) $this->nivel_bloqueo >= 3) {
            return true;
        }

        if (!$this->bloqueado_hasta) {
            return false;
        }

        return now()->lessThan($this->bloqueado_hasta);
    }

    public function esBloqueoPermanente(): bool
    {
        return (int) $this->nivel_bloqueo >= 3;
    }

    /**
     * Minutos restantes de bloqueo temporal (0 si no aplica o es permanente).
     */
    public function minutosBloqueoRestantes(): int
    {
        if (!$this->bloqueado_hasta || now()->greaterThanOrEqualTo($this->bloqueado_hasta)) {
            return 0;
        }

        return max(1, (int) ceil(now()->diffInSeconds($this->bloqueado_hasta) / 60));
    }

    /**
     * Registra un intento fallido de contraseña. Al llegar al límite,
     * escala el nivel de bloqueo (1: 5 min, 2: 30 min, 3: permanente).
     * Retorna true si la cuenta quedó bloqueada con este intento.
     */
    public function registrarIntentoFallido(): bool
    {
        $this->intentos_fallidos = (int) $this->intentos_fallidos + 1;

        if ($this->intentos_fallidos < self::MAX_INTENTOS) {
            $this->save();
            return false;
        }

        // Escalar nivel de bloqueo
        $this->nivel_bloqueo = min(3, (int) $this->nivel_bloqueo + 1);
        $this->fecha_bloqueo = now();

        $duracion = self::DURACIONES_BLOQUEO[$this->nivel_bloqueo] ?? null;
        $this->bloqueado_hasta = $duracion ? now()->addMinutes($duracion) : null;

        $this->save();
        return true;
    }

    /**
     * Intentos restantes antes del próximo bloqueo.
     */
    public function intentosRestantes(): int
    {
        return max(0, self::MAX_INTENTOS - (int) $this->intentos_fallidos);
    }

    /**
     * Marca un inicio de sesión exitoso: limpia contadores y bloqueos,
     * registra el último acceso y emite un token de sesión única.
     */
    public function registrarAccesoExitoso(): void
    {
        $this->intentos_fallidos = 0;
        $this->nivel_bloqueo = 0;
        $this->bloqueado_hasta = null;
        $this->fecha_bloqueo = null;
        $this->fecha_ult_login = now();
        $this->primer_login = false;
        $this->session_token = Str::random(40);
        $this->save();
    }

    /**
     * Revoca el token de sesión única (cierre de sesión).
     */
    public function revocarSesion(): void
    {
        $this->session_token = null;
        $this->save();
    }

    /**
     * Desbloquea la cuenta por completo (acción de administrador).
     */
    public function desbloquear(): void
    {
        $this->intentos_fallidos = 0;
        $this->nivel_bloqueo = 0;
        $this->bloqueado_hasta = null;
        $this->fecha_bloqueo = null;
        $this->save();
    }

    /**
     * Genera y persiste el código de recuperación de contraseña (válido 15 min).
     */
    public function generarCodigoRecuperacion(string $codigo): void
    {
        $this->codigo_recuperacion = $codigo;
        $this->codigo_recuperacion_expira = now()->addMinutes(15);
        $this->save();
    }

    public function limpiarCodigoRecuperacion(): void
    {
        $this->codigo_recuperacion = null;
        $this->codigo_recuperacion_expira = null;
        $this->save();
    }
}
