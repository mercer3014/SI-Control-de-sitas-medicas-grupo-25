<?php

namespace App\Http\Controllers;

use App\Models\EspecialidadModel;
use App\Models\MedicoModel;
use App\Models\MedicoEspecialidadModel;
use App\Models\PersonaModel;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MedicoController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicoModel::with(['persona', 'especialidades'])->latest();

        if ($request->filled('buscar')) {
            $term = '%' . trim($request->buscar) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('matricula_profesional', 'ilike', $term)
                  ->orWhereHas('persona', function ($qp) use ($term) {
                      $qp->where('nombres', 'ilike', $term)
                         ->orWhere('apellidos', 'ilike', $term)
                         ->orWhere('ci', 'ilike', $term);
                  });
            });
        }

        if ($request->filled('especialidad_id')) {
            $query->whereHas('especialidades', function ($q) use ($request) {
                $q->where('especialidades.id', $request->especialidad_id);
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->boolean('estado'));
        }

        $medicos = $query->paginate(15)->withQueryString();
        $especialidades = EspecialidadModel::where('estado', true)->orderBy('nombre')->get();

        return Inertia::render('Medicos/Index', [
            'medicos' => $medicos,
            'especialidades' => $especialidades,
            'filters' => $request->only(['buscar', 'especialidad_id', 'estado']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ci' => 'required|string|max:20|unique:personas,ci',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date|before:today',
            'genero' => 'required|in:M,F,O',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'matricula_profesional' => 'required|string|max:50|unique:medicos,matricula_profesional',
            'especialidades' => 'required|array|min:1',
            'especialidades.*.id' => 'required|exists:especialidades,id',
            'especialidades.*.precio' => 'required|numeric|min:0',
        ], [
            'ci.unique' => 'Ya existe una persona registrada con este CI.',
            'matricula_profesional.unique' => 'Esta matrícula profesional ya está asignada a otro médico.',
            'especialidades.required' => 'Debes asignar al menos una especialidad al médico.',
        ]);

        DB::transaction(function () use ($request) {
            $persona = PersonaModel::create([
                'ci' => trim($request->ci),
                'nombres' => trim($request->nombres),
                'apellidos' => trim($request->apellidos),
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'telefono' => $request->telefono ? trim($request->telefono) : null,
                'direccion' => $request->direccion ? trim($request->direccion) : null,
            ]);

            $medico = MedicoModel::create([
                'persona_id' => $persona->id,
                'matricula_profesional' => trim($request->matricula_profesional),
                'estado' => true,
            ]);

            foreach ($request->especialidades as $esp) {
                MedicoEspecialidadModel::create([
                    'medico_id' => $medico->id,
                    'especialidad_id' => $esp['id'],
                    'precio_consulta' => $esp['precio'] ?? 0,
                ]);
            }

            BitacoraService::registrar(
                'REGISTRO_MEDICO',
                'Médicos',
                "Médico registrado: Dr(a). {$persona->nombre_completo} (Mat: {$medico->matricula_profesional})"
            );
        });

        return back()->with('success', 'Médico registrado exitosamente con sus especialidades.');
    }

    public function show(MedicoModel $medico)
    {
        $medico->load(['persona', 'especialidades', 'medicoEspecialidades.horarios.dia']);

        return Inertia::render('Medicos/Perfil', [
            'medico' => $medico,
        ]);
    }

    public function update(Request $request, MedicoModel $medico)
    {
        $persona = $medico->persona;

        $request->validate([
            'ci' => 'required|string|max:20|unique:personas,ci,' . $persona->id,
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date|before:today',
            'genero' => 'required|in:M,F,O',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'matricula_profesional' => 'required|string|max:50|unique:medicos,matricula_profesional,' . $medico->id,
            'estado' => 'boolean',
            'especialidades' => 'required|array|min:1',
            'especialidades.*.id' => 'required|exists:especialidades,id',
            'especialidades.*.precio' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $medico, $persona) {
            $persona->update([
                'ci' => trim($request->ci),
                'nombres' => trim($request->nombres),
                'apellidos' => trim($request->apellidos),
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'telefono' => $request->telefono ? trim($request->telefono) : null,
                'direccion' => $request->direccion ? trim($request->direccion) : null,
            ]);

            $medico->update([
                'matricula_profesional' => trim($request->matricula_profesional),
                'estado' => $request->boolean('estado', true),
            ]);

            // Sincronizar especialidades
            $syncData = [];
            foreach ($request->especialidades as $esp) {
                $syncData[$esp['id']] = ['precio_consulta' => $esp['precio'] ?? 0];
            }
            $medico->especialidades()->sync($syncData);

            BitacoraService::registrar(
                'MODIFICACION_MEDICO',
                'Médicos',
                "Médico actualizado: Dr(a). {$persona->nombre_completo}"
            );
        });

        return back()->with('success', 'Datos del médico actualizados correctamente.');
    }

    public function destroy(MedicoModel $medico)
    {
        $nuevoEstado = !$medico->estado;
        $medico->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'ACTIVACION_MEDICO' : 'DESACTIVACION_MEDICO';
        BitacoraService::registrar($accion, 'Médicos', "Médico {$medico->persona->nombre_completo} cambiado a " . ($nuevoEstado ? 'Activo' : 'Inactivo'));

        return back()->with('success', "Estado del médico actualizado a " . ($nuevoEstado ? 'activo' : 'inactivo') . ".");
    }
}
