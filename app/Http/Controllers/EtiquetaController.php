<?php

namespace App\Http\Controllers;

use App\Models\Etiqueta;
use App\Http\Requests\StoreEtiquetaRequest;
use Illuminate\Http\Request;

class EtiquetaController extends Controller
{
    public function index(Request $request)
    {
        $query = Etiqueta::withCount('documentos');

        // Búsqueda (Solo en el nombre de la etiqueta)
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where('nombre', 'LIKE', "%{$buscar}%");
        }

        // Filtro por Etiquetas (Selección múltiple)
        if ($request->filled('tags')) {
            $query->whereIn('id', $request->tags);
        }

        // Ordenación
        $sortField = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['nombre', 'created_at', 'documentos_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $direction);
        }

        $etiquetas = $query->paginate(10)->withQueryString();
        $todasEtiquetasParaFiltro = Etiqueta::orderBy('nombre')->get();

        if ($request->ajax()) {
            return view('etiquetas._table', compact('etiquetas'))->render();
        }

        return view('etiquetas.index', compact('etiquetas', 'todasEtiquetasParaFiltro'));
    }

    public function create()
    {
        return view('etiquetas.create');
    }

    public function store(StoreEtiquetaRequest $request)
    {
        Etiqueta::create($request->validated());
        return redirect()->route('etiquetas.index')->with('success', 'Etiqueta creada.');
    }

    public function show(Etiqueta $etiqueta)
    {
        $etiqueta->load('documentos');
        return view('etiquetas.show', compact('etiqueta'));
    }

    public function edit(Etiqueta $etiqueta)
    {
        return view('etiquetas.edit', compact('etiqueta'));
    }

    public function update(StoreEtiquetaRequest $request, Etiqueta $etiqueta)
    {
        $etiqueta->update($request->validated());
        return redirect()->route('etiquetas.index')->with('success', 'Etiqueta actualizada.');
    }

    public function destroy(Etiqueta $etiqueta)
    {
        $etiqueta->documentos()->detach();
        $etiqueta->delete();
        return redirect()->route('etiquetas.index')->with('success', 'Etiqueta eliminada.');
    }
}
