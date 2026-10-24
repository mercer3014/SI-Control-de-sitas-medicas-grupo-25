<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AntecedenteMedico extends Model
{
    use HasFactory;

    protected $table = 'antecedentes_medicos';

    protected $fillable = [
        'paciente_id',
        'condicion',
        'descripcion',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}
