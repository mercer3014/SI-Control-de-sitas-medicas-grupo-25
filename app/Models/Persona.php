<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'personas';

    protected $fillable = [
        'ci',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'direccion',
        'fecha_registro',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_registro' => 'datetime',
    ];

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'persona_id');
    }

    public function medico()
    {
        return $this->hasOne(Medico::class, 'persona_id');
    }

    public function paciente()
    {
        return $this->hasOne(Paciente::class, 'persona_id');
    }
}
