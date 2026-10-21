<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\HallPassIncompatibilityGroup;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HallPassIncompatibilityController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|salidas.incompatibilities'),
        ];
    }

    /**
     * Display a listing of the incompatibility groups.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $query = HallPassIncompatibilityGroup::with(['students.groupRel'])
            ->withCount('students')
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('students', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $groups = $query->paginate(15)->withQueryString();

        return view('salidas.incompatibilities.index', compact('groups', 'search'));
    }

    /**
     * Show the form for creating a new incompatibility group.
     */
    public function create()
    {
        $students = $this->getSortedStudents();
        $classGroups = Group::orderBy('course')->orderBy('name')->get();
        $selectedStudentIds = old('student_ids', []);

        return view('salidas.incompatibilities.form', [
            'incompatibility' => new HallPassIncompatibilityGroup(),
            'students' => $students,
            'classGroups' => $classGroups,
            'selectedStudentIds' => (array) $selectedStudentIds,
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created incompatibility group in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'student_ids' => 'required|array|min:2',
            'student_ids.*' => 'exists:users,id',
        ], [
            'name.required' => 'El nombre del grupo es obligatorio.',
            'student_ids.required' => 'Debes seleccionar al menos dos alumnos para formar un grupo de incompatibilidad.',
            'student_ids.min' => 'Debes seleccionar al menos dos alumnos para que haya incompatibilidad entre ellos.',
        ]);

        $group = HallPassIncompatibilityGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $group->students()->sync($validated['student_ids']);

        return redirect()->route('salidas.incompatibilities.index')
            ->with('success', 'Grupo de incompatibilidad creado correctamente.');
    }

    /**
     * Show the form for editing the specified incompatibility group.
     */
    public function edit(HallPassIncompatibilityGroup $incompatibility)
    {
        $incompatibility->load('students');
        $students = $this->getSortedStudents();
        $classGroups = Group::orderBy('course')->orderBy('name')->get();
        $selectedStudentIds = old('student_ids', $incompatibility->students->pluck('id')->toArray());

        return view('salidas.incompatibilities.form', [
            'incompatibility' => $incompatibility,
            'students' => $students,
            'classGroups' => $classGroups,
            'selectedStudentIds' => (array) $selectedStudentIds,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified incompatibility group in storage.
     */
    public function update(Request $request, HallPassIncompatibilityGroup $incompatibility)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'student_ids' => 'required|array|min:2',
            'student_ids.*' => 'exists:users,id',
        ], [
            'name.required' => 'El nombre del grupo es obligatorio.',
            'student_ids.required' => 'Debes seleccionar al menos dos alumnos para formar un grupo de incompatibilidad.',
            'student_ids.min' => 'Debes seleccionar al menos dos alumnos para que haya incompatibilidad entre ellos.',
        ]);

        $incompatibility->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $incompatibility->students()->sync($validated['student_ids']);

        return redirect()->route('salidas.incompatibilities.index')
            ->with('success', 'Grupo de incompatibilidad actualizado correctamente.');
    }

    /**
     * Remove the specified incompatibility group from storage.
     */
    public function destroy(HallPassIncompatibilityGroup $incompatibility)
    {
        $incompatibility->students()->detach();
        $incompatibility->delete();

        return redirect()->route('salidas.incompatibilities.index')
            ->with('success', 'Grupo de incompatibilidad eliminado correctamente.');
    }

    /**
     * Get sorted list of students.
     */
    private function getSortedStudents()
    {
        return User::role('alumno')
            ->with('groupRel')
            ->get()
            ->sort(function ($a, $b) {
                $aGroup = mb_strtolower(trim($a->groupRel?->name ?? ''));
                $bGroup = mb_strtolower(trim($b->groupRel?->name ?? ''));
                if ($aGroup !== $bGroup) {
                    return strcoll($aGroup, $bGroup);
                }

                $aLastName = mb_strtolower(trim($a->last_name ?? ''));
                $bLastName = mb_strtolower(trim($b->last_name ?? ''));
                $cmpLast = strcoll($aLastName, $bLastName);
                if ($cmpLast !== 0) {
                    return $cmpLast;
                }

                $aName = mb_strtolower(trim($a->name));
                $bName = mb_strtolower(trim($b->name));
                return strcoll($aName, $bName);
            })
            ->values();
    }
}
