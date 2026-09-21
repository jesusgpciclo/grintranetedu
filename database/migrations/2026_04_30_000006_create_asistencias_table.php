<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de Asistencia: registro individual de asistencia por alumno y sesión.
     * Usa la tabla users (los alumnos son usuarios con rol 'alumno' y group_id).
     */
    public function up(): void
    {
        Schema::create('asistencias_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_id')->constrained('sesiones')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('estado', ['presente', 'falta', 'falta_justificada', 'retraso'])->default('presente');
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->unique(['sesion_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias_sesiones');
    }
};
