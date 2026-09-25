<?php

namespace Tests\Feature;

use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuardiasFase4Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;

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

        $this->user = User::factory()->create([
            'name' => 'Docente PWA',
            'email' => 'pwa@example.com',
        ]);
        $this->user->assignRole('profesor');

        $template = ScheduleTemplate::create([
            'name' => 'Plantilla General',
            'is_active' => true,
        ]);

        TimeSlot::create([
            'schedule_template_id' => $template->id,
            'name' => '1ª Hora',
            'start_time' => '08:30:00',
            'end_time' => '09:25:00',
            'order' => 1,
        ]);
    }

    /** @test */
    public function manifest_json_is_served_properly_for_pwa()
    {
        $this->actingAs($this->user);

        $response = $this->get('/manifest.json');
        $response->assertOk();
        $response->assertSee('GR Intranet EDU');
        $response->assertSee('standalone');
    }

    /** @test */
    public function service_worker_is_served_properly()
    {
        $this->actingAs($this->user);

        $response = $this->get('/sw.js');
        $response->assertOk();
        $response->assertSee('gr-intranet-v2');
    }

    /** @test */
    public function privacy_policy_route_renders_rgpd_content()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('privacidad'));
        $response->assertOk();
        $response->assertSee('Política de Privacidad');
        $response->assertSee('RGPD');
        $response->assertSee('Responsable del Tratamiento');
    }

    /** @test */
    public function parte_view_contains_slot_filter_navigation()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('guardias.parte'));
        $response->assertOk();
        $response->assertSee('Filtrar Hora:');
        $response->assertSee('Todas las horas');
        $response->assertSee('data-slot-id="1"', false);
    }
}
