<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Requests\StoreCategoriaRequest;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->get('view') === 'tree') {
            $tree = Categoria::whereNull('parent_id')
                ->with(['childrenRecursive', 'documentos'])
                ->orderBy('nombre')
                ->get();

            if ($request->ajax()) {
                return view('categorias._tree', compact('tree'))->render();
            }

            return view('categorias.index', compact('tree'));
        }

        $query = Categoria::with(['parent'])->withCount('documentos');

        // Búsqueda
        if ($request->filled('buscar')) {
            $query->where('nombre', 'LIKE', "%{$request->buscar}%");
        }

        // Ordenación
        $sortField = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['nombre', 'created_at', 'documentos_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $direction);
        }

        $carpetas = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('categorias._table', compact('carpetas'))->render();
        }

        return view('categorias.index', compact('carpetas'));
    }

    public function create()
    {
        $carpetasPadre = Categoria::orderBy('nombre')->get();
        return view('categorias.create', compact('carpetasPadre'));
    }

    public function store(StoreCategoriaRequest $request)
    {
        Categoria::create($request->validated());
        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    public function show(Categoria $categoria)
    {
        $categoria->load('documentos', 'tipoRecurso');
        return view('categorias.show', compact('categoria'));
    }

    public function edit(Categoria $categoria)
    {
        $carpetasPadre = Categoria::where('id', '!=', $categoria->id)->orderBy('nombre')->get();
        return view('categorias.edit', compact('categoria', 'carpetasPadre'));
    }

    public function update(StoreCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());
        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->documentos()->detach();
        $categoria->delete();
        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada correctamente.');
    }
}
