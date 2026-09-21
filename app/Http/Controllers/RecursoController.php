<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use App\Models\TipoRecurso;
use App\Models\User;
use Illuminate\Http\Request;

class RecursoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $query = Recurso::with(['tipo', 'responsable']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhere('numero_serie', 'like', "%{$search}%")
                  ->orWhere('ubicacion', 'like', "%{$search}%")
                  ->orWhere('estado', 'like', "%{$search}%")
                  ->orWhereHas('tipo', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('responsable', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['nombre', 'estado', 'ubicacion', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'desc';

        $recursos = $query->orderBy($sort, $direction)->paginate($request->input('per_page', 25))->withQueryString();

        return view('recursos.index', compact('recursos', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        $tipos = TipoRecurso::all();
        $usuarios = User::all();
        return view('recursos.create', compact('tipos', 'usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_id' => 'required|exists:tipo_recursos,id',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255|unique:recursos,numero_serie',
            'estado' => 'required|in:disponible,en reparación,dado de baja',
            'ubicacion' => 'nullable|string|max:255',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        Recurso::create($request->all());

        return redirect()->route('recursos.index')->with('success', 'Recurso creado correctamente.');
    }

    public function edit(Recurso $recurso)
    {
        $tipos = TipoRecurso::all();
        $usuarios = User::all();
        return view('recursos.edit', compact('recurso', 'tipos', 'usuarios'));
    }

    public function update(Request $request, Recurso $recurso)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_id' => 'required|exists:tipo_recursos,id',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255|unique:recursos,numero_serie,' . $recurso->id,
            'estado' => 'required|in:disponible,en reparación,dado de baja',
            'ubicacion' => 'nullable|string|max:255',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        $recurso->update($request->all());

        return redirect()->route('recursos.index')->with('success', 'Recurso actualizado correctamente.');
    }

    public function destroy(Recurso $recurso)
    {
        $recurso->delete();
        return redirect()->route('recursos.index')->with('success', 'Recurso eliminado correctamente.');
    }
}
