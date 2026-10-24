<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Persona;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Paciente::with(['persona'])->latest();

        if ($request->filled('buscar')) {
            $term = '%' . trim($request->buscar) . '%';
            $query->whereHas('persona', function ($qp) use ($term) {
                $qp->where('nombres', 'ilike', $term)
                   ->orWhere('apellidos', 'ilike', $term)
                   ->orWhere('ci', 'ilike', $term)
                   ->orWhere('telefono', 'ilike', $term);
            });
        }

        if ($request->filled('tipo_sangre')) {
            $query->where('tipo_sangre', $request->tipo_sangre);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->boolean('estado'));
        }

        $pacientes = $query->paginate(15)->withQueryString();

        return Inertia::render('Pacientes/Index', [
            'pacientes' => $pacientes,
            'filters' => $request->only(['buscar', 'tipo_sangre', 'estado']),
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
            'tipo_sangre' => 'nullable|string|max:5',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|string|max:20',
            'antecedentes' => 'nullable|string',
        ], [
            'ci.unique' => 'Ya existe un paciente o persona con este documento de identidad.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
        ]);

        DB::transaction(function () use ($request) {
            $persona = Persona::create([
                'ci' => trim($request->ci),
                'nombres' => trim($request->nombres),
                'apellidos' => trim($request->apellidos),
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'telefono' => $request->telefono ? trim($request->telefono) : null,
                'direccion' => $request->direccion ? trim($request->direccion) : null,
            ]);

            $paciente = Paciente::create([
                'persona_id' => $persona->id,
                'tipo_sangre' => $request->tipo_sangre,
                'contacto_emergencia_nombre' => $request->contacto_emergencia_nombre ? trim($request->contacto_emergencia_nombre) : null,
                'contacto_emergencia_telefono' => $request->contacto_emergencia_telefono ? trim($request->contacto_emergencia_telefono) : null,
                'estado' => true,
            ]);

            if ($request->filled('antecedentes')) {
                $paciente->antecedentes()->create([
                    'condicion' => 'Antecedentes Generales',
                    'descripcion' => trim($request->antecedentes),
                ]);
            }

            BitacoraService::registrar(
                'REGISTRO_PACIENTE',
                'Pacientes',
                "Paciente registrado: {$persona->nombre_completo} (CI: {$persona->ci})"
            );
        });

        return back()->with('success', 'Paciente registrado exitosamente.');
    }

    public function show(Paciente $paciente)
    {
        $paciente->load(['persona', 'antecedentes', 'citas.medicoEspecialidad.medico.persona', 'citas.medicoEspecialidad.especialidad', 'citas.estado']);

        return Inertia::render('Pacientes/Show', [
            'paciente' => $paciente,
        ]);
    }

    public function update(Request $request, Paciente $paciente)
    {
        $persona = $paciente->persona;

        $request->validate([
            'ci' => 'required|string|max:20|unique:personas,ci,' . $persona->id,
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date|before:today',
            'genero' => 'required|in:M,F,O',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'tipo_sangre' => 'nullable|string|max:5',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|string|max:20',
            'estado' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $paciente, $persona) {
            $persona->update([
                'ci' => trim($request->ci),
                'nombres' => trim($request->nombres),
                'apellidos' => trim($request->apellidos),
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'telefono' => $request->telefono ? trim($request->telefono) : null,
                'direccion' => $request->direccion ? trim($request->direccion) : null,
            ]);

            $paciente->update([
                'tipo_sangre' => $request->tipo_sangre,
                'contacto_emergencia_nombre' => $request->contacto_emergencia_nombre ? trim($request->contacto_emergencia_nombre) : null,
                'contacto_emergencia_telefono' => $request->contacto_emergencia_telefono ? trim($request->contacto_emergencia_telefono) : null,
                'estado' => $request->boolean('estado', true),
            ]);

            BitacoraService::registrar(
                'MODIFICACION_PACIENTE',
                'Pacientes',
                "Paciente actualizado: {$persona->nombre_completo} (CI: {$persona->ci})"
            );
        });

        return back()->with('success', 'Datos del paciente actualizados correctamente.');
    }

    public function destroy(Paciente $paciente)
    {
        $nuevoEstado = !$paciente->estado;
        $paciente->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'ACTIVACION_PACIENTE' : 'DESACTIVACION_PACIENTE';
        BitacoraService::registrar($accion, 'Pacientes', "Paciente {$paciente->persona->nombre_completo} cambiado a " . ($nuevoEstado ? 'Activo' : 'Inactivo'));

        return back()->with('success', "Estado del paciente actualizado a " . ($nuevoEstado ? 'activo' : 'inactivo') . ".");
    }
}
