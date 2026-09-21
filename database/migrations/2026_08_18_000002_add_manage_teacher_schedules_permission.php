<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::firstOrCreate(['name' => 'manage teacher schedules', 'guard_name' => 'web']);

        $admin = Role::where('name', 'admin')->first();
        if ($admin && !$admin->hasPermissionTo($perm)) {
            $admin->givePermissionTo($perm);
        }

        $directiva = Role::where('name', 'directiva')->first();
        if ($directiva && !$directiva->hasPermissionTo($perm)) {
            $directiva->givePermissionTo($perm);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::where('name', 'manage teacher schedules')->first();
        if ($perm) {
            $perm->delete();
        }
    }
};
