<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de Criterios de Evaluación: cada CE pertenece a un RA.
     */
    public function up(): void
    {
        Schema::create('criterios_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->string('codigo');                          // Ej: "CE1.1"
            $table->text('descripcion');                       // Descripción del criterio
            $table->decimal('peso', 5, 2)->default(0);         // Peso porcentual dentro del RA
            $table->foreignId('resultado_aprendizaje_id')->constrained('resultados_aprendizaje')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criterios_evaluacion');
    }
};
