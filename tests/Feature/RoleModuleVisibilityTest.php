<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleModuleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('school_years')->updateOrInsert(
            ['id' => 1],
            ['name' => '2026/2027', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // Seed roles & permissions
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'profesor']);
        Role::firstOrCreate(['name' => 'alumno']);
        PermissionManagerService::applyDefaultAssignments();
    }

    /** @test */
    public function admin_sees_all_intranet_modules_and_administration_menus()
    {
        $admin = User::factory()->create(['name' => 'Super Admin']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);

        // Sidebar modules should all be present for Admin
        $response->assertSee('Mi Día a Día');
        $response->assertSee('Gestión y Dirección');
        $response->assertSee('Centro');
        $response->assertSee('Salidas');
        $response->assertSee('Cuaderno del Profesor');
        $response->assertSee('Recursos e Incidencias');
        $response->assertSee('Documentación');
        $response->assertSee('Administración');
        $response->assertSee('Usuarios');
        $response->assertSee('Roles');
    }

    /** @test */
    public function student_role_only_sees_permitted_modules_and_not_forbidden_ones()
    {
        $alumnoUser = User::factory()->create(['name' => 'Alumno Test']);
        $alumnoUser->assignRole('alumno');

        $response = $this->actingAs($alumnoUser)->get(route('dashboard'));
        $response->assertStatus(200);

        // Alumno must NOT see Guardias claustro, Cuadrantes, Administración, Centro interno
        $response->assertDontSee('Mi Día a Día');
        $response->assertDontSee('Gestión y Dirección');
        $response->assertDontSee('Administración');
        $response->assertDontSee('Cuadrante Semanal Completo');
        $response->assertDontSee('Control de Justificaciones');

        // Alumno has salidas.view, so sees Salidas (monitor/live)
        $response->assertSee('Salidas');
    }

    /** @test */
    public function custom_role_with_specific_module_permissions_dynamically_shows_only_those_menus()
    {
        // Create custom role "coordinador-salidas"
        $customRole = Role::create(['name' => 'coordinador-salidas']);
        $customRole->syncPermissions(['salidas.view', 'salidas.create', 'salidas.manage']);

        $user = User::factory()->create(['name' => 'Coordinador Salidas']);
        $user->assignRole($customRole);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // Sees Salidas module and submenus
        $response->assertSee('Salidas');
        $response->assertSee('Pase de Salida');
        $response->assertSee('Historial');

        // Does NOT see other modules
        $response->assertDontSee('Mi Día a Día');
        $response->assertDontSee('Gestión y Dirección');
        $response->assertDontSee('Cuaderno del Profesor');
        $response->assertDontSee('Administración');
    }

    /** @test */
    public function revoking_module_permission_hides_menu_for_that_role()
    {
        $role = Role::create(['name' => 'personal-tic']);
        $role->syncPermissions(['tic.view', 'tic.manage']);

        $user = User::factory()->create(['name' => 'Técnico TIC']);
        $user->assignRole($role);

        // Initially sees Recursos e Incidencias
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertSee('Recursos e Incidencias');
        $response->assertSee('Reservas TIC');

        // Revoke permissions from this role
        $role->syncPermissions([]);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Now does not see Recursos e Incidencias
        $response2 = $this->actingAs($user->fresh())->get(route('dashboard'));
        $response2->assertDontSee('Recursos e Incidencias');
        $response2->assertDontSee('Reservas TIC');
    }
}
