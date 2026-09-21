<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentImportController extends Controller
{
    /**
     * Formulario de importación CSV de alumnos (formato Séneca).
     */
    public function create(Group $group)
    {
        return view('students.import', compact('group'));
    }

    /**
     * Procesar importación CSV.
     * Formato Séneca esperado: Apellidos;Nombre;DNI/NIE;Email
     */
    public function store(Request $request, Group $group)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(explode("\n", $content));

        $imported = 0;
        $errors = [];
        $skipped = 0;

        foreach ($lines as $index => $line) {
            // Saltar cabecera
            if ($index === 0) {
                continue;
            }

            $line = trim($line);
            if (empty($line)) continue;

            // Séneca usa punto y coma como separador
            $fields = str_getcsv($line, ';');

            if (count($fields) < 2) {
                $errors[] = "Línea " . ($index + 1) . ": formato inválido.";
                continue;
            }

            $apellidos = trim($fields[0] ?? '');
            $nombre = trim($fields[1] ?? '');
            $dni = trim($fields[2] ?? '');
            $email = trim($fields[3] ?? '');

            if (empty($nombre) || empty($apellidos)) {
                $errors[] = "Línea " . ($index + 1) . ": nombre o apellidos vacíos.";
                continue;
            }

            // Generar email si no existe
            if (empty($email)) {
                $email = Str::slug($nombre . '.' . $apellidos, '.') . '@alumno.instituto.es';
            }

            // Verificar si ya existe
            $existing = User::where('email', $email)->first();
            if ($existing) {
                // Actualizar grupo si es diferente
                if ($existing->group_id !== $group->id) {
                    $existing->update(['group_id' => $group->id]);
                }
                $skipped++;
                continue;
            }

            User::create([
                'name' => $nombre,
                'last_name' => $apellidos,
                'email' => $email,
                'password' => Hash::make('alumno1234'),
                'group_id' => $group->id,
            ]);

            // Asignar rol alumno
            $user = User::where('email', $email)->first();
            if ($user && !$user->hasRole('alumno')) {
                $user->assignRole('alumno');
            }

            $imported++;
        }

        $message = "Importación completada: {$imported} alumnos importados.";
        if ($skipped > 0) $message .= " {$skipped} ya existían.";
        if (!empty($errors)) $message .= " " . count($errors) . " errores.";

        return redirect()->route('groups.show', $group)
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /**
     * Descargar plantilla CSV de ejemplo (formato Séneca).
     */
    public function downloadTemplate()
    {
        $content = "Apellidos;Nombre;DNI/NIE;Email\n";
        $content .= "García López;Juan;12345678A;juan.garcia@example.com\n";
        $content .= "Martínez Ruiz;María;87654321B;maria.martinez@example.com\n";

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="plantilla_alumnos_seneca.csv"');
    }
}
