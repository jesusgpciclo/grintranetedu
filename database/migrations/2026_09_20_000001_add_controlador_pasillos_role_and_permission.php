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

        // 1. Crear permisos de salidas si no existen
        $permView = Permission::firstOrCreate(['name' => 'salidas.view', 'guard_name' => 'web']);
        $permReturn = Permission::firstOrCreate(['name' => 'salidas.return_monitor', 'guard_name' => 'web']);

        // 2. Crear roles
        $roleControlador = Role::firstOrCreate(['name' => 'controlador-pasillo', 'guard_name' => 'web']);
        $roleConserje = Role::firstOrCreate(['name' => 'conserje', 'guard_name' => 'web']);
        $roleAdmin = Role::where('name', 'admin')->first();
        $roleDirectiva = Role::where('name', 'directiva')->first();
        $roleDirector = Role::where('name', 'director')->first();

        // 3. Asignaciones de permisos:
        // Conserje solo puede ver el monitor (NO puede regresar alumnos)
        if ($roleConserje) {
            if (!$roleConserje->hasPermissionTo($permView)) {
                $roleConserje->givePermissionTo($permView);
            }
            if ($roleConserje->hasPermissionTo($permReturn)) {
                $roleConserje->revokePermissionTo($permReturn);
            }
        }

        // Controlador de pasillos tiene permiso de monitor y de regresar alumnos
        if ($roleControlador) {
            if (!$roleControlador->hasPermissionTo($permView)) {
                $roleControlador->givePermissionTo($permView);
            }
            if (!$roleControlador->hasPermissionTo($permReturn)) {
                $roleControlador->givePermissionTo($permReturn);
            }
        }

        // Admin, Directiva y Director pueden regresar alumnos en monitor
        if ($roleAdmin && !$roleAdmin->hasPermissionTo($permReturn)) {
            $roleAdmin->givePermissionTo($permReturn);
        }
        if ($roleDirectiva && !$roleDirectiva->hasPermissionTo($permReturn)) {
            $roleDirectiva->givePermissionTo($permReturn);
        }
        if ($roleDirector && !$roleDirector->hasPermissionTo($permReturn)) {
            $roleDirector->givePermissionTo($permReturn);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permReturn = Permission::where('name', 'salidas.return_monitor')->first();
        if ($permReturn) {
            $permReturn->delete();
        }

        $roleControlador = Role::where('name', 'controlador-pasillo')->first();
        if ($roleControlador) {
            $roleControlador->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
