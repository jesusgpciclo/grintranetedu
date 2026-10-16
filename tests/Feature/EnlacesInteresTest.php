<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EnlacesInteresTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        PermissionManagerService::applyDefaultAssignments();
    }

    public function test_catalog_contains_enlaces_interes_module_and_permission(): void
    {
        $catalog = PermissionManagerService::getCatalog();
        $this->assertArrayHasKey('enlaces_interes', $catalog);
        $this->assertArrayHasKey('enlaces.view', $catalog['enlaces_interes']['permissions']);

        $keys = PermissionManagerService::getAllPermissionKeys();
        $this->assertContains('enlaces.view', $keys);
    }

    public function test_authorized_user_with_enlaces_permission_can_view_links_page(): void
    {
        $profesor = User::factory()->create();
        $profesor->assignRole('profesor');

        $response = $this->actingAs($profesor)->get(route('enlaces.index'));

        $response->assertStatus(200);
        $response->assertSee('Enlaces de');
        $response->assertSee('GUARDIAS');
        $response->assertSee('https://myblog-4eixzn7jtc.live-website.com/#/parte');
        $response->assertSee('PARTES DE INCIDENCIA');
        $response->assertSee('https://forms.gle/dgcJD6BQU3uJsH2M6');
        $response->assertSee('RESERVA DE AULAS');
        $response->assertSee('RESERVA DE PORTÁTILES');
        $response->assertSee('INCIDENCIAS TDE');
        $response->assertSee('https://myblog-4eixzn7jtc.live-website.com/#/incidencias');
        $response->assertSee('PROTOCOLO RECREOS INCLEMENCIAS METEOROLÓGICAS O SIMILAR');
        $response->assertSee('target="_blank"', false);
        $response->assertSee('<a href="https://myblog-4eixzn7jtc.live-website.com/#/parte"', false);
        $response->assertDontSee('Haz clic aquí');
    }

    public function test_admin_can_view_links_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('enlaces.index'));
        $response->assertStatus(200);
        $response->assertSee('Enlaces de');
    }

    public function test_user_without_permission_cannot_view_links_page(): void
    {
        $userWithoutPermission = User::factory()->create();
        // No permission assigned

        $response = $this->actingAs($userWithoutPermission)->get(route('enlaces.index'));
        $response->assertStatus(403);
    }

    public function test_sidebar_displays_enlaces_menu_for_authorized_users(): void
    {
        $profesor = User::factory()->create();
        $profesor->assignRole('profesor');

        $response = $this->actingAs($profesor)->get(route('enlaces.index'));
        $response->assertStatus(200);
        $response->assertSee(route('enlaces.index'));
        $response->assertSee('Enlaces de interés');
    }

    public function test_sidebar_does_not_display_enlaces_menu_for_unauthorized_users(): void
    {
        $user = User::factory()->create();
        // Give only dashboard.view so user can see dashboard
        $perm = Permission::firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);
        $user->givePermissionTo($perm);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee(route('enlaces.index'));
    }
}
