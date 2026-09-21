<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Documento;
use App\Models\Group;
use App\Models\Incidencia;
use App\Models\Modulo;
use App\Models\SchoolYear;
use App\Models\Sesion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityPhase1Test extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $profesor1;
    protected User $profesor2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Roles
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'profesor']);

        // Active School Year
        SchoolYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');

        $this->profesor1 = User::factory()->create(['email' => 'prof1@test.com']);
        $this->profesor1->assignRole('profesor');

        $this->profesor2 = User::factory()->create(['email' => 'prof2@test.com']);
        $this->profesor2->assignRole('profesor');
    }

    /** @test */
    public function non_admin_cannot_access_backups()
    {
        $response = $this->actingAs($this->profesor1)->get(route('backups.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_backups()
    {
        $response = $this->actingAs($this->admin)->get(route('backups.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function path_traversal_on_backup_download_is_prevented()
    {
        // 1. Direct path traversal route attempt (rejected at router level with 404)
        $response = $this->actingAs($this->admin)->get('/backups/download/../../.env');
        $response->assertStatus(404);

        // 2. Non-SQL file attempt (rejected by BackupController with redirect & error message)
        $response2 = $this->actingAs($this->admin)->get('/backups/download/unauthorized.txt');
        $response2->assertRedirect(route('backups.index'));
        $response2->assertSessionHas('error');
    }

    /** @test */
    public function teacher_cannot_grade_unassigned_modules()
    {
        $group = Group::create(['course' => '1º ESO', 'name' => 'A', 'school_year_id' => 1]);
        $modulo = Modulo::create(['codigo' => 'MAT-01', 'nombre' => 'Matemáticas', 'group_id' => $group->id]);
        
        // Modulo assigned to profesor2, NOT profesor1
        $modulo->profesores()->attach($this->profesor2->id);

        $actividad = Actividad::create([
            'titulo' => 'Examen 1',
            'modulo_id' => $modulo->id,
            'peso' => 1,
            'es_evaluable' => true,
        ]);

        $alumno = User::factory()->create(['group_id' => $group->id]);

        $response = $this->actingAs($this->profesor1)->post(route('notas.guardar'), [
            'notas' => [
                [
                    'student_id' => $alumno->id,
                    'actividad_id' => $actividad->id,
                    'valor' => 9.5,
                ]
            ]
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_cannot_delete_other_teacher_document()
    {
        $documento = Documento::create([
            'titulo' => 'Programación Didáctica',
            'categoria' => 'programacion',
            'user_id' => $this->profesor2->id,
        ]);

        $response = $this->actingAs($this->profesor1)->delete(route('documentos.destroy', $documento));
        $response->assertStatus(403);

        $this->assertDatabaseHas('documentos', ['id' => $documento->id]);
    }

    /** @test */
    public function teacher_cannot_delete_other_teacher_incident()
    {
        $incidencia = Incidencia::create([
            'titulo' => 'Proyector roto',
            'descripcion' => 'No enciende la lámpara',
            'fecha' => now()->format('Y-m-d'),
            'prioridad' => 'alta',
            'estado' => 'abierta',
            'user_id' => $this->profesor2->id,
        ]);

        $response = $this->actingAs($this->profesor1)->delete(route('incidencias.destroy', $incidencia));
        $response->assertStatus(403);

        $this->assertDatabaseHas('incidencias', ['id' => $incidencia->id]);
    }

    /** @test */
    public function teacher_cannot_modify_other_teacher_session()
    {
        $group = Group::create(['course' => '1º ESO', 'name' => 'B', 'school_year_id' => 1]);
        $modulo = Modulo::create(['codigo' => 'LEN-01', 'nombre' => 'Lengua', 'group_id' => $group->id]);
        $modulo->profesores()->attach($this->profesor2->id);

        $sesion = Sesion::create([
            'fecha' => now()->format('Y-m-d'),
            'modulo_id' => $modulo->id,
            'group_id' => $group->id,
            'profesor_id' => $this->profesor2->id,
            'contenidos' => 'Sintaxis',
        ]);

        $response = $this->actingAs($this->profesor1)->delete(route('sesiones.destroy', $sesion));
        $response->assertStatus(403);

        $this->assertDatabaseHas('sesiones', ['id' => $sesion->id]);
    }
}
