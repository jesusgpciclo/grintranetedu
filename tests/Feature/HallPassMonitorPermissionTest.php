<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\HallPass;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HallPassMonitorPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        PermissionManagerService::applyDefaultAssignments();
    }

    public function test_salidas_return_monitor_permission_exists_in_catalog(): void
    {
        $catalog = PermissionManagerService::getCatalog();
        $this->assertArrayHasKey('salidas', $catalog);
        $this->assertArrayHasKey('salidas.return_monitor', $catalog['salidas']['permissions']);
        $this->assertContains('salidas.return_monitor', PermissionManagerService::getAllPermissionKeys());
    }

    public function test_conserje_cannot_return_students_in_hallway_monitor(): void
    {
        $conserje = User::factory()->create();
        $conserje->assignRole('conserje');

        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        $response = $this->actingAs($conserje)
            ->patchJson(route('salidas.update', $pass->id));

        $response->assertStatus(403);
        $response->assertJsonFragment([
            'error' => 'No tienes permisos para marcar el regreso de alumnos en el monitor de pasillos.'
        ]);

        $this->assertNull($pass->fresh()->end_time);
    }

    public function test_controlador_pasillo_can_return_students_in_hallway_monitor(): void
    {
        $controlador = User::factory()->create();
        $controlador->assignRole('controlador-pasillo');

        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        $response = $this->actingAs($controlador)
            ->patchJson(route('salidas.update', $pass->id));

        $response->assertStatus(200);
        $this->assertNotNull($pass->fresh()->end_time);
    }

    public function test_teacher_who_issued_the_pass_can_return_their_own_student(): void
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
            'start_time' => now(),
        ]);

        $response = $this->actingAs($teacher)
            ->patchJson(route('salidas.update', $pass->id));

        $response->assertStatus(200);
        $this->assertNotNull($pass->fresh()->end_time);
    }

    public function test_other_teacher_without_permission_cannot_return_different_teacher_student(): void
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
            'reason' => 'Enfermería',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        // Teacher 2 tries to return Teacher 1's student
        $response = $this->actingAs($teacher2)
            ->patchJson(route('salidas.update', $pass->id));

        $response->assertStatus(403);
        $this->assertNull($pass->fresh()->end_time);
    }

    public function test_monitor_view_shows_read_only_mode_for_conserje(): void
    {
        $conserje = User::factory()->create();
        $conserje->assignRole('conserje');

        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        $response = $this->actingAs($conserje)->get(route('salidas.monitor'));

        $response->assertStatus(200);
        $response->assertSee('Modo Supervisión (Solo Lectura)');
        $response->assertSee('Solo lectura');
        $response->assertDontSee('Regresar alumno');
    }

    public function test_monitor_view_shows_active_control_and_button_for_controlador_pasillo(): void
    {
        $controlador = User::factory()->create();
        $controlador->assignRole('controlador-pasillo');

        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        $response = $this->actingAs($controlador)->get(route('salidas.monitor'));

        $response->assertStatus(200);
        $response->assertSee('Control de Pasillo Activo');
        $response->assertSee('Regresar alumno');
    }
}
