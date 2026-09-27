<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileMultiRoleSwitchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        PermissionManagerService::applyDefaultAssignments();
    }

    public function test_single_role_user_does_not_see_role_switcher(): void
    {
        $teacher = User::factory()->create(['name' => 'Profesor Solo']);
        $teacher->assignRole('profesor');

        $this->assertFalse($teacher->hasMultipleRoles());

        $response = $this->actingAs($teacher)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertDontSee('id="role-selector-section"', false);
        $response->assertDontSee('id="sidebar-role-switch-form"', false);
    }

    public function test_multi_role_user_sees_role_switcher_in_profile_and_sidebar(): void
    {
        $user = User::factory()->create(['name' => 'Docente Jefatura']);
        $user->assignRole(['profesor', 'jefatura']);

        $this->assertTrue($user->hasMultipleRoles());

        $response = $this->actingAs($user)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertSee('id="role-selector-section"', false);
        $response->assertSee('Cambiar Rol Activo');
        $response->assertSee('Profesor');
        $response->assertSee('Jefatura');
        $response->assertSee('Modo Completo');
        $response->assertSee('id="sidebar-role-switch-form"', false);
    }

    public function test_user_can_switch_active_role_and_permissions_adapt_accordingly(): void
    {
        $user = User::factory()->create(['name' => 'Usuario Híbrido']);
        $user->assignRole(['profesor', 'jefatura']);

        // 1. Switch to 'jefatura'
        $switchJefatura = $this->actingAs($user)->post(route('profile.switch-role'), [
            'role' => 'jefatura',
        ]);
        $switchJefatura->assertRedirect();
        $switchJefatura->assertSessionHas('status', 'Has cambiado tu rol activo a: Jefatura.');

        $this->assertEquals('jefatura', $user->fresh()->getActiveRoleName());
        $this->assertTrue($user->fresh()->hasRole('jefatura'));
        $this->assertFalse($user->fresh()->hasRole('profesor'));

        // Can access history as Jefatura
        $this->actingAs($user->fresh())->get(route('salidas.history'))->assertStatus(200);

        // 2. Switch to 'profesor'
        $switchProfesor = $this->actingAs($user->fresh())->post(route('profile.switch-role'), [
            'role' => 'profesor',
        ]);
        $switchProfesor->assertRedirect();
        $switchProfesor->assertSessionHas('status', 'Has cambiado tu rol activo a: Profesor.');

        $this->assertEquals('profesor', $user->fresh()->getActiveRoleName());
        $this->assertTrue($user->fresh()->hasRole('profesor'));
        $this->assertFalse($user->fresh()->hasRole('jefatura'));

        // Cannot access history as Profesor (403)
        $this->actingAs($user->fresh())->get(route('salidas.history'))->assertStatus(403);

        // 3. Switch to 'all' (Modo Completo)
        $switchAll = $this->actingAs($user->fresh())->post(route('profile.switch-role'), [
            'role' => 'all',
        ]);
        $switchAll->assertRedirect();
        $this->assertNull($user->fresh()->getActiveRoleName());
        $this->assertTrue($user->fresh()->hasRole('profesor'));
        $this->assertTrue($user->fresh()->hasRole('jefatura'));
        $this->actingAs($user->fresh())->get(route('salidas.history'))->assertStatus(200);
    }

    public function test_user_cannot_switch_to_unassigned_role(): void
    {
        $teacher = User::factory()->create(['name' => 'Profesor Normal']);
        $teacher->assignRole('profesor');

        // Attempt privilege escalation to 'admin'
        $response = $this->actingAs($teacher)->post(route('profile.switch-role'), [
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertNull($teacher->fresh()->getActiveRoleName());
        $this->assertFalse($teacher->fresh()->hasRole('admin'));
    }
}
