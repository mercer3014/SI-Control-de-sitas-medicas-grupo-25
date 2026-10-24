<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Usuario;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_pacientes' => Paciente::where('estado', true)->count(),
            'total_medicos' => Medico::where('estado', true)->count(),
            'total_especialidades' => Especialidad::where('estado', true)->count(),
            'total_usuarios' => Usuario::where('estado', true)->count(),
        ];

        $ultimasBitacoras = Bitacora::with('usuario.persona')
            ->latest('fecha_hora')
            ->take(6)
            ->get();

        $ultimosPacientes = Paciente::with('persona')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'ultimasBitacoras' => $ultimasBitacoras,
            'ultimosPacientes' => $ultimosPacientes,
        ]);
    }
}
