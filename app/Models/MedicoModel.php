<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicoModel extends Model
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
        return $this->belongsTo(PersonaModel::class, 'persona_id');
    }

    public function especialidades()
    {
        return $this->belongsToMany(EspecialidadModel::class, 'medico_especialidad', 'medico_id', 'especialidad_id')
                    ->withPivot('precio_consulta');
    }

    public function medicoEspecialidades()
    {
        return $this->hasMany(MedicoEspecialidadModel::class, 'medico_id');
    }

    public function ausencias()
    {
        return $this->hasMany(AusenciaMedicaModel::class, 'medico_id');
    }
}
