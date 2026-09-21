<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tic_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tic_resource_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->foreignId('time_slot_id')->constrained()->onDelete('cascade');
            $table->text('observations')->nullable();
            $table->timestamps();

            // Prevent overlapping: one booking per resource, per date, per time slot
            $table->unique(['tic_resource_id', 'date', 'time_slot_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tic_bookings');
    }
};
