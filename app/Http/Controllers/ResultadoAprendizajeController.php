<?php

namespace App\Http\Controllers;

use App\Models\ResultadoAprendizaje;
use App\Models\Modulo;
use Illuminate\Http\Request;

class ResultadoAprendizajeController extends Controller
{
    /**
     * Almacenar un nuevo RA para un módulo.
     */
    public function store(Request $request, Modulo $modulo)
    {
        $this->authorizeManagement();

        $request->validate([
            'codigo' => 'required|string|max:50',
            'descripcion' => 'required|string|max:2000',
            'peso' => 'nullable|numeric|min:0|max:100',
        ]);

        $maxOrden = $modulo->resultadosAprendizaje()->max('orden') ?? 0;
        $modulo->resultadosAprendizaje()->create(array_merge($request->all(), ['orden' => $maxOrden + 1]));

        return redirect()->route('modulos.show', $modulo)->with('success', 'Resultado de Aprendizaje creado correctamente.');
    }

    /**
     * Actualizar un RA.
     */
    public function update(Request $request, ResultadoAprendizaje $ra)
    {
        $this->authorizeManagement();

        $request->validate([
            'codigo' => 'required|string|max:50',
            'descripcion' => 'required|string|max:2000',
            'peso' => 'nullable|numeric|min:0|max:100',
        ]);

        $ra->update($request->all());

        return redirect()->route('modulos.show', $ra->modulo_id)->with('success', 'RA actualizado correctamente.');
    }

    /**
     * Eliminar un RA.
     */
    public function destroy(ResultadoAprendizaje $ra)
    {
        $this->authorizeManagement();
        $moduloId = $ra->modulo_id;
        $ra->delete();

        return redirect()->route('modulos.show', $moduloId)->with('success', 'RA eliminado correctamente.');
    }

    /**
     * Reordenar RAs vía drag & drop (AJAX).
     */
    public function reorder(Request $request)
    {
        $this->authorizeManagement();

        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:resultados_aprendizaje,id',
            'order.*.position' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            ResultadoAprendizaje::where('id', $item['id'])->update(['orden' => $item['position']]);
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
