<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->after('recurso_id')->constrained('school_years')->onDelete('set null');
        });

        // Update existing incidencias to active school year
        $activeYear = DB::table('school_years')->where('is_active', true)->first();
        if ($activeYear) {
            DB::table('incidencias')->update(['school_year_id' => $activeYear->id]);
        }
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }
};
