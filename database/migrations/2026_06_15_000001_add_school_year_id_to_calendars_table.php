<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->after('parent_id')->constrained('school_years')->onDelete('set null');
        });

        // Set default school_year_id for existing calendars
        $activeYear = DB::table('school_years')->where('is_active', true)->first();
        if ($activeYear) {
            DB::table('calendars')->update(['school_year_id' => $activeYear->id]);
        }
    }

    public function down(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropColumn('school_year_id');
        });
    }
};
