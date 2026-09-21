<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\Ausencia;
use App\Models\Group;
use App\Models\ScheduleSelection;
use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\UserSchedule;
use App\Services\GuardiaEquityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuardiasFase1Test extends TestCase
{
    use RefreshDatabase;

    protected User $teacher1;
    protected User $teacher2;
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

        $this->template = ScheduleTemplate::create([
            'name' => 'Plantilla General',
            'is_active' => true,
        ]);

        $this->timeSlot = TimeSlot::create([
            'schedule_template_id' => $this->template->id,
            'name' => '1ª Hora',
            'start_time' => '08:30:00',
            'end_time' => '09:25:00',
            'order' => 1,
        ]);
    }

    /** @test */
    public function any_teacher_can_report_absence_for_colleague_and_redirect_to_parte()
    {
        $this->actingAs($this->teacher1);

        $response = $this->post(route('ausencias.store'), [
            'fecha' => '2026-09-15',
            'time_slot_id' => $this->timeSlot->id,
            'user_id' => $this->teacher2->id,
            'tarea' => 'Ejercicios de sintaxis en el libro pág 24',
            'redirect_to' => 'parte',
        ]);

        $response->assertRedirect(route('guardias.parte', ['date' => '2026-09-15']));

        $this->assertDatabaseHas('ausencias', [
            'user_id' => $this->teacher2->id,
            'fecha' => '2026-09-15 00:00:00',
            'time_slot_id' => $this->timeSlot->id,
            'tarea' => 'Ejercicios de sintaxis en el libro pág 24',
        ]);
    }

    /** @test */
    public function teacher_can_delete_their_future_absence_from_parte()
    {
        $this->actingAs($this->teacher1);

        $futureSlot = TimeSlot::create([
            'schedule_template_id' => $this->template->id,
            'name' => '6ª Hora Futura',
            'start_time' => '23:30:00',
            'end_time' => '23:59:00',
            'order' => 6,
        ]);

        $ausencia = Ausencia::create([
            'user_id' => $this->teacher1->id,
            'fecha' => Carbon::tomorrow()->format('Y-m-d'),
            'time_slot_id' => $futureSlot->id,
            'tarea' => 'Tarea a cancelar',
            'justificada' => false,
        ]);

        $response = $this->delete(route('ausencias.destroy', $ausencia));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('ausencias', [
            'id' => $ausencia->id,
        ]);
    }

    /** @test */
    public function reagrupaciones_count_as_one_guardia_hour_with_accumulated_difficulty()
    {
        $group1 = Group::create(['name' => '1º ESO A', 'course' => '1º ESO', 'dificultad' => 2]);
        $group2 = Group::create(['name' => '1º ESO B', 'course' => '1º ESO', 'dificultad' => 3]);

        $date = '2026-09-20';

        // Teacher 1 covers two groups in the same slot (reagrupación)
        Ausencia::create([
            'user_id' => $this->teacher2->id,
            'fecha' => $date,
            'time_slot_id' => $this->timeSlot->id,
            'group_id' => $group1->id,
            'guardia_user_id' => $this->teacher1->id,
            'guardia_confirmed_at' => now(),
            'tarea' => 'Tarea grupo 1',
        ]);

        Ausencia::create([
            'user_id' => $this->teacher2->id,
            'fecha' => $date,
            'time_slot_id' => $this->timeSlot->id,
            'group_id' => $group2->id,
            'guardia_user_id' => $this->teacher1->id,
            'guardia_confirmed_at' => now(),
            'tarea' => 'Tarea grupo 2',
        ]);

        // Schedule teacher 1 on duty on Monday slot
        $userSched = UserSchedule::create([
            'user_id' => $this->teacher1->id,
            'school_year_id' => 1,
            'schedule_template_id' => $this->template->id,
        ]);

        ScheduleSelection::create([
            'user_schedule_id' => $userSched->id,
            'time_slot_id' => $this->timeSlot->id,
            'day' => '1',
            'value' => 'Guardia',
        ]);

        $equityService = app(GuardiaEquityService::class);
        $available = $equityService->getAvailableGuardiasForSlot('2026-09-21', $this->timeSlot->id); // Next monday

        $t1Data = $available->firstWhere('user_id', $this->teacher1->id);

        $this->assertNotNull($t1Data);
        // Only 1 guardia hour counted despite 2 covered groups in same slot
        $this->assertEquals(1, $t1Data['guardias_count']);
        // Puntuación should be 2 + 3 = 5 points
        $this->assertEquals(5, $t1Data['puntuacion']);
    }

    /** @test */
    public function absent_guard_teachers_are_identified_for_red_badge()
    {
        $date = '2026-09-21'; // Monday

        // Teacher 1 has guard duty on Monday slot 1
        $userSched = UserSchedule::create([
            'user_id' => $this->teacher1->id,
            'school_year_id' => 1,
            'schedule_template_id' => $this->template->id,
        ]);

        ScheduleSelection::create([
            'user_schedule_id' => $userSched->id,
            'time_slot_id' => $this->timeSlot->id,
            'day' => '1',
            'value' => 'Guardia',
        ]);

        // Teacher 1 reports absence on that date and slot
        Ausencia::create([
            'user_id' => $this->teacher1->id,
            'fecha' => $date,
            'time_slot_id' => $this->timeSlot->id,
            'es_guardia' => true,
            'tarea' => 'Ausencia guardia',
        ]);

        $equityService = app(GuardiaEquityService::class);
        $available = $equityService->getAvailableGuardiasForSlot($date, $this->timeSlot->id);
        $absents = $equityService->getAbsentGuardiasForSlot($date, $this->timeSlot->id);

        // Teacher 1 is NOT available for selection
        $this->assertFalse($available->contains('user_id', $this->teacher1->id));

        // Teacher 1 IS in absents list to display in red
        $this->assertTrue($absents->contains('user_id', $this->teacher1->id));
        $this->assertTrue($absents->firstWhere('user_id', $this->teacher1->id)['is_absent']);
    }
}
