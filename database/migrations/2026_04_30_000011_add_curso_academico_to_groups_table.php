<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añadir curso académico a la tabla de grupos (ej: "2025/2026").
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('curso_academico')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('curso_academico');
        });
    }
};
