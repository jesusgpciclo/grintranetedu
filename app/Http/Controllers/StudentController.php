<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');
        $query = User::role('alumno')->with('groupRel');
        $groupsQuery = Group::query();

        if ($activeSchoolYearId) {
            $groupsQuery->where('school_year_id', $activeSchoolYearId);
            $query->whereHas('groupRel', fn($q) => $q->where('school_year_id', $activeSchoolYearId));
        }

        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $tutoredGroupIds = Group::where('tutor_id', $user->id)->pluck('id');
            $query->whereIn('group_id', $tutoredGroupIds);
            $groupsQuery->where('tutor_id', $user->id);
        }
        
        $groups = $groupsQuery->get();

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        if ($sort === 'group') {
            $query->leftJoin('groups', 'users.group_id', '=', 'groups.id')
                  ->orderBy('groups.course', $direction)
                  ->orderBy('groups.name', $direction)
                  ->select('users.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $perPage = $request->get('per_page', 30);
        if ($perPage === 'all') {
            $students = $query->paginate(max(1, $query->count()))->appends($request->all());
        } else {
            $students = $query->paginate((int) $perPage)->appends($request->all());
        }

        return view('students.index', compact('students', 'groups', 'sort', 'direction', 'perPage'));
    }

    public function create()
    {
        $user = auth()->user();
        $query = Group::query();
        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $query->where('tutor_id', $user->id);
        }
        $groups = $query->get();
        return view('students.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:users',
            'group_id' => 'nullable|exists:groups,id',
            'password' => 'nullable|string|min:6',
        ]);

        if ($request->group_id && $user->hasRole('profesor') && !$user->hasRole('admin')) {
            $group = Group::find($request->group_id);
            if ($group->tutor_id !== $user->id) {
                abort(403, 'No tienes permiso para asignar alumnos a este grupo.');
            }
        }

        $student = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'observaciones' => $request->observaciones,
            'email' => $request->email,
            'password' => Hash::make($request->password ?: $request->email),
            'group_id' => $request->group_id,
        ]);

        $student->assignRole('alumno');

        return redirect()->route('students.index')->with('success', 'Alumno creado correctamente.');
    }

    public function edit($id)
    {
        $student = User::findOrFail($id);
        $user = auth()->user();

        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $tutoredGroupIds = Group::where('tutor_id', $user->id)->pluck('id')->toArray();
            if (!in_array($student->group_id, $tutoredGroupIds)) {
                abort(403, 'No tienes permiso para editar este alumno.');
            }
        }

        $query = Group::query();
        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $query->where('tutor_id', $user->id);
        }
        $groups = $query->get();

        return view('students.edit', compact('student', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $student = User::findOrFail($id);
        $user = auth()->user();

        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $tutoredGroupIds = Group::where('tutor_id', $user->id)->pluck('id')->toArray();
            if (!in_array($student->group_id, $tutoredGroupIds)) {
                abort(403, 'No tienes permiso para editar este alumno.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:users,email,'.$student->id,
            'group_id' => 'nullable|exists:groups,id',
            'password' => 'nullable|string|min:6',
        ]);

        if ($request->group_id && $user->hasRole('profesor') && !$user->hasRole('admin')) {
            $group = Group::find($request->group_id);
            if ($group->tutor_id !== $user->id) {
                abort(403, 'No tienes permiso para asignar alumnos a este grupo.');
            }
        }

        $data = [
            'name' => $request->name,
            'last_name' => $request->last_name,
            'observaciones' => $request->observaciones,
            'email' => $request->email,
            'group_id' => $request->group_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $student->update($data);

        return redirect()->route('students.index')->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy($id)
    {
        $student = User::findOrFail($id);
        $user = auth()->user();

        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $tutoredGroupIds = Group::where('tutor_id', $user->id)->pluck('id')->toArray();
            if (!in_array($student->group_id, $tutoredGroupIds)) {
                abort(403, 'No tienes permiso para eliminar este alumno.');
            }
        }

        $student->delete();
        return redirect()->route('students.index')->with('success', 'Alumno eliminado correctamente.');
    }

    public function export(Request $request, $format)
    {
        $user = auth()->user();
        $query = User::role('alumno')->with('groupRel');

        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $tutoredGroupIds = Group::where('tutor_id', $user->id)->pluck('id');
            $query->whereIn('group_id', $tutoredGroupIds);
        }

        $students = $query->get();

        $data = $students->map(function ($student) {
            return [
                'name' => $student->name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'group' => $student->groupRel ? ($student->groupRel->course . ' ' . $student->groupRel->name) : null,
                'observaciones' => $student->observaciones,
            ];
        })->toArray();

        return $this->downloadFormattedData($data, 'alumnos_' . date('Y-md_His'), $format);
    }

    public function template($format)
    {
        if ($format === 'seneca') {
            $data = [
                [
                    'Alumno/a' => 'Algaba Marín, Francisco',
                    'Unidad' => '1º GM SMR B',
                ],
                [
                    'Alumno/a' => 'Algaba Postigo, Pablo',
                    'Unidad' => '1º GM SMR B',
                ]
            ];

            return $this->downloadFormattedData($data, 'plantilla_alumnos_seneca', 'csv');
        }

        $data = [
            [
                'name' => 'Juan',
                'last_name' => 'Pérez',
                'email' => 'juan@example.com',
                'group' => '1º ESO A',
                'observaciones' => 'Repetidor',
            ],
            [
                'name' => 'Ana',
                'last_name' => 'Gómez',
                'email' => 'ana@example.com',
                'group' => '2º Bachillerato C',
                'observaciones' => '',
            ]
        ];

        return $this->downloadFormattedData($data, 'plantilla_alumnos', $format);
    }

    public function import(Request $request)
    {
        // Increase time limit for mass imports
        set_time_limit(300);

        $user = auth()->user();
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $content = file_get_contents($file->getRealPath());

        // Strip UTF-8 BOM if present
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }
        
        $data = [];
        
        try {
            if ($extension === 'json') {
                $data = json_decode($content, true);
            } elseif ($extension === 'yaml' || $extension === 'yml') {
                $data = \Symfony\Component\Yaml\Yaml::parse($content);
            } elseif ($extension === 'csv' || $extension === 'txt') {
                // Determine delimiter from first non-empty line
                $firstLine = strtok($content, "\r\n");
                $delimiter = ',';
                if ($firstLine !== false) {
                    if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                        $delimiter = ';';
                    } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                        $delimiter = "\t";
                    }
                }

                $rawLines = preg_split('/\r\n|\r|\n/', $content);
                $rawLines = array_values(array_filter($rawLines, fn($l) => trim($l) !== ''));

                // Find header line (skipping any preceding metadata if present)
                $startIndex = 0;
                foreach ($rawLines as $idx => $line) {
                    if (stripos($line, 'Alumno') !== false || stripos($line, 'Unidad') !== false || stripos($line, 'name') !== false || stripos($line, 'apellidos') !== false) {
                        $startIndex = $idx;
                        break;
                    }
                }

                $lines = array_slice($rawLines, $startIndex);

                if (count($lines) > 0) {
                    $headerLine = array_shift($lines);
                    $headers = str_getcsv($headerLine, $delimiter);
                    $headers = array_map(function($h) {
                        return trim($h, " \t\n\r\0\x0B\"'");
                    }, $headers);

                    foreach ($lines as $line) {
                        if (trim($line) === '') continue;
                        $row = str_getcsv($line, $delimiter);
                        if (count($headers) === count($row)) {
                            $data[] = array_combine($headers, $row);
                        } elseif (count($row) > count($headers)) {
                            $data[] = array_combine($headers, array_slice($row, 0, count($headers)));
                        } elseif (count($row) >= 1 && count($headers) >= 1) {
                            $row = array_pad($row, count($headers), '');
                            $data[] = array_combine($headers, $row);
                        }
                    }
                }
            } else {
                return back()->withErrors('Formato no soportado. Usa CSV, JSON o YAML.');
            }
        } catch (\Exception $e) {
            return back()->withErrors('Error al procesar el archivo: ' . $e->getMessage());
        }

        if (empty($data)) {
            return back()->withErrors('El archivo está vacío o no tiene el formato correcto.');
        }

        $imported = 0;
        foreach ($data as $row) {
            $parsed = $this->parseStudentRow($row);

            $name = $parsed['name'];
            $lastName = $parsed['last_name'];
            $email = $parsed['email'];
            $groupRaw = $parsed['group_raw'];
            $observaciones = $parsed['observaciones'];

            if (empty($name) && empty($lastName)) {
                continue;
            }

            $groupId = null;
            if (!empty($groupRaw)) {
                $groupId = $this->resolveGroupId($groupRaw, $user);
                if ($groupId === false) {
                    continue; // Skip if restricted tutor
                }
            }

            // Find existing student
            $student = null;
            if (!empty($email)) {
                $student = User::where('email', $email)->first();
            }

            if (!$student && !empty($name) && !empty($lastName)) {
                $student = User::role('alumno')
                    ->where('name', $name)
                    ->where('last_name', $lastName)
                    ->first();
                if ($student) {
                    $email = $student->email;
                }
            }

            // Generate clean institutional email if absent
            if (empty($email)) {
                $baseSlug = Str::slug($name . '.' . $lastName, '.');
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

            if ($student) {
                if (!$student->hasRole('alumno')) {
                    continue;
                }

                if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
                    $tutoredGroupIds = Group::where('tutor_id', $user->id)->pluck('id')->toArray();
                    if ($student->group_id && !in_array($student->group_id, $tutoredGroupIds)) {
                        continue;
                    }
                }

                $updateData = [
                    'name' => $name,
                    'last_name' => $lastName,
                ];
                if ($groupId !== null) {
                    $updateData['group_id'] = $groupId;
                }
                if (!empty($observaciones)) {
                    $updateData['observaciones'] = $observaciones;
                }

                $student->update($updateData);
            } else {
                $studentData = [
                    'name' => $name,
                    'last_name' => $lastName,
                    'email' => $email,
                    'password' => Hash::make('alumno1234'),
                    'group_id' => $groupId,
                ];
                if (!empty($observaciones)) {
                    $studentData['observaciones'] = $observaciones;
                }

                $student = User::create($studentData);
                $student->assignRole('alumno');
            }
            $imported++;
        }

        return back()->with('success', "Se han importado/actualizado $imported alumnos correctamente.");
    }

    protected function parseStudentRow(array $row)
    {
        $cleanRow = [];
        foreach ($row as $k => $v) {
            $kClean = mb_strtolower(trim($k, " \t\n\r\0\x0B\"'"));
            $kClean = preg_replace('/^\xEF\xBB\xBF/', '', $kClean);
            $cleanRow[$kClean] = is_string($v) ? trim($v) : $v;
        }

        $name = '';
        $lastName = '';
        $email = '';
        $groupRaw = '';
        $observaciones = '';

        // 1. Single column full name check ("Alumno/a", "Alumno", "Estudiante", "Apellidos y Nombre", etc.)
        $fullNameVal = null;
        $fullNameKey = null;
        foreach ($cleanRow as $k => $v) {
            if (in_array($k, [
                'alumno/a', 'alumno / a', 'alumno', 'alumnos', 'alumna', 'alumno(a)',
                'estudiante', 'estudiantes', 'apellidos y nombre', 'apellidos y nombres',
                'apellidos, nombre', 'nombre y apellidos', 'student', 'full_name'
            ])) {
                $fullNameVal = $v;
                $fullNameKey = $k;
                break;
            }
        }

        // Check for separate name & last_name
        $explicitName = $cleanRow['name'] ?? $cleanRow['nombre'] ?? null;
        $explicitLastName = $cleanRow['last_name'] ?? $cleanRow['apellidos'] ?? $cleanRow['apellido'] ?? null;

        if (!empty($explicitName) && !empty($explicitLastName)) {
            $name = $explicitName;
            $lastName = $explicitLastName;
        } elseif (!empty($fullNameVal)) {
            // Séneca format: "Apellidos, Nombre" (e.g. "Algaba Marín, Francisco")
            if (str_contains($fullNameVal, ',')) {
                $parts = explode(',', $fullNameVal, 2);
                $lastName = trim($parts[0]);
                $name = trim($parts[1]);
            } else {
                $parts = preg_split('/\s+/', trim($fullNameVal));
                if (count($parts) > 1) {
                    if (str_contains($fullNameKey, 'apellido')) {
                        $name = array_pop($parts);
                        $lastName = implode(' ', $parts);
                    } else {
                        $name = array_shift($parts);
                        $lastName = implode(' ', $parts);
                    }
                } else {
                    $name = $fullNameVal;
                    $lastName = '';
                }
            }
        } elseif (!empty($explicitName)) {
            $name = $explicitName;
            $lastName = $explicitLastName ?? '';
        }

        // 2. Check group / unidad
        foreach ($cleanRow as $k => $v) {
            if (in_array($k, ['unidad', 'unidades', 'grupo', 'grupos', 'group', 'curso', 'course', 'clase'])) {
                $groupRaw = $v;
                break;
            }
        }

        // 3. Check email
        foreach ($cleanRow as $k => $v) {
            if (in_array($k, ['email', 'e-mail', 'correo', 'correo electrónico', 'mail'])) {
                $email = $v;
                break;
            }
        }

        // 4. Check observaciones
        foreach ($cleanRow as $k => $v) {
            if (in_array($k, ['observaciones', 'observacion', 'notas', 'notes', 'comments'])) {
                $observaciones = $v;
                break;
            }
        }

        return [
            'name' => $name,
            'last_name' => $lastName,
            'email' => $email,
            'group_raw' => $groupRaw,
            'observaciones' => $observaciones,
        ];
    }

    protected function resolveGroupId($groupRaw, $user)
    {
        $trimmed = trim($groupRaw);
        if ($trimmed === '') {
            return null;
        }

        // 1. Try matching course and name (splitting last word as group letter/name, e.g. "1º GM SMR B")
        $parts = preg_split('/\s+/', $trimmed);
        $group = null;

        if (count($parts) >= 2) {
            $groupName = array_pop($parts);
            $groupCourse = implode(' ', $parts);
            $group = Group::where('course', $groupCourse)->where('name', $groupName)->first();
        }

        // 2. Try matching course or name alone
        if (!$group) {
            $group = Group::where('course', $trimmed)->orWhere('name', $trimmed)->first();
        }

        // 3. Try matching full concatenated name "course name" in PHP (database-agnostic)
        if (!$group) {
            $normalizedTrimmed = mb_strtolower($trimmed);
            $group = Group::all()->first(function($g) use ($normalizedTrimmed) {
                return mb_strtolower(trim($g->course . ' ' . $g->name)) === $normalizedTrimmed;
            });
        }

        // 4. If group doesn't exist yet, auto-create it if admin or non-profesor
        if (!$group && ($user->hasRole('admin') || !$user->hasRole('profesor'))) {
            $parts = preg_split('/\s+/', $trimmed);
            if (count($parts) >= 2) {
                $groupName = array_pop($parts);
                $groupCourse = implode(' ', $parts);
            } else {
                $groupCourse = $trimmed;
                $groupName = 'A';
            }

            $activeSchoolYearId = session('active_school_year_id')
                ?? \App\Models\SchoolYear::where('is_active', true)->value('id')
                ?? \App\Models\SchoolYear::latest('id')->value('id');

            $group = Group::create([
                'course' => $groupCourse,
                'name' => $groupName,
                'school_year_id' => $activeSchoolYearId,
            ]);
        }

        // Permission check for profesor role
        if ($group && $user->hasRole('profesor') && !$user->hasRole('admin')) {
            if ($group->tutor_id !== $user->id) {
                return false;
            }
        }

        return $group ? $group->id : null;
    }

    private function downloadFormattedData($data, $filenameBase, $format)
    {
        $filename = $filenameBase . '.' . $format;

        if ($format === 'json') {
            return response()->json($data)->withHeaders([
                'Content-Disposition' => 'attachment; filename=' . $filename,
            ]);
        } elseif ($format === 'yaml' || $format === 'yml') {
            $yaml = \Symfony\Component\Yaml\Yaml::dump($data);
            return response($yaml)->withHeaders([
                'Content-Type' => 'text/yaml',
                'Content-Disposition' => 'attachment; filename=' . $filename,
            ]);
        } elseif ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=' . $filename,
            ];
            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                if (count($data) > 0) {
                    fputcsv($file, array_keys($data[0]));
                    foreach ($data as $row) {
                        fputcsv($file, $row);
                    }
                } else {
                    fputcsv($file, ['name', 'last_name', 'email', 'group']);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        abort(400, 'Invalid format');
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();
        $studentIds = $request->input('student_ids', []);

        if (empty($studentIds)) {
            return back()->withErrors('No se seleccionó ningún alumno.');
        }

        $query = User::whereIn('id', $studentIds)->role('alumno');

        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $tutoredGroupIds = Group::where('tutor_id', $user->id)->pluck('id')->toArray();
            $query->whereIn('group_id', $tutoredGroupIds);
        }

        $deletedCount = $query->delete();

        return back()->with('success', "Se han eliminado $deletedCount alumnos correctamente.");
    }

    public function bulkChangeGroup(Request $request)
    {
        $user = auth()->user();
        $studentIds = $request->input('student_ids', []);
        $newGroupId = $request->input('new_group_id');

        if (empty($studentIds)) {
            return back()->withErrors('No se seleccionó ningún alumno.');
        }

        if ($newGroupId && $user->hasRole('profesor') && !$user->hasRole('admin')) {
            $group = Group::find($newGroupId);
            if (!$group || $group->tutor_id !== $user->id) {
                abort(403, 'No tienes permiso para asignar alumnos a este grupo.');
            }
        }

        $query = User::whereIn('id', $studentIds)->role('alumno');

        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $tutoredGroupIds = Group::where('tutor_id', $user->id)->pluck('id')->toArray();
            $query->whereIn('group_id', $tutoredGroupIds);
        }

        $updatedCount = $query->update(['group_id' => $newGroupId ?: null]);

        return back()->with('success', "Se ha cambiado de grupo a $updatedCount alumnos correctamente.");
    }
}
