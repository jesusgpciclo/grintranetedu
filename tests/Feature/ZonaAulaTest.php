<?php

namespace Tests\Feature;

use App\Models\Aula;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZonaAulaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles or basic setup needed
        // Create an active school year in DB as the middleware / app might expect it
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

        // Create an admin or user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /** @test */
    public function can_create_aula_with_capacity()
    {
        $response = $this->post(route('aulas.store'), [
            'tipo' => 'aula',
            'identificacion' => 'ID-A101',
            'nombre' => 'Aula 101 de Prueba',
            'capacidad' => 25,
            'ubicacion' => 'Planta Baja',
            'equipamiento' => ['Proyector', 'Aire acondicionado'],
            'descripcion' => 'Una descripción de prueba'
        ]);

        $response->assertRedirect(route('aulas.index'));
        $this->assertDatabaseHas('aulas', [
            'tipo' => 'aula',
            'identificacion' => 'ID-A101',
            'nombre' => 'Aula 101 de Prueba',
            'capacidad' => 25,
            'ubicacion' => 'Planta Baja',
        ]);
    }

    /** @test */
    public function can_create_zona_without_capacity()
    {
        $response = $this->post(route('aulas.store'), [
            'tipo' => 'zona',
            'identificacion' => 'ID-ZPATIO',
            'nombre' => 'Patio Principal',
            'capacidad' => 100, // should be forced to null because type is zona
            'ubicacion' => 'Exterior',
            'descripcion' => 'Patio exterior'
        ]);

        $response->assertRedirect(route('aulas.index'));
        $this->assertDatabaseHas('aulas', [
            'tipo' => 'zona',
            'identificacion' => 'ID-ZPATIO',
            'nombre' => 'Patio Principal',
            'capacidad' => null, // check that it is null
            'ubicacion' => 'Exterior',
        ]);
    }

    /** @test */
    public function can_update_aula_and_change_to_zona_clearing_capacity()
    {
        $aula = Aula::create([
            'tipo' => 'aula',
            'identificacion' => 'ID-TEMP',
            'nombre' => 'Aula Temporal',
            'capacidad' => 30,
            'ubicacion' => 'Planta 1'
        ]);

        $response = $this->put(route('aulas.update', $aula), [
            'tipo' => 'zona',
            'identificacion' => 'ID-TEMP-ZONA',
            'nombre' => 'Zona Temporal Actualizada',
            'capacidad' => 15, // should be forced to null since we are changing tipo to zona
            'ubicacion' => 'Planta 1 Actualizada',
            'descripcion' => 'Actualizado a zona'
        ]);

        $response->assertRedirect(route('aulas.index'));
        
        $aula->refresh();
        $this->assertEquals('zona', $aula->tipo);
        $this->assertEquals('ID-TEMP-ZONA', $aula->identificacion);
        $this->assertEquals('Zona Temporal Actualizada', $aula->nombre);
        $this->assertNull($aula->capacidad);
    }

    /** @test */
    public function can_download_templates_in_various_formats()
    {
        $formats = ['csv', 'json', 'yaml'];
        foreach ($formats as $format) {
            $response = $this->get(route('aulas.template', $format));
            $response->assertStatus(200);
            $response->assertHeader('Content-Disposition', 'attachment; filename=plantilla_zonas_aulas.' . $format);
        }
    }

    /** @test */
    public function can_download_exports_in_various_formats()
    {
        Aula::create([
            'tipo' => 'aula',
            'identificacion' => 'ID-EXP',
            'nombre' => 'Aula Exportada',
            'capacidad' => 10,
        ]);

        $formats = ['csv', 'json', 'yaml'];
        foreach ($formats as $format) {
            $response = $this->get(route('aulas.export', $format));
            $response->assertStatus(200);
            $this->assertStringContainsString('attachment; filename=zonas_aulas_', $response->headers->get('Content-Disposition'));
        }
    }

    /** @test */
    public function can_import_csv_file()
    {
        $csvContent = "tipo,identificacion,nombre,capacidad,ubicacion,equipamiento,descripcion\n"
                    . "aula,ID-IMP-CSV,Aula Importada CSV,35,Planta 2,\"Proyector, Aire acondicionado\",De prueba CSV\n"
                    . "zona,,Zona Patio Importada,,Exterior,,De prueba patio";
        
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('zonas.csv', $csvContent);

        $response = $this->post(route('aulas.import'), [
            'file' => $file
        ]);

        $response->assertRedirect(route('aulas.index'));
        $this->assertDatabaseHas('aulas', [
            'tipo' => 'aula',
            'identificacion' => 'ID-IMP-CSV',
            'nombre' => 'Aula Importada CSV',
            'capacidad' => 35,
            'ubicacion' => 'Planta 2',
        ]);
        $this->assertDatabaseHas('aulas', [
            'tipo' => 'zona',
            'nombre' => 'Zona Patio Importada',
            'capacidad' => null,
            'ubicacion' => 'Exterior',
        ]);
    }

    /** @test */
    public function can_import_json_file()
    {
        $data = [
            [
                'tipo' => 'aula',
                'identificacion' => 'ID-IMP-JSON',
                'nombre' => 'Aula Importada JSON',
                'capacidad' => 45,
                'ubicacion' => 'Planta 3',
                'equipamiento' => 'Webcam',
                'descripcion' => 'De prueba JSON'
            ]
        ];
        
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('zonas.json', json_encode($data));

        $response = $this->post(route('aulas.import'), [
            'file' => $file
        ]);

        $response->assertRedirect(route('aulas.index'));
        $this->assertDatabaseHas('aulas', [
            'tipo' => 'aula',
            'identificacion' => 'ID-IMP-JSON',
            'nombre' => 'Aula Importada JSON',
            'capacidad' => 45,
        ]);
    }

    /** @test */
    public function can_create_absence_referencing_aula()
    {
        $aula = Aula::create([
            'tipo' => 'aula',
            'nombre' => 'Aula de Ausencia Test',
            'capacidad' => 30
        ]);

        $template = \App\Models\ScheduleTemplate::create([
            'name' => 'Plantilla Test',
            'active_days' => [1, 2, 3, 4, 5]
        ]);

        $timeSlot = \App\Models\TimeSlot::create([
            'schedule_template_id' => $template->id,
            'name' => '1ª Hora',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'order' => 1
        ]);

        $group = \App\Models\Group::create([
            'course' => '1º ESO',
            'name' => 'A',
            'school_year_id' => 1
        ]);

        // Register absence
        $response = $this->post(route('ausencias.store'), [
            'fecha' => now()->format('Y-m-d'),
            'time_slot_ids' => [$timeSlot->id],
            'group_id' => $group->id,
            'zona_id' => $aula->id,
            'tarea' => 'Realizar ejercicios de la página 10',
            'es_guardia' => false
        ]);

        $response->assertRedirect(route('ausencias.index', ['date' => now()->format('Y-m-d')]));

        $this->assertDatabaseHas('ausencias', [
            'zona_id' => $aula->id,
            'group_id' => $group->id,
            'time_slot_id' => $timeSlot->id,
            'tarea' => 'Realizar ejercicios de la página 10'
        ]);

        // Check relationship resolves to Aula
        $ausencia = \App\Models\Ausencia::where('zona_id', $aula->id)->first();
        $this->assertInstanceOf(Aula::class, $ausencia->zona);
        $this->assertEquals('Aula de Ausencia Test', $ausencia->zona->nombre);
    }
}
