<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');
        $query = Group::with(['tutor', 'schoolYear'])->withCount('students');

        if ($activeSchoolYearId) {
            $query->where('school_year_id', $activeSchoolYearId);
        }

        if ($user->hasRole('profesor')) {
            $query->where('tutor_id', $user->id);
        }

        $groups = $query->get();
        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        $tutors = User::role(['admin', 'profesor'])->get();
        if ($tutors->isEmpty()) {
            $tutors = User::all();
        }
        $schoolYears = SchoolYear::orderBy('name', 'desc')->get();
        $activeSchoolYearId = session('active_school_year_id');
        return view('groups.create', compact('tutors', 'schoolYears', 'activeSchoolYearId'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'course' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'tutor_id' => 'nullable|exists:users,id',
            'school_year_id' => 'nullable|exists:school_years,id',
        ]);

        $data = $request->only(['course', 'name', 'tutor_id', 'school_year_id']);
        if (empty($data['school_year_id'])) {
            $data['school_year_id'] = session('active_school_year_id');
        }

        Group::create($data);

        return redirect()->route('groups.index')->with('success', 'Grupo creado correctamente.');
    }

    public function show(Group $group)
    {
        $user = auth()->user();
        
        // Authorization: Admin or the actual tutor
        if (!$user->hasRole('admin') && $group->tutor_id !== $user->id) {
            abort(403, 'No tienes permiso para ver este grupo.');
        }

        $group->load(['tutor', 'students']);
        return view('groups.show', compact('group'));
    }

    public function edit(Group $group)
    {
        $this->authorizeAdmin();
        $tutors = User::role(['admin', 'profesor'])->get();
        if ($tutors->isEmpty()) {
            $tutors = User::all();
        }
        $schoolYears = SchoolYear::orderBy('name', 'desc')->get();
        $activeSchoolYearId = session('active_school_year_id');
        return view('groups.edit', compact('group', 'tutors', 'schoolYears', 'activeSchoolYearId'));
    }

    public function update(Request $request, Group $group)
    {
        $this->authorizeAdmin();
        $request->validate([
            'course' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'tutor_id' => 'nullable|exists:users,id',
            'school_year_id' => 'nullable|exists:school_years,id',
        ]);

        $group->update($request->only(['course', 'name', 'tutor_id', 'school_year_id']));

        return redirect()->route('groups.index')->with('success', 'Grupo actualizado correctamente.');
    }

    public function destroy(Group $group)
    {
        $this->authorizeAdmin();
        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Grupo eliminado correctamente.');
    }

    public function export(Request $request, $format)
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');
        $query = Group::with(['tutor', 'schoolYear']);

        if ($activeSchoolYearId) {
            $query->where('school_year_id', $activeSchoolYearId);
        }

        if ($user->hasRole('profesor') && !$user->hasRole('admin')) {
            $query->where('tutor_id', $user->id);
        }

        $groups = $query->get();

        $data = $groups->map(function ($group) {
            return [
                'course' => $group->course,
                'name' => $group->name,
                'tutor_email' => $group->tutor ? $group->tutor->email : null,
            ];
        })->toArray();

        return $this->downloadFormattedData($data, 'groups_' . date('Y-md_His'), $format);
    }

    public function template($format)
    {
        $this->authorizeAdmin();
        $data = [
            [
                'course' => '1º ESO',
                'name' => 'A',
                'tutor_email' => 'tutor@example.com'
            ],
            [
                'course' => '1º ESO',
                'name' => 'B',
                'tutor_email' => ''
            ]
        ];

        return $this->downloadFormattedData($data, 'plantilla_grupos', $format);
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
                    fputcsv($file, ['course', 'name', 'tutor_email']);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        abort(400, 'Invalid format');
    }

    public function import(Request $request)
    {
        $this->authorizeAdmin();
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
                    // Remove potential BOM from first header
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

        $activeSchoolYearId = session('active_school_year_id');
        if (!$activeSchoolYearId) {
            return back()->withErrors('Debe seleccionar un curso escolar activo primero.');
        }

        $imported = 0;
        foreach ($data as $row) {
            if (empty($row['course']) || empty($row['name'])) {
                continue;
            }

            $tutorId = null;
            if (!empty($row['tutor_email'])) {
                $tutor = User::where('email', trim($row['tutor_email']))->first();
                if ($tutor) {
                    $tutorId = $tutor->id;
                }
            }

            Group::updateOrCreate(
                [
                    'course' => trim($row['course']),
                    'name' => trim($row['name']),
                    'school_year_id' => $activeSchoolYearId,
                ],
                [
                    'tutor_id' => $tutorId,
                ]
            );
            $imported++;
        }

        return back()->with('success', "Se han importado/actualizado $imported grupos correctamente.");
    }

    private function authorizeAdmin()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Acción no autorizada.');
        }
    }
}
