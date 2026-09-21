<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('subtipo')->nullable();
            $table->string('procedencia')->nullable();
            $table->string('estado');
            $table->text('descripcion')->nullable();
            $table->date('fecha_alta')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->string('num_registro_general')->nullable();
            $table->string('num_serie')->nullable();
            $table->string('edificio')->nullable();
            $table->string('planta')->nullable();
            $table->string('localizacion')->nullable();
            $table->string('dependencia_adscripcion')->nullable();
            $table->boolean('solicitud_retirada')->default(false);
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
