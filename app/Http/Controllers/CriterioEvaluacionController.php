<?php

namespace App\Http\Controllers;

use App\Models\CriterioEvaluacion;
use App\Models\ResultadoAprendizaje;
use Illuminate\Http\Request;

class CriterioEvaluacionController extends Controller
{
    /**
     * Almacenar un nuevo CE para un RA.
     */
    public function store(Request $request, ResultadoAprendizaje $ra)
    {
        $this->authorizeManagement();

        $request->validate([
            'codigo' => 'required|string|max:50',
            'descripcion' => 'required|string|max:2000',
            'peso' => 'nullable|numeric|min:0|max:100',
        ]);

        $maxOrden = $ra->criteriosEvaluacion()->max('orden') ?? 0;
        $ra->criteriosEvaluacion()->create(array_merge($request->all(), ['orden' => $maxOrden + 1]));

        return redirect()->route('modulos.show', $ra->modulo_id)->with('success', 'Criterio de Evaluación creado correctamente.');
    }

    /**
     * Actualizar un CE.
     */
    public function update(Request $request, CriterioEvaluacion $ce)
    {
        $this->authorizeManagement();

        $request->validate([
            'codigo' => 'required|string|max:50',
            'descripcion' => 'required|string|max:2000',
            'peso' => 'nullable|numeric|min:0|max:100',
        ]);

        $ce->update($request->all());

        return redirect()->route('modulos.show', $ce->resultadoAprendizaje->modulo_id)->with('success', 'CE actualizado correctamente.');
    }

    /**
     * Eliminar un CE.
     */
    public function destroy(CriterioEvaluacion $ce)
    {
        $this->authorizeManagement();
        $moduloId = $ce->resultadoAprendizaje->modulo_id;
        $ce->delete();

        return redirect()->route('modulos.show', $moduloId)->with('success', 'CE eliminado correctamente.');
    }

    /**
     * Reordenar CEs vía drag & drop (AJAX).
     */
    public function reorder(Request $request)
    {
        $this->authorizeManagement();

        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:criterios_evaluacion,id',
            'order.*.position' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            CriterioEvaluacion::where('id', $item['id'])->update(['orden' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    private function authorizeManagement()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'directiva'])) {
            abort(403, 'No tienes permiso para esta acción.');
        }
    }
}
