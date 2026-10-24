<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioAtencion extends Model
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
        return $this->belongsTo(MedicoEspecialidad::class, 'medico_especialidad_id');
    }

    public function dia()
    {
        return $this->belongsTo(DiaSemana::class, 'dia_id');
    }
}
