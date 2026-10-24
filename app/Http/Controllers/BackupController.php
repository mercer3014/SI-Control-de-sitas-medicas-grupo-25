<?php

namespace App\Http\Controllers;

use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BackupController extends Controller
{
    public function index()
    {
        $driver = config('database.default');
        if ($driver === 'pgsql') {
            $tables = DB::select("
                SELECT 
                    relname as table_name,
                    n_live_tup as row_count,
                    pg_size_pretty(pg_total_relation_size(relid)) as total_size
                FROM pg_stat_user_tables
                ORDER BY relname ASC;
            ");

            $dbSize = DB::select("SELECT pg_size_pretty(pg_database_size(current_database())) as size;")[0]->size ?? '0 MB';
        } else {
            $tables = [];
            $dbSize = 'Local DB';
        }

        return Inertia::render('Backups/Index', [
            'tables' => $tables,
            'dbSize' => $dbSize,
            'dbName' => config("database.connections.{$driver}.database", 'clinica_db'),
        ]);
    }

    public function download()
    {
        $dbName = config('database.connections.pgsql.database');
        $fileName = "backup_clinica_" . date('Y-m-d_H-i-s') . ".sql";

        // Generar volcado estructurado de las tablas principales
        $tables = [
            'roles', 'permisos', 'rol_permiso', 'personas', 'usuarios', 
            'bitacora', 'especialidades', 'medicos', 'medico_especialidad', 
            'pacientes', 'antecedentes_medicos', 'dias_semana', 'horarios_atencion', 
            'ausencias_medicas', 'estados_cita', 'citas', 'atenciones_clinicas'
        ];

        $sqlContent = "-- ========================================================\n";
        $sqlContent .= "-- RESPALDO DE BASE DE DATOS: CLINICA VIDA SALUD\n";
        $sqlContent .= "-- FECHA: " . date('Y-m-d H:i:s') . "\n";
        $sqlContent .= "-- GESTOR: PostgreSQL 18\n";
        $sqlContent .= "-- ========================================================\n\n";

        foreach ($tables as $table) {
            $records = DB::table($table)->get();
            if ($records->count() > 0) {
                $sqlContent .= "-- Datos de tabla: {$table}\n";
                foreach ($records as $row) {
                    $rowArray = (array) $row;
                    $cols = array_keys($rowArray);
                    $vals = array_map(function ($val) {
                        if (is_null($val)) return 'NULL';
                        if (is_bool($val)) return $val ? 'TRUE' : 'FALSE';
                        if (is_numeric($val)) return $val;
                        return "'" . addslashes((string)$val) . "'";
                    }, array_values($rowArray));

                    $sqlContent .= "INSERT INTO {$table} (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
                }
                $sqlContent .= "\n";
            }
        }

        BitacoraService::registrar('GENERACION_RESPALDO', 'Seguridad', "Se descargó copia de seguridad {$fileName}");

        return response($sqlContent, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
