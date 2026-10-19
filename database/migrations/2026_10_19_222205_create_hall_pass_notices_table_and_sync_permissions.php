<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Spatie\Permission\PermissionRegistrar;
use App\Services\PermissionManagerService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hall_pass_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        // Sincronizar permisos en base de datos y asignar permisos por defecto
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        PermissionManagerService::applyDefaultAssignments();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_pass_notices');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
