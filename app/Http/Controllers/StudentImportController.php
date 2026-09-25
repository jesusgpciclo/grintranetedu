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
     * Formatos soportados:
     *  1) Séneca: Alumno/a,Unidad  (con "Apellidos, Nombre")
     *  2) Séneca clásico: Apellidos;Nombre;DNI/NIE;Email
     */
    public function store(Request $request, Group $group)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $content = file_get_contents($file->getRealPath());
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $rawLines = preg_split('/\r\n|\r|\n/', $content);
        $rawLines = array_values(array_filter($rawLines, fn($l) => trim($l) !== ''));

        if (empty($rawLines)) {
            return redirect()->route('groups.show', $group)->withErrors('El archivo está vacío.');
        }

        // Determinar delimitador
        $firstLine = $rawLines[0];
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $imported = 0;
        $errors = [];
        $skipped = 0;

        $startIndex = 0;
        if (stripos($firstLine, 'Alumno') !== false || stripos($firstLine, 'Apellidos') !== false || stripos($firstLine, 'Nombre') !== false || stripos($firstLine, 'Unidad') !== false) {
            $startIndex = 1;
        }

        for ($index = $startIndex; $index < count($rawLines); $index++) {
            $line = trim($rawLines[$index]);
            if (empty($line)) continue;

            $fields = str_getcsv($line, $delimiter);
            if (count($fields) < 1) {
                $errors[] = "Línea " . ($index + 1) . ": formato inválido.";
                continue;
            }

            $firstField = trim($fields[0] ?? '');
            $apellidos = '';
            $nombre = '';
            $email = '';

            // Formato A: "Apellidos, Nombre" en primera columna (Séneca: "Algaba Marín, Francisco")
            if (str_contains($firstField, ',')) {
                $parts = explode(',', $firstField, 2);
                $apellidos = trim($parts[0]);
                $nombre = trim($parts[1]);
                $email = trim($fields[2] ?? '');
            } elseif (count($fields) >= 2 && !empty($fields[1])) {
                // Formato B: Apellidos;Nombre;DNI;Email
                $apellidos = $firstField;
                $nombre = trim($fields[1]);
                $email = trim($fields[3] ?? '');
            } else {
                $parts = preg_split('/\s+/', $firstField);
                if (count($parts) > 1) {
                    $nombre = array_pop($parts);
                    $apellidos = implode(' ', $parts);
                } else {
                    $nombre = $firstField;
                    $apellidos = '';
                }
            }

            if (empty($nombre) && empty($apellidos)) {
                $errors[] = "Línea " . ($index + 1) . ": nombre o apellidos vacíos.";
                continue;
            }

            // Buscar si ya existe
            $existing = null;
            if (!empty($email)) {
                $existing = User::where('email', $email)->first();
            }
            if (!$existing && !empty($nombre) && !empty($apellidos)) {
                $existing = User::role('alumno')
                    ->where('name', $nombre)
                    ->where('last_name', $apellidos)
                    ->first();
                if ($existing) {
                    $email = $existing->email;
                }
            }

            // Generar email si no existe
            if (empty($email)) {
                $baseSlug = Str::slug($nombre . '.' . $apellidos, '.');
                if (empty($baseSlug)) {
                    $baseSlug = 'alumno.' . Str::random(5);
                }
                $candidateEmail = $baseSlug . '@alumno.instituto.es';
                $counter = 1;
                while (User::where('email', $candidateEmail)->exists()) {
                    $candidateEmail = $baseSlug . $counter . '@alumno.instituto.es';
                    $counter++;
                }
                $email = $candidateEmail;
            }

            if ($existing) {
                // Actualizar grupo si es diferente
                if ($existing->group_id !== $group->id) {
                    $existing->update(['group_id' => $group->id]);
                }
                $skipped++;
                continue;
            }

            $newStudent = User::create([
                'name' => $nombre,
                'last_name' => $apellidos,
                'email' => $email,
                'password' => Hash::make('alumno1234'),
                'group_id' => $group->id,
            ]);

            $newStudent->assignRole('alumno');
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
        $content = "Alumno/a,Unidad\n";
        $content .= "\"Algaba Marín, Francisco\",1º GM SMR B\n";
        $content .= "\"Algaba Postigo, Pablo\",1º GM SMR B\n";

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="plantilla_alumnos_seneca.csv"');
    }
}
