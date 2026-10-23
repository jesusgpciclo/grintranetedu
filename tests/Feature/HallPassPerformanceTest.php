<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\HallPass;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\PermissionManagerService;

class HallPassPerformanceTest extends TestCase
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

    public function test_salidas_index_loads_successfully(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $groupA = Group::create(['course' => '1º ESO', 'name' => 'A', 'academic_year' => '2025/2026']);
        $studentA = User::factory()->create(['name' => 'Ana', 'last_name' => 'García', 'group_id' => $groupA->id]);
        $studentA->assignRole('alumno');

        $response = $this->actingAs($teacher)->get(route('salidas.index'));

        $response->assertStatus(200);
        $response->assertSee('id="student-grid"', false);
        $response->assertSee('id="class-selector"', false);
    }

    public function test_salidas_cards_endpoint_returns_json_with_rendered_html_for_group(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $groupA = Group::create(['course' => '1º ESO', 'name' => 'A', 'academic_year' => '2025/2026']);
        $groupB = Group::create(['course' => '2º ESO', 'name' => 'B', 'academic_year' => '2025/2026']);

        $studentA = User::factory()->create(['name' => 'Carlos', 'last_name' => 'Alonso', 'group_id' => $groupA->id]);
        $studentA->assignRole('alumno');

        $studentB = User::factory()->create(['name' => 'Beatriz', 'last_name' => 'Zamora', 'group_id' => $groupB->id]);
        $studentB->assignRole('alumno');

        $response = $this->actingAs($teacher)->getJson(route('salidas.cards', ['group_id' => $groupA->id]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['html', 'count', 'todayPasses', 'groupId', 'hasActiveInGroup']);
        $response->assertJson([
            'count' => 1,
            'groupId' => (string) $groupA->id,
        ]);

        $html = $response->json('html');
        $this->assertStringContainsString('Alonso, Carlos', $html);
        $this->assertStringNotContainsString('Zamora, Beatriz', $html);
    }

    public function test_salidas_cards_endpoint_supports_search(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $groupA = Group::create(['course' => '1º ESO', 'name' => 'A', 'academic_year' => '2025/2026']);
        $student = User::factory()->create(['name' => 'David', 'last_name' => 'Navarro', 'group_id' => $groupA->id]);
        $student->assignRole('alumno');

        $response = $this->actingAs($teacher)->getJson(route('salidas.cards', ['search' => 'Navarro']));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('count'));
        $this->assertStringContainsString('Navarro, David', $response->json('html'));
    }

    public function test_salidas_index_respects_cookie_for_selected_class(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $groupA = Group::create(['course' => '1º ESO', 'name' => 'A', 'academic_year' => '2025/2026']);
        $groupB = Group::create(['course' => '2º ESO', 'name' => 'B', 'academic_year' => '2025/2026']);

        $studentA = User::factory()->create(['name' => 'Elena', 'last_name' => 'Serrano', 'group_id' => $groupA->id]);
        $studentA->assignRole('alumno');

        $studentB = User::factory()->create(['name' => 'Fernando', 'last_name' => 'Torres', 'group_id' => $groupB->id]);
        $studentB->assignRole('alumno');

        // Request with cookie for groupB
        $response = $this->actingAs($teacher)
            ->withCookie('salidas_selected_class', (string) $groupB->id)
            ->get(route('salidas.index'));

        $response->assertStatus(200);
        $response->assertSee('Torres, Fernando', false);
        $response->assertDontSee('Serrano, Elena', false);
    }
}
