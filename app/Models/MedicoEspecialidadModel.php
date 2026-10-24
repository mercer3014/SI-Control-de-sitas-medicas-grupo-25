<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicoEspecialidadModel extends Model
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
        return $this->belongsTo(MedicoModel::class, 'medico_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(EspecialidadModel::class, 'especialidad_id');
    }

    public function horarios()
    {
        return $this->hasMany(HorarioAtencionModel::class, 'medico_especialidad_id');
    }

    public function citas()
    {
        return $this->hasMany(CitaModel::class, 'medico_especialidad_id');
    }
}
