<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    private function checkProfesorRoleExists()
    {
        return Role::where('name', 'profesor')->exists();
    }

    public function index(Request $request)
    {
        $roleExists = $this->checkProfesorRoleExists();
        
        $query = $roleExists ? User::role('profesor') : User::whereRaw('1 = 0');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('departamento', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');

        if (in_array($sort, ['name', 'last_name', 'email', 'departamento', 'created_at'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $request->get('per_page', 30);
        if ($perPage === 'all') {
            $teachers = $query->paginate(max(1, $query->count()))->appends($request->all());
        } else {
            $teachers = $query->paginate((int) $perPage)->appends($request->all());
        }

        return view('teachers.index', compact('teachers', 'sort', 'direction', 'perPage', 'roleExists'));
    }

    public function create()
    {
        if (!$this->checkProfesorRoleExists()) {
            return redirect()->route('teachers.index')->with('error', 'El rol "profesor" no existe en el sistema. Debes crearlo previamente en la sección de Administración > Roles.');
        }

        return view('teachers.create');
    }

    public function store(Request $request)
    {
        if (!$this->checkProfesorRoleExists()) {
            return back()->withErrors('El rol "profesor" no existe en el sistema. Debes crearlo previamente en la sección de Administración > Roles.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:6',
        ]);

        $teacher = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'departamento' => $request->departamento,
            'observaciones' => $request->observaciones,
            'email' => $request->email,
            'password' => Hash::make($request->password ?: $request->email),
        ]);

        $teacher->assignRole('profesor');

        return redirect()->route('teachers.index')->with('success', 'Profesor creado correctamente.');
    }

    public function edit($id)
    {
        $teacher = User::role('profesor')->findOrFail($id);
        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, $id)
    {
        $teacher = User::role('profesor')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($teacher->id)],
            'password' => 'nullable|string|min:6',
        ]);

        $data = [
            'name' => $request->name,
            'last_name' => $request->last_name,
            'departamento' => $request->departamento,
            'observaciones' => $request->observaciones,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $teacher->update($data);

        return redirect()->route('teachers.index')->with('success', 'Profesor actualizado correctamente.');
    }

    public function destroy($id)
    {
        $teacher = User::role('profesor')->findOrFail($id);
        $teacher->delete();
        return redirect()->route('teachers.index')->with('success', 'Profesor eliminado correctamente.');
    }

    public function export(Request $request, $format)
    {
        if (!$this->checkProfesorRoleExists()) {
            return back()->withErrors('El rol "profesor" no existe en el sistema.');
        }

        $teachers = User::role('profesor')->get();

        $data = $teachers->map(function ($teacher) {
            return [
                'name' => $teacher->name,
                'last_name' => $teacher->last_name,
                'email' => $teacher->email,
                'departamento' => $teacher->departamento,
                'observaciones' => $teacher->observaciones,
            ];
        })->toArray();

        return $this->downloadFormattedData($data, 'profesores_' . date('Y-md_His'), $format);
    }

    public function template($format)
    {
        $data = [
            [
                'name' => 'Carlos',
                'last_name' => 'García',
                'email' => 'carlos.garcia@example.com',
                'departamento' => 'Matemáticas',
                'observaciones' => 'Tutor 1º Bach A',
            ],
            [
                'name' => 'María',
                'last_name' => 'López',
                'email' => 'maria.lopez@example.com',
                'departamento' => 'Lengua y Literatura',
                'observaciones' => 'Jefa de Departamento',
            ]
        ];

        return $this->downloadFormattedData($data, 'plantilla_profesores', $format);
    }

    public function import(Request $request)
    {
        if (!$this->checkProfesorRoleExists()) {
            return back()->withErrors('El rol "profesor" no existe en el sistema. Debes crearlo previamente en la sección de Administración > Roles.');
        }

        set_time_limit(300);

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
                // Leer líneas completas
                $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                
                // Detectar si es el formato de Séneca (Junta de Andalucía)
                $isSenecaFormat = false;
                if (count($lines) > 0 && stripos($lines[0], 'PERSONAL DEL CENTRO') !== false) {
                    $isSenecaFormat = true;
                    // Eliminar las primeras líneas hasta encontrar los encabezados reales
                    while (count($lines) > 0) {
                        $currentLine = array_shift($lines);
                        if (stripos($currentLine, 'Empleado/a') !== false && stripos($currentLine, 'Cuenta Google/Microsoft') !== false) {
                            break; // Encontramos la línea de cabecera de Séneca
                        }
                    }
                }

                if ($isSenecaFormat) {
                    // Procesar las líneas de Séneca
                    foreach ($lines as $line) {
                        $parsedLine = str_getcsv($line);
                        if (count($parsedLine) >= 2) {
                            $empleado = trim($parsedLine[0]); // "García Pérez, Jesús"
                            $email = trim($parsedLine[1]);    // "jgarper521@g.educaand.es"
                            
                            // Extraer nombre y apellidos
                            $parts = explode(',', $empleado);
                            if (count($parts) == 2) {
                                $data[] = [
                                    'last_name' => trim($parts[0]),
                                    'name' => trim($parts[1]),
                                    'email' => $email
                                ];
                            }
                        }
                    }
                } else {
                    // Formato CSV normal
                    $parsedLines = array_map('str_getcsv', file($file->getRealPath()));
                    if (count($parsedLines) > 0) {
                        $headers = array_shift($parsedLines);
                        if (count($headers) > 0) {
                            $headers[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers[0]);
                        }
                        foreach ($parsedLines as $pLine) {
                            if (count($headers) === count($pLine)) {
                                $data[] = array_combine($headers, $pLine);
                            }
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

            $teacher = User::where('email', trim($row['email']))->first();

            $updateData = [
                'name' => trim($row['name']),
                'last_name' => trim($row['last_name']),
            ];
            if (isset($row['departamento'])) {
                $updateData['departamento'] = trim($row['departamento']);
            }
            if (isset($row['observaciones'])) {
                $updateData['observaciones'] = trim($row['observaciones']);
            }

            if ($teacher) {
                if (!$teacher->hasRole('profesor')) {
                    $teacher->assignRole('profesor');
                }
                $teacher->update($updateData);
            } else {
                $teacher = User::create(array_merge($updateData, [
                    'email' => trim($row['email']),
                    'password' => Hash::make(trim($row['email'])),
                ]));
                $teacher->assignRole('profesor');
            }
            $imported++;
        }

        return back()->with('success', "Se han importado/actualizado $imported profesores correctamente.");
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
                    fputcsv($file, ['name', 'last_name', 'email']);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        abort(400, 'Invalid format');
    }

    public function bulkDelete(Request $request)
    {
        $teacherIds = $request->input('teacher_ids', []);

        if (empty($teacherIds)) {
            return back()->withErrors('No se seleccionó ningún profesor.');
        }

        $query = User::whereIn('id', $teacherIds)->role('profesor');
        $deletedCount = $query->delete();

        return back()->with('success', "Se han eliminado $deletedCount profesores correctamente.");
    }
}
