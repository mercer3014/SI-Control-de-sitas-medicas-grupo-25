<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EspecialidadModel extends Model
{
    use HasFactory;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function medicos()
    {
        return $this->belongsToMany(MedicoModel::class, 'medico_especialidad', 'especialidad_id', 'medico_id')
                    ->withPivot('precio_consulta');
    }

    public function medicoEspecialidades()
    {
        return $this->hasMany(MedicoEspecialidadModel::class, 'especialidad_id');
    }
}
