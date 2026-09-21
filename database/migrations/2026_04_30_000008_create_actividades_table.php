<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de Actividades: tareas, exámenes, proyectos vinculados a un módulo.
     */
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->foreignId('modulo_id')->constrained('modulos')->onDelete('cascade');
            $table->foreignId('criterio_evaluacion_id')->nullable()->constrained('criterios_evaluacion')->onDelete('set null');
            $table->date('fecha_entrega')->nullable();
            $table->decimal('peso', 5, 2)->default(0);         // Peso en la calificación del CE
            $table->boolean('es_evaluable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
