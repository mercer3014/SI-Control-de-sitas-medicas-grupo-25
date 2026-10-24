<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonaModel extends Model
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

    protected $appends = ['nombre_completo', 'iniciales', 'edad'];

    public function getInicialesAttribute(): string
    {
        $n = mb_substr(trim($this->nombres ?? ''), 0, 1);
        $a = mb_substr(trim($this->apellidos ?? ''), 0, 1);
        return mb_strtoupper($n . $a);
    }

    public function getEdadAttribute(): ?int
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function usuario()
    {
        return $this->hasOne(UsuarioModel::class, 'persona_id');
    }

    public function medico()
    {
        return $this->hasOne(MedicoModel::class, 'persona_id');
    }

    public function paciente()
    {
        return $this->hasOne(PacienteModel::class, 'persona_id');
    }
}
