<?php

namespace App\Http\Controllers;

use App\Models\BitacoraModel;
use App\Models\EspecialidadModel;
use App\Models\MedicoModel;
use App\Models\PacienteModel;
use App\Models\UsuarioModel;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_pacientes' => PacienteModel::where('estado', true)->count(),
            'total_medicos' => MedicoModel::where('estado', true)->count(),
            'total_especialidades' => EspecialidadModel::where('estado', true)->count(),
            'total_usuarios' => UsuarioModel::where('estado', true)->count(),
        ];

        $ultimasBitacoras = BitacoraModel::with('usuario.persona')
            ->latest('fecha_hora')
            ->take(6)
            ->get();

        $ultimosPacientes = PacienteModel::with('persona')
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
