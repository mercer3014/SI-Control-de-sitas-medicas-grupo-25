<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Bloqueo escalonado por niveles: 0 = sin bloqueos, 1 = 5 min, 2 = 30 min, 3 = permanente
            $table->smallInteger('nivel_bloqueo')->default(0)->after('intentos_fallidos');
            $table->timestamp('fecha_bloqueo')->nullable()->after('bloqueado_hasta');
            // Primer ingreso al sistema (para forzar cambio de contraseña inicial)
            $table->boolean('primer_login')->default(true)->after('estado');
            // Auditoría de acceso
            $table->timestamp('fecha_ult_login')->nullable();
            // Token de sesión única (invalida sesiones previas del mismo usuario)
            $table->string('session_token', 40)->nullable();
            // Código de recuperación de contraseña (6 dígitos) con su expiración
            $table->string('codigo_recuperacion', 10)->nullable();
            $table->timestamp('codigo_recuperacion_expira')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn([
                'nivel_bloqueo',
                'fecha_bloqueo',
                'primer_login',
                'fecha_ult_login',
                'session_token',
                'codigo_recuperacion',
                'codigo_recuperacion_expira',
            ]);
        });
    }
};
