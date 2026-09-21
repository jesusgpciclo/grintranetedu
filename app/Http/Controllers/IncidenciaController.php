<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $activeSchoolYearId = session('active_school_year_id');

        $query = Incidencia::with(['user', 'recurso']);

        if ($activeSchoolYearId) {
            $query->where('school_year_id', $activeSchoolYearId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('recurso', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        $incidencias = $query->orderBy($sort, $direction)->paginate($request->input('per_page', 25))->withQueryString();

        return view('incidencias.index', compact('incidencias', 'search', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $selectedRecursoId = $request->query('recurso_id');
        $tiposRecurso = \App\Models\TipoRecurso::with('recursos')->get();
        $selectedTipoId = null;
        if ($selectedRecursoId) {
            $recurso = \App\Models\Recurso::find($selectedRecursoId);
            if ($recurso) $selectedTipoId = $recurso->tipo_id;
        }
        return view('incidencias.create', compact('tiposRecurso', 'selectedRecursoId', 'selectedTipoId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha' => 'required|date',
            'prioridad' => 'required|in:baja,media,alta',
            'recurso_id' => 'nullable|exists:recursos,id',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['estado'] = 'abierta';
        $validated['school_year_id'] = session('active_school_year_id');

        Incidencia::create($validated);

        return redirect()->route('incidencias.index')->with('success', 'Incidencia reportada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Incidencia $incidencia)
    {
        return view('incidencias.show', compact('incidencia'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Incidencia $incidencia)
    {
        $user = Auth::user();
        if ($incidencia->user_id !== $user->id && !$user->hasAnyRole(['admin', 'directiva'])) {
            abort(403, 'No tienes permisos para modificar esta incidencia.');
        }
        $tiposRecurso = \App\Models\TipoRecurso::with('recursos')->get();
        $selectedTipoId = $incidencia->recurso ? $incidencia->recurso->tipo_id : null;
        return view('incidencias.edit', compact('incidencia', 'tiposRecurso', 'selectedTipoId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Incidencia $incidencia)
    {
        $user = Auth::user();
        if ($incidencia->user_id !== $user->id && !$user->hasAnyRole(['admin', 'directiva'])) {
            abort(403, 'No tienes permisos para modificar esta incidencia.');
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha' => 'required|date',
            'prioridad' => 'required|in:baja,media,alta',
            'estado' => 'required|in:abierta,en curso,resuelta,cerrada',
            'recurso_id' => 'nullable|exists:recursos,id',
        ]);

        $incidencia->update($validated);

        return redirect()->route('incidencias.index')->with('success', 'Incidencia actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Incidencia $incidencia)
    {
        $user = Auth::user();
        if ($incidencia->user_id !== $user->id && !$user->hasAnyRole(['admin', 'directiva'])) {
            abort(403, 'No tienes permisos para eliminar esta incidencia.');
        }

        $incidencia->delete();
        return redirect()->route('incidencias.index')->with('success', 'Incidencia eliminada correctamente.');
    }
}
