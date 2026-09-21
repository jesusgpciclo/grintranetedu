<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class ModuloController extends Controller
{


    /**
     * Listado de módulos.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');
        $query = Modulo::with(['group', 'profesores'])->withCount('resultadosAprendizaje');

        // Filter by groups in the active school year
        if ($activeSchoolYearId) {
            $query->whereHas('group', fn($q) => $q->where('school_year_id', $activeSchoolYearId));
        }

        // Profesores solo ven sus módulos
        if ($user->hasRole('profesor') && !$user->hasAnyRole(['admin', 'directiva'])) {
            $query->whereHas('profesores', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%");
            });
        }

        // Ordenación
        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->input('sort_by');
            if (in_array($sortBy, ['codigo', 'nombre', 'horas_semanales', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('nombre', 'asc');
        }

        $modulos = $query->paginate($request->input('per_page', 25));
        return view('modulos.index', compact('modulos'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'directiva']), 403, 'No tienes permiso para esta acción.');
        $activeSchoolYearId = session('active_school_year_id');
        $groups = Group::when($activeSchoolYearId, fn($q) => $q->where('school_year_id', $activeSchoolYearId))
            ->orderBy('course')->orderBy('name')->get();
        $profesores = User::role(['admin', 'profesor'])->orderBy('name')->get();
        return view('modulos.create', compact('groups', 'profesores'));
    }

    /**
     * Almacenar nuevo módulo.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'directiva']), 403, 'No tienes permiso para esta acción.');
        $request->validate([
            'codigo' => 'required|string|max:50|unique:modulos,codigo',
            'nombre' => 'required|string|max:255',
            'group_id' => 'nullable|exists:groups,id',
            'profesores_ids' => 'nullable|array',
            'profesores_ids.*' => 'exists:users,id',
            'horas_semanales' => 'nullable|integer|min:0|max:40',
            'descripcion' => 'nullable|string|max:2000',
        ]);

        $modulo = Modulo::create($request->except('profesores_ids'));

        if ($request->has('profesores_ids')) {
            $modulo->profesores()->sync($request->profesores_ids);
        }

        return redirect()->route('modulos.index')->with('success', 'Módulo creado correctamente.');
    }

    /**
     * Detalle del módulo con sus RA y CE.
     */
    public function show(Modulo $modulo)
    {
        $modulo->load(['group', 'profesores', 'resultadosAprendizaje.criteriosEvaluacion', 'actividades']);
        return view('modulos.show', compact('modulo'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Modulo $modulo)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'directiva']), 403, 'No tienes permiso para esta acción.');
        $activeSchoolYearId = session('active_school_year_id');
        // Show all groups but default filter to active school year
        $groups = Group::orderBy('course')->orderBy('name')->get();
        $profesores = User::role(['admin', 'profesor'])->orderBy('name')->get();
        return view('modulos.edit', compact('modulo', 'groups', 'profesores'));
    }

    /**
     * Actualizar módulo.
     */
    public function update(Request $request, Modulo $modulo)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'directiva']), 403, 'No tienes permiso para esta acción.');
        $request->validate([
            'codigo' => 'required|string|max:50|unique:modulos,codigo,' . $modulo->id,
            'nombre' => 'required|string|max:255',
            'group_id' => 'nullable|exists:groups,id',
            'profesores_ids' => 'nullable|array',
            'profesores_ids.*' => 'exists:users,id',
            'horas_semanales' => 'nullable|integer|min:0|max:40',
            'descripcion' => 'nullable|string|max:2000',
        ]);

        $modulo->update($request->except('profesores_ids'));

        if ($request->has('profesores_ids')) {
            $modulo->profesores()->sync($request->profesores_ids);
        } else {
            $modulo->profesores()->detach();
        }

        return redirect()->route('modulos.index')->with('success', 'Módulo actualizado correctamente.');
    }

    /**
     * Eliminar módulo.
     */
    public function destroy(Modulo $modulo)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'directiva']), 403, 'No tienes permiso para esta acción.');
        $modulo->delete();
        return redirect()->route('modulos.index')->with('success', 'Módulo eliminado correctamente.');
    }
}
