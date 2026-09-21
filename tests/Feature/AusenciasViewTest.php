<?php

namespace Tests\Feature;

use App\Models\Ausencia;
use App\Models\Group;
use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\Aula;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AusenciasViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $profesor;
    protected TimeSlot $timeSlot1;
    protected TimeSlot $timeSlot2;
    protected Group $group;
    protected Aula $aula;

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

        $this->profesor = User::factory()->create([
            'name' => 'Carlos',
            'last_name' => 'Gómez',
            'departamento' => 'Matemáticas',
        ]);
        $this->profesor->assignRole('profesor');

        $template = ScheduleTemplate::create(['name' => 'Plantilla Base', 'is_active' => true]);
        
        $this->timeSlot1 = TimeSlot::create([
            'schedule_template_id' => $template->id,
            'name' => '1ª Hora',
            'start_time' => '08:30:00',
            'end_time' => '09:25:00',
            'order' => 1,
        ]);

        $this->timeSlot2 = TimeSlot::create([
            'schedule_template_id' => $template->id,
            'name' => '2ª Hora',
            'start_time' => '09:25:00',
            'end_time' => '10:20:00',
            'order' => 2,
        ]);

        $this->group = Group::create([
            'course' => '3º ESO',
            'name' => 'B',
            'school_year_id' => 1,
        ]);

        $this->aula = Aula::create([
            'nombre' => 'Aula 12',
            'is_aula' => true,
            'dificultad' => 1,
        ]);
    }

    /** @test */
    public function ausencias_index_view_renders_cards_and_badges_properly()
    {
        $today = now()->format('Y-m-d');

        // Ausencia 1: Sin cubrir con enlace a classroom
        $ausencia1 = Ausencia::create([
            'user_id' => $this->profesor->id,
            'fecha' => $today,
            'time_slot_id' => $this->timeSlot1->id,
            'group_id' => $this->group->id,
            'zona_id' => $this->aula->id,
            'tarea' => 'Realizar los ejercicios de la página 45 del libro de matemáticas.',
            'enlace_tarea' => 'https://classroom.google.com/c/math101',
            'justificada' => false,
        ]);

        $response = $this->actingAs($this->profesor)->get(route('ausencias.index', ['date' => $today]));

        $response->assertStatus(200);

        // Verifica nombre de grupo y aula
        $response->assertSee('3º ESO B');
        $response->assertSee('Aula 12');

        // Verifica docente y tarea
        $response->assertSee('Carlos Gómez');
        $response->assertSee('Realizar los ejercicios de la página 45');

        // Verifica badges requeridos
        $response->assertSee('Sin cubrir');
        $response->assertSee('[🔗 Classroom]');
        $response->assertSee('Justif. Pendiente');

        // Verifica botón punteado "Añadir en este tramo" en el slot con ausencias
        $response->assertSee('Añadir en este tramo');

        // Verifica fila colapsada dashed en el slot vacío
        $response->assertSee('Sin ausencias en este tramo');
    }

    /** @test */
    public function ausencias_card_shows_guardia_confirmada_and_justificada_badges()
    {
        $today = now()->format('Y-m-d');
        $guardiaDocente = User::factory()->create(['name' => 'Ana Guardia']);

        // Ausencia con guardia confirmada y justificada
        Ausencia::create([
            'user_id' => $this->profesor->id,
            'fecha' => $today,
            'time_slot_id' => $this->timeSlot1->id,
            'group_id' => $this->group->id,
            'zona_id' => $this->aula->id,
            'tarea' => 'Lectura comprensiva de historia.',
            'enlace_tarea' => 'https://centro.es/material.pdf',
            'guardia_user_id' => $guardiaDocente->id,
            'guardia_confirmed_at' => now(),
            'justificada' => true,
        ]);

        $response = $this->actingAs($this->profesor)->get(route('ausencias.index', ['date' => $today]));

        $response->assertStatus(200);
        $response->assertSee('Guardia Confirmada');
        $response->assertSee('Justificada');
        $response->assertSee('[📎 Archivo]');
    }
}
