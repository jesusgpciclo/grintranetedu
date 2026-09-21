<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de Observaciones/Incidencias de alumnos.
     * Usa la tabla users (los alumnos son usuarios con group_id).
     */
    public function up(): void
    {
        Schema::create('observaciones_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('profesor_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['positiva', 'negativa', 'informativa', 'seguimiento'])->default('informativa');
            $table->text('descripcion');
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observaciones_alumnos');
    }
};
