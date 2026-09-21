<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurso_id')->constrained('recursos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->datetime('fecha_inicio');
            $table->datetime('fecha_fin');
            $table->text('motivo')->nullable();
            $table->enum('estado', ['solicitada', 'confirmada', 'cancelada', 'finalizada'])->default('confirmada');
            $table->timestamps();
            
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas_recursos');
    }
};
