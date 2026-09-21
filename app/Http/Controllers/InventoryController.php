<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InventoryItem::query();
        $activeSchoolYearId = session('active_school_year_id');

        if ($activeSchoolYearId) {
            $query->where('school_year_id', $activeSchoolYearId);
        }

        // Aplicar filtros dinámicamente
        $query->when($request->filled('tipo'), fn($q) => $q->where('tipo', $request->tipo))
              ->when($request->filled('subtipo'), fn($q) => $q->where('subtipo', $request->subtipo))
              ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado))
              ->when($request->filled('edificio'), fn($q) => $q->where('edificio', $request->edificio))
              ->when($request->filled('planta'), fn($q) => $q->where('planta', $request->planta))
              ->when($request->filled('dependencia'), fn($q) => $q->where('dependencia_adscripcion', $request->dependencia))
              ->when($request->filled('search'), fn($q) => $q->where('descripcion', 'like', '%' . $request->search . '%'));

        $items = $query->latest()->paginate($request->input('per_page', 25));

        // Obtener valores únicos para los selectores de filtros
        $tipos = InventoryItem::distinct()->pluck('tipo');
        $subtipos = InventoryItem::distinct()->pluck('subtipo');
        $estados = ['En uso', 'No disponible', 'Pendiente de retirar por APAE', 'Cualquiera'];
        $edificios = InventoryItem::distinct()->pluck('edificio');
        $plantas = InventoryItem::distinct()->pluck('planta');
        $dependencias = InventoryItem::distinct()->pluck('dependencia_adscripcion');

        return view('inventory.index', compact('items', 'tipos', 'subtipos', 'estados', 'edificios', 'plantas', 'dependencias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:255',
            'subtipo' => 'nullable|string|max:255',
            'procedencia' => 'nullable|string|max:255',
            'estado' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_alta' => 'nullable|date',
            'precio' => 'nullable|numeric',
            'num_registro_general' => 'nullable|string|max:255',
            'num_serie' => 'nullable|string|max:255',
            'edificio' => 'nullable|string|max:255',
            'planta' => 'nullable|string|max:255',
            'localizacion' => 'nullable|string|max:255',
            'dependencia_adscripcion' => 'nullable|string|max:255',
            'solicitud_retirada' => 'boolean',
            'fecha_baja' => 'nullable|date',
            'motivo_baja' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        if (!$request->has('solicitud_retirada')) {
            $validated['solicitud_retirada'] = false;
        }

        $validated['school_year_id'] = session('active_school_year_id');

        InventoryItem::create($validated);

        return redirect()->route('inventory.index')->with('success', 'Material inventariado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InventoryItem $inventory)
    {
        return view('inventory.show', compact('inventory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryItem $inventory)
    {
        return view('inventory.edit', compact('inventory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryItem $inventory)
    {
        $validated = $request->validate([
            'tipo' => 'required|string|max:255',
            'subtipo' => 'nullable|string|max:255',
            'procedencia' => 'nullable|string|max:255',
            'estado' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_alta' => 'nullable|date',
            'precio' => 'nullable|numeric',
            'num_registro_general' => 'nullable|string|max:255',
            'num_serie' => 'nullable|string|max:255',
            'edificio' => 'nullable|string|max:255',
            'planta' => 'nullable|string|max:255',
            'localizacion' => 'nullable|string|max:255',
            'dependencia_adscripcion' => 'nullable|string|max:255',
            'solicitud_retirada' => 'boolean',
            'fecha_baja' => 'nullable|date',
            'motivo_baja' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        if (!$request->has('solicitud_retirada')) {
            $validated['solicitud_retirada'] = false;
        }

        $inventory->update($validated);

        return redirect()->route('inventory.index')->with('success', 'Material actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index')->with('success', 'Material eliminado correctamente.');
    }
}
