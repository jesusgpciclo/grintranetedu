<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de documentos institucionales (gestor documental).
     */
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('categoria');              // normativa, programacion, acta, plantilla, otro
            $table->string('departamento')->nullable();
            $table->string('curso')->nullable();       // Curso académico al que pertenece
            $table->string('url')->nullable();          // Enlace externo (Google Drive, etc.)
            $table->string('archivo')->nullable();      // Ruta al archivo subido
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
