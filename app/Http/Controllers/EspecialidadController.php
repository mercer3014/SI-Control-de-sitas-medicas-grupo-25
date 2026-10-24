<?php

namespace App\Http\Controllers;

use App\Models\EspecialidadModel;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EspecialidadController extends Controller
{
    public function index(Request $request)
    {
        $query = EspecialidadModel::withCount('medicos')->latest();

        if ($request->filled('buscar')) {
            $term = '%' . trim($request->buscar) . '%';
            $query->where('nombre', 'ilike', $term)
                  ->orWhere('descripcion', 'ilike', $term);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->boolean('estado'));
        }

        $especialidades = $query->paginate(15)->withQueryString();

        return Inertia::render('Especialidades/Index', [
            'especialidades' => $especialidades,
            'filters' => $request->only(['buscar', 'estado']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:especialidades,nombre',
            'descripcion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'Ingresa el nombre de la especialidad.',
            'nombre.unique' => 'Ya existe una especialidad registrada con este nombre.',
        ]);

        $esp = EspecialidadModel::create([
            'nombre' => trim($request->nombre),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
            'estado' => true,
        ]);

        BitacoraService::registrar('CREACION_ESPECIALIDAD', 'Catálogos', "Especialidad creada: {$esp->nombre}");

        return back()->with('success', "Especialidad '{$esp->nombre}' agregada con éxito.");
    }

    public function update(Request $request, EspecialidadModel $especialidad)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:especialidades,nombre,' . $especialidad->id,
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'boolean',
        ], [
            'nombre.required' => 'Ingresa el nombre de la especialidad.',
            'nombre.unique' => 'Ya existe otra especialidad con este nombre.',
        ]);

        $especialidad->update([
            'nombre' => trim($request->nombre),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
            'estado' => $request->boolean('estado', true),
        ]);

        BitacoraService::registrar('MODIFICACION_ESPECIALIDAD', 'Catálogos', "Especialidad editada: {$especialidad->nombre}");

        return back()->with('success', "Especialidad '{$especialidad->nombre}' actualizada correctamente.");
    }

    public function destroy(EspecialidadModel $especialidad)
    {
        $nuevoEstado = !$especialidad->estado;
        $especialidad->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'ACTIVACION_ESPECIALIDAD' : 'DESACTIVACION_ESPECIALIDAD';
        BitacoraService::registrar($accion, 'Catálogos', "Especialidad {$especialidad->nombre} cambiada a " . ($nuevoEstado ? 'Activa' : 'Inactiva'));

        return back()->with('success', "Estado de la especialidad modificado a " . ($nuevoEstado ? 'activo' : 'inactivo') . ".");
    }
}
