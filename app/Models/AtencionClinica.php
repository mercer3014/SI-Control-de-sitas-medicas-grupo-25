<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtencionClinica extends Model
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
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}
