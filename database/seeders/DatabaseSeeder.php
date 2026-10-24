<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Persona;
use App\Models\Usuario;
use App\Models\DiaSemana;
use App\Models\EstadoCita;
use App\Models\Especialidad;
use App\Models\Bitacora;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Días de la semana
        $dias = [
            ['id' => 1, 'nombre' => 'Lunes'],
            ['id' => 2, 'nombre' => 'Martes'],
            ['id' => 3, 'nombre' => 'Miércoles'],
            ['id' => 4, 'nombre' => 'Jueves'],
            ['id' => 5, 'nombre' => 'Viernes'],
            ['id' => 6, 'nombre' => 'Sábado'],
            ['id' => 7, 'nombre' => 'Domingo'],
        ];
        foreach ($dias as $d) {
            DiaSemana::updateOrCreate(['id' => $d['id']], $d);
        }

        // 2. Estados de Cita
        $estados = [
            ['id' => 1, 'nombre' => 'Programada'],
            ['id' => 2, 'nombre' => 'Confirmada'],
            ['id' => 3, 'nombre' => 'Atendida'],
            ['id' => 4, 'nombre' => 'Cancelada'],
            ['id' => 5, 'nombre' => 'Inasistencia'],
        ];
        foreach ($estados as $est) {
            EstadoCita::updateOrCreate(['id' => $est['id']], $est);
        }

        // 3. Roles
        $roles = [
            ['id' => 1, 'nombre' => 'Administrador', 'descripcion' => 'Control total de configuración, usuarios y catálogos.'],
            ['id' => 2, 'nombre' => 'Recepcionista', 'descripcion' => 'Gestión de agenda, pacientes y programación de citas.'],
            ['id' => 3, 'nombre' => 'Médico', 'descripcion' => 'Consulta de agenda diaria y registro de atenciones.'],
            ['id' => 4, 'nombre' => 'Paciente', 'descripcion' => 'Portal de autoservicio para consulta y reserva de citas.'],
        ];
        foreach ($roles as $r) {
            Rol::updateOrCreate(['id' => $r['id']], $r);
        }

        // 4. Permisos
        $permisos = [
            ['nombre' => 'gestionar_usuarios', 'descripcion' => 'Crear, listar y desactivar usuarios'],
            ['nombre' => 'gestionar_roles', 'descripcion' => 'Ver y administrar roles y permisos'],
            ['nombre' => 'ver_bitacora', 'descripcion' => 'Consultar registros de auditoría'],
            ['nombre' => 'gestionar_backups', 'descripcion' => 'Exportar y restaurar respaldos de base de datos'],
            ['nombre' => 'gestionar_especialidades', 'descripcion' => 'Administrar catálogo de especialidades'],
            ['nombre' => 'gestionar_medicos', 'descripcion' => 'Administrar registro y especialidades médicas'],
            ['nombre' => 'gestionar_pacientes', 'descripcion' => 'Administrar ficha y datos de pacientes'],
            ['nombre' => 'gestionar_horarios', 'descripcion' => 'Configurar disponibilidad y turnos médicos'],
            ['nombre' => 'programar_citas', 'descripcion' => 'Registrar y reservar citas médicas'],
            ['nombre' => 'cancelar_citas', 'descripcion' => 'Cancelar citas programadas'],
            ['nombre' => 'registrar_atencion', 'descripcion' => 'Diagnóstico y tratamiento clínico'],
            ['nombre' => 'ver_reportes', 'descripcion' => 'Generar reportes estadísticos y administrativos'],
        ];

        $adminRol = Rol::find(1);
        $recepRol = Rol::find(2);
        $medicoRol = Rol::find(3);

        foreach ($permisos as $p) {
            $perm = Permiso::firstOrCreate(['nombre' => $p['nombre']], $p);
            // Admin tiene todos los permisos
            $adminRol->permisos()->syncWithoutDetaching([$perm->id]);

            // Asignar a Recepcionista
            if (in_array($p['nombre'], ['gestionar_pacientes', 'programar_citas', 'cancelar_citas'])) {
                $recepRol->permisos()->syncWithoutDetaching([$perm->id]);
            }

            // Asignar a Médico
            if (in_array($p['nombre'], ['registrar_atencion'])) {
                $medicoRol->permisos()->syncWithoutDetaching([$perm->id]);
            }
        }

        // 5. Persona y Usuario Administrador Base
        $adminPersona = Persona::firstOrCreate(
            ['ci' => '10000001'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'Sistema',
                'fecha_nacimiento' => '1990-01-01',
                'genero' => 'M',
                'telefono' => '70000000',
                'direccion' => 'Av. Busch y 2do Anillo, Clínica Vida Salud',
            ]
        );

        Usuario::firstOrCreate(
            ['username' => 'admin'],
            [
                'persona_id' => $adminPersona->id,
                'rol_id' => 1,
                'email' => 'admin@vidasalud.com',
                'password' => Hash::make('admin123'),
                'estado' => true,
            ]
        );

        // 6. Especialidades Base
        $especialidades = [
            ['nombre' => 'Medicina General', 'descripcion' => 'Atención primaria y diagnóstico preventivo general.'],
            ['nombre' => 'Pediatría', 'descripcion' => 'Atención médica integral infantil y del adolescente.'],
            ['nombre' => 'Ginecología y Obstetricia', 'descripcion' => 'Cuidado de la salud femenina y control prenatal.'],
            ['nombre' => 'Odontología', 'descripcion' => 'Salud bucal, ortodoncia y tratamientos dentales.'],
            ['nombre' => 'Cardiología', 'descripcion' => 'Prevención, diagnóstico y tratamiento de enfermedades cardiovasculares.'],
        ];
        foreach ($especialidades as $esp) {
            Especialidad::firstOrCreate(['nombre' => $esp['nombre']], $esp);
        }

        // Bitácora de inicialización
        Bitacora::create([
            'usuario_id' => 1,
            'accion' => 'INICIALIZACION_SISTEMA',
            'modulo' => 'Seguridad',
            'detalle' => 'Inicialización de datos maestros, roles, permisos y especialidades.',
            'ip_origen' => '127.0.0.1',
        ]);
    }
}
