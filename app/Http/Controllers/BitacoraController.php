<?php

namespace App\Http\Controllers;

use App\Models\BitacoraModel;
use App\Models\UsuarioModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $query = BitacoraModel::with(['usuario.persona'])->latest('fecha_hora');

        if ($request->filled('modulo')) {
            $query->where('modulo', 'ilike', '%' . $request->modulo . '%');
        }

        if ($request->filled('accion')) {
            $query->where('accion', 'ilike', '%' . $request->accion . '%');
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_hora', $request->fecha);
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        $bitacoras = $query->paginate(20)->withQueryString();
        $usuarios = UsuarioModel::with('persona')->where('estado', true)->get();

        return Inertia::render('Bitacora/Index', [
            'bitacoras' => $bitacoras,
            'usuarios' => $usuarios,
            'filters' => $request->only(['modulo', 'accion', 'fecha', 'usuario_id']),
        ]);
    }
}
