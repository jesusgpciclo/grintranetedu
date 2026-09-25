<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentSenecaImportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);

        // Active school year
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        SchoolYear::updateOrInsert(
            ['id' => 1],
            ['name' => '2026/2027', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);
    }

    /** @test */
    public function can_import_students_using_seneca_format_comma_delimited()
    {
        $csvContent = "Alumno/a,Unidad\n" .
                      "\"Algaba Marín, Francisco\",1º GM SMR B\n" .
                      "\"Algaba Postigo, Pablo\",1º GM SMR B\n";

        $file = UploadedFile::fake()->createWithContent('alumnos_seneca.csv', $csvContent);

        $response = $this->post(route('students.mass-import'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('success');

        // Check group was resolved/created
        $group = Group::where('course', '1º GM SMR')->where('name', 'B')->first();
        $this->assertNotNull($group, 'El grupo 1º GM SMR B debió haberse creado o encontrado');

        // Check students created
        $student1 = User::where('name', 'Francisco')->where('last_name', 'Algaba Marín')->first();
        $this->assertNotNull($student1, 'El alumno Francisco Algaba Marín debe existir');
        $this->assertTrue($student1->hasRole('alumno'));
        $this->assertEquals($group->id, $student1->group_id);
        $this->assertNotEmpty($student1->email);

        $student2 = User::where('name', 'Pablo')->where('last_name', 'Algaba Postigo')->first();
        $this->assertNotNull($student2, 'El alumno Pablo Algaba Postigo debe existir');
        $this->assertTrue($student2->hasRole('alumno'));
        $this->assertEquals($group->id, $student2->group_id);
    }

    /** @test */
    public function can_import_students_using_seneca_format_semicolon_delimited()
    {
        $csvContent = "Alumno/a;Unidad\n" .
                      "\"García López, María\";2º ESO A\n";

        $file = UploadedFile::fake()->createWithContent('alumnos_puntoycoma.csv', $csvContent);

        $response = $this->post(route('students.mass-import'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('success');

        $group = Group::where('course', '2º ESO')->where('name', 'A')->first();
        $this->assertNotNull($group);

        $student = User::where('name', 'María')->where('last_name', 'García López')->first();
        $this->assertNotNull($student);
        $this->assertTrue($student->hasRole('alumno'));
        $this->assertEquals($group->id, $student->group_id);
    }

    /** @test */
    public function can_import_students_with_utf8_bom()
    {
        $bom = "\xEF\xBB\xBF";
        $csvContent = $bom . "Alumno/a,Unidad\n" .
                      "\"Pérez Gómez, José\",1º Bachillerato B\n";

        $file = UploadedFile::fake()->createWithContent('alumnos_bom.csv', $csvContent);

        $response = $this->post(route('students.mass-import'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('success');

        $student = User::where('name', 'José')->where('last_name', 'Pérez Gómez')->first();
        $this->assertNotNull($student);
        $this->assertTrue($student->hasRole('alumno'));
    }

    /** @test */
    public function reimporting_updates_student_group_without_duplicate()
    {
        $csv1 = "Alumno/a,Unidad\n" .
                "\"Algaba Marín, Francisco\",1º GM SMR B\n";
        $file1 = UploadedFile::fake()->createWithContent('alumnos1.csv', $csv1);
        $this->post(route('students.mass-import'), ['file' => $file1]);

        $this->assertEquals(1, User::where('name', 'Francisco')->where('last_name', 'Algaba Marín')->count());

        $csv2 = "Alumno/a,Unidad\n" .
                "\"Algaba Marín, Francisco\",2º GM SMR B\n";
        $file2 = UploadedFile::fake()->createWithContent('alumnos2.csv', $csv2);
        $this->post(route('students.mass-import'), ['file' => $file2]);

        $this->assertEquals(1, User::where('name', 'Francisco')->where('last_name', 'Algaba Marín')->count());

        $newGroup = Group::where('course', '2º GM SMR')->where('name', 'B')->first();
        $student = User::where('name', 'Francisco')->where('last_name', 'Algaba Marín')->first();
        $this->assertEquals($newGroup->id, $student->group_id);
    }

    /** @test */
    public function standard_csv_format_import_still_works()
    {
        $group = Group::create(['course' => '1º ESO', 'name' => 'A', 'school_year_id' => 1]);

        $csvContent = "name,last_name,email,group,observaciones\n" .
                      "Lucía,Méndez,lucia.mendez@example.com,1º ESO A,Repetidora\n";

        $file = UploadedFile::fake()->createWithContent('alumnos_std.csv', $csvContent);

        $response = $this->post(route('students.mass-import'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('success');

        $student = User::where('email', 'lucia.mendez@example.com')->first();
        $this->assertNotNull($student);
        $this->assertEquals('Lucía', $student->name);
        $this->assertEquals('Méndez', $student->last_name);
        $this->assertEquals($group->id, $student->group_id);
        $this->assertEquals('Repetidora', $student->observaciones);
    }

    /** @test */
    public function can_import_using_group_student_import_controller()
    {
        $group = Group::create(['course' => '1º GM SMR', 'name' => 'B', 'school_year_id' => 1]);

        $csvContent = "Alumno/a,Unidad\n" .
                      "\"Algaba Marín, Francisco\",1º GM SMR B\n";

        $file = UploadedFile::fake()->createWithContent('grupo_alumnos.csv', $csvContent);

        $response = $this->post(route('students.import.process', $group), [
            'csv_file' => $file,
        ]);

        $response->assertSessionHas('success');

        $student = User::where('name', 'Francisco')->where('last_name', 'Algaba Marín')->first();
        $this->assertNotNull($student);
        $this->assertEquals($group->id, $student->group_id);
        $this->assertTrue($student->hasRole('alumno'));
    }

    /** @test */
    public function can_download_seneca_template()
    {
        $response = $this->get(route('students.template', 'seneca'));
        $response->assertOk();
        $this->assertStringContainsString('Alumno/a,Unidad', $response->streamedContent());
        $this->assertStringContainsString('Algaba Marín, Francisco', $response->streamedContent());
    }
}
