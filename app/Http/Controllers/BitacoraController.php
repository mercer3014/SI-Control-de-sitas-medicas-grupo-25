<?php

namespace App\Http\Controllers;

use App\Models\BitacoraModel;
use App\Models\UsuarioModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $query = BitacoraModel::with(['usuario.persona'])->latest('fecha_hora');

        if ($request->filled('buscar')) {
            $term = '%' . trim($request->buscar) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('detalle', 'ilike', $term)
                  ->orWhere('accion', 'ilike', $term)
                  ->orWhere('modulo', 'ilike', $term)
                  ->orWhere('ip_origen', 'ilike', $term)
                  ->orWhereHas('usuario', function ($qu) use ($term) {
                      $qu->where('username', 'ilike', $term)
                         ->orWhereHas('persona', function ($qp) use ($term) {
                             $qp->where('nombres', 'ilike', $term)
                                ->orWhere('apellidos', 'ilike', $term);
                         });
                  });
            });
        }

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

        $bitacoras = $query->paginate(25)->withQueryString();
        $usuarios = UsuarioModel::with('persona')->where('estado', true)->get();

        // Cargar últimos registros del archivo físico de log del servidor
        $serverLogs = [];
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            $content = File::get($logPath);
            $lines = explode(PHP_EOL, trim($content));
            $recentLines = array_slice($lines, -60);
            $serverLogs = array_reverse(array_filter($recentLines));
        }

        return Inertia::render('Bitacora/Index', [
            'bitacoras' => $bitacoras,
            'usuarios' => $usuarios,
            'serverLogs' => array_values($serverLogs),
            'filters' => $request->only(['buscar', 'modulo', 'accion', 'fecha', 'usuario_id']),
        ]);
    }
}
