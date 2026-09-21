<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\Modulo;
use App\Models\Sesion;
use App\Models\ObservacionAlumno;
use App\Models\Nota;
use App\Models\Asistencia;
use Illuminate\Http\Request;

class CuadernoController extends Controller
{
    /**
     * Vista principal del cuaderno de clase.
     * Muestra el resumen por grupo y permite seleccionar un alumno.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');

        // Obtener módulos del profesor, filtrados por curso escolar activo
        if ($user->hasAnyRole(['admin', 'directiva'])) {
            $modulos = Modulo::with('group')
                ->when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
                ->orderBy('nombre')->get();
        } else {
            $modulos = $user->modulos()->with('group')
                ->when($activeSchoolYearId, fn($q) => $q->whereHas('group', fn($g) => $g->where('school_year_id', $activeSchoolYearId)))
                ->orderBy('nombre')->get();
        }

        $moduloSeleccionado = null;
        $alumnos = collect();
        $resumen = [];

        if ($request->filled('modulo_id')) {
            $moduloSeleccionado = Modulo::with('group')->find($request->modulo_id);

            if ($moduloSeleccionado && $moduloSeleccionado->group) {
                $alumnos = User::where('group_id', $moduloSeleccionado->group_id)
                    ->orderBy('last_name')->orderBy('name')->get();

                foreach ($alumnos as $alumno) {
                    // Contar asistencias
                    $totalSesiones = Sesion::where('modulo_id', $moduloSeleccionado->id)
                        ->where('group_id', $moduloSeleccionado->group_id)->count();
                    $faltas = Asistencia::where('user_id', $alumno->id)
                        ->whereHas('sesion', fn($q) => $q->where('modulo_id', $moduloSeleccionado->id))
                        ->where('estado', 'falta')->count();
                    $retrasos = Asistencia::where('user_id', $alumno->id)
                        ->whereHas('sesion', fn($q) => $q->where('modulo_id', $moduloSeleccionado->id))
                        ->where('estado', 'retraso')->count();

                    // Nota media
                    $notaMedia = Nota::where('user_id', $alumno->id)
                        ->whereHas('actividad', fn($q) => $q->where('modulo_id', $moduloSeleccionado->id))
                        ->avg('valor');

                    // Observaciones
                    $obsCount = ObservacionAlumno::where('alumno_id', $alumno->id)->count();

                    $porcentajeFaltas = $totalSesiones > 0 ? round(($faltas / $totalSesiones) * 100, 1) : 0;
                    $alertaAbsentismo = $porcentajeFaltas >= 15.0;

                    $resumen[$alumno->id] = [
                        'total_sesiones' => $totalSesiones,
                        'faltas' => $faltas,
                        'retrasos' => $retrasos,
                        'porcentaje_faltas' => $porcentajeFaltas,
                        'alerta_absentismo' => $alertaAbsentismo,
                        'nota_media' => $notaMedia ? round($notaMedia, 2) : null,
                        'observaciones' => $obsCount,
                    ];
                }
            }
        }

        return view('cuaderno.index', compact('modulos', 'moduloSeleccionado', 'alumnos', 'resumen'));
    }

    /**
     * Vista detallada de un alumno en un módulo.
     */
    public function alumno(Request $request, User $alumno)
    {
        $moduloId = $request->get('modulo_id');
        $modulo = Modulo::with(['actividades.notas' => fn($q) => $q->where('user_id', $alumno->id)])->find($moduloId);

        $asistencias = Asistencia::where('user_id', $alumno->id)
            ->whereHas('sesion', fn($q) => $q->where('modulo_id', $moduloId))
            ->with('sesion')
            ->orderByDesc('created_at')->get();

        $observaciones = ObservacionAlumno::where('alumno_id', $alumno->id)
            ->with('profesor')
            ->orderByDesc('fecha')->get();

        return view('cuaderno.alumno', compact('alumno', 'modulo', 'asistencias', 'observaciones'));
    }
}
