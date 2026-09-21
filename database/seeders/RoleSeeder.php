<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Roles
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleDirector = Role::firstOrCreate(['name'=> 'director']);
        $roleProfesor = Role::firstOrCreate(['name'=> 'profesor']);
        $roleJefeDeDepartamento = Role::firstOrCreate(['name'=> 'jefe-de-departamento']);
        $roleAlumno = Role::firstOrCreate(['name'=> 'alumno']);
        $roleAdministrativo = Role::firstOrCreate(['name'=> 'administrativo']);
        $roleTecnico_tic = Role::firstOrCreate(['name'=> 'tecnico-tic']);
        $roleDirectiva = Role::firstOrCreate(['name' => 'directiva']);
        $roleControladorPasillo = Role::firstOrCreate(['name' => 'controlador-pasillo']);
        $roleConserje = Role::firstOrCreate(['name' => 'conserje']);

        // Crear Permisos (opcional para ahora, pero bueno tenerlos)
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'manage roles']);
        $permManageTeacherSchedules = Permission::firstOrCreate(['name' => 'manage teacher schedules']);

        // Asignar permisos a rol admin y directiva
        $roleAdmin->givePermissionTo(Permission::all());
        $roleDirectiva->givePermissionTo($permManageTeacherSchedules);

        // Crear Usuario Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'last_name' => 'Principal',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]
        );

        $admin->assignRole($roleAdmin);

        // Crear Usuario Normal
        $alumno = User::updateOrCreate(
            ['email' => 'alumno@example.com'],
            [
                'name' => 'Alumno',
                'last_name' => 'Prueba',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]
        );

        $alumno->assignRole($roleAlumno);
    }
}
