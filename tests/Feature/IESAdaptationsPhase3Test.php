<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Group;
use App\Models\HallPass;
use App\Models\Modulo;
use App\Models\SchoolYear;
use App\Models\Sesion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IESAdaptationsPhase3Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher;
    protected Group $group;
    protected Modulo $modulo;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'profesor']);
        Role::firstOrCreate(['name' => 'alumno']);

        $year = SchoolYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');

        $this->teacher = User::factory()->create(['email' => 'profesor@test.com']);
        $this->teacher->assignRole('profesor');

        $this->group = Group::create([
            'course' => '1º ESO',
            'name' => 'A',
            'school_year_id' => $year->id,
        ]);

        $this->modulo = Modulo::create([
            'codigo' => 'MAT-01',
            'nombre' => 'Matemáticas',
            'group_id' => $this->group->id,
        ]);
        $this->modulo->profesores()->attach($this->teacher->id);
    }

    /** @test */
    public function absentism_alert_triggers_when_unexcused_absences_reach_15_percent()
    {
        $student = User::factory()->create(['group_id' => $this->group->id]);
        $student->assignRole('alumno');

        // Create 10 sessions for this module
        for ($i = 0; $i < 10; $i++) {
            $sesion = Sesion::create([
                'fecha' => now()->subDays($i)->format('Y-m-d'),
                'modulo_id' => $this->modulo->id,
                'group_id' => $this->group->id,
                'profesor_id' => $this->teacher->id,
                'contenidos' => 'Tema ' . ($i + 1),
            ]);

            // Mark 2 absences (20% absentism rate)
            if ($i < 2) {
                Asistencia::create([
                    'sesion_id' => $sesion->id,
                    'user_id' => $student->id,
                    'estado' => 'falta',
                ]);
            } else {
                Asistencia::create([
                    'sesion_id' => $sesion->id,
                    'user_id' => $student->id,
                    'estado' => 'presente',
                ]);
            }
        }

        $response = $this->actingAs($this->teacher)
            ->withSession(['active_school_year_id' => $this->group->school_year_id])
            ->get(route('cuaderno.index', ['modulo_id' => $this->modulo->id]));

        $response->assertStatus(200);
        $response->assertSee('20%');
        $response->assertSee('Riesgo de pérdida de evaluación continua');
    }

    /** @test */
    public function hall_pass_prevents_exceeding_group_limit()
    {
        $student1 = User::factory()->create(['group_id' => $this->group->id]);
        $student1->assignRole('alumno');

        $student2 = User::factory()->create(['group_id' => $this->group->id]);
        $student2->assignRole('alumno');

        $student3 = User::factory()->create(['group_id' => $this->group->id]);
        $student3->assignRole('alumno');

        // Active pass 1 for student 1
        HallPass::create([
            'user_id' => $student1->id,
            'teacher_id' => $this->teacher->id,
            'reason' => 'Baño',
            'date' => now()->format('Y-m-d'),
            'start_time' => now(),
        ]);

        // Active pass 2 for student 2
        HallPass::create([
            'user_id' => $student2->id,
            'teacher_id' => $this->teacher->id,
            'reason' => 'Secretaría',
            'date' => now()->format('Y-m-d'),
            'start_time' => now(),
        ]);

        // Attempting pass 3 for student 3 in the same group
        $response = $this->actingAs($this->teacher)->post(route('salidas.store'), [
            'student_id' => $student3->id,
            'reason' => 'Enfermería',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Ya hay 2 alumnos fuera de clase en este grupo. Finaliza un pase antes de autorizar otro.']);
    }
}
