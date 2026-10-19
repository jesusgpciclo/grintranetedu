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
        Schema::create('hall_pass_incompatibility_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('hall_pass_incompatibility_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('hall_pass_incompatibility_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_pass_incompatibility_group_user');
        Schema::dropIfExists('hall_pass_incompatibility_groups');
    }
};
