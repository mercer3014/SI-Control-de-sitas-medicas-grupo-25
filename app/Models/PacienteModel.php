<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacienteModel extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'persona_id',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'tipo_sangre',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function persona()
    {
        return $this->belongsTo(PersonaModel::class, 'persona_id');
    }

    public function antecedentes()
    {
        return $this->hasMany(AntecedenteMedicoModel::class, 'paciente_id');
    }

    public function citas()
    {
        return $this->hasMany(CitaModel::class, 'paciente_id');
    }
}
