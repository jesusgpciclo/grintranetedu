<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\HallPass;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeploymentSalidasPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        PermissionManagerService::applyDefaultAssignments();
    }

    public function test_dashboard_role_exists_and_controls_access(): void
    {
        $userWithoutDashboard = User::factory()->create();
        $userWithoutDashboard->assignRole('profesor');

        // Should be redirected from /dashboard to salidas.index
        $this->actingAs($userWithoutDashboard)
            ->get(route('dashboard'))
            ->assertRedirect(route('salidas.index'));

        // Assign dashboard role
        $userWithoutDashboard->assignRole('dashboard');

        // Now can access dashboard
        $this->actingAs($userWithoutDashboard)
            ->get(route('dashboard'))
            ->assertStatus(200);

        // Sidebar shows Dashboard link only when user has dashboard role or is admin
        $response = $this->actingAs($userWithoutDashboard)->get(route('salidas.index'));
        $response->assertSee('<span>Dashboard</span>', false);

        $userWithoutDashboard->removeRole('dashboard');
        $response2 = $this->actingAs($userWithoutDashboard)->get(route('salidas.index'));
        $response2->assertDontSee('<span>Dashboard</span>', false);
    }

    public function test_default_entry_redirects_to_salidas(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $conserje = User::factory()->create();
        $conserje->assignRole('conserje');

        // Root '/' redirect
        $this->actingAs($teacher)
            ->get('/')
            ->assertRedirect(route('salidas.index'));

        $this->actingAs($conserje)
            ->get('/')
            ->assertRedirect(route('salidas.monitor'));
    }

    public function test_conserje_visiting_salidas_index_redirects_to_monitor(): void
    {
        $conserje = User::factory()->create();
        $conserje->assignRole('conserje');

        $this->actingAs($conserje)
            ->get(route('salidas.index'))
            ->assertRedirect(route('salidas.monitor'));
    }

    public function test_other_modules_hidden_for_profesor_in_sidebar(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $responseTeacher = $this->actingAs($teacher)->get(route('salidas.index'));
        $responseTeacher->assertStatus(200);
        $responseTeacher->assertDontSee('Parte de Guardia');
        $responseTeacher->assertDontSee('Cuaderno de Notas');
        $responseTeacher->assertDontSee('Reservas TIC');
        $responseTeacher->assertDontSee('Inventario Centro');
        $responseTeacher->assertDontSee('Gestionar Usuarios');
        $responseTeacher->assertSee('Pasillos');

        $responseAdmin = $this->actingAs($admin)->get(route('salidas.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Parte de Guardia');
        $responseAdmin->assertSee('Pasillos');
        $responseAdmin->assertSee('Administración');
    }

    public function test_jefatura_can_access_everything_in_salidas(): void
    {
        $jefatura = User::factory()->create();
        $jefatura->assignRole('jefatura');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $group = Group::create([
            'name' => '1º ESO A',
            'course' => '1º ESO',
        ]);
        $student->update(['group_id' => $group->id]);

        // 1. Can access classroom dashboard
        $this->actingAs($jefatura)
            ->get(route('salidas.index'))
            ->assertStatus(200);

        // 2. Can issue pass
        $responsePass = $this->actingAs($jefatura)
            ->postJson(route('salidas.store'), [
                'student_id' => $student->id,
                'reason' => 'Jefatura',
            ]);
        $responsePass->assertStatus(200);
        $passId = $responsePass->json('id');

        // 3. Can view monitor
        $this->actingAs($jefatura)
            ->get(route('salidas.monitor'))
            ->assertStatus(200)
            ->assertSee('Control de Pasillo Activo')
            ->assertSee('Regresar alumno');

        // 4. Can return student in monitor
        $responseReturn = $this->actingAs($jefatura)
            ->patchJson(route('salidas.update', $passId), [
                'source' => 'monitor',
            ]);
        $responseReturn->assertStatus(200);

        // 5. Can access history
        $this->actingAs($jefatura)
            ->get(route('salidas.history'))
            ->assertStatus(200);
    }

    public function test_profesor_cannot_return_in_monitor_and_cannot_view_history(): void
    {
        $teacher1 = User::factory()->create();
        $teacher1->assignRole('profesor');

        $teacher2 = User::factory()->create();
        $teacher2->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher2->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        // Teacher 1 attempts to return in monitor
        $this->actingAs($teacher1)
            ->patchJson(route('salidas.update', $pass->id), ['source' => 'monitor'])
            ->assertStatus(403);

        // Teacher 1 attempts to access history
        $this->actingAs($teacher1)
            ->get(route('salidas.history'))
            ->assertStatus(403);

        // In classroom manager, history icon is hidden for teacher
        $this->actingAs($teacher1)
            ->get(route('salidas.index'))
            ->assertStatus(200)
            ->assertDontSee('title="Historial"', false);
    }

    public function test_conserje_can_only_view_monitor(): void
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

        // 1. Cannot access classroom dashboard (redirects to monitor)
        $this->actingAs($conserje)
            ->get(route('salidas.index'))
            ->assertRedirect(route('salidas.monitor'));

        // 2. Can view monitor in read-only mode
        $this->actingAs($conserje)
            ->get(route('salidas.monitor'))
            ->assertStatus(200)
            ->assertSee('Modo Supervisión (Solo Lectura)')
            ->assertDontSee('Volver al Gestor')
            ->assertDontSee('Regresar alumno');

        // 3. Cannot return students in monitor
        $this->actingAs($conserje)
            ->patchJson(route('salidas.update', $pass->id), ['source' => 'monitor'])
            ->assertStatus(403);

        // 4. Cannot access history
        $this->actingAs($conserje)
            ->get(route('salidas.history'))
            ->assertStatus(403);
    }

    public function test_curso_activo_role_controls_school_year_selector_visibility(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Teacher does NOT see CURSO ACTIVO selector by default
        $responseTeacher = $this->actingAs($teacher)->get(route('salidas.index'));
        $responseTeacher->assertStatus(200);
        $responseTeacher->assertDontSee('CURSO ACTIVO');

        // Assign curso-activo role to teacher
        $teacher->assignRole('curso-activo');
        $responseTeacherWithRole = $this->actingAs($teacher)->get(route('salidas.index'));
        $responseTeacherWithRole->assertStatus(200);
        $responseTeacherWithRole->assertSee('CURSO ACTIVO');

        // Admin always sees CURSO ACTIVO selector
        $responseAdmin = $this->actingAs($admin)->get(route('salidas.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('CURSO ACTIVO');
    }

    public function test_centro_and_alumnos_modules_are_hidden_for_profesor(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $responseTeacher = $this->actingAs($teacher)->get(route('salidas.index'));
        $responseTeacher->assertStatus(200);

        // Submenus for Centro and Alumnos are completely hidden for Profesor
        $responseTeacher->assertDontSee('<span>Centro</span>', false);
        $responseTeacher->assertDontSee('<span>Alumnos y Grupos</span>', false);
        $responseTeacher->assertDontSee('<span>Grupos</span>', false);
        $responseTeacher->assertDontSee('<span>Alumnos</span>', false);
        $responseTeacher->assertDontSee('<span>Profesores</span>', false);
        $responseTeacher->assertDontSee('<span>Cursos</span>', false);
        $responseTeacher->assertDontSee('<span>Zonas</span>', false);

        // Admin can see both
        $responseAdmin = $this->actingAs($admin)->get(route('salidas.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('<span>Centro</span>', false);
        $responseAdmin->assertSee('<span>Alumnos y Grupos</span>', false);
        $responseAdmin->assertSee('<span>Grupos</span>', false);
        $responseAdmin->assertSee('<span>Alumnos</span>', false);
    }
}

