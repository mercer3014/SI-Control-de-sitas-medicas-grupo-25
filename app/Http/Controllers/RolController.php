<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Rol;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::with(['permisos'])->withCount('usuarios')->orderBy('id')->get();
        $permisos = Permiso::orderBy('nombre')->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permisos' => $permisos,
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        $request->validate([
            'descripcion' => 'nullable|string|max:255',
            'permisos' => 'array',
            'permisos.*' => 'exists:permisos,id',
            'estado' => 'boolean',
        ]);

        $rol->update([
            'descripcion' => $request->descripcion,
            'estado' => $request->boolean('estado', true),
        ]);

        if ($request->has('permisos')) {
            $rol->permisos()->sync($request->permisos);
        }

        BitacoraService::registrar(
            'MODIFICACION_ROL',
            'Seguridad',
            "Se actualizó el rol: {$rol->nombre}"
        );

        return back()->with('success', "Rol {$rol->nombre} actualizado exitosamente.");
    }
}
