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
        
        $data = [];
        
        try {
            if ($extension === 'json') {
                $data = json_decode($content, true);
            } elseif ($extension === 'yaml' || $extension === 'yml') {
                $data = \Symfony\Component\Yaml\Yaml::parse($content);
            } elseif ($extension === 'csv') {
                $lines = array_map('str_getcsv', file($file->getRealPath()));
                if (count($lines) > 0) {
                    $headers = array_shift($lines);
                    if (count($headers) > 0) {
                        $headers[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers[0]);
                    }
                    foreach ($lines as $line) {
                        if (count($headers) === count($line)) {
                            $data[] = array_combine($headers, $line);
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
            if (empty($row['name']) || empty($row['last_name']) || empty($row['email'])) {
                continue;
            }

            $groupId = null;
            if (!empty($row['group'])) {
                $parts = explode(' ', trim($row['group']));
                if (count($parts) >= 2) {
                    $groupName = array_pop($parts);
                    $groupCourse = implode(' ', $parts);
                    $group = Group::where('course', $groupCourse)->where('name', $groupName)->first();
                    if ($group) {
                        if ($user->hasRole('profesor') && !$user->hasRole('admin') && $group->tutor_id !== $user->id) {
                            continue;
                        }
                        $groupId = $group->id;
                    }
                }
            }

            $student = User::where('email', trim($row['email']))->first();
            
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
                    'name' => trim($row['name']),
                    'last_name' => trim($row['last_name']),
                    'group_id' => $groupId,
                ];
                if (isset($row['observaciones'])) {
                    $updateData['observaciones'] = trim($row['observaciones']);
                }

                $student->update($updateData);
            } else {
                $studentData = [
                    'name' => trim($row['name']),
                    'last_name' => trim($row['last_name']),
                    'email' => trim($row['email']),
                    'password' => Hash::make(trim($row['email'])),
                    'group_id' => $groupId,
                ];
                if (isset($row['observaciones'])) {
                    $studentData['observaciones'] = trim($row['observaciones']);
                }
                $student = User::create($studentData);
                $student->assignRole('alumno');
            }
            $imported++;
        }

        return back()->with('success', "Se han importado/actualizado $imported alumnos correctamente.");
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
