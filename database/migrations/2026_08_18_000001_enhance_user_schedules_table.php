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
        Schema::table('user_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('user_schedules', 'school_year_id')) {
                $table->foreignId('school_year_id')->nullable()->after('user_id')->constrained('school_years')->onDelete('cascade');
            }
        });

        Schema::table('schedule_selections', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_selections', 'type')) {
                $table->string('type')->nullable()->after('day')->comment('clase, guardia, texto');
            }
            if (!Schema::hasColumn('schedule_selections', 'subject')) {
                $table->string('subject')->nullable()->after('type');
            }
            if (!Schema::hasColumn('schedule_selections', 'group_id')) {
                $table->foreignId('group_id')->nullable()->after('subject')->constrained('groups')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_selections', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_selections', 'group_id')) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            }
            if (Schema::hasColumn('schedule_selections', 'subject')) {
                $table->dropColumn('subject');
            }
            if (Schema::hasColumn('schedule_selections', 'type')) {
                $table->dropColumn('type');
            }
        });

        Schema::table('user_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('user_schedules', 'school_year_id')) {
                $table->dropForeign(['school_year_id']);
                $table->dropColumn('school_year_id');
            }
        });
    }
};
