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
        Schema::table('aulas', function (Blueprint $table) {
            if (!Schema::hasColumn('aulas', 'dificultad')) {
                $table->integer('dificultad')->default(1)->after('descripcion');
            }
            if (!Schema::hasColumn('aulas', 'aforo')) {
                $table->integer('aforo')->nullable()->after('dificultad');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            if (Schema::hasColumn('aulas', 'dificultad')) {
                $table->dropColumn('dificultad');
            }
            if (Schema::hasColumn('aulas', 'aforo')) {
                $table->dropColumn('aforo');
            }
        });
    }
};
