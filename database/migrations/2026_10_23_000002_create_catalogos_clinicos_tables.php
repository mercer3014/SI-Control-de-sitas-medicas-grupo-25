<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 9. Catálogo de Especialidades
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // 10. Subclase Médicos
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('personas')->onDelete('restrict');
            $table->string('matricula_profesional', 50)->unique();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // 11. Relación Médicos - Especialidades (M:N)
        Schema::create('medico_especialidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('restrict');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('restrict');
            $table->decimal('precio_consulta', 10, 2)->default(0.00);
            $table->unique(['medico_id', 'especialidad_id']);
            $table->timestamps();
        });

        // 12. Subclase Pacientes
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('personas')->onDelete('restrict');
            $table->string('contacto_emergencia_nombre', 100)->nullable();
            $table->string('contacto_emergencia_telefono', 20)->nullable();
            $table->string('tipo_sangre', 5)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // 13. Antecedentes Médicos
        Schema::create('antecedentes_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->string('condicion', 150);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antecedentes_medicos');
        Schema::dropIfExists('pacientes');
        Schema::dropIfExists('medico_especialidad');
        Schema::dropIfExists('medicos');
        Schema::dropIfExists('especialidades');
    }
};
