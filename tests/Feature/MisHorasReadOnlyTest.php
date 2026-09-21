<?php

namespace Tests\Feature;

use App\Models\Guardia;
use App\Models\ScheduleSelection;
use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\UserSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MisHorasReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected User $profesor;
    protected ScheduleTemplate $template;
    protected TimeSlot $slot1;
    protected TimeSlot $slot2;

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

        $this->profesor = User::factory()->create(['name' => 'Elena Martínez']);
        $this->profesor->assignRole('profesor');

        $this->template = ScheduleTemplate::create([
            'name' => 'Plantilla Oficial',
            'is_active' => true,
        ]);

        $this->slot1 = TimeSlot::create([
            'schedule_template_id' => $this->template->id,
            'name' => '1ª Hora',
            'start_time' => '08:15:00',
            'end_time' => '09:15:00',
            'order' => 1,
        ]);

        $this->slot2 = TimeSlot::create([
            'schedule_template_id' => $this->template->id,
            'name' => '2ª Hora',
            'start_time' => '09:15:00',
            'end_time' => '10:15:00',
            'order' => 2,
        ]);
    }

    /** @test */
    public function mis_horas_screen_is_read_only_without_edit_buttons()
    {
        $this->actingAs($this->profesor);

        $response = $this->get(route('guardias.mis-horas'));

        $response->assertStatus(200);

        // Verifica que no existan botones interactivos de toggle ni scripts de modificación
        $response->assertDontSee('guardia-toggle-btn');
        $response->assertDontSee('toggleGuardiaSlot');
        $response->assertDontSee('Autoguardado instantáneo');
        $response->assertDontSee('Basta con pulsar en el símbolo (+)');

        // Verifica el banner informativo de solo lectura y sincronización
        $response->assertSee('Horario sincronizado de solo lectura');
        $response->assertSee('horario personal');
    }

    /** @test */
    public function mis_horas_shows_guardias_derived_from_personal_schedule()
    {
        $this->actingAs($this->profesor);

        // Crear horario personal del profesor con una guardia el lunes (día 1) a 1ª hora
        $userSchedule = UserSchedule::create([
            'user_id' => $this->profesor->id,
            'school_year_id' => 1,
            'schedule_template_id' => $this->template->id,
        ]);

        $guardia = Guardia::create(['name' => 'Guardia de Patio / Recreo']);

        ScheduleSelection::create([
            'user_schedule_id' => $userSchedule->id,
            'time_slot_id' => $this->slot1->id,
            'day' => '1',
            'guardia_id' => $guardia->id,
            'type' => 'guardia',
            'value' => 'Guardia: ' . $guardia->name,
        ]);

        $response = $this->get(route('guardias.mis-horas'));

        $response->assertStatus(200);

        // Debe renderizar la guardia en modo solo lectura con el nombre extraído del horario personal
        $response->assertSee('data-is-guardia="1"', false);
        $response->assertSee('Guardia de Patio / Recreo');

        // En las celdas sin guardia debe mostrarse el guion de solo lectura
        $response->assertSee('—');
    }
}
