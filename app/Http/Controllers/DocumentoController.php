<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    /**
     * Listado de documentos (filtrable por categoría).
     */
    public function index(Request $request)
    {
        $query = Documento::with('autor');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        // Filtros específicos
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        if ($request->filled('departamento')) {
            $query->where('departamento', $request->departamento);
        }

        // Ordenación
        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->input('sort_by');
            if (in_array($sortBy, ['titulo', 'categoria', 'departamento', 'curso', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderByDesc('created_at');
        }

        $documentos = $query->paginate(20);
        $categorias = ['normativa', 'programacion', 'acta', 'plantilla', 'otro'];
        $departamentos = Documento::select('departamento')->distinct()->whereNotNull('departamento')->pluck('departamento');

        return view('documentos.index', compact('documentos', 'categorias', 'departamentos'));
    }

    /**
     * Formulario de creación.
     */
    public function create()
    {
        return view('documentos.create');
    }

    /**
     * Almacenar documento.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'categoria' => 'required|string|max:100',
            'departamento' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:20',
            'url' => 'nullable|url|max:500',
            'archivo' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,zip,rar,png,jpg,jpeg|max:10240',
        ]);

        $data = $request->except('archivo');
        $data['user_id'] = auth()->id();

        if ($request->hasFile('archivo')) {
            $data['archivo'] = $request->file('archivo')->store('documentos', 'public');
        }

        Documento::create($data);

        return redirect()->route('documentos.index')->with('success', 'Documento registrado correctamente.');
    }

    /**
     * Editar documento.
     */
    public function edit(Documento $documento)
    {
        $user = auth()->user();
        if ($documento->user_id !== $user->id && !$user->hasAnyRole(['admin', 'directiva'])) {
            abort(403, 'No tienes permisos para editar este documento.');
        }
        return view('documentos.edit', compact('documento'));
    }

    /**
     * Actualizar documento.
     */
    public function update(Request $request, Documento $documento)
    {
        $user = auth()->user();
        if ($documento->user_id !== $user->id && !$user->hasAnyRole(['admin', 'directiva'])) {
            abort(403, 'No tienes permisos para modificar este documento.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'categoria' => 'required|string|max:100',
            'departamento' => 'nullable|string|max:255',
            'curso' => 'nullable|string|max:20',
            'url' => 'nullable|url|max:500',
            'archivo' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,odp,zip,rar,png,jpg,jpeg|max:10240',
        ]);

        $data = $request->except('archivo');

        if ($request->hasFile('archivo')) {
            if ($documento->archivo) {
                Storage::disk('public')->delete($documento->archivo);
            }
            $data['archivo'] = $request->file('archivo')->store('documentos', 'public');
        }

        $documento->update($data);

        return redirect()->route('documentos.index')->with('success', 'Documento actualizado correctamente.');
    }

    /**
     * Eliminar documento.
     */
    public function destroy(Documento $documento)
    {
        $user = auth()->user();
        if ($documento->user_id !== $user->id && !$user->hasAnyRole(['admin', 'directiva'])) {
            abort(403, 'No tienes permisos para eliminar este documento.');
        }

        $documento->delete();
        return redirect()->route('documentos.index')->with('success', 'Documento eliminado correctamente.');
    }

    /**
     * API endpoint to retrieve documents for attachments.
     */
    public function apiList(Request $request)
    {
        $search = $request->query('search', '');
        $query = Documento::query();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        $documentos = $query->orderByDesc('created_at')->limit(30)->get();

        return response()->json($documentos->map(function($doc) {
            return [
                'id' => $doc->id,
                'titulo' => $doc->titulo,
                'url' => $doc->archivo ? asset('storage/' . $doc->archivo) : $doc->url,
            ];
        }));
    }
}
