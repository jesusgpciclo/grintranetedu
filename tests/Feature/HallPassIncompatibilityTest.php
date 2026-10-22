<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Group;
use App\Models\HallPass;
use App\Models\HallPassIncompatibilityGroup;
use App\Services\PermissionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HallPassIncompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        PermissionManagerService::applyDefaultAssignments();
    }

    public function test_incompatibility_index_screen_renders_for_authorized_users(): void
    {
        $teacher = User::factory()->create(['name' => 'Profesor', 'last_name' => 'Prueba']);
        $teacher->assignRole('profesor');

        $group = HallPassIncompatibilityGroup::create([
            'name' => 'Grupo Conflicto 1',
            'description' => 'No deben salir juntos',
        ]);

        $response = $this->actingAs($teacher)->get(route('salidas.incompatibilities.index'));

        $response->assertStatus(200);
        $response->assertSee('Grupo Conflicto 1');
        $response->assertSee('Incompatibilidades en Pasillos');
    }

    public function test_can_create_incompatibility_group(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student1 = User::factory()->create();
        $student1->assignRole('alumno');

        $student2 = User::factory()->create();
        $student2->assignRole('alumno');

        $response = $this->actingAs($teacher)->post(route('salidas.incompatibilities.store'), [
            'name' => 'Grupo A-B',
            'description' => 'Conflicto en recreos',
            'student_ids' => [$student1->id, $student2->id],
        ]);

        $response->assertRedirect(route('salidas.incompatibilities.index'));
        $this->assertDatabaseHas('hall_pass_incompatibility_groups', [
            'name' => 'Grupo A-B',
            'description' => 'Conflicto en recreos',
        ]);

        $group = HallPassIncompatibilityGroup::where('name', 'Grupo A-B')->first();
        $this->assertCount(2, $group->students);
    }

    public function test_cannot_create_group_with_less_than_two_students(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student1 = User::factory()->create();
        $student1->assignRole('alumno');

        $response = $this->actingAs($teacher)->post(route('salidas.incompatibilities.store'), [
            'name' => 'Grupo Inválido',
            'student_ids' => [$student1->id],
        ]);

        $response->assertSessionHasErrors('student_ids');
        $this->assertDatabaseMissing('hall_pass_incompatibility_groups', [
            'name' => 'Grupo Inválido',
        ]);
    }

    public function test_can_update_incompatibility_group(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student1 = User::factory()->create();
        $student1->assignRole('alumno');
        $student2 = User::factory()->create();
        $student2->assignRole('alumno');
        $student3 = User::factory()->create();
        $student3->assignRole('alumno');

        $group = HallPassIncompatibilityGroup::create([
            'name' => 'Grupo Inicial',
        ]);
        $group->students()->attach([$student1->id, $student2->id]);

        $response = $this->actingAs($teacher)->put(route('salidas.incompatibilities.update', $group), [
            'name' => 'Grupo Modificado',
            'description' => 'Nueva descripción',
            'student_ids' => [$student2->id, $student3->id],
        ]);

        $response->assertRedirect(route('salidas.incompatibilities.index'));
        $this->assertDatabaseHas('hall_pass_incompatibility_groups', [
            'id' => $group->id,
            'name' => 'Grupo Modificado',
            'description' => 'Nueva descripción',
        ]);

        $group->refresh();
        $this->assertEqualsCanonicalizing([$student2->id, $student3->id], $group->students->pluck('id')->all());
    }

    public function test_can_delete_incompatibility_group(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $student1 = User::factory()->create();
        $student1->assignRole('alumno');
        $student2 = User::factory()->create();
        $student2->assignRole('alumno');

        $group = HallPassIncompatibilityGroup::create([
            'name' => 'Grupo a Borrar',
        ]);
        $group->students()->attach([$student1->id, $student2->id]);

        $response = $this->actingAs($teacher)->delete(route('salidas.incompatibilities.destroy', $group));

        $response->assertRedirect(route('salidas.incompatibilities.index'));
        $this->assertDatabaseMissing('hall_pass_incompatibility_groups', [
            'id' => $group->id,
        ]);
    }

    public function test_warning_when_incompatible_student_is_already_in_hallway(): void
    {
        $teacher = User::factory()->create(['name' => 'Profesor', 'last_name' => 'Prueba']);
        $teacher->assignRole('profesor');

        $classGroup = Group::create(['name' => '2º ESO A', 'course' => '2º ESO']);

        $student1 = User::factory()->create([
            'name' => 'Juan',
            'last_name' => 'Perez',
            'group_id' => $classGroup->id,
        ]);
        $student1->assignRole('alumno');

        $student2 = User::factory()->create([
            'name' => 'Mario',
            'last_name' => 'Gomez',
            'group_id' => $classGroup->id,
        ]);
        $student2->assignRole('alumno');

        // Incompatibility group between Juan and Mario
        $incompGroup = HallPassIncompatibilityGroup::create(['name' => 'Incompatibilidad Juan y Mario']);
        $incompGroup->students()->attach([$student1->id, $student2->id]);

        // Juan is currently in the hallway
        HallPass::create([
            'user_id' => $student1->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Material',
            'date' => now()->format('Y-m-d'),
            'start_time' => now(),
            'end_time' => null,
        ]);

        // Teacher tries to let Mario out
        $response = $this->actingAs($teacher)->postJson(route('salidas.store'), [
            'student_id' => $student2->id,
            'reason' => 'Baño',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'warning' => true,
            'needs_incompatibility_confirmation' => true,
        ]);
        $response->assertJsonFragment([
            'needs_incompatibility_confirmation' => true,
        ]);
        $this->assertStringNotContainsString('Juan Perez', $response->json('message'));
        $this->assertStringContainsString('AVISO: Este alumno o alumna tiene incompatibilidad con otros que actualmente están fuera en el pasillo', $response->json('message'));
    }

    public function test_can_force_pass_when_incompatible_student_is_in_hallway(): void
    {
        $teacher = User::factory()->create(['name' => 'Profesor', 'last_name' => 'Prueba']);
        $teacher->assignRole('profesor');

        $student1 = User::factory()->create(['name' => 'Juan', 'last_name' => 'Perez']);
        $student1->assignRole('alumno');

        $student2 = User::factory()->create(['name' => 'Mario', 'last_name' => 'Gomez']);
        $student2->assignRole('alumno');

        $incompGroup = HallPassIncompatibilityGroup::create(['name' => 'Incompatibilidad Juan y Mario']);
        $incompGroup->students()->attach([$student1->id, $student2->id]);

        // Juan is in the hallway
        HallPass::create([
            'user_id' => $student1->id,
            'teacher_id' => $teacher->id,
            'reason' => 'Material',
            'date' => now()->format('Y-m-d'),
            'start_time' => now(),
            'end_time' => null,
        ]);

        // Teacher confirms and forces the exit
        $response = $this->actingAs($teacher)->postJson(route('salidas.store'), [
            'student_id' => $student2->id,
            'reason' => 'Material',
            'force_incompatibility' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('hall_passes', [
            'user_id' => $student2->id,
            'end_time' => null,
        ]);
    }

    public function test_incompatibility_form_has_student_pagination_elements(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $response = $this->actingAs($teacher)->get(route('salidas.incompatibilities.create'));

        $response->assertStatus(200);
        $response->assertSee('id="student-pagination"', false);
        $response->assertSee('id="student-pagination-info"', false);
        $response->assertSee('id="student-pagination-controls"', false);
    }

    public function test_incompatibility_group_card_paginates_students_when_over_40(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $incompGroup = HallPassIncompatibilityGroup::create(['name' => 'Grupo Grande Mas de 40']);

        // Create 45 students
        $students = User::factory()->count(45)->create();
        foreach ($students as $st) {
            $st->assignRole('alumno');
        }
        $incompGroup->students()->attach($students->pluck('id')->toArray());

        $response = $this->actingAs($teacher)->get(route('salidas.incompatibilities.index'));

        $response->assertStatus(200);
        $response->assertSee('group-' . $incompGroup->id . '-page-info', false);
        $response->assertSee('group-' . $incompGroup->id . '-prev-btn', false);
        $response->assertSee('group-' . $incompGroup->id . '-next-btn', false);
        $response->assertSee('data-student-page="2"', false);
    }

    public function test_incompatibility_group_card_does_not_paginate_students_when_40_or_less(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('profesor');

        $incompGroup = HallPassIncompatibilityGroup::create(['name' => 'Grupo Pequeno Menor de 40']);

        // Create 10 students
        $students = User::factory()->count(10)->create();
        foreach ($students as $st) {
            $st->assignRole('alumno');
        }
        $incompGroup->students()->attach($students->pluck('id')->toArray());

        $response = $this->actingAs($teacher)->get(route('salidas.incompatibilities.index'));

        $response->assertStatus(200);
        $response->assertDontSee('group-' . $incompGroup->id . '-page-info', false);
        $response->assertDontSee('group-' . $incompGroup->id . '-prev-btn', false);
    }
}

