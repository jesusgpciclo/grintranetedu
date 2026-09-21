<?php

namespace App\Http\Controllers;

use App\Models\Rubrica;
use App\Models\CriterioRubrica;
use App\Models\Actividad;
use Illuminate\Http\Request;

class RubricaController extends Controller
{
    /**
     * Formulario de creación de rúbrica para una actividad.
     */
    public function create(Actividad $actividad)
    {
        return view('rubricas.create', compact('actividad'));
    }

    /**
     * Almacenar rúbrica con sus criterios.
     */
    public function store(Request $request, Actividad $actividad)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'criterios' => 'required|array|min:1',
            'criterios.*.nombre' => 'required|string|max:255',
            'criterios.*.descripcion' => 'nullable|string|max:1000',
            'criterios.*.peso' => 'required|numeric|min:0|max:100',
        ]);

        $rubrica = Rubrica::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'actividad_id' => $actividad->id,
        ]);

        foreach ($request->criterios as $criterio) {
            $niveles = [];
            if (!empty($criterio['niveles'])) {
                foreach ($criterio['niveles'] as $nivel) {
                    if (!empty($nivel['nombre'])) {
                        $niveles[] = $nivel;
                    }
                }
            }

            CriterioRubrica::create([
                'rubrica_id' => $rubrica->id,
                'nombre' => $criterio['nombre'],
                'descripcion' => $criterio['descripcion'] ?? null,
                'peso' => $criterio['peso'],
                'niveles' => !empty($niveles) ? $niveles : null,
            ]);
        }

        return redirect()->route('actividades.show', $actividad)->with('success', 'Rúbrica creada correctamente.');
    }

    /**
     * Ver detalle de rúbrica.
     */
    public function show(Rubrica $rubrica)
    {
        $rubrica->load(['actividad', 'criterios']);
        return view('rubricas.show', compact('rubrica'));
    }

    /**
     * Eliminar rúbrica.
     */
    public function destroy(Rubrica $rubrica)
    {
        $actividadId = $rubrica->actividad_id;
        $rubrica->delete();
        return redirect()->route('actividades.show', $actividadId)->with('success', 'Rúbrica eliminada.');
    }
}
