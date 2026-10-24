<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Bitacora;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CicloUnoQATest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Asegurar que la base de datos tenga los seeders básicos
        $this->artisan('db:seed');
    }

    /**
     * Prueba 1: Login exitoso con credenciales válidas y registro en bitácora (CU-01, CU-10)
     */
    public function test_login_exitoso_y_registro_en_bitacora(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // Verificar registro en bitácora
        $this->assertDatabaseHas('bitacora', [
            'accion' => 'INICIO_SESION',
            'modulo' => 'Seguridad',
        ]);
    }

    /**
     * Prueba 2: Bloqueo de cuenta tras 5 intentos fallidos consecutivos (CU-01)
     */
    public function test_bloqueo_de_cuenta_tras_cinco_intentos_fallidos(): void
    {
        $usuario = Usuario::where('username', 'admin')->first();
        $usuario->update(['intentos_fallidos' => 0, 'bloqueado_hasta' => null]);

        for ($i = 1; $i <= 5; $i++) {
            $response = $this->post('/login', [
                'login' => 'admin',
                'password' => 'password_erroneo',
            ]);
        }

        $usuario->refresh();
        $this->assertEquals(5, $usuario->intentos_fallidos);
        $this->assertNotNull($usuario->bloqueado_hasta);
        $this->assertTrue($usuario->isBloqueado());

        // Verificar registro en bitácora
        $this->assertDatabaseHas('bitacora', [
            'accion' => 'BLOQUEO_SEGURIDAD',
            'modulo' => 'Seguridad',
        ]);
    }

    /**
     * Prueba 3: Solicitud de código de recuperación por correo (CU-01, Resend)
     */
    public function test_solicitud_recuperacion_contrasena_genera_token(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'admin@vidasalud.com',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'admin@vidasalud.com',
        ]);
    }

    /**
     * Prueba 4: Creación de especialidad y prevención de nombres duplicados (CU-04)
     */
    public function test_crear_especialidad_rechaza_duplicados(): void
    {
        $admin = Usuario::where('username', 'admin')->first();

        // Crear una especialidad
        $this->actingAs($admin)->post('/especialidades', [
            'nombre' => 'Traumatología',
            'descripcion' => 'Lesiones osteoarticulares',
        ]);

        $this->assertDatabaseHas('especialidades', [
            'nombre' => 'Traumatología',
        ]);

        // Intentar registrar la misma especialidad
        $response = $this->actingAs($admin)->post('/especialidades', [
            'nombre' => 'Traumatología',
            'descripcion' => 'Duplicado',
        ]);

        $response->assertSessionHasErrors(['nombre']);
    }

    /**
     * Prueba 5: Registro de médico y rechazo de matrícula duplicada (CU-03)
     */
    public function test_crear_medico_y_rechazar_matricula_duplicada(): void
    {
        $admin = Usuario::where('username', 'admin')->first();
        $esp = Especialidad::first();

        // 1. Registro exitoso
        $this->actingAs($admin)->post('/medicos', [
            'ci' => '22410211',
            'nombres' => 'Carlos',
            'apellidos' => 'Mamani',
            'fecha_nacimiento' => '1985-05-10',
            'genero' => 'M',
            'telefono' => '71234567',
            'direccion' => 'Calle Sucre #120',
            'matricula_profesional' => 'MP-7788',
            'especialidades' => [
                ['id' => $esp->id, 'precio' => 150.00]
            ],
        ]);

        $this->assertDatabaseHas('medicos', [
            'matricula_profesional' => 'MP-7788',
        ]);

        // 2. Intentar registrar otro médico con la misma matrícula profesional
        $response = $this->actingAs($admin)->post('/medicos', [
            'ci' => '99887766',
            'nombres' => 'Pedro',
            'apellidos' => 'Rojas',
            'fecha_nacimiento' => '1980-01-01',
            'genero' => 'M',
            'matricula_profesional' => 'MP-7788',
            'especialidades' => [
                ['id' => $esp->id, 'precio' => 100.00]
            ],
        ]);

        $response->assertSessionHasErrors(['matricula_profesional']);
    }

    /**
     * Prueba 6: Registro de paciente y rechazo de CI duplicado (CU-02)
     */
    public function test_crear_paciente_y_rechazar_ci_duplicado(): void
    {
        $admin = Usuario::where('username', 'admin')->first();

        // 1. Registro exitoso
        $this->actingAs($admin)->post('/pacientes', [
            'ci' => '8492019',
            'nombres' => 'Ana',
            'apellidos' => 'Torres',
            'fecha_nacimiento' => '1998-09-15',
            'genero' => 'F',
            'telefono' => '76543210',
            'tipo_sangre' => 'O+',
            'contacto_emergencia_nombre' => 'Roberto Torres',
            'contacto_emergencia_telefono' => '78901234',
        ]);

        $this->assertDatabaseHas('personas', [
            'ci' => '8492019',
            'nombres' => 'Ana',
        ]);

        // 2. Intentar registrar otro paciente con el mismo CI
        $response = $this->actingAs($admin)->post('/pacientes', [
            'ci' => '8492019',
            'nombres' => 'Mariana',
            'apellidos' => 'Suárez',
            'fecha_nacimiento' => '2000-01-01',
            'genero' => 'F',
        ]);

        $response->assertSessionHasErrors(['ci']);
    }

    /**
     * Prueba 7: Baja lógica (desactivación) no borra físicamente el registro (RN11)
     */
    public function test_baja_logica_de_paciente_no_elimina_registro_fisico(): void
    {
        $admin = Usuario::where('username', 'admin')->first();
        $paciente = Paciente::first();

        $this->assertNotNull($paciente);
        $this->assertTrue($paciente->estado);

        // Desactivar
        $this->actingAs($admin)->delete("/pacientes/{$paciente->id}");

        $paciente->refresh();
        $this->assertFalse($paciente->estado);
        // Sigue existiendo en la base de datos
        $this->assertDatabaseHas('pacientes', ['id' => $paciente->id, 'estado' => false]);
    }

    /**
     * Prueba 8: Generación y descarga de respaldo SQL (CU-11)
     */
    public function test_respaldo_sql_genera_contenido_valido(): void
    {
        $admin = Usuario::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/backups/download');

        $response->assertStatus(200);
        $this->assertStringContainsString('RESPALDO DE BASE DE DATOS: CLINICA VIDA SALUD', $response->getContent());
        $this->assertStringContainsString('INSERT INTO', $response->getContent());
    }
}
