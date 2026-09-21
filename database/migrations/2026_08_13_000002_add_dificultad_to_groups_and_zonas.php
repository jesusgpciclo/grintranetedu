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
        Schema::table('groups', function (Blueprint $table) {
            $table->integer('dificultad')->default(1)->after('school_year_id');
        });

        Schema::table('zonas', function (Blueprint $table) {
            $table->integer('dificultad')->default(1)->after('nombre');
            $table->integer('aforo')->nullable()->after('dificultad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('dificultad');
        });

        Schema::table('zonas', function (Blueprint $table) {
            $table->dropColumn(['dificultad', 'aforo']);
        });
    }
};
