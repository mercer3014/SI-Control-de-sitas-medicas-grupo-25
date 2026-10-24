<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'codigo_reserva',
        'paciente_id',
        'medico_especialidad_id',
        'estado_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'motivo_consulta',
        'motivo_cancelacion',
        'fecha_registro',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_registro' => 'datetime',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function medicoEspecialidad()
    {
        return $this->belongsTo(MedicoEspecialidad::class, 'medico_especialidad_id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoCita::class, 'estado_id');
    }

    public function atencion()
    {
        return $this->hasOne(AtencionClinica::class, 'cita_id');
    }
}
