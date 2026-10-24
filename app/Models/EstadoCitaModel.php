<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoCitaModel extends Model
{
    protected $table = 'estados_cita';
    public $timestamps = false;
    protected $fillable = ['id', 'nombre'];

    public const PROGRAMADA = 1;
    public const CONFIRMADA = 2;
    public const ATENDIDA = 3;
    public const CANCELADA = 4;
    public const INASISTENCIA = 5;
}
