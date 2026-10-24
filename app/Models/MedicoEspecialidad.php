<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicoEspecialidad extends Model
{
    use HasFactory;

    protected $table = 'medico_especialidad';

    protected $fillable = [
        'medico_id',
        'especialidad_id',
        'precio_consulta',
    ];

    protected $casts = [
        'precio_consulta' => 'decimal:2',
    ];

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioAtencion::class, 'medico_especialidad_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'medico_especialidad_id');
    }
}
