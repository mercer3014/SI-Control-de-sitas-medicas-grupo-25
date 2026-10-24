<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolModel extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function usuarios()
    {
        return $this->hasMany(UsuarioModel::class, 'rol_id');
    }

    public function permisos()
    {
        return $this->belongsToMany(PermisoModel::class, 'rol_permiso', 'rol_id', 'permiso_id');
    }
}
