<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\HallPass;
use App\Models\SchoolYear;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityHardeningPhaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        SchoolYear::updateOrInsert(
            ['id' => 1],
            ['name' => '2026/2027', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'directiva', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'jefatura', 'guard_name' => 'web']);
    }

    /** @test */
    public function un_alumno_no_puede_modificar_a_un_profesor(): void
    {
        $alumno = User::factory()->create();
        $alumno->assignRole('alumno');

        $profesor = User::factory()->create(['email' => 'docente.original@centro.es']);
        $profesor->assignRole('profesor');

        $this->actingAs($alumno)
            ->patch(route('teachers.update', $profesor->id), [
                'name' => 'Docente Modificado',
                'last_name' => 'Hacked',
                'email' => 'docente.hacked@centro.es',
                'password' => 'NuevaPassword#123',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'docente.hacked@centro.es']);
        $this->assertDatabaseHas('users', ['email' => 'docente.original@centro.es']);
    }

    /** @test */
    public function un_alumno_no_puede_borrar_ni_modificar_a_otro_alumno(): void
    {
        $group = Group::create(['name' => '1º ESO A', 'course' => '1º ESO']);

        $alumnoA = User::factory()->create(['group_id' => $group->id]);
        $alumnoA->assignRole('alumno');

        $alumnoB = User::factory()->create(['name' => 'AlumnoB', 'group_id' => $group->id]);
        $alumnoB->assignRole('alumno');

        // Intento de borrado
        $this->actingAs($alumnoA)
            ->delete(route('students.destroy', $alumnoB->id))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $alumnoB->id]);

        // Intento de edición
        $this->actingAs($alumnoA)
            ->patch(route('students.update', $alumnoB->id), [
                'name' => 'ModificadoPorAlumnoA',
                'last_name' => 'Apellidos',
                'email' => 'alumnob.mod@centro.es',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['name' => 'ModificadoPorAlumnoA']);
    }

    /** @test */
    public function un_profesor_no_puede_borrar_el_pase_autorizado_por_otro_profesor(): void
    {
        $profesor1 = User::factory()->create();
        $profesor1->assignRole('profesor');

        $profesor2 = User::factory()->create();
        $profesor2->assignRole('profesor');

        $alumno = User::factory()->create();
        $alumno->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $alumno->id,
            'teacher_id' => $profesor1->id,
            'reason' => 'Baño',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        // Profesor 2 intenta borrar el pase autorizado por Profesor 1
        $response = $this->actingAs($profesor2)
            ->delete(route('salidas.destroy', $pass->id));

        $response->assertForbidden();
        $this->assertDatabaseHas('hall_passes', ['id' => $pass->id]);
    }

    /** @test */
    public function el_profesor_autorizador_si_puede_cancelar_su_propio_pase(): void
    {
        $profesor = User::factory()->create();
        $profesor->assignRole('profesor');

        $alumno = User::factory()->create();
        $alumno->assignRole('alumno');

        $pass = HallPass::create([
            'user_id' => $alumno->id,
            'teacher_id' => $profesor->id,
            'reason' => 'Biblioteca',
            'date' => now()->toDateString(),
            'start_time' => now(),
        ]);

        $response = $this->actingAs($profesor)
            ->delete(route('salidas.destroy', $pass->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('hall_passes', ['id' => $pass->id]);
    }

    /** @test */
    public function la_ordenacion_del_historial_de_salidas_rechaza_inyecciones_sql(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = 'asc,(SELECT 1 FROM (SELECT SLEEP(0))a)';

        $this->actingAs($admin)
            ->get(route('salidas.history', ['sort' => 'duracion', 'direction' => $payload]))
            ->assertOk();
    }

    /** @test */
    public function google_oauth_rechaza_dominios_no_autorizados_cuando_esta_configurado(): void
    {
        config(['services.google.allowed_domain' => 'iescentro.es']);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-outside-999');
        $abstractUser->shouldReceive('getEmail')->andReturn('hacker@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Usuario Externo');
        $abstractUser->id = 'google-outside-999';
        $abstractUser->email = 'hacker@gmail.com';
        $abstractUser->name = 'Usuario Externo';
        $abstractUser->avatar = 'https://lh3.googleusercontent.com/avatar_out';
        $abstractUser->user = [
            'family_name' => 'Externo',
            'given_name' => 'Usuario',
        ];

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'hacker@gmail.com']);
    }

    /** @test */
    public function token_de_reset_expira_tras_60_minutos(): void
    {
        $user = User::factory()->create(['email' => 'usuario.reset@centro.es']);

        // Token generado hace 61 minutos
        DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make('token-expirado-123'),
            'created_at' => Carbon::now()->subMinutes(61),
        ]);

        $response = $this->post(route('password.update'), [
            'token' => 'token-expirado-123',
            'email' => $user->email,
            'password' => 'NuevaPasswordValida123!',
            'password_confirmation' => 'NuevaPasswordValida123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function cambio_de_curso_escolar_funciona_por_post_con_permisos(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $nuevoCurso = SchoolYear::create([
            'name' => '2027/2028',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('school-year.set-active'), [
            'set_school_year_id' => $nuevoCurso->id,
        ]);

        $response->assertSessionHas('active_school_year_id', $nuevoCurso->id);
    }
}
