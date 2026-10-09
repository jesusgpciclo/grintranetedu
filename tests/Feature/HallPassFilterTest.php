<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\HallPass;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HallPassFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        PermissionManagerService::applyDefaultAssignments();
    }

    public function test_can_filter_history_by_single_date(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $teacher = User::factory()->create(['name' => 'Profesor', 'last_name' => 'Prueba']);
        $teacher->assignRole('profesor');

        $group = Group::create(['name' => 'A', 'course' => '1 ESO']);

        $student1 = User::factory()->create(['name' => 'Carlos', 'last_name' => 'Gomez', 'group_id' => $group->id]);
        $student1->assignRole('alumno');

        $student2 = User::factory()->create(['name' => 'Ana', 'last_name' => 'Lopez', 'group_id' => $group->id]);
        $student2->assignRole('alumno');

        $passToday = HallPass::create([
            'user_id' => $student1->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => '2026-10-09',
            'start_time' => '2026-10-09 10:00:00',
            'end_time' => '2026-10-09 10:05:00',
        ]);

        $passYesterday = HallPass::create([
            'user_id' => $student2->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Agua',
            'date' => '2026-10-08',
            'start_time' => '2026-10-08 09:00:00',
            'end_time' => '2026-10-08 09:04:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('salidas.history', ['date_from' => '2026-10-09']));

        $response->assertStatus(200);
        $response->assertSee('Gomez, Carlos');
        $response->assertDontSee('Lopez, Ana');
    }

    public function test_can_filter_history_between_dates(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $teacher = User::factory()->create(['name' => 'Profesor', 'last_name' => 'Prueba']);
        $teacher->assignRole('profesor');

        $group = Group::create(['name' => 'B', 'course' => '2 ESO']);

        $student1 = User::factory()->create(['name' => 'Lucas', 'last_name' => 'Diaz', 'group_id' => $group->id]);
        $student1->assignRole('alumno');

        $student2 = User::factory()->create(['name' => 'Marta', 'last_name' => 'Ruiz', 'group_id' => $group->id]);
        $student2->assignRole('alumno');

        $student3 = User::factory()->create(['name' => 'Elena', 'last_name' => 'Sanz', 'group_id' => $group->id]);
        $student3->assignRole('alumno');

        HallPass::create([
            'user_id' => $student1->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => '2026-10-01',
            'start_time' => '2026-10-01 10:00:00',
            'end_time' => '2026-10-01 10:05:00',
        ]);

        HallPass::create([
            'user_id' => $student2->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Enfermería',
            'date' => '2026-10-05',
            'start_time' => '2026-10-05 11:00:00',
            'end_time' => '2026-10-05 11:10:00',
        ]);

        HallPass::create([
            'user_id' => $student3->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Secretaría',
            'date' => '2026-10-15',
            'start_time' => '2026-10-15 12:00:00',
            'end_time' => '2026-10-15 12:05:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('salidas.history', [
                'date_from' => '2026-10-02',
                'date_to' => '2026-10-06'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Ruiz, Marta');
        $response->assertDontSee('Diaz, Lucas');
        $response->assertDontSee('Sanz, Elena');
    }

    public function test_can_filter_history_between_times(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $teacher = User::factory()->create(['name' => 'Profesor', 'last_name' => 'Prueba']);
        $teacher->assignRole('profesor');

        $group = Group::create(['name' => 'C', 'course' => '3 ESO']);

        $studentMorning = User::factory()->create(['name' => 'Marcos', 'last_name' => 'Vega', 'group_id' => $group->id]);
        $studentMorning->assignRole('alumno');

        $studentNoon = User::factory()->create(['name' => 'Lucia', 'last_name' => 'Blanco', 'group_id' => $group->id]);
        $studentNoon->assignRole('alumno');

        HallPass::create([
            'user_id' => $studentMorning->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Baño',
            'date' => '2026-10-09',
            'start_time' => '2026-10-09 08:30:00',
            'end_time' => '2026-10-09 08:35:00',
        ]);

        HallPass::create([
            'user_id' => $studentNoon->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Biblioteca',
            'date' => '2026-10-09',
            'start_time' => '2026-10-09 13:15:00',
            'end_time' => '2026-10-09 13:20:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('salidas.history', [
                'time_from' => '12:00',
                'time_to' => '14:00'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Blanco, Lucia');
        $response->assertDontSee('Vega, Marcos');
    }

    public function test_mobile_view_renders_student_info_cards(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $teacher = User::factory()->create(['name' => 'Raul', 'last_name' => 'Sanchez']);
        $teacher->assignRole('profesor');

        $group = Group::create(['name' => 'Bach-A', 'course' => '1 BACH']);

        $student = User::factory()->create([
            'name' => 'Valeria',
            'last_name' => 'Navarro',
            'group_id' => $group->id
        ]);
        $student->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $student->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Enfermería Urgente',
            'date' => '2026-10-09',
            'start_time' => '2026-10-09 11:20:00',
            'end_time' => '2026-10-09 11:35:00',
        ]);

        $response = $this->actingAs($admin)->get(route('salidas.history'));

        $response->assertStatus(200);
        // Mobile card assertions
        $response->assertSee('id="pass-card-' . $pass->id . '"', false);
        $response->assertSee('Navarro, Valeria');
        $response->assertSee('1 BACH Bach-A');
        $response->assertSee('Enfermería Urgente');
        $response->assertSee('15 min');
        $response->assertSee('09/10/2026');
        $response->assertSee('11:20');
        $response->assertSee('11:35');
        $response->assertSee('Raul Sanchez');
    }
}
