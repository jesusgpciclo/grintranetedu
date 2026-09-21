<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de Rúbricas: plantilla de evaluación vinculada a una actividad.
     */
    public function up(): void
    {
        Schema::create('rubricas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('actividad_id')->constrained('actividades')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('criterios_rubrica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubrica_id')->constrained('rubricas')->onDelete('cascade');
            $table->string('nombre');                          // Ej: "Funcionalidad"
            $table->text('descripcion')->nullable();
            $table->decimal('peso', 5, 2)->default(0);         // Peso dentro de la rúbrica
            $table->json('niveles')->nullable();                // JSON: [{nivel:"Excelente",puntos:10,desc:"..."}, ...]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterios_rubrica');
        Schema::dropIfExists('rubricas');
    }
};
