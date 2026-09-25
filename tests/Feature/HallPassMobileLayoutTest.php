<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\PermissionManagerService;

class HallPassMobileLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PermissionManagerService::syncDatabasePermissions();
        $profRole = Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);
        $profRole->syncPermissions(['salidas.view', 'salidas.create']);
        Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);
    }

    public function test_salidas_dashboard_contains_mobile_responsive_adaptations(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $group = Group::create([
            'course' => '3º ESO',
            'name' => 'A',
            'academic_year' => '2025/2026',
        ]);

        $student = User::factory()->create([
            'name' => 'Alumno1',
            'last_name' => 'Apellido',
            'group_id' => $group->id,
        ]);
        $student->assignRole('alumno');

        $response = $this->actingAs($teacher)->get(route('salidas.index'));

        $response->assertStatus(200);

        // 1. Stats container has hidden sm:flex so it is hidden on mobile
        $response->assertSee('hidden sm:flex gap-2 sm:gap-3', false);

        // 2. Direct mobile options grid is present with the 4 reasons
        $response->assertSee('sm:hidden grid grid-cols-4 gap-1.5', false);
        $response->assertSee('Baño');
        $response->assertSee('Agua');
        $response->assertSee('Enfermedad');
        $response->assertSee('Otro motivo');

        // 3. The old mobile "+" tap button is no longer present
        $response->assertDontSee('student-mobile-tap');
    }
}
