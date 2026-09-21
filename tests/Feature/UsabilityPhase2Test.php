<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\HallPass;
use App\Models\Incidencia;
use App\Models\Modulo;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsabilityPhase2Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected SchoolYear $year1;
    protected SchoolYear $year2;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'alumno']);

        $this->year1 = SchoolYear::create([
            'name' => '2024/2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_active' => false,
        ]);

        $this->year2 = SchoolYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');
    }

    /** @test */
    public function students_are_filtered_by_active_school_year()
    {
        $groupYear1 = Group::create(['course' => '1º ESO', 'name' => 'A', 'school_year_id' => $this->year1->id]);
        $groupYear2 = Group::create(['course' => '2º ESO', 'name' => 'B', 'school_year_id' => $this->year2->id]);

        $studentYear1 = User::factory()->create(['name' => 'JuanAntiguo', 'group_id' => $groupYear1->id]);
        $studentYear1->assignRole('alumno');

        $studentYear2 = User::factory()->create(['name' => 'MariaNuevo', 'group_id' => $groupYear2->id]);
        $studentYear2->assignRole('alumno');

        $response = $this->actingAs($this->admin)
            ->withSession(['active_school_year_id' => $this->year2->id])
            ->get(route('students.index'));

        $response->assertStatus(200);
        $response->assertSee('MariaNuevo');
        $response->assertDontSee('JuanAntiguo');
    }

    /** @test */
    public function sessions_and_modules_respect_active_school_year()
    {
        $groupYear1 = Group::create(['course' => '1º Bach', 'name' => 'A', 'school_year_id' => $this->year1->id]);
        $groupYear2 = Group::create(['course' => '2º Bach', 'name' => 'B', 'school_year_id' => $this->year2->id]);

        Modulo::create(['codigo' => 'BIO-01', 'nombre' => 'BiologiaVieja', 'group_id' => $groupYear1->id]);
        Modulo::create(['codigo' => 'FIS-01', 'nombre' => 'FisicaNueva', 'group_id' => $groupYear2->id]);

        $response = $this->actingAs($this->admin)
            ->withSession(['active_school_year_id' => $this->year2->id])
            ->get(route('sesiones.create'));

        $response->assertStatus(200);
        $response->assertSee('FisicaNueva');
        $response->assertDontSee('BiologiaVieja');
    }

    /** @test */
    public function sidebar_view_composer_injects_badge_counts()
    {
        $teacher = User::factory()->create();
        $group = Group::create(['course' => '1º ESO', 'name' => 'A', 'school_year_id' => $this->year2->id]);
        $student = User::factory()->create(['group_id' => $group->id]);

        // Create an active hall pass
        HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => now()->format('Y-m-d'),
            'start_time' => now(),
        ]);

        // Create an open incident
        Incidencia::create([
            'titulo' => 'Raton no funciona',
            'descripcion' => 'Pila agotada',
            'fecha' => now()->format('Y-m-d'),
            'prioridad' => 'baja',
            'estado' => 'abierta',
            'user_id' => $teacher->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Salidas');
        $response->assertSee('Incidencias');
    }
}
