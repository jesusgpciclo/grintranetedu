<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Http\Request;

class NotaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');
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
        $actividades = collect();
        $students = collect();
        $notasMap = [];

        if ($request->filled('modulo_id')) {
            $moduloSeleccionado = Modulo::with(['group', 'actividades.criteriosEvaluacion'])->find($request->modulo_id);
            if ($moduloSeleccionado && $moduloSeleccionado->group) {
                $students = User::where('group_id', $moduloSeleccionado->group_id)
                    ->orderBy('last_name')->orderBy('name')->get();
                $actividades = $moduloSeleccionado->actividades()->where('es_evaluable', true)->orderBy('titulo')->get();

                $notas = Nota::whereIn('actividad_id', $actividades->pluck('id'))
                    ->whereIn('user_id', $students->pluck('id'))->get();
                foreach ($notas as $nota) {
                    $notasMap[$nota->user_id][$nota->actividad_id] = $nota;
                }
            }
        }

        return view('notas.index', compact('modulos', 'moduloSeleccionado', 'actividades', 'students', 'notasMap'));
    }

    public function guardarNotas(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'notas' => 'required|array',
            'notas.*.student_id' => 'required|exists:users,id',
            'notas.*.actividad_id' => 'required|exists:actividades,id',
            'notas.*.valor' => 'nullable|numeric|min:0|max:10',
        ]);

        if (!$user->hasAnyRole(['admin', 'directiva'])) {
            $actividadIds = collect($request->notas)->pluck('actividad_id')->unique();
            $userModuloIds = $user->modulos()->pluck('modulos.id')->toArray();
            
            $validActividadesCount = \App\Models\Actividad::whereIn('id', $actividadIds)
                ->whereIn('modulo_id', $userModuloIds)
                ->count();

            if ($validActividadesCount !== $actividadIds->count()) {
                abort(403, 'No tienes permiso para calificar actividades de módulos no asignados.');
            }
        }

        foreach ($request->notas as $data) {
            if ($data['valor'] !== null && $data['valor'] !== '') {
                Nota::updateOrCreate(
                    ['user_id' => $data['student_id'], 'actividad_id' => $data['actividad_id']],
                    ['valor' => $data['valor'], 'observaciones' => $data['observaciones'] ?? null]
                );
            }
        }

        return redirect()->back()->with('success', 'Notas guardadas correctamente.');
    }

    /**
     * Boletín de calificaciones basado en la jerarquía RA → CE → Actividades.
     *
     * Lógica de cálculo:
     * 1. Para cada CE: se calcula la media ponderada de las actividades vinculadas a ese CE
     *    (usando el peso de la actividad como ponderación).
     * 2. Para cada RA: se calcula la media ponderada de sus CEs (usando el peso del CE).
     * 3. Nota final del módulo: media ponderada de todos los RAs (usando el peso del RA).
     */
    public function boletin(Request $request)
    {
        $user = auth()->user();
        $activeSchoolYearId = session('active_school_year_id');
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
        $calificaciones = [];
        $desglose = [];

        if ($request->filled('modulo_id')) {
            $moduloSeleccionado = Modulo::with([
                'group',
                'resultadosAprendizaje.criteriosEvaluacion.actividades.notas',
            ])->find($request->modulo_id);

            if ($moduloSeleccionado && $moduloSeleccionado->group) {
                $students = User::where('group_id', $moduloSeleccionado->group_id)
                    ->orderBy('last_name')->orderBy('name')->get();

                $ras = $moduloSeleccionado->resultadosAprendizaje;

                foreach ($students as $student) {
                    $notaFinal = 0;
                    $totalPesoRA = 0;
                    $desgloseRA = [];

                    foreach ($ras as $ra) {
                        $notaRA = 0;
                        $totalPesoCE = 0;

                        foreach ($ra->criteriosEvaluacion as $ce) {
                            // Buscar actividades vinculadas a este CE
                            $notaCE = 0;
                            $totalPesoAct = 0;

                            foreach ($ce->actividades as $act) {
                                if (!$act->es_evaluable) continue;
                                $nota = $act->notas->where('user_id', $student->id)->first();
                                if ($nota && $act->peso > 0) {
                                    $notaCE += ($nota->valor * $act->peso);
                                    $totalPesoAct += $act->peso;
                                }
                            }

                            $valorCE = $totalPesoAct > 0 ? ($notaCE / $totalPesoAct) : 0;

                            if ($ce->peso > 0) {
                                $notaRA += ($valorCE * $ce->peso);
                                $totalPesoCE += $ce->peso;
                            }
                        }

                        $valorRA = $totalPesoCE > 0 ? ($notaRA / $totalPesoCE) : 0;

                        $desgloseRA[] = [
                            'ra' => $ra,
                            'nota' => round($valorRA, 2),
                        ];

                        if ($ra->peso > 0) {
                            $notaFinal += ($valorRA * $ra->peso);
                            $totalPesoRA += $ra->peso;
                        }
                    }

                    $notaFinalCalc = $totalPesoRA > 0 ? ($notaFinal / $totalPesoRA) : 0;

                    $calificaciones[] = [
                        'student' => $student,
                        'nota_final' => round($notaFinalCalc, 2),
                        'desglose' => $desgloseRA,
                    ];
                }
            }
        }

        return view('notas.boletin', compact('modulos', 'moduloSeleccionado', 'calificaciones'));
    }
}
