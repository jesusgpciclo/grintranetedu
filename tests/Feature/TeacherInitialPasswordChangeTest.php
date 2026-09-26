<?php

namespace Tests\Feature;

use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherInitialPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

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

        $this->admin = User::factory()->create([
            'email' => 'admin@instituto.es',
        ]);
        $this->admin->assignRole('admin');
    }

    /** @test */
    public function imported_teacher_has_must_change_password_flag_set()
    {
        $this->actingAs($this->admin);

        $csv = "name,last_name,email,departamento\n" .
               "Carlos,García,carlos.garcia@instituto.es,Informática\n";

        $file = UploadedFile::fake()->createWithContent('profesores.csv', $csv);

        $response = $this->post(route('teachers.mass-import'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('success');

        $teacher = User::where('email', 'carlos.garcia@instituto.es')->first();
        $this->assertNotNull($teacher);
        $this->assertTrue($teacher->must_change_password);
        $this->assertTrue(Hash::check('carlos.garcia@instituto.es', $teacher->password));
    }

    /** @test */
    public function logging_in_with_email_as_password_forces_password_change()
    {
        $teacher = User::factory()->create([
            'name' => 'Laura',
            'last_name' => 'Martínez',
            'email' => 'laura.martinez@instituto.es',
            'password' => Hash::make('laura.martinez@instituto.es'),
            'must_change_password' => false, // even if false initially
        ]);
        $teacher->assignRole('profesor');

        $response = $this->post(route('login'), [
            'email' => 'laura.martinez@instituto.es',
            'password' => 'laura.martinez@instituto.es',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('warning');

        $teacher->refresh();
        $this->assertTrue($teacher->must_change_password);

        // Cannot navigate to dashboard while must_change_password is true
        $this->actingAs($teacher);
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertRedirect(route('profile.edit'));

        // Changing password unblocks the user
        $updateResponse = $this->patch(route('profile.update'), [
            'name' => 'Laura',
            'last_name' => 'Martínez',
            'email' => 'laura.martinez@instituto.es',
            'password' => 'NuevaSegura2026!',
            'password_confirmation' => 'NuevaSegura2026!',
        ]);

        $updateResponse->assertSessionHas('status');

        $teacher->refresh();
        $this->assertFalse($teacher->must_change_password);

        // Can now access dashboard
        $allowedDashboard = $this->get(route('dashboard'));
        $allowedDashboard->assertOk();
    }

    /** @test */
    public function logging_in_with_google_does_not_require_password_change()
    {
        // Teacher imported with must_change_password = true
        $teacher = User::factory()->create([
            'name' => 'Jesús',
            'last_name' => 'García',
            'email' => 'jesus.garcia@g.educaand.es',
            'password' => Hash::make('jesus.garcia@g.educaand.es'),
            'must_change_password' => true,
        ]);
        $teacher->assignRole('profesor');

        // Mock Socialite Google User
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-id-12345');
        $abstractUser->shouldReceive('getEmail')->andReturn('jesus.garcia@g.educaand.es');
        $abstractUser->shouldReceive('getName')->andReturn('Jesús García');
        $abstractUser->id = 'google-unique-id-12345';
        $abstractUser->email = 'jesus.garcia@g.educaand.es';
        $abstractUser->name = 'Jesús García';
        $abstractUser->avatar = 'https://lh3.googleusercontent.com/avatar';
        $abstractUser->user = [
            'family_name' => 'García',
            'given_name' => 'Jesús',
        ];

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');
        $response->assertRedirect('dashboard');

        $teacher->refresh();
        // Since logged in with Google, must_change_password should be false
        $this->assertFalse($teacher->must_change_password);
        $this->assertEquals('google-unique-id-12345', $teacher->google_id);

        // Can access dashboard without restriction
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertOk();
    }
}
