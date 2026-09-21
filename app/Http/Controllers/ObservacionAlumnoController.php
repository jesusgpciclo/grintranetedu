<?php

namespace App\Http\Controllers;

use App\Models\ObservacionAlumno;
use App\Models\User;
use Illuminate\Http\Request;

class ObservacionAlumnoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ObservacionAlumno::with(['alumno', 'profesor']);

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%");
            });
        }

        if ($user->hasRole('profesor') && !$user->hasAnyRole(['admin', 'directiva'])) {
            $query->where('profesor_id', $user->id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Ordenación
        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->input('sort_by');
            if (in_array($sortBy, ['tipo', 'fecha', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderByDesc('fecha');
        }

        $observaciones = $query->paginate(20);
        return view('observaciones.index', compact('observaciones'));
    }

    public function create(Request $request)
    {
        $students = User::whereNotNull('group_id')->orderBy('last_name')->orderBy('name')->get();
        $selectedStudentId = $request->get('alumno_id');
        return view('observaciones.create', compact('students', 'selectedStudentId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:users,id',
            'tipo' => 'required|in:positiva,negativa,informativa,seguimiento',
            'descripcion' => 'required|string|max:2000',
            'fecha' => 'required|date',
        ]);

        ObservacionAlumno::create(array_merge($request->all(), [
            'profesor_id' => auth()->id(),
        ]));

        return redirect()->route('observaciones.index')->with('success', 'Observación registrada.');
    }

    public function destroy(ObservacionAlumno $observacion)
    {
        $user = auth()->user();
        if ($observacion->profesor_id !== $user->id && !$user->hasAnyRole(['admin', 'directiva'])) {
            abort(403, 'No puedes eliminar esta observación.');
        }
        $observacion->delete();
        return redirect()->route('observaciones.index')->with('success', 'Observación eliminada.');
    }
}
