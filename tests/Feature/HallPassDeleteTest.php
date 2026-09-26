<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\HallPass;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HallPassDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        PermissionManagerService::applyDefaultAssignments();
    }

    public function test_teacher_can_delete_individual_hall_pass_via_json(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(10),
            'end_time' => now(),
        ]);

        $response = $this->actingAs($teacher)
            ->deleteJson(route('salidas.history.destroy', $pass->id));

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Salida eliminada correctamente.'
        ]);

        $this->assertDatabaseMissing('hall_passes', [
            'id' => $pass->id
        ]);
    }

    public function test_admin_can_bulk_delete_hall_passes(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $pass1 = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $admin->id,
            'reason' => 'Agua',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(20),
            'end_time' => now()->subMinutes(15),
        ]);

        $pass2 = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $admin->id,
            'reason' => 'Enfermedad',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('salidas.history.bulk-delete'), [
                'ids' => [$pass1->id, $pass2->id]
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Se han eliminado 2 salidas correctamente.'
        ]);

        $this->assertDatabaseMissing('hall_passes', ['id' => $pass1->id]);
        $this->assertDatabaseMissing('hall_passes', ['id' => $pass2->id]);
    }

    public function test_unauthorized_user_cannot_delete_hall_pass(): void
    {
        $student = User::factory()->create();
        $student->assignRole('alumno');

        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $pass = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(15),
            'end_time' => now(),
        ]);

        $response = $this->actingAs($student)
            ->deleteJson(route('salidas.history.destroy', $pass->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('hall_passes', [
            'id' => $pass->id
        ]);
    }

    public function test_history_view_displays_delete_actions_and_checkboxes(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create(['name' => 'Carlos', 'last_name' => 'García']);
        $student->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now()->subMinutes(10),
            'end_time' => now(),
        ]);

        $response = $this->actingAs($teacher)
            ->get(route('salidas.history'));

        $response->assertStatus(200);
        $response->assertSee('select-all-passes');
        $response->assertSee('btn-delete-selected');
        $response->assertSee('pass-checkbox');
        $response->assertSee('confirmDeletePass(' . $pass->id, false);
    }
}
