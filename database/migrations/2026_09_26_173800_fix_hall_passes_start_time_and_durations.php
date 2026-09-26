<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Prevent MySQL/MariaDB from automatically updating start_time on row update
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `hall_passes` MODIFY `start_time` TIMESTAMP NULL DEFAULT NULL");
        }

        // 2. Repair historical records where start_time was corrupted by ON UPDATE CURRENT_TIMESTAMP
        DB::table('hall_passes')
            ->whereNotNull('end_time')
            ->whereNotNull('created_at')
            ->whereColumn('start_time', '>', 'end_time')
            ->update(['start_time' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for start_time definition and historical data cleanup
    }
};
