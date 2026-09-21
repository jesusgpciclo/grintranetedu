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
            try {
                $table->dropForeign(['zona_id']);
            } catch (\Exception $e) {
                // Ignore if it fails due to driver-specific behavior
            }
            $table->foreign('zona_id')->references('id')->on('aulas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ausencias', function (Blueprint $table) {
            try {
                $table->dropForeign(['zona_id']);
            } catch (\Exception $e) {
                // Ignore
            }
            $table->foreign('zona_id')->references('id')->on('zonas')->onDelete('cascade');
        });
    }
};
