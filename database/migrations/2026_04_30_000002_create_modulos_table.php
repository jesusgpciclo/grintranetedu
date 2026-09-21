<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabla de Módulos: asignaturas o módulos formativos del centro.
     */
    public function up(): void
    {
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();                // Código del módulo (Ej: "0484")
            $table->string('nombre');                          // Nombre (Ej: "Bases de Datos")
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null');
            $table->foreignId('profesor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('horas_semanales')->default(0);    // Horas semanales
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};
