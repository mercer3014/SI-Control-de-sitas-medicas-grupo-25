<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->rol && strtolower($user->rol->nombre) === 'administrador') {
            return redirect()->route('especialidades.index');
        }

        return redirect()->route('pacientes.index');
    }
}
