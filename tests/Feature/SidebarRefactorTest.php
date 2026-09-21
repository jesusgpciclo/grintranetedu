<?php

namespace Tests\Feature;

use App\Models\Ausencia;
use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SidebarRefactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $profesor;
    protected User $directivo;
    protected User $admin;
    protected TimeSlot $timeSlot;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        \Illuminate\Support\Facades\DB::table('school_years')->updateOrInsert(
            ['id' => 1],
            [
                'name' => '2026/2027',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        Role::firstOrCreate(['name' => 'profesor']);
        Role::firstOrCreate(['name' => 'directiva']);
        Role::firstOrCreate(['name' => 'admin']);

        $this->profesor = User::factory()->create(['name' => 'Docente Claustro']);
        $this->profesor->assignRole('profesor');

        $this->directivo = User::factory()->create(['name' => 'Jefe de Estudios']);
        $this->directivo->assignRole('directiva');

        $this->admin = User::factory()->create(['name' => 'Administrador']);
        $this->admin->assignRole('admin');

        $template = ScheduleTemplate::create(['name' => 'Plantilla Base', 'is_active' => true]);
        $this->timeSlot = TimeSlot::create([
            'schedule_template_id' => $template->id,
            'name' => '1ª Hora',
            'start_time' => '08:30:00',
            'end_time' => '09:25:00',
            'order' => 1,
        ]);
    }

    /** @test */
    public function teacher_sees_mi_dia_a_dia_and_does_not_see_gestion_y_direccion()
    {
        $response = $this->actingAs($this->profesor)->get(route('dashboard'));

        $response->assertStatus(200);

        // Sección 1: Mi Día a Día visible
        $response->assertSee('Mi Día a Día');
        $response->assertSee('Parte de Guardia');
        $response->assertSee('Mis Guardias Asignadas');
        $response->assertSee('Mis Ausencias');
        $response->assertSee('Mi Horario de Guardias');

        // Sección 2: Gestión y Dirección NO visible para profesor ordinario
        $response->assertDontSee('Gestión y Dirección');
        $response->assertDontSee('Cuadrante Semanal Completo');
        $response->assertDontSee('Control de Justificaciones');
        $response->assertDontSee('Estadísticas y Equidad');
    }

    /** @test */
    public function directivo_sees_both_mi_dia_a_dia_and_gestion_y_direccion()
    {
        $response = $this->actingAs($this->directivo)->get(route('dashboard'));

        $response->assertStatus(200);

        // Sección 1: Mi Día a Día
        $response->assertSee('Mi Día a Día');
        $response->assertSee('Parte de Guardia');

        // Sección 2: Gestión y Dirección visible
        $response->assertSee('Gestión y Dirección');
        $response->assertSee('Cuadrante Semanal Completo');
        $response->assertSee('Control de Justificaciones');
        $response->assertSee('Estadísticas y Equidad');
        $response->assertSee('Configuración de Tramos y Aulas');
    }

    /** @test */
    public function admin_sees_gestion_y_direccion()
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Gestión y Dirección');
        $response->assertSee('Cuadrante Semanal Completo');
    }

    /** @test */
    public function mis_guardias_asignadas_badge_shows_today_assigned_count()
    {
        $today = now()->format('Y-m-d');

        // Assign 2 guardias for this teacher today
        Ausencia::create([
            'user_id' => User::factory()->create()->id,
            'fecha' => $today,
            'time_slot_id' => $this->timeSlot->id,
            'guardia_user_id' => $this->profesor->id,
            'guardia_confirmed_at' => now(),
            'tarea' => 'Guardia 1',
        ]);

        Ausencia::create([
            'user_id' => User::factory()->create()->id,
            'fecha' => $today,
            'time_slot_id' => $this->timeSlot->id,
            'guardia_user_id' => $this->profesor->id,
            'guardia_confirmed_at' => now(),
            'tarea' => 'Guardia 2',
        ]);

        $response = $this->actingAs($this->profesor)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Mis Guardias Asignadas');
        $response->assertSee('2');
    }
}
