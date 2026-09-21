<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        PermissionManagerService::applyDefaultAssignments();
    }

    public function test_catalog_contains_comprehensive_crud_and_module_permissions(): void
    {
        $catalog = PermissionManagerService::getCatalog();

        $this->assertArrayHasKey('guardias', $catalog);
        $this->assertArrayHasKey('ausencias', $catalog);
        $this->assertArrayHasKey('usuarios_roles', $catalog);
        $this->assertArrayHasKey('alumnos_profesores', $catalog);
        $this->assertArrayHasKey('centro', $catalog);
        $this->assertArrayHasKey('horarios', $catalog);
        $this->assertArrayHasKey('salidas', $catalog);
        $this->assertArrayHasKey('cuaderno_evaluacion', $catalog);
        $this->assertArrayHasKey('comunicacion_documentos', $catalog);
        $this->assertArrayHasKey('tic_mantenimiento', $catalog);

        // Check specific CRUD permissions
        $keys = PermissionManagerService::getAllPermissionKeys();
        $this->assertContains('guardias.view', $keys);
        $this->assertContains('ausencias.create', $keys);
        $this->assertContains('users.create', $keys);
        $this->assertContains('students.import', $keys);
        $this->assertContains('salidas.create', $keys);
        $this->assertContains('modulos.manage', $keys);
        $this->assertContains('cuaderno.manage', $keys);
    }

    public function test_admin_can_access_permission_matrix(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('roles.matrix'));
        $response->assertStatus(200);
        $response->assertSee('Matriz de Permisos por Rol');
        $response->assertSee('Guardias del Centro');
        $response->assertSee('Gestor de Salidas');
    }

    public function test_regular_teacher_cannot_access_permission_matrix(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $response = $this->actingAs($teacher)->get(route('roles.matrix'));
        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_permission_via_ajax(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $role = Role::firstOrCreate(['name' => 'test-role', 'guard_name' => 'web']);
        $permissionName = 'salidas.create';

        // Toggle ON
        $response = $this->actingAs($admin)->postJson(route('roles.matrix.toggle'), [
            'role_id' => $role->id,
            'permission' => $permissionName,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'attached' => true,
        ]);

        $this->assertTrue($role->fresh()->hasPermissionTo($permissionName));

        // Toggle OFF
        $responseOff = $this->actingAs($admin)->postJson(route('roles.matrix.toggle'), [
            'role_id' => $role->id,
            'permission' => $permissionName,
        ]);

        $responseOff->assertStatus(200);
        $responseOff->assertJson([
            'success' => true,
            'attached' => false,
        ]);

        $this->assertFalse($role->fresh()->hasPermissionTo($permissionName));
    }

    public function test_admin_can_update_matrix_in_batch(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $role = Role::firstOrCreate(['name' => 'batch-test-role', 'guard_name' => 'web']);

        $response = $this->actingAs($admin)->post(route('roles.matrix.update'), [
            'matrix' => [
                $role->id => ['guardias.view', 'salidas.view', 'messages.view'],
            ],
        ]);

        $response->assertRedirect(route('roles.matrix'));
        $response->assertSessionHas('success');

        $this->assertTrue($role->fresh()->hasPermissionTo('guardias.view'));
        $this->assertTrue($role->fresh()->hasPermissionTo('salidas.view'));
        $this->assertTrue($role->fresh()->hasPermissionTo('messages.view'));
    }
}
