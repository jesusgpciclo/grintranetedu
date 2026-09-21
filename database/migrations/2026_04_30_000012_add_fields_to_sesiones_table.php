<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añadir campos extra a sesiones: tareas mandadas y enlaces a recursos.
     */
    public function up(): void
    {
        Schema::table('sesiones', function (Blueprint $table) {
            $table->text('tareas_mandadas')->nullable()->after('actividades_realizadas');
            $table->json('enlaces_recursos')->nullable()->after('tareas_mandadas');
        });
    }

    public function down(): void
    {
        Schema::table('sesiones', function (Blueprint $table) {
            $table->dropColumn(['tareas_mandadas', 'enlaces_recursos']);
        });
    }
};
