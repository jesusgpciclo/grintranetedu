<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ausencias', function (Blueprint $table) {
            $table->foreignId('guardia_user_id')->nullable()->constrained('users')->nullOnDelete()->after('tarea');
            $table->timestamp('guardia_confirmed_at')->nullable()->after('guardia_user_id');
            $table->string('enlace_tarea')->nullable()->after('guardia_confirmed_at');
            $table->text('observaciones_guardia')->nullable()->after('enlace_tarea');
            $table->boolean('es_guardia')->default(false)->after('observaciones_guardia');
            $table->boolean('justificada')->default(false)->after('es_guardia');
            $table->text('justificacion_nota')->nullable()->after('justificada');
            
            // Make group_id and zona_id nullable for guardia-hour absences
            $table->foreignId('group_id')->nullable()->change();
            $table->foreignId('zona_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ausencias', function (Blueprint $table) {
            $table->dropForeign(['guardia_user_id']);
            $table->dropColumn([
                'guardia_user_id',
                'guardia_confirmed_at',
                'enlace_tarea',
                'observaciones_guardia',
                'es_guardia',
                'justificada',
                'justificacion_nota'
            ]);
        });
    }
};
