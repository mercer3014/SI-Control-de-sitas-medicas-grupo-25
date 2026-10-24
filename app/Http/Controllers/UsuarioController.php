<?php

namespace App\Http\Controllers;

use App\Models\PersonaModel;
use App\Models\RolModel;
use App\Models\UsuarioModel;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = UsuarioModel::with(['persona', 'rol'])->latest();

        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->where(function ($q) use ($term) {
                $q->where('username', 'ilike', $term)
                  ->orWhere('email', 'ilike', $term)
                  ->orWhereHas('persona', function ($qp) use ($term) {
                      $qp->where('nombres', 'ilike', $term)
                         ->orWhere('apellidos', 'ilike', $term)
                         ->orWhere('ci', 'ilike', $term);
                  });
            });
        }

        if ($request->filled('rol_id')) {
            $query->where('rol_id', $request->rol_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->boolean('estado'));
        }

        $usuarios = $query->paginate(15)->withQueryString();
        $roles = RolModel::where('estado', true)->get();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'filters' => $request->only(['buscar', 'rol_id', 'estado']),
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
            'rol_id' => 'required|exists:roles,id',
            'username' => 'required|string|max:50|unique:usuarios,username',
            'email' => 'required|email|max:100|unique:usuarios,email',
            'password' => 'required|string|min:6',
        ], [
            'ci.unique' => 'Ya existe una persona registrada con este documento de identidad.',
            'username.unique' => 'Este nombre de usuario ya se encuentra en uso.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
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

            $usuario = UsuarioModel::create([
                'persona_id' => $persona->id,
                'rol_id' => $request->rol_id,
                'username' => strtolower(trim($request->username)),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'estado' => true,
            ]);

            BitacoraService::registrar(
                'CREACION_USUARIO',
                'Seguridad',
                "Usuario creado: {$usuario->username} (CI: {$persona->ci}, Rol ID: {$usuario->rol_id})",
                $usuario->id
            );
        });

        return back()->with('success', 'Usuario registrado exitosamente.');
    }

    public function update(Request $request, UsuarioModel $usuario)
    {
        $persona = $usuario->persona;

        $request->validate([
            'ci' => 'required|string|max:20|unique:personas,ci,' . $persona->id,
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'required|date|before:today',
            'genero' => 'required|in:M,F,O',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:200',
            'rol_id' => 'required|exists:roles,id',
            'username' => 'required|string|max:50|unique:usuarios,username,' . $usuario->id,
            'email' => 'required|email|max:100|unique:usuarios,email,' . $usuario->id,
            'password' => 'nullable|string|min:6',
            'estado' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $usuario, $persona) {
            $persona->update([
                'ci' => trim($request->ci),
                'nombres' => trim($request->nombres),
                'apellidos' => trim($request->apellidos),
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'telefono' => $request->telefono ? trim($request->telefono) : null,
                'direccion' => $request->direccion ? trim($request->direccion) : null,
            ]);

            $userData = [
                'rol_id' => $request->rol_id,
                'username' => strtolower(trim($request->username)),
                'email' => strtolower(trim($request->email)),
                'estado' => $request->boolean('estado', true),
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $usuario->update($userData);

            BitacoraService::registrar(
                'MODIFICACION_USUARIO',
                'Seguridad',
                "Se actualizaron los datos del usuario: {$usuario->username}"
            );
        });

        return back()->with('success', 'Datos del usuario actualizados correctamente.');
    }

    public function toggle(UsuarioModel $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes cambiar el estado de tu propia cuenta activa.');
        }

        $nuevoEstado = !$usuario->estado;
        $usuario->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'ACTIVACION_USUARIO' : 'DESACTIVACION_USUARIO';
        BitacoraService::registrar($accion, 'Seguridad', "Estado cambiado a " . ($nuevoEstado ? 'Activo' : 'Inactivo') . " para {$usuario->username}", $usuario->id);

        return back()->with('success', 'Estado del usuario modificado con éxito.');
    }

    public function destroy(UsuarioModel $usuario)
    {
        // No permitir eliminar al propio usuario logueado
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta activa.');
        }

        $usuario->update(['estado' => false]);

        BitacoraService::registrar('ELIMINACION_USUARIO', 'Seguridad', "Usuario eliminado (baja lógica): {$usuario->username}", $usuario->id);

        return back()->with('success', 'Usuario eliminado correctamente.');
    }
}
