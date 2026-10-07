<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\HallPass;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\PermissionManagerService;
use Carbon\Carbon;

class HallPassTodoImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PermissionManagerService::syncDatabasePermissions();
        $profRole = Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);
        $profRole->syncPermissions(['salidas.view', 'salidas.create']);
        Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'conserje', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'jefatura', 'guard_name' => 'web']);
    }

    public function test_active_students_appear_first_and_rest_are_alphabetical(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $group = Group::create([
            'course' => '1º ESO',
            'name' => 'A',
            'academic_year' => '2025/2026',
        ]);

        // Student Z (will be active)
        $studentZ = User::factory()->create([
            'name' => 'Zacarias',
            'last_name' => 'Zapata',
            'group_id' => $group->id,
        ]);
        $studentZ->assignRole('alumno');

        // Student A (inactive)
        $studentA = User::factory()->create([
            'name' => 'Alberto',
            'last_name' => 'Alvarez',
            'group_id' => $group->id,
        ]);
        $studentA->assignRole('alumno');

        // Student B (inactive)
        $studentB = User::factory()->create([
            'name' => 'Beatriz',
            'last_name' => 'Bernal',
            'group_id' => $group->id,
        ]);
        $studentB->assignRole('alumno');

        // Give Zacarias an active pass
        HallPass::create([
            'user_id' => $studentZ->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(5),
            'end_time' => null,
        ]);

        $response = $this->actingAs($teacher)->get(route('salidas.index'));
        $response->assertStatus(200);

        // Active Zacarias appears before Alberto
        $content = $response->getContent();
        $posZ = strpos($content, 'Zacarias Zapata');
        $posA = strpos($content, 'Alberto Alvarez');
        $posB = strpos($content, 'Beatriz Bernal');

        $this->assertNotFalse($posZ);
        $this->assertNotFalse($posA);
        $this->assertNotFalse($posB);

        // Zacarias is first because he is active
        $this->assertLessThan($posA, $posZ);
        // Alberto is before Beatriz because of alphabetical sorting
        $this->assertLessThan($posB, $posA);
    }

    public function test_favorite_groups_are_persisted_in_database_and_synced(): void
    {
        $teacher = User::factory()->create([
            'favorite_groups' => ['1'],
        ]);
        $teacher->assignRole('profesor');

        $group1 = Group::create(['id' => 1, 'course' => '1º ESO', 'name' => 'A', 'academic_year' => '2025/2026']);
        $group2 = Group::create(['id' => 2, 'course' => '2º ESO', 'name' => 'B', 'academic_year' => '2025/2026']);

        // Toggle group 2 to favorites
        $response = $this->actingAs($teacher)->postJson(route('salidas.toggle-favorite'), [
            'group_id' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_favorite' => true,
        ]);

        $teacher->refresh();
        $this->assertContains('1', $teacher->favorite_groups);
        $this->assertContains('2', $teacher->favorite_groups);

        // Toggle group 1 to remove
        $responseRemove = $this->actingAs($teacher)->postJson(route('salidas.toggle-favorite'), [
            'group_id' => 1,
        ]);

        $responseRemove->assertStatus(200);
        $responseRemove->assertJson([
            'success' => true,
            'is_favorite' => false,
        ]);

        $teacher->refresh();
        $this->assertNotContains('1', $teacher->favorite_groups);
        $this->assertContains('2', $teacher->favorite_groups);

        // When viewing dashboard, favorites are passed
        $dashResponse = $this->actingAs($teacher)->get(route('salidas.index'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertViewHas('userFavorites', ['2']);
    }

    public function test_teacher_can_edit_pass_time_for_their_authorized_exit(): void
    {
        $teacher1 = User::factory()->create();
        $teacher1->assignRole('profesor');

        $teacher2 = User::factory()->create();
        $teacher2->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher1->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => Carbon::now()->subMinutes(15),
            'end_time' => Carbon::now()->subMinutes(5),
        ]);

        // Teacher 2 cannot edit teacher 1's pass time
        $forbiddenResponse = $this->actingAs($teacher2)->patchJson(route('salidas.pass.update-time', $pass->id), [
            'duration_minutes' => 10,
        ]);
        $forbiddenResponse->assertStatus(403);

        // Teacher 1 can edit duration
        $okResponse = $this->actingAs($teacher1)->patchJson(route('salidas.pass.update-time', $pass->id), [
            'duration_minutes' => 8,
        ]);
        $okResponse->assertStatus(200);

        $pass->refresh();
        $start = Carbon::parse($pass->start_time);
        $end = Carbon::parse($pass->end_time);
        $this->assertEquals(8, $start->diffInMinutes($end));
    }

    public function test_auto_return_hall_passes_command_closes_active_passes(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Biblioteca',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(30),
            'end_time' => null,
        ]);

        $this->assertNull($pass->end_time);

        // Run auto-return command
        $this->artisan('salidas:auto-return')
            ->assertExitCode(0);

        $pass->refresh();
        $this->assertNotNull($pass->end_time);
    }

    public function test_monitor_view_is_compact_list_table_format(): void
    {
        $conserje = User::factory()->create();
        $conserje->assignRole('conserje');

        $group = Group::create([
            'course' => '4º ESO',
            'name' => 'B',
            'academic_year' => '2025/2026',
        ]);

        $student = User::factory()->create([
            'name' => 'Carmen',
            'last_name' => 'Delgado',
            'group_id' => $group->id,
        ]);
        $student->assignRole('alumno');

        HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $conserje->id,
            'reason' => 'Enfermería',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(7),
            'end_time' => null,
        ]);

        $response = $this->actingAs($conserje)->get(route('salidas.monitor'));
        $response->assertStatus(200);

        // Table headers and high density list elements are present
        $response->assertSee('<table', false);
        $response->assertSee('Alumno', false);
        $response->assertSee('Grupo', false);
        $response->assertSee('Motivo', false);
        $response->assertSee('Tiempo Fuera', false);
        $response->assertSee('Carmen Delgado');
        $response->assertSee('Enfermería');
    }

    public function test_favicon_is_present_in_html(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $response = $this->actingAs($teacher)->get(route('salidas.index'));
        $response->assertStatus(200);
        $response->assertSee('rel="icon"', false);
        $response->assertSee('favicon.svg', false);
    }
}
