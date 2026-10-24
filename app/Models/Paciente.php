<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
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
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function antecedentes()
    {
        return $this->hasMany(AntecedenteMedico::class, 'paciente_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }
}
