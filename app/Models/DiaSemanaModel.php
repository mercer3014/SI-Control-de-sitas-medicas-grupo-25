<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaSemanaModel extends Model
{
    protected $table = 'dias_semana';
    public $timestamps = false;
    protected $fillable = ['id', 'nombre'];
}
