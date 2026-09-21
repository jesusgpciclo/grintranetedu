<?php

namespace App\Http\Controllers;

use App\Models\Actualizacion;
use Illuminate\Http\Request;

class ActualizacionController extends Controller
{
    /**
     * Listado de actualizaciones (accesible a todos).
     */
    public function index()
    {
        $actualizaciones = Actualizacion::with('autor')->orderByDesc('fecha')->get();
        return view('actualizaciones.index', compact('actualizaciones'));
    }

    /**
     * Formulario de creación (solo admin).
     */
    public function create()
    {
        $this->authorizeAdmin();
        return view('actualizaciones.create');
    }

    /**
     * Almacenar nueva actualización.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'version' => 'required|string|max:50',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:5000',
            'fecha' => 'required|date',
        ]);

        Actualizacion::create(array_merge($request->all(), [
            'autor_id' => auth()->id(),
        ]));

        return redirect()->route('actualizaciones.index')->with('success', 'Actualización registrada.');
    }

    /**
     * Eliminar actualización (solo admin).
     */
    public function destroy(Actualizacion $actualizacion)
    {
        $this->authorizeAdmin();
        $actualizacion->delete();
        return redirect()->route('actualizaciones.index')->with('success', 'Actualización eliminada.');
    }

    private function authorizeAdmin()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Acción no autorizada.');
        }
    }
}
