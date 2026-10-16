<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;
use App\Services\PermissionManagerService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Limpiar caché de permisos de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Sincronizar permisos en la base de datos y asignar permisos por defecto
        PermissionManagerService::applyDefaultAssignments();

        // 3. Limpiar caché nuevamente
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
