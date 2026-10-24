<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 14. Catálogo Días de la Semana
        Schema::create('dias_semana', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->string('nombre', 15)->unique();
        });

        // 15. Configuración de Horarios Regulares
        Schema::create('horarios_atencion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_especialidad_id')->constrained('medico_especialidad')->onDelete('restrict');
            $table->unsignedSmallInteger('dia_id');
            $table->foreign('dia_id')->references('id')->on('dias_semana')->onDelete('restrict');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('intervalo_minutos')->default(30);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // 16. Excepciones y Ausencias
        Schema::create('ausencias_medicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('restrict');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('motivo', 200);
            $table->timestamps();
        });

        // 17. Catálogo de Estados de Cita
        Schema::create('estados_cita', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->string('nombre', 50)->unique();
        });

        // 18. Citas Médicas (Transacción Principal)
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_reserva', 20)->unique();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('restrict');
            $table->foreignId('medico_especialidad_id')->constrained('medico_especialidad')->onDelete('restrict');
            $table->unsignedSmallInteger('estado_id');
            $table->foreign('estado_id')->references('id')->on('estados_cita')->onDelete('restrict');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->text('motivo_consulta')->nullable();
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps();
        });

        // 19. Atenciones Clínicas (Conclusión Médica)
        Schema::create('atenciones_clinicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->unique()->constrained('citas')->onDelete('restrict');
            $table->text('diagnostico');
            $table->text('tratamiento');
            $table->text('notas_adicionales')->nullable();
            $table->timestamp('fecha_atencion')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atenciones_clinicas');
        Schema::dropIfExists('citas');
        Schema::dropIfExists('estados_cita');
        Schema::dropIfExists('ausencias_medicas');
        Schema::dropIfExists('horarios_atencion');
        Schema::dropIfExists('dias_semana');
    }
};
