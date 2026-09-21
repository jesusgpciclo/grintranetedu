<?php

namespace App\Http\Controllers;

use App\Models\TipoRecurso;
use Illuminate\Http\Request;

class TipoRecursoController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoRecurso::withCount('categorias');

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where('nombre', 'LIKE', "%{$buscar}%");
        }

        // Ordenación
        $sortField = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['nombre', 'created_at', 'categorias_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $direction);
        }

        $tipo_recursos = $query->paginate($request->input('per_page', 25))->withQueryString();

        if ($request->ajax()) {
            return view('tipo_recursos._table', compact('tipo_recursos'))->render();
        }

        return view('tipo_recursos.index', compact('tipo_recursos'));
    }

    public function create()
    {
        return view('tipo_recursos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:tipo_recursos,nombre',
        ], [
            'nombre.required' => 'El nombre del recurso es obligatorio.',
            'nombre.unique' => 'Ya existe un recurso con este nombre.',
        ]);
        TipoRecurso::create($validated);
        return redirect()->route('tipo-recursos.index')->with('success', 'Tipo de Recurso creado correctamente.');
    }

    public function edit(TipoRecurso $tipo_recurso)
    {
        return view('tipo_recursos.edit', compact('tipo_recurso'));
    }

    public function update(Request $request, TipoRecurso $tipo_recurso)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:tipo_recursos,nombre,' . $tipo_recurso->id,
        ], [
            'nombre.required' => 'El nombre del recurso es obligatorio.',
            'nombre.unique' => 'Ya existe un recurso con este nombre.',
        ]);
        $tipo_recurso->update($validated);
        return redirect()->route('tipo-recursos.index')->with('success', 'Tipo de Recurso actualizado correctamente.');
    }

    public function destroy(TipoRecurso $tipo_recurso)
    {
        // Set null for associated categories, or just let DB cascade set null
        $tipo_recurso->categorias()->update(['tipo_recurso_id' => null]);
        $tipo_recurso->delete();
        return redirect()->route('tipo-recursos.index')->with('success', 'Tipo de Recurso eliminado correctamente.');
    }
}
