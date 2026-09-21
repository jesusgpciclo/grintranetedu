<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añadir tipo de actividad (examen, tarea, proyecto, práctica).
     */
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->string('tipo')->default('tarea')->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
