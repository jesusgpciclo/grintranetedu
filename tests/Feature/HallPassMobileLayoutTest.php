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

        // 2. The unified list row contains only the bathroom (service) icon and the more (+) icon
        $response->assertSee('aria-label="Baño"', false);
        $response->assertSee('aria-label="Más opciones"', false);
        $response->assertSee('openStudentDetailsModal(' . $student->id . ')', false);

        // 3. The student details modal is present with larger font, quick reason buttons, custom reason and history
        $response->assertSee('id="student-details-modal-overlay"', false);
        $response->assertSee('id="modal-student-name"', false);
        $response->assertSee('id="modal-custom-reason-input"', false);
        $response->assertSee('id="modal-history-list"', false);
        $response->assertSee('selectModalReason', false);
        $response->assertSee('submitModalCustomReason', false);
        $response->assertSee('promptEditPassTime', false);
    }

    public function test_salidas_dashboard_contains_favorite_course_star_and_scripts(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $group = Group::create([
            'course' => '1º GS DAW',
            'name' => 'A',
            'academic_year' => '2025/2026',
        ]);

        $response = $this->actingAs($teacher)->get(route('salidas.index'));

        $response->assertStatus(200);
        $response->assertSee('id="favorite-class-btn"', false);
        $response->assertSee('id="favorite-star-icon"', false);
        $response->assertSee('toggleCurrentClassFavorite', false);
        $response->assertSee('salidas_favorite_groups', false);
        $response->assertSee('renderClassSelector', false);
    }
}

