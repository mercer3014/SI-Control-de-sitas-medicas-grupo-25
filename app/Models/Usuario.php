<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'persona_id',
        'rol_id',
        'username',
        'email',
        'password',
        'intentos_fallidos',
        'bloqueado_hasta',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'bloqueado_hasta' => 'datetime',
        'estado' => 'boolean',
        'password' => 'hashed',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'usuario_id');
    }

    public function hasRole(string $nombre): bool
    {
        return $this->rol && strtolower($this->rol->nombre) === strtolower($nombre);
    }

    public function isBloqueado(): bool
    {
        if (!$this->bloqueado_hasta) {
            return false;
        }
        return now()->lessThan($this->bloqueado_hasta);
    }
}
