<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Events\AusenciaRegistradaEvent;
use App\Exceptions\AusenciaOperationException;
use App\Models\Aula;
use App\Models\Ausencia;
use App\Models\Group;
use App\Models\ScheduleSelection;
use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\UserSchedule;
use App\Services\GuardiaEquityService;
use App\Services\Guardias\AusenciaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AusenciaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AusenciaService $service;
    protected User $profesor;
    protected User $directivo;
    protected TimeSlot $futureSlot;
    protected TimeSlot $pastSlot;
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

        $this->profesor = User::factory()->create(['name' => 'Profesor Titular']);
        $this->profesor->assignRole('profesor');

        $this->directivo = User::factory()->create(['name' => 'Jefe de Estudios']);
        $this->directivo->assignRole('directiva');

        $template = ScheduleTemplate::create(['name' => 'Plantilla General', 'is_active' => true]);

        $this->futureSlot = TimeSlot::create([
            'schedule_template_id' => $template->id,
            'name' => 'Hora Futura',
            'start_time' => '23:00:00',
            'end_time' => '23:55:00',
            'order' => 6,
        ]);

        $this->pastSlot = TimeSlot::create([
            'schedule_template_id' => $template->id,
            'name' => 'Hora Pasada',
            'start_time' => '07:00:00',
            'end_time' => '07:55:00',
            'order' => 1,
        ]);

        $this->group = Group::create(['course' => '2º Bach', 'name' => 'A', 'school_year_id' => 1]);
        $this->aula = Aula::create(['nombre' => 'Aula 101', 'is_aula' => true]);

        $this->service = new AusenciaService(new GuardiaEquityService());
    }

    /** @test */
    public function registrar_ausencia_creates_record_and_dispatches_event(): void
    {
        Event::fake([AusenciaRegistradaEvent::class]);

        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $datos = [
            'user_id' => $this->profesor->id,
            'fecha' => $tomorrow,
            'time_slot_id' => $this->futureSlot->id,
            'group_id' => $this->group->id,
            'zona_id' => $this->aula->id,
            'tarea' => 'Realizar lectura y resumen del tema 4.',
        ];

        $ausencia = $this->service->registrarAusencia($datos);

        $this->assertInstanceOf(Ausencia::class, $ausencia);
        $this->assertDatabaseHas('ausencias', [
            'id' => $ausencia->id,
            'user_id' => $this->profesor->id,
            'time_slot_id' => $this->futureSlot->id,
            'tarea' => 'Realizar lectura y resumen del tema 4.',
        ]);

        Event::assertDispatched(AusenciaRegistradaEvent::class, function (AusenciaRegistradaEvent $event) use ($ausencia) {
            return $event->ausencia->id === $ausencia->id;
        });
    }

    /** @test */
    public function registrar_ausencia_marks_es_guardia_if_teacher_had_guard_duty(): void
    {
        Event::fake([AusenciaRegistradaEvent::class]);

        $tomorrow = Carbon::tomorrow();
        $tomorrowStr = $tomorrow->format('Y-m-d');
        $dayNumber = $tomorrow->dayOfWeekIso; // 1-7

        // Asignar al docente una guardia en este tramo y día en su horario
        $userSchedule = UserSchedule::create([
            'user_id' => $this->profesor->id,
            'school_year_id' => 1,
            'schedule_template_id' => $this->futureSlot->schedule_template_id,
        ]);

        ScheduleSelection::create([
            'user_schedule_id' => $userSchedule->id,
            'time_slot_id' => $this->futureSlot->id,
            'day' => (string) $dayNumber,
            'value' => 'Guardia',
        ]);

        $datos = [
            'user_id' => $this->profesor->id,
            'fecha' => $tomorrowStr,
            'time_slot_id' => $this->futureSlot->id,
            'tarea' => 'Indisposición en hora de guardia',
        ];

        $ausencia = $this->service->registrarAusencia($datos);

        // Debe haberse marcado automáticamente como ausencia de guardia
        $this->assertTrue($ausencia->es_guardia);
        $this->assertDatabaseHas('ausencias', [
            'id' => $ausencia->id,
            'es_guardia' => true,
        ]);
    }

    /** @test */
    public function eliminar_ausencia_throws_exception_if_not_authorized(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $otroProfesor = User::factory()->create(['name' => 'Otro Docente']);
        $otroProfesor->assignRole('profesor');

        $ausencia = Ausencia::create([
            'user_id' => $otroProfesor->id,
            'fecha' => $tomorrow,
            'time_slot_id' => $this->futureSlot->id,
            'tarea' => 'Tarea de otro',
        ]);

        $this->expectException(AusenciaOperationException::class);
        $this->expectExceptionMessage('No tienes autorización para eliminar una ausencia que pertenece a otro docente.');

        $this->service->eliminarAusencia($ausencia->id, $this->profesor);
    }

    /** @test */
    public function eliminar_ausencia_throws_exception_if_slot_already_started(): void
    {
        $today = now()->format('Y-m-d');

        // Ausencia en un tramo pasado de hoy
        $ausencia = Ausencia::create([
            'user_id' => $this->profesor->id,
            'fecha' => $today,
            'time_slot_id' => $this->pastSlot->id,
            'tarea' => 'Falta matinal',
        ]);

        $this->expectException(AusenciaOperationException::class);
        $this->expectExceptionMessage('No es posible eliminar la ausencia porque la hora lectiva ya ha dado comienzo.');

        $this->service->eliminarAusencia($ausencia->id, $this->profesor);
    }

    /** @test */
    public function eliminar_ausencia_throws_exception_if_covered_unless_directiva(): void
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $sustituto = User::factory()->create(['name' => 'Sustituto']);

        $ausencia = Ausencia::create([
            'user_id' => $this->profesor->id,
            'fecha' => $tomorrow,
            'time_slot_id' => $this->futureSlot->id,
            'tarea' => 'Guardia cubierta',
            'guardia_user_id' => $sustituto->id,
            'guardia_confirmed_at' => now(),
        ]);

        // 1. Docente regular no puede
        try {
            $this->service->eliminarAusencia($ausencia->id, $this->profesor);
            $this->fail('Se esperaba AusenciaOperationException');
        } catch (AusenciaOperationException $e) {
            $this->assertStringContainsString('ya ha sido cubierta o firmada', $e->getMessage());
        }

        // 2. Directivo SÍ puede y libera al sustituto
        $result = $this->service->eliminarAusencia($ausencia->id, $this->directivo);
        $this->assertTrue($result);
        $this->assertDatabaseMissing('ausencias', ['id' => $ausencia->id]);
    }
}
