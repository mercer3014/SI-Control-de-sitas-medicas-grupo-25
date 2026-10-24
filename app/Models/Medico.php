<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    protected $table = 'medicos';

    protected $fillable = [
        'persona_id',
        'matricula_profesional',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'medico_especialidad', 'medico_id', 'especialidad_id')
                    ->withPivot('precio_consulta');
    }

    public function medicoEspecialidades()
    {
        return $this->hasMany(MedicoEspecialidad::class, 'medico_id');
    }

    public function ausencias()
    {
        return $this->hasMany(AusenciaMedica::class, 'medico_id');
    }
}
