<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de Aulas: gestiona los espacios físicos del centro educativo.
     */
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                     // Nombre del aula (Ej: "Aula 101")
            $table->integer('capacidad')->default(30);    // Capacidad máxima de alumnos
            $table->json('equipamiento')->nullable();     // Equipamiento: ["proyector","pizarra digital",...]
            $table->string('ubicacion')->nullable();      // Ubicación: "Planta 1", "Edificio B"
            $table->text('descripcion')->nullable();      // Descripción adicional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
