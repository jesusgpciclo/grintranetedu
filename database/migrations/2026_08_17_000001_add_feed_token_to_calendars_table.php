<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('calendars', 'feed_token')) {
            Schema::table('calendars', function (Blueprint $table) {
                $table->string('feed_token', 64)->nullable()->unique();
            });

            // Generate token for existing calendars
            $calendars = DB::table('calendars')->whereNull('feed_token')->get();
            foreach ($calendars as $cal) {
                DB::table('calendars')
                    ->where('id', $cal->id)
                    ->update(['feed_token' => Str::random(32)]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('calendars', 'feed_token')) {
            Schema::table('calendars', function (Blueprint $table) {
                $table->dropColumn('feed_token');
            });
        }
    }
};
