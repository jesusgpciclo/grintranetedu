<?php

namespace Tests\Feature;

use App\Models\Ausencia;
use App\Models\ScheduleSelection;
use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\UserSchedule;
use App\Services\GuardiaEquityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuardiasFase3Test extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected User $titular;
    protected User $substitute;
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

        $this->teacher = User::factory()->create([
            'name' => 'Profesor Prueba',
            'email' => 'profe@example.com',
            'password' => Hash::make('password123'),
        ]);
        $this->teacher->assignRole('profesor');

        $this->titular = User::factory()->create([
            'name' => 'Titular Biologia',
            'email' => 'titular@example.com',
            'departamento' => 'Biología y Geología',
        ]);
        $this->titular->assignRole('profesor');

        $this->substitute = User::factory()->create([
            'name' => 'Sustituto Biologia',
            'email' => 'sustituto@example.com',
            'titular_user_id' => $this->titular->id,
        ]);
        $this->substitute->assignRole('profesor');

        $this->template = ScheduleTemplate::create([
            'name' => 'Plantilla General',
            'is_active' => true,
        ]);

        $this->timeSlot = TimeSlot::create([
            'schedule_template_id' => $this->template->id,
            'name' => '3ª Hora',
            'start_time' => '10:45:00',
            'end_time' => '11:40:00',
            'order' => 3,
        ]);
    }

    /** @test */
    public function teacher_can_update_departamento_and_predefined_avatar_in_profile()
    {
        $this->actingAs($this->teacher);

        $response = $this->patch(route('profile.update'), [
            'name' => 'Profesor Prueba Modificado',
            'email' => 'profe@example.com',
            'departamento' => 'Física y Química',
            'predefined_avatar' => 'avatars/predefined/teacher_3.svg',
        ]);

        $response->assertSessionHas('status');

        $this->teacher->refresh();
        $this->assertEquals('Física y Química', $this->teacher->departamento);
        $this->assertEquals('avatars/predefined/teacher_3.svg', $this->teacher->avatar);
        $this->assertStringContainsString('avatars/predefined/teacher_3.svg', $this->teacher->avatar_url);
    }

    /** @test */
    public function logging_in_with_default_password_profesor_triggers_must_change_password_flag()
    {
        $userWithDefaultPass = User::factory()->create([
            'email' => 'nuevo@example.com',
            'password' => Hash::make('profesor'),
            'must_change_password' => false,
        ]);
        $userWithDefaultPass->assignRole('profesor');

        $response = $this->post(route('login'), [
            'email' => 'nuevo@example.com',
            'password' => 'profesor',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $userWithDefaultPass->refresh();
        $this->assertTrue($userWithDefaultPass->must_change_password);
    }

    /** @test */
    public function user_with_must_change_password_is_blocked_from_other_routes()
    {
        $this->teacher->update(['must_change_password' => true]);
        $this->actingAs($this->teacher);

        // Try accessing dashboard
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('warning');

        // Accessing profile.edit is allowed
        $responseProfile = $this->get(route('profile.edit'));
        $responseProfile->assertOk();
        $responseProfile->assertSee('Cambio de contraseña requerido');

        // Updating password clears must_change_password
        $responseUpdate = $this->patch(route('profile.update'), [
            'name' => $this->teacher->name,
            'email' => $this->teacher->email,
            'password' => 'nuevaPassword123!',
            'password_confirmation' => 'nuevaPassword123!',
        ]);

        $responseUpdate->assertSessionHas('status');
        $this->teacher->refresh();
        $this->assertFalse($this->teacher->must_change_password);

        // Now dashboard is accessible
        $responseAllowed = $this->get(route('dashboard'));
        $responseAllowed->assertOk();
    }

    /** @test */
    public function substitute_teacher_inherits_guardia_slot_when_titular_is_absent()
    {
        $date = '2026-09-22'; // Tuesday

        // Titular has a guard duty slot on Tuesday (day 2)
        $userSched = UserSchedule::create([
            'user_id' => $this->titular->id,
            'school_year_id' => 1,
            'schedule_template_id' => $this->template->id,
        ]);

        ScheduleSelection::create([
            'user_schedule_id' => $userSched->id,
            'time_slot_id' => $this->timeSlot->id,
            'day' => '2',
            'value' => 'Guardia',
        ]);

        // Titular is absent on this date and slot
        Ausencia::create([
            'user_id' => $this->titular->id,
            'fecha' => $date,
            'time_slot_id' => $this->timeSlot->id,
            'es_guardia' => true,
            'tarea' => 'Baja médica',
        ]);

        $equityService = app(GuardiaEquityService::class);
        $available = $equityService->getAvailableGuardiasForSlot($date, $this->timeSlot->id);

        // Titular is absent so NOT in available list
        $this->assertFalse($available->contains('user_id', $this->titular->id));

        // Substitute is active and inherits the guard slot!
        $this->assertTrue($available->contains('user_id', $this->substitute->id));
        $subData = $available->firstWhere('user_id', $this->substitute->id);
        $this->assertStringContainsString('Sustituto', $subData['name']);
        $this->assertEquals('Biología y Geología', $subData['departamento']);
    }
}
