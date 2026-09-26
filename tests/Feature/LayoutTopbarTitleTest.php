<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use Spatie\Permission\Models\Role;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LayoutTopbarTitleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PermissionManagerService::syncDatabasePermissions();
        $profRole = Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);
        $profRole->syncPermissions(['salidas.view', 'salidas.create', 'guardias.view']);
        Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);
    }

    public function test_topbar_displays_intranet_and_module_title_in_header(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $response = $this->actingAs($teacher)->get(route('salidas.index'));

        $response->assertStatus(200);

        // Header displays GR Intranet EDU and module title
        $response->assertSee('GR Intranet EDU');
        $response->assertSee('Gestor de <span class="text-blue-500">salidas</span>', false);

        // Body card no longer displays the old duplicate title section with the subtitle
        $response->assertDontSee('Control de pases al pasillo');
    }

    public function test_topbar_displays_guardias_module_title_on_parte(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $response = $this->actingAs($teacher)->get(route('guardias.parte'));

        $response->assertStatus(200);
        $response->assertSee('GR Intranet EDU');
        $response->assertSee('Parte de <span class="text-sky-500">guardia</span>', false);
    }
}
