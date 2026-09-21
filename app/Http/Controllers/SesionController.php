<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\Modulo;
use App\Models\Group;
use App\Models\Asistencia;
use App\Models\User;
use Illuminate\Http\Request;

class SesionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Sesion::with(['modulo', 'group', 'profesor'])->withCount('asistencias');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contenidos', 'like', "%{$search}%");
            });
        }

        if ($user->hasRole('profesor') && !$user->hasAnyRole(['admin', 'directiva'])) {
            $query->where('profesor_id', $user->id)
                  ->orWhereHas('modulo.profesores', fn($q) => $q->where('users.id', $user->id));
        }
        if ($request->filled('modulo_id')) {
            $query->where('modulo_id', $request->modulo_id);
        }
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        // Ordenación
        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->input('sort_by');
            if (in_array($sortBy, ['fecha', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderByDesc('fecha');
        }

        $sesiones = $query->paginate(20);
        $activeSchoolYearId = session('active_school_year_id');
        $modulos = Modulo::when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
            ->orderBy('nombre')->get();
        $groups = Group::when($activeSchoolYearId, fn($q) => $q->where('school_year_id', $activeSchoolYearId))
            ->orderBy('course')->orderBy('name')->get();

        return view('sesiones.index', compact('sesiones', 'modulos', 'groups'));
    }

    public function create()
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');
        $modulos = $user->hasAnyRole(['admin', 'directiva'])
            ? Modulo::with('group')
                ->when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
                ->orderBy('nombre')->get()
            : $user->modulos()->with('group')
                ->when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
                ->orderBy('nombre')->get();
        $groups = Group::when($activeSchoolYearId, fn($q) => $q->where('school_year_id', $activeSchoolYearId))
            ->orderBy('course')->orderBy('name')->get();
        return view('sesiones.create', compact('modulos', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'modulo_id' => 'required|exists:modulos,id',
            'group_id' => 'required|exists:groups,id',
            'contenidos' => 'nullable|string|max:5000',
            'actividades_realizadas' => 'nullable|string|max:5000',
            'tareas_mandadas' => 'nullable|string|max:5000',
            'observaciones' => 'nullable|string|max:2000',
        ]);

        $sesion = Sesion::create(array_merge($request->all(), [
            'profesor_id' => auth()->id(),
        ]));

        return redirect()->route('sesiones.show', $sesion)->with('success', 'Sesión registrada. Ahora puedes pasar lista.');
    }

    public function show(Sesion $sesion)
    {
        $sesion->load(['modulo', 'group', 'profesor', 'asistencias.alumno']);
        $students = User::where('group_id', $sesion->group_id)->orderBy('last_name')->orderBy('name')->get();
        $asistenciaMap = $sesion->asistencias->keyBy('user_id');

        return view('sesiones.show', compact('sesion', 'students', 'asistenciaMap'));
    }

    public function edit(Sesion $sesion)
    {
        $this->authorizeSesion($sesion);
        $user = auth()->user();
        $modulos = $user->hasAnyRole(['admin', 'directiva'])
            ? Modulo::with('group')->orderBy('nombre')->get()
            : $user->modulos()->with('group')->orderBy('nombre')->get();
        $groups = Group::orderBy('course')->orderBy('name')->get();
        return view('sesiones.edit', compact('sesion', 'modulos', 'groups'));
    }

    public function update(Request $request, Sesion $sesion)
    {
        $this->authorizeSesion($sesion);
        $request->validate([
            'fecha' => 'required|date',
            'modulo_id' => 'required|exists:modulos,id',
            'group_id' => 'required|exists:groups,id',
            'contenidos' => 'nullable|string|max:5000',
            'actividades_realizadas' => 'nullable|string|max:5000',
            'tareas_mandadas' => 'nullable|string|max:5000',
            'observaciones' => 'nullable|string|max:2000',
        ]);

        $sesion->update($request->all());
        return redirect()->route('sesiones.show', $sesion)->with('success', 'Sesión actualizada.');
    }

    public function guardarAsistencia(Request $request, Sesion $sesion)
    {
        $this->authorizeSesion($sesion);
        $request->validate([
            'asistencias' => 'required|array',
            'asistencias.*.user_id' => 'required|exists:users,id',
            'asistencias.*.estado' => 'required|in:presente,falta,falta_justificada,retraso',
            'asistencias.*.observacion' => 'nullable|string|max:500',
        ]);

        foreach ($request->asistencias as $data) {
            Asistencia::updateOrCreate(
                ['sesion_id' => $sesion->id, 'user_id' => $data['user_id']],
                ['estado' => $data['estado'], 'observacion' => $data['observacion'] ?? null]
            );
        }

        return redirect()->route('sesiones.show', $sesion)->with('success', 'Asistencia guardada correctamente.');
    }

    public function destroy(Sesion $sesion)
    {
        $this->authorizeSesion($sesion);
        $sesion->delete();
        return redirect()->route('sesiones.index')->with('success', 'Sesión eliminada.');
    }

    private function authorizeSesion(Sesion $sesion)
    {
        $user = auth()->user();
        $canManage = $user->hasAnyRole(['admin', 'directiva'])
            || $sesion->profesor_id === $user->id
            || ($sesion->modulo && $sesion->modulo->profesores()->where('users.id', $user->id)->exists());

        if (!$canManage) {
            abort(403, 'No tienes permisos para gestionar esta sesión de clase.');
        }
    }
}
