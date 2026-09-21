<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->after('tutor_id')->constrained('school_years')->onDelete('set null');
        });

        // Try to associate existing groups with a school year based on `curso_academico` or fallback to active
        $activeYear = DB::table('school_years')->where('is_active', true)->first();
        $allYears = DB::table('school_years')->get();

        $groups = DB::table('groups')->get();
        foreach ($groups as $group) {
            $matchedYearId = null;
            if (!empty($group->curso_academico)) {
                // E.g. '2025/2026' or '25/26' -> check if we find a match
                foreach ($allYears as $year) {
                    if (str_contains($group->curso_academico, substr($year->name, -2)) || str_contains($year->name, substr($group->curso_academico, -2))) {
                        $matchedYearId = $year->id;
                        break;
                    }
                }
            }

            DB::table('groups')->where('id', $group->id)->update([
                'school_year_id' => $matchedYearId ?: ($activeYear ? $activeYear->id : null)
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }
};
