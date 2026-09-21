<?php

namespace Tests\Feature;

use App\Models\Ausencia;
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

class GuardiaEquityTieBreakerTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacherA;
    protected User $teacherB;
    protected User $teacherC;
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

        $this->teacherA = User::factory()->create(['name' => 'Profesor A']);
        $this->teacherB = User::factory()->create(['name' => 'Profesor B']);
        $this->teacherC = User::factory()->create(['name' => 'Profesor C']);

        $this->teacherA->assignRole('profesor');
        $this->teacherB->assignRole('profesor');
        $this->teacherC->assignRole('profesor');

        $this->template = ScheduleTemplate::create([
            'name' => 'Plantilla Base',
            'is_active' => true,
        ]);

        $this->timeSlot = TimeSlot::create([
            'schedule_template_id' => $this->template->id,
            'name' => '2ª Hora',
            'start_time' => '09:25:00',
            'end_time' => '10:20:00',
            'order' => 2,
        ]);

        // Assign all 3 teachers to guard duty on Tuesday (day 2)
        foreach ([$this->teacherA, $this->teacherB, $this->teacherC] as $teacher) {
            $sched = UserSchedule::create([
                'user_id' => $teacher->id,
                'school_year_id' => 1,
                'schedule_template_id' => $this->template->id,
            ]);
            ScheduleSelection::create([
                'user_schedule_id' => $sched->id,
                'time_slot_id' => $this->timeSlot->id,
                'day' => '2',
                'value' => 'Guardia',
            ]);
        }
    }

    /** @test */
    public function equity_sorts_by_hours_and_tiebreaks_by_oldest_guardia_date()
    {
        // Teacher A and Teacher B both have 1 covered guardia
        // Teacher A covered it on 2026-09-01 (older)
        Ausencia::create([
            'user_id' => User::factory()->create()->id,
            'fecha' => '2026-09-01',
            'time_slot_id' => $this->timeSlot->id,
            'guardia_user_id' => $this->teacherA->id,
            'guardia_confirmed_at' => '2026-09-01 10:00:00',
            'tarea' => 'Tarea previa A',
        ]);

        // Teacher B covered it on 2026-09-15 (more recent)
        Ausencia::create([
            'user_id' => User::factory()->create()->id,
            'fecha' => '2026-09-15',
            'time_slot_id' => $this->timeSlot->id,
            'guardia_user_id' => $this->teacherB->id,
            'guardia_confirmed_at' => '2026-09-15 10:00:00',
            'tarea' => 'Tarea previa B',
        ]);

        // Teacher C has 2 covered guardias
        Ausencia::create([
            'user_id' => User::factory()->create()->id,
            'fecha' => '2026-09-02',
            'time_slot_id' => $this->timeSlot->id,
            'guardia_user_id' => $this->teacherC->id,
            'guardia_confirmed_at' => '2026-09-02 10:00:00',
            'tarea' => 'Tarea previa C1',
        ]);
        Ausencia::create([
            'user_id' => User::factory()->create()->id,
            'fecha' => '2026-09-08',
            'time_slot_id' => $this->timeSlot->id,
            'guardia_user_id' => $this->teacherC->id,
            'guardia_confirmed_at' => '2026-09-08 10:00:00',
            'tarea' => 'Tarea previa C2',
        ]);

        $service = app(GuardiaEquityService::class);
        $date = '2026-09-22'; // Tuesday
        $available = $service->getAvailableGuardiasForSlot($date, $this->timeSlot->id);

        // Teacher A and B have 1 guardia; Teacher C has 2 guardias
        // Teacher A should be first (older date 2026-09-01 vs 2026-09-15) and marked recommended!
        $this->assertEquals($this->teacherA->id, $available->values()[0]['user_id']);
        $this->assertTrue($available->values()[0]['is_recommended']);

        // Teacher B should be second
        $this->assertEquals($this->teacherB->id, $available->values()[1]['user_id']);
        $this->assertFalse($available->values()[1]['is_recommended']);

        // Teacher C should be third (2 guardias)
        $this->assertEquals($this->teacherC->id, $available->values()[2]['user_id']);
        $this->assertFalse($available->values()[2]['is_recommended']);
    }

    /** @test */
    public function teacher_with_absence_at_same_slot_is_automatically_excluded()
    {
        $date = '2026-09-22'; // Tuesday

        // Teacher A reports absence for this date & slot
        Ausencia::create([
            'user_id' => $this->teacherA->id,
            'fecha' => $date,
            'time_slot_id' => $this->timeSlot->id,
            'tarea' => 'Médico',
        ]);

        $service = app(GuardiaEquityService::class);
        $available = $service->getAvailableGuardiasForSlot($date, $this->timeSlot->id);

        // Teacher A is excluded
        $this->assertFalse($available->contains('user_id', $this->teacherA->id));

        // Teacher B and C are present
        $this->assertTrue($available->contains('user_id', $this->teacherB->id));
        $this->assertTrue($available->contains('user_id', $this->teacherC->id));

        // First available (Teacher B) is now recommended
        $this->assertTrue($available->first()['is_recommended']);
    }
}
