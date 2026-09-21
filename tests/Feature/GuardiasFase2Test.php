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

class GuardiasFase2Test extends TestCase
{
    use RefreshDatabase;

    protected User $teacher1;
    protected User $teacher2;
    protected User $directivo;
    protected TimeSlot $timeSlot;
    protected ScheduleTemplate $template;

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
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'directiva']);

        $this->teacher1 = User::factory()->create([
            'name' => 'Profesor Uno',
            'email' => 'profe1@example.com',
            'departamento' => 'Matemáticas',
        ]);
        $this->teacher1->assignRole('profesor');

        $this->teacher2 = User::factory()->create([
            'name' => 'Profesor Dos',
            'email' => 'profe2@example.com',
            'departamento' => 'Lengua',
        ]);
        $this->teacher2->assignRole('profesor');

        $this->directivo = User::factory()->create([
            'name' => 'Director Centro',
            'email' => 'director@example.com',
            'departamento' => 'Dirección',
        ]);
        $this->directivo->assignRole('directiva');

        $this->template = ScheduleTemplate::create([
            'name' => 'Plantilla General',
            'is_active' => true,
        ]);

        $this->timeSlot = TimeSlot::create([
            'schedule_template_id' => $this->template->id,
            'name' => '2ª Hora',
            'start_time' => '09:25:00',
            'end_time' => '10:20:00',
            'order' => 2,
        ]);
    }

    /** @test */
    public function teacher_can_toggle_their_own_guardia_slot_on_and_off()
    {
        $this->actingAs($this->teacher1);

        // 1. Toggle on (Tuesday = day 2)
        $responseOn = $this->postJson(route('guardias.toggle-hora'), [
            'time_slot_id' => $this->timeSlot->id,
            'day' => 2,
        ]);

        $responseOn->assertOk();
        $responseOn->assertJson([
            'success' => true,
            'is_guardia' => true,
        ]);

        $this->assertDatabaseHas('schedule_selections', [
            'time_slot_id' => $this->timeSlot->id,
            'day' => '2',
            'value' => 'Guardia',
        ]);

        // 2. Toggle off
        $responseOff = $this->postJson(route('guardias.toggle-hora'), [
            'time_slot_id' => $this->timeSlot->id,
            'day' => 2,
        ]);

        $responseOff->assertOk();
        $responseOff->assertJson([
            'success' => true,
            'is_guardia' => false,
        ]);

        $this->assertDatabaseMissing('schedule_selections', [
            'time_slot_id' => $this->timeSlot->id,
            'day' => '2',
            'value' => 'Guardia',
        ]);
    }

    /** @test */
    public function regular_teacher_cannot_toggle_other_teachers_guardia()
    {
        $this->actingAs($this->teacher1);

        $response = $this->postJson(route('guardias.toggle-hora'), [
            'time_slot_id' => $this->timeSlot->id,
            'day' => 1,
            'user_id' => $this->teacher2->id,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function directiva_can_toggle_any_teachers_guardia()
    {
        $this->actingAs($this->directivo);

        $response = $this->postJson(route('guardias.toggle-hora'), [
            'time_slot_id' => $this->timeSlot->id,
            'day' => 3,
            'user_id' => $this->teacher2->id,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_guardia' => true,
        ]);

        $this->assertDatabaseHas('schedule_selections', [
            'time_slot_id' => $this->timeSlot->id,
            'day' => '3',
            'value' => 'Guardia',
        ]);
    }

    /** @test */
    public function mis_horas_view_renders_grid_and_shows_marked_guardias()
    {
        $this->actingAs($this->teacher1);

        // Mark a guardia on Thursday (day 4)
        $this->postJson(route('guardias.toggle-hora'), [
            'time_slot_id' => $this->timeSlot->id,
            'day' => 4,
        ]);

        $response = $this->get(route('guardias.mis-horas'));
        $response->assertOk();
        $response->assertSee('Horario de guardias con alumnos');
        $response->assertSee('data-is-guardia="1"', false);
    }
}
