<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AusenciaMedicaModel extends Model
{
    protected $table = 'ausencias_medicas';

    protected $fillable = [
        'medico_id',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function medico()
    {
        return $this->belongsTo(MedicoModel::class, 'medico_id');
    }
}
