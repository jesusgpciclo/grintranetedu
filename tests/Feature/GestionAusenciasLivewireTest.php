<?php

namespace Tests\Feature;

use App\Livewire\GestionAusencias;
use App\Models\Aula;
use App\Models\Ausencia;
use App\Models\Group;
use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GestionAusenciasLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $profesor;
    protected User $directivo;
    protected User $admin;
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

        $this->profesor = User::factory()->create(['name' => 'Profesor Juan']);
        $this->profesor->assignRole('profesor');

        $this->directivo = User::factory()->create(['name' => 'Jefe Estudios']);
        $this->directivo->assignRole('directiva');

        $this->admin = User::factory()->create(['name' => 'Admin Sistema']);
        $this->admin->assignRole('admin');

        $template = ScheduleTemplate::create(['name' => 'Base', 'is_active' => true]);

        // Future slot (e.g. 23:00 to 23:55)
        $this->futureSlot = TimeSlot::create([
            'schedule_template_id' => $template->id,
            'name' => 'Última Hora Futura',
            'start_time' => '23:00:00',
            'end_time' => '23:55:00',
            'order' => 10,
        ]);

        // Past slot (e.g. 07:00 to 07:55)
        $this->pastSlot = TimeSlot::create([
            'schedule_template_id' => $template->id,
            'name' => 'Primera Hora Pasada',
            'start_time' => '07:00:00',
            'end_time' => '07:55:00',
            'order' => 1,
        ]);

        $this->group = Group::create(['course' => '1º ESO', 'name' => 'A', 'school_year_id' => 1]);
        $this->aula = Aula::create(['nombre' => 'Aula Magna', 'is_aula' => true]);
    }

    /** @test */
    public function teacher_cannot_delete_covered_absence_but_directivo_can()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $guardiaUser = User::factory()->create(['name' => 'Profesor Sustituto']);

        $ausencia = Ausencia::create([
            'user_id' => $this->profesor->id,
            'fecha' => $tomorrow,
            'time_slot_id' => $this->futureSlot->id,
            'group_id' => $this->group->id,
            'zona_id' => $this->aula->id,
            'tarea' => 'Ejercicios',
            'guardia_user_id' => $guardiaUser->id,
            'guardia_confirmed_at' => now(), // Covered!
        ]);

        // El docente que la creó NO puede borrarla porque ya está cubierta
        $this->assertFalse($ausencia->canBeDeletedBy($this->profesor));
        $this->assertFalse($ausencia->canBeEditedBy($this->profesor));

        // El directivo y admin SÍ pueden borrarla
        $this->assertTrue($ausencia->canBeDeletedBy($this->directivo));
        $this->assertTrue($ausencia->canBeDeletedBy($this->admin));
    }

    /** @test */
    public function teacher_can_delete_own_future_uncovered_absence()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $ausencia = Ausencia::create([
            'user_id' => $this->profesor->id,
            'fecha' => $tomorrow,
            'time_slot_id' => $this->futureSlot->id,
            'group_id' => $this->group->id,
            'zona_id' => $this->aula->id,
            'tarea' => 'Ejercicios pendientes',
        ]);

        $this->assertTrue($ausencia->canBeDeletedBy($this->profesor));
    }

    /** @test */
    public function gestion_ausencias_component_handles_creation_and_deletion()
    {
        $this->actingAs($this->profesor);

        $component = new GestionAusencias();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $component->mount($tomorrow);

        // 1. Abre modal para un tramo
        $component->openCreateModal($this->futureSlot->id);
        $this->assertTrue($component->showCreateModal);
        $this->assertEquals($this->futureSlot->id, $component->time_slot_id);

        // 2. Rellena y guarda
        $component->group_id = $this->group->id;
        $component->zona_id = $this->aula->id;
        $component->tarea = 'Completar examen en Classroom';
        $component->enlace_tarea = 'https://classroom.google.com/c/123';
        $component->saveAusencia();

        $this->assertFalse($component->showCreateModal);

        $this->assertDatabaseHas('ausencias', [
            'user_id' => $this->profesor->id,
            'time_slot_id' => $this->futureSlot->id,
            'tarea' => 'Completar examen en Classroom',
        ]);

        $created = Ausencia::where('user_id', $this->profesor->id)
            ->where('time_slot_id', $this->futureSlot->id)
            ->first();

        // 3. Borrado con confirmación
        $component->confirmDelete($created->id);
        $this->assertTrue($component->showDeleteModal);
        $this->assertEquals($created->id, $component->ausenciaIdToDelete);

        $component->deleteAusencia();
        $this->assertFalse($component->showDeleteModal);
        $this->assertDatabaseMissing('ausencias', ['id' => $created->id]);
    }

    /** @test */
    public function reactive_date_navigation_works_correctly()
    {
        $component = new GestionAusencias();
        $baseDate = '2026-10-15';
        $component->mount($baseDate);

        $component->previousDay();
        $this->assertEquals('2026-10-14', $component->date);

        $component->nextDay();
        $this->assertEquals('2026-10-15', $component->date);

        $component->today();
        $this->assertEquals(now()->format('Y-m-d'), $component->date);
    }
}
