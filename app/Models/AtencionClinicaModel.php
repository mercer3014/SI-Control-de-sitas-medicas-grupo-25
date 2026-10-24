<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtencionClinicaModel extends Model
{
    protected $table = 'atenciones_clinicas';

    protected $fillable = [
        'cita_id',
        'diagnostico',
        'tratamiento',
        'notas_adicionales',
        'fecha_atencion',
    ];

    protected $casts = [
        'fecha_atencion' => 'datetime',
    ];

    public function cita()
    {
        return $this->belongsTo(CitaModel::class, 'cita_id');
    }
}
