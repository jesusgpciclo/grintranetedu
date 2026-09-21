<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Http\Requests\StoreSchoolYearRequest;
use App\Http\Requests\UpdateSchoolYearRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SchoolYearController extends Controller implements \Illuminate\Routing\Controllers\HasMiddleware
{
    public static function middleware(): array
    {
        // Enforce admin role for all School Year management operations
        return [
            'role:admin',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $isActiveFilter = $request->input('is_active');
        $perPage = (int) $request->input('per_page', 25);

        // Enforce exact pagination defaults
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 25;
        }

        $query = SchoolYear::query();

        // Search
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filtering
        if ($isActiveFilter !== null && $isActiveFilter !== '') {
            $query->where('is_active', (bool) $isActiveFilter);
        }

        // Sorting
        if (in_array($sort, ['name', 'is_active', 'created_at'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Handle Export requests
        if ($request->has('export')) {
            $format = $request->input('export');
            $data = $query->get();
            return $this->exportData($data, $format);
        }

        $schoolYears = $query->paginate($perPage)->withQueryString();

        return view('school_years.index', compact('schoolYears', 'search', 'sort', 'direction', 'isActiveFilter', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('school_years.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchoolYearRequest $request)
    {
        $validated = $request->validated();
        $isFirst = SchoolYear::count() === 0;

        DB::transaction(function () use ($validated, $isFirst) {
            $isActive = isset($validated['is_active']) ? (bool)$validated['is_active'] : $isFirst;

            if ($isActive) {
                // Deactivate all others
                SchoolYear::where('is_active', true)->update(['is_active' => false]);
            }

            SchoolYear::create([
                'name' => $validated['name'],
                'is_active' => $isActive,
            ]);
        });

        // Clear active school year session to force refresh
        Session::forget('active_school_year_id');

        return redirect()->route('school-years.index')->with('success', 'Curso escolar creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolYear $schoolYear)
    {
        return view('school_years.edit', compact('schoolYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolYearRequest $request, SchoolYear $schoolYear)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $schoolYear) {
            $isActive = isset($validated['is_active']) ? (bool)$validated['is_active'] : $schoolYear->is_active;

            if ($isActive && !$schoolYear->is_active) {
                // Set others to inactive
                SchoolYear::where('is_active', true)->update(['is_active' => false]);
            }

            $schoolYear->update([
                'name' => $validated['name'],
                'is_active' => $isActive,
            ]);
        });

        // Clear active school year session to force refresh
        Session::forget('active_school_year_id');

        return redirect()->route('school-years.index')->with('success', 'Curso escolar actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolYear $schoolYear)
    {
        if ($schoolYear->is_active) {
            return redirect()->route('school-years.index')->with('error', 'No se puede eliminar el curso escolar activo.');
        }

        $schoolYear->delete();
        Session::forget('active_school_year_id');

        return redirect()->route('school-years.index')->with('success', 'Curso escolar eliminado correctamente.');
    }

    /**
     * Mark a school year as active.
     */
    public function activate(SchoolYear $schoolYear)
    {
        DB::transaction(function () use ($schoolYear) {
            SchoolYear::where('is_active', true)->update(['is_active' => false]);
            $schoolYear->update(['is_active' => true]);
        });

        Session::forget('active_school_year_id');

        return redirect()->route('school-years.index')->with('success', 'Curso escolar marcado como activo.');
    }

    /**
     * Export data helper.
     */
    private function exportData($data, $format)
    {
        if ($format === 'json') {
            return response()->json($data);
        }

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="cursos_escolares.csv"',
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM for Excel compliance
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

                // Headers
                fputcsv($file, ['ID', 'Nombre', 'Estado (Activo)', 'Fecha Creación']);

                foreach ($data as $row) {
                    $status = $row->is_active ? 'Activo' : 'Inactivo';
                    // Sanitize against CSV formula injection
                    $name = $row->name;
                    if (in_array(substr($name, 0, 1), ['=', '+', '-', '@'])) {
                        $name = "'" . $name;
                    }

                    fputcsv($file, [
                        $row->id,
                        $name,
                        $status,
                        $row->created_at ? $row->created_at->format('Y-m-d H:i') : ''
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($format === 'print') {
            return view('school_years.print', compact('data'));
        }

        abort(400, 'Formato de exportación no soportado.');
    }
}
