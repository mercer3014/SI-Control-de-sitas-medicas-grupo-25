<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioAtencionModel extends Model
{
    protected $table = 'horarios_atencion';

    protected $fillable = [
        'medico_especialidad_id',
        'dia_id',
        'hora_inicio',
        'hora_fin',
        'intervalo_minutos',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'intervalo_minutos' => 'integer',
    ];

    public function medicoEspecialidad()
    {
        return $this->belongsTo(MedicoEspecialidadModel::class, 'medico_especialidad_id');
    }

    public function dia()
    {
        return $this->belongsTo(DiaSemanaModel::class, 'dia_id');
    }
}
