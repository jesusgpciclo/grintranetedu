<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->enum('type', [
                'holiday',           // Festivo
                'vacation',          // Vacaciones
                'non_teaching',      // Día no lectivo
                'evaluation',        // Período de evaluación
                'department_meeting',// Reunión de departamento
                'faculty_meeting',   // Claustro
                'other',             // Otro
            ])->default('other');
            $table->date('start_date');
            $table->date('end_date')->nullable(); // for multi-day events
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable(); // override calendar color
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
