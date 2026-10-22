<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\HallPass;
use App\Models\HallPassNotice;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HallPassNoticeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        PermissionManagerService::applyDefaultAssignments();
    }

    public function test_authorized_user_can_access_notices_index(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create(['name' => 'Luis', 'last_name' => 'Martinez']);
        $student->assignRole('alumno');

        HallPassNotice::create([
            'user_id' => $student->id,
            'message' => 'Debe salir siempre acompañado',
            'is_active' => true,
            'created_by' => $teacher->id,
        ]);

        $response = $this->actingAs($teacher)->get(route('salidas.notices.index'));

        $response->assertStatus(200);
        $response->assertSee('Avisos Especiales de Salidas');
        $response->assertSee('Debe salir siempre acompañado');
        $response->assertSee('Luis');
    }

    public function test_unauthorized_user_cannot_access_notices_index(): void
    {
        $conserje = User::factory()->create();
        $conserje->assignRole('conserje'); // Conserje no tiene permiso salidas.notices por defecto

        $response = $this->actingAs($conserje)->get(route('salidas.notices.index'));

        $response->assertStatus(403);
    }

    public function test_can_create_hall_pass_notice(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create(['name' => 'Elena', 'last_name' => 'Ruiz']);
        $student->assignRole('alumno');

        $response = $this->actingAs($teacher)->post(route('salidas.notices.store'), [
            'user_id' => $student->id,
            'message' => 'Dejar salir siempre que lo solicite por prescripción médica',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('salidas.notices.index'));
        $this->assertDatabaseHas('hall_pass_notices', [
            'user_id' => $student->id,
            'message' => 'Dejar salir siempre que lo solicite por prescripción médica',
            'is_active' => true,
            'created_by' => $teacher->id,
        ]);
    }

    public function test_can_toggle_notice_active_state(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $notice = HallPassNotice::create([
            'user_id' => $student->id,
            'message' => 'Aviso temporal',
            'is_active' => true,
            'created_by' => $teacher->id,
        ]);

        $response = $this->actingAs($teacher)->patch(route('salidas.notices.toggle', $notice));

        $response->assertRedirect();
        $this->assertFalse($notice->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($teacher)->patch(route('salidas.notices.toggle', $notice));
        $this->assertTrue($notice->fresh()->is_active);
    }

    public function test_can_update_notice(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $notice = HallPassNotice::create([
            'user_id' => $student->id,
            'message' => 'Texto anterior',
            'is_active' => true,
            'created_by' => $teacher->id,
        ]);

        $response = $this->actingAs($teacher)->put(route('salidas.notices.update', $notice), [
            'user_id' => $student->id,
            'message' => 'Texto nuevo y modificado',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('salidas.notices.index'));
        $this->assertDatabaseHas('hall_pass_notices', [
            'id' => $notice->id,
            'message' => 'Texto nuevo y modificado',
        ]);
    }

    public function test_can_delete_notice(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        $notice = HallPassNotice::create([
            'user_id' => $student->id,
            'message' => 'Aviso a borrar',
            'is_active' => true,
        ]);

        $response = $this->actingAs($teacher)->delete(route('salidas.notices.destroy', $notice));

        $response->assertRedirect(route('salidas.notices.index'));
        $this->assertDatabaseMissing('hall_pass_notices', ['id' => $notice->id]);
    }

    public function test_warning_when_student_has_active_notice_on_pass_creation(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create(['name' => 'Sofia', 'last_name' => 'Castro']);
        $student->assignRole('alumno');

        HallPassNotice::create([
            'user_id' => $student->id,
            'message' => 'Debe salir acompañado/a',
            'is_active' => true,
        ]);

        $response = $this->actingAs($teacher)->postJson(route('salidas.store'), [
            'student_id' => $student->id,
            'reason' => 'Material',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'warning' => true,
            'needs_notice_confirmation' => true,
        ]);
        $this->assertStringContainsString('Debe salir acompañado/a', $response->json('message'));
        $this->assertStringContainsString('AVISO DE SALIDA', $response->json('message'));
    }

    public function test_can_force_pass_when_student_has_active_notice(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        HallPassNotice::create([
            'user_id' => $student->id,
            'message' => 'Debe salir acompañado/a',
            'is_active' => true,
        ]);

        $response = $this->actingAs($teacher)->postJson(route('salidas.store'), [
            'student_id' => $student->id,
            'reason' => 'Material',
            'force_notice' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hall_passes', [
            'user_id' => $student->id,
            'end_time' => null,
        ]);
    }

    public function test_inactive_notice_does_not_trigger_warning(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student = User::factory()->create();
        $student->assignRole('alumno');

        HallPassNotice::create([
            'user_id' => $student->id,
            'message' => 'Aviso desactivado',
            'is_active' => false,
        ]);

        $response = $this->actingAs($teacher)->postJson(route('salidas.store'), [
            'student_id' => $student->id,
            'reason' => 'Material',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hall_passes', [
            'user_id' => $student->id,
            'end_time' => null,
        ]);
    }

    public function test_permission_access_for_incompatibilities_and_notices(): void
    {
        $conserje = User::factory()->create();
        $conserje->assignRole('conserje');

        // Conserje has neither salidas.incompatibilities nor salidas.notices
        $this->actingAs($conserje)->get(route('salidas.incompatibilities.index'))->assertStatus(403);
        $this->actingAs($conserje)->get(route('salidas.notices.index'))->assertStatus(403);

        // Granting the permission explicitly grants access
        $conserje->givePermissionTo('salidas.incompatibilities');
        $this->actingAs($conserje->fresh())->get(route('salidas.incompatibilities.index'))->assertStatus(200);

        $conserje->givePermissionTo('salidas.notices');
        $this->actingAs($conserje->fresh())->get(route('salidas.notices.index'))->assertStatus(200);
    }

    public function test_notice_form_has_student_pagination_elements(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $response = $this->actingAs($teacher)->get(route('salidas.notices.create'));

        $response->assertStatus(200);
        $response->assertSee('id="student-pagination"', false);
        $response->assertSee('id="student-pagination-info"', false);
        $response->assertSee('id="student-pagination-controls"', false);
    }
}

