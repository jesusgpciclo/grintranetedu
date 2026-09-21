<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade campo 'orden' a RAs y CEs para drag & drop,
     * y crea la tabla pivote actividad_criterio_evaluacion
     * para asignar múltiples CE a una actividad.
     */
    public function up(): void
    {
        // Campo orden para RAs
        if (!Schema::hasColumn('resultados_aprendizaje', 'orden')) {
            Schema::table('resultados_aprendizaje', function (Blueprint $table) {
                $table->unsignedInteger('orden')->default(0)->after('peso');
            });
        }

        // Campo orden para CEs
        if (!Schema::hasColumn('criterios_evaluacion', 'orden')) {
            Schema::table('criterios_evaluacion', function (Blueprint $table) {
                $table->unsignedInteger('orden')->default(0)->after('peso');
            });
        }

        // Tabla pivote: una actividad puede evaluar múltiples CEs
        if (!Schema::hasTable('actividad_criterio_evaluacion')) {
            Schema::create('actividad_criterio_evaluacion', function (Blueprint $table) {
                $table->id();
                $table->foreignId('actividad_id')->constrained('actividades')->onDelete('cascade');
                $table->foreignId('criterio_evaluacion_id')->constrained('criterios_evaluacion')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['actividad_id', 'criterio_evaluacion_id'], 'act_ce_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_criterio_evaluacion');

        if (Schema::hasColumn('criterios_evaluacion', 'orden')) {
            Schema::table('criterios_evaluacion', function (Blueprint $table) {
                $table->dropColumn('orden');
            });
        }

        if (Schema::hasColumn('resultados_aprendizaje', 'orden')) {
            Schema::table('resultados_aprendizaje', function (Blueprint $table) {
                $table->dropColumn('orden');
            });
        }
    }
};
