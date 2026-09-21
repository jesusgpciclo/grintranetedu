<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Modulo;
use App\Models\CriterioEvaluacion;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Actividad::with(['modulo', 'criteriosEvaluacion'])->withCount('notas');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($user->hasRole('profesor') && !$user->hasAnyRole(['admin', 'directiva'])) {
            $query->whereHas('modulo.profesores', fn($q) => $q->where('users.id', $user->id));
        }
        if ($request->filled('modulo_id')) {
            $query->where('modulo_id', $request->modulo_id);
        }

        // Ordenación
        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->input('sort_by');
            if (in_array($sortBy, ['titulo', 'tipo', 'fecha_entrega', 'peso', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderByDesc('fecha_entrega');
        }

        $actividades = $query->paginate(20);
        $activeSchoolYearId = session('active_school_year_id');
        $modulos = Modulo::when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
            ->orderBy('nombre')->get();
        return view('actividades.index', compact('actividades', 'modulos'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');
        if ($user->hasAnyRole(['admin', 'directiva'])) {
            $modulos = Modulo::when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
                ->orderBy('nombre')->get();
        } else {
            $modulos = $user->modulos()
                ->when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
                ->orderBy('nombre')->get();
        }

        $criterios = collect();
        if ($request->filled('modulo_id')) {
            $criterios = CriterioEvaluacion::whereHas('resultadoAprendizaje', fn($q) => $q->where('modulo_id', $request->modulo_id))
                ->with('resultadoAprendizaje')
                ->orderBy('codigo')
                ->get();
        }
        return view('actividades.create', compact('modulos', 'criterios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:5000',
            'modulo_id' => 'required|exists:modulos,id',
            'criterios_ids' => 'nullable|array',
            'criterios_ids.*' => 'exists:criterios_evaluacion,id',
            'fecha_entrega' => 'nullable|date',
            'peso' => 'nullable|numeric|min:0|max:100',
        ]);
        $data = $request->except('criterios_ids');
        $data['es_evaluable'] = $request->has('es_evaluable');
        $actividad = Actividad::create($data);

        if ($request->has('criterios_ids')) {
            $actividad->criteriosEvaluacion()->sync($request->criterios_ids);
        }

        return redirect()->route('actividades.index')->with('success', 'Actividad creada correctamente.');
    }

    public function show(Actividad $actividad)
    {
        $actividad->load(['modulo', 'criteriosEvaluacion.resultadoAprendizaje', 'notas.alumno', 'rubrica.criterios']);
        return view('actividades.show', compact('actividad'));
    }

    public function edit(Actividad $actividad)
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');
        if ($user->hasAnyRole(['admin', 'directiva'])) {
            $modulos = Modulo::when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
                ->orderBy('nombre')->get();
        } else {
            $modulos = $user->modulos()
                ->when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
                ->orderBy('nombre')->get();
        }
        $criterios = CriterioEvaluacion::whereHas('resultadoAprendizaje', fn($q) => $q->where('modulo_id', $actividad->modulo_id))
            ->with('resultadoAprendizaje')
            ->orderBy('codigo')
            ->get();
        $actividad->load('criteriosEvaluacion');
        return view('actividades.edit', compact('actividad', 'modulos', 'criterios'));
    }

    public function update(Request $request, Actividad $actividad)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:5000',
            'modulo_id' => 'required|exists:modulos,id',
            'criterios_ids' => 'nullable|array',
            'criterios_ids.*' => 'exists:criterios_evaluacion,id',
            'fecha_entrega' => 'nullable|date',
            'peso' => 'nullable|numeric|min:0|max:100',
        ]);
        $data = $request->except('criterios_ids');
        $data['es_evaluable'] = $request->has('es_evaluable');
        $actividad->update($data);

        $actividad->criteriosEvaluacion()->sync($request->criterios_ids ?? []);

        return redirect()->route('actividades.index')->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(Actividad $actividad)
    {
        $actividad->delete();
        return redirect()->route('actividades.index')->with('success', 'Actividad eliminada correctamente.');
    }
}
