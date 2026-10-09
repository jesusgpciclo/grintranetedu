<?php

namespace App\Http\Controllers;

use App\Models\HallPass;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;

class HallPassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if ($user && ($user->hasRole('conserje') || (!$user->can('salidas.create') && !$user->can('salidas.manage') && !$user->hasRole(['admin', 'jefatura', 'directiva', 'director', 'profesor'])))) {
            return redirect()->route('salidas.monitor');
        }

        // Auto-cerrar salidas no regresadas de días anteriores
        HallPass::whereNull('end_time')
            ->whereDate('date', '<', now()->toDateString())
            ->update(['end_time' => now()]);

        // Teacher Dashboard
        // Get all groups
        $groups = Group::orderBy('name')->get();

        // Active passes
        $activePasses = HallPass::whereNull('end_time')->with(['student.groupRel'])->get();
        $activeStudentIds = $activePasses->pluck('user_id')->flip();

        // Get students (Users with role 'alumno')
        // Sorted: 1) Active passes first, 2) Alphabetical order by last_name (apellidos), then name
        $students = User::role('alumno')
            ->with('groupRel')
            ->get()
            ->sort(function ($a, $b) use ($activeStudentIds) {
                $aActive = isset($activeStudentIds[$a->id]) ? 0 : 1;
                $bActive = isset($activeStudentIds[$b->id]) ? 0 : 1;
                if ($aActive !== $bActive) {
                    return $aActive <=> $bActive;
                }

                $aLastName = mb_strtolower(trim($a->last_name ?? ''));
                $bLastName = mb_strtolower(trim($b->last_name ?? ''));
                $cmpLast = strcoll($aLastName, $bLastName);
                if ($cmpLast !== 0) {
                    return $cmpLast;
                }

                $aName = mb_strtolower(trim($a->name));
                $bName = mb_strtolower(trim($b->name));
                return strcoll($aName, $bName);
            })
            ->values();

        // Today's passes for history stats per student, eager loading teacher
        $todayPassesByStudent = HallPass::whereDate('date', now()->toDateString())
            ->with('teacher')
            ->orderBy('start_time', 'desc')
            ->get()
            ->groupBy('user_id');

        $todayPassesJson = $todayPassesByStudent->map(function ($passes) use ($user) {
            return $passes->map(function ($pass) use ($user) {
                $start = $pass->start_time ? \Carbon\Carbon::parse($pass->start_time) : null;
                $end = $pass->end_time ? \Carbon\Carbon::parse($pass->end_time) : null;
                $duration = ($start && $end) ? max(1, $start->diffInMinutes($end)) : null;

                return [
                    'id' => $pass->id,
                    'user_id' => $pass->user_id,
                    'teacher_id' => $pass->teacher_id,
                    'teacher_name' => $pass->teacher ? trim($pass->teacher->name . ' ' . ($pass->teacher->last_name ?? '')) : 'Profesor',
                    'reason' => $pass->reason,
                    'start_time' => $start ? $start->format('H:i') : '',
                    'end_time' => $end ? $end->format('H:i') : null,
                    'duration_minutes' => $duration,
                    'can_edit_time' => ($user && $pass->teacher_id === $user->id) || ($user && $user->hasRole(['admin', 'jefatura', 'directiva', 'director'])),
                ];
            });
        });

        // Statistics
        $stats = [
            'active_count' => $activePasses->count(),
            'today_count' => HallPass::whereDate('date', now()->toDateString())->count(),
        ];

        // Favorite groups from user profile in DB (if available)
        $userFavorites = ($user && isset($user->favorite_groups)) ? (array) $user->favorite_groups : [];

        return view('salidas.dashboard', compact('students', 'activePasses', 'groups', 'stats', 'todayPassesByStudent', 'todayPassesJson', 'userFavorites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('salidas.create') && !$user->hasRole(['admin', 'jefatura', 'directiva', 'director', 'profesor']))) {
            return response()->json(['error' => 'No tienes permiso para emitir pases de salida.'], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:users,id', // 'student_id' comes from frontend but maps to 'user_id'
            'reason' => 'required|string|max:255',
        ]);

        // Check active pass for the same student
        $activePass = HallPass::where('user_id', $request->student_id)
            ->whereNull('end_time')
            ->first();

        if ($activePass) {
            return response()->json(['error' => 'El alumno ya tiene un pase activo.'], 400);
        }

        // Check group active passes limit (2 max per group unless forced)
        $studentUser = User::find($request->student_id);
        if ($studentUser && $studentUser->group_id && !$request->boolean('force')) {
            $activeGroupPassesCount = HallPass::whereNull('end_time')
                ->whereHas('student', function($q) use ($studentUser) {
                    $q->where('group_id', $studentUser->group_id);
                })
                ->count();

            if ($activeGroupPassesCount >= 2) {
                return response()->json(['error' => 'Ya hay 2 alumnos fuera de clase en este grupo. Finaliza un pase antes de autorizar otro.'], 422);
            }
        }

        $pass = HallPass::create([
            'user_id' => $request->student_id,
            'teacher_id' => auth()->id(),
            'reason' => $request->reason,
            'date' => now()->format('Y-m-d'),
            'start_time' => now(),
        ]);

        return response()->json($pass->load('student'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HallPass $hallPass)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $canReturnInMonitor = $user->can('salidas.return_monitor') ||
            $user->can('salidas.manage') ||
            $user->hasRole(['admin', 'jefatura', 'directiva', 'director']);

        // Si la petición se emite desde el monitor de pasillos
        if ($request->input('source') === 'monitor') {
            if (!$canReturnInMonitor) {
                return response()->json([
                    'error' => 'No tienes permisos para marcar el regreso de alumnos en el monitor de pasillos.'
                ], 403);
            }
        } else {
            // Regreso desde el gestor de aula (profesor responsable o jefatura/admin/directiva)
            $isTeacherOfPass = ($hallPass->teacher_id === $user->id);
            if (!$isTeacherOfPass && !$canReturnInMonitor) {
                return response()->json([
                    'error' => 'No tienes permisos para marcar el regreso de alumnos en el monitor de pasillos.'
                ], 403);
            }
        }

        $hallPass->update([
            'end_time' => now(),
        ]);

        return response()->json($hallPass);
    }

    /**
     * Valida si el usuario tiene permiso para acceder o gestionar el historial de salidas.
     */
    protected function authorizeHistory(): void
    {
        $user = auth()->user();
        $canAccessHistory = $user && (
            $user->can('salidas.manage') ||
            $user->hasRole(['admin', 'jefatura', 'directiva', 'director'])
        );

        if (!$canAccessHistory) {
            abort(403, 'No tienes permiso para consultar o gestionar el historial de salidas.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HallPass $hallPass)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        $canDelete = $user->hasRole(['admin', 'jefatura', 'directiva', 'director', 'profesor'])
            || $user->can('salidas.manage')
            || $hallPass->teacher_id === $user->id;

        if (!$canDelete) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'No tienes permiso para eliminar esta salida.'], 403);
            }
            return back()->withErrors('No tienes permiso para eliminar esta salida.');
        }

        $hallPass->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Salida eliminada correctamente.']);
        }

        return back()->with('success', 'Salida eliminada correctamente.');
    }

    public function bulkDelete(Request $request)
    {
        $this->authorizeHistory();

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:hall_passes,id',
        ]);

        $count = HallPass::whereIn('id', $request->ids)->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => "Se han eliminado {$count} salidas correctamente."]);
        }

        return back()->with('success', "Se han eliminado {$count} salidas correctamente.");
    }

    public function monitor()
    {
        // Auto-cerrar salidas no regresadas de días anteriores
        HallPass::whereNull('end_time')
            ->whereDate('date', '<', now()->toDateString())
            ->update(['end_time' => now()]);

        // Monitor de Pasillo
        $activePasses = HallPass::whereNull('end_time')
            ->with(['student.groupRel', 'teacher'])
            ->orderBy('start_time', 'desc')
            ->get();

        $todayPassesByStudent = HallPass::whereDate('date', now()->toDateString())
            ->with('teacher')
            ->orderBy('start_time', 'desc')
            ->get()
            ->groupBy('user_id');
            
        return view('salidas.monitor', compact('activePasses', 'todayPassesByStudent'));
    }

    public function returnAll(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $user = auth()->user();
        $canReturnInMonitor = $user && (
            $user->can('salidas.return_monitor') ||
            $user->can('salidas.manage') ||
            $user->hasRole(['admin', 'jefatura', 'directiva', 'director'])
        );

        if (!$canReturnInMonitor && (!$user || !$user->hasRole('profesor'))) {
            return response()->json([
                'error' => 'No tienes permisos para finalizar pases de forma masiva.'
            ], 403);
        }

        $affected = HallPass::whereNull('end_time')
            ->whereHas('student', function($q) use ($request) {
                $q->where('group_id', $request->group_id);
            })
            ->update(['end_time' => now()]);
            
        return response()->json(['message' => "Finalizados $affected pases."]);
    }

    /**
     * Construye la consulta del historial aplicando filtros de búsqueda, fechas, horas y ordenación.
     */
    protected function buildHistoryQuery(Request $request)
    {
        $query = HallPass::with(['student.groupRel', 'teacher']);

        // Search filter (texto)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereHas('groupRel', function ($gq) use ($search) {
                            $gq->where('name', 'like', "%{$search}%")
                              ->orWhere('course', 'like', "%{$search}%");
                        });
                  });
            });
        }

        // Filtro por fecha o entre fechas
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateFrom = min($request->date_from, $request->date_to);
            $dateTo = max($request->date_from, $request->date_to);
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('date', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Filtro entre horas (sobre hora de inicio)
        if ($request->filled('time_from')) {
            $timeFrom = strlen($request->time_from) === 5 ? $request->time_from . ':00' : $request->time_from;
            $query->whereTime('start_time', '>=', $timeFrom);
        }
        if ($request->filled('time_to')) {
            $timeTo = strlen($request->time_to) === 5 ? $request->time_to . ':59' : $request->time_to;
            $query->whereTime('start_time', '<=', $timeTo);
        }

        // Sorting
        $sort = $request->input('sort', 'fecha');
        $direction = $request->input('direction', 'desc');
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        switch ($sort) {
            case 'alumno':
                $query->join('users as students', 'hall_passes.user_id', '=', 'students.id')
                    ->select('hall_passes.*')
                    ->orderBy('students.name', $direction)
                    ->orderBy('students.last_name', $direction);
                break;
            case 'clase':
                $query->join('users as students', 'hall_passes.user_id', '=', 'students.id')
                    ->leftJoin('groups', 'students.group_id', '=', 'groups.id')
                    ->select('hall_passes.*')
                    ->orderBy('groups.course', $direction)
                    ->orderBy('groups.name', $direction);
                break;
            case 'motivo':
                $query->orderBy('reason', $direction);
                break;
            case 'duracion':
                if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                    $query->orderByRaw('(julianday(end_time) - julianday(start_time)) ' . $direction);
                } else {
                    $query->orderByRaw('TIMESTAMPDIFF(MINUTE, start_time, end_time) ' . $direction);
                }
                break;
            case 'profesor':
                $query->join('users as teachers', 'hall_passes.teacher_id', '=', 'teachers.id')
                    ->select('hall_passes.*')
                    ->orderBy('teachers.name', $direction)
                    ->orderBy('teachers.last_name', $direction);
                break;
            case 'fecha':
            default:
                $query->orderBy('date', $direction)
                    ->orderBy('start_time', $direction);
                break;
        }

        return $query;
    }

    public function history(Request $request)
    {
        $this->authorizeHistory();

        $query = $this->buildHistoryQuery($request);

        $passes = $query->paginate(20)->withQueryString();

        return view('salidas.history', compact('passes'));
    }

    public function exportCsv(Request $request)
    {
        $this->authorizeHistory();

        $query = $this->buildHistoryQuery($request);

        $passes = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="historial_salidas_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($passes) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['Fecha', 'Hora Inicio', 'Hora Fin', 'Alumno', 'Clase', 'Motivo', 'Duración (min)', 'Autorizado por']);

            foreach ($passes as $pass) {
                fputcsv($file, [
                    $pass->date->format('d/m/Y'),
                    $pass->start_time ? $pass->start_time->format('H:i') : '-',
                    $pass->end_time ? $pass->end_time->format('H:i') : '-',
                    trim(($pass->student?->name ?? '') . ' ' . ($pass->student?->last_name ?? '')),
                    trim(($pass->student?->groupRel?->course ?? '') . ' ' . ($pass->student?->groupRel?->name ?? '')),
                    $pass->reason,
                    $pass->duration_formatted,
                    $pass->teacher_full_name
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printHistory(Request $request)
    {
        $this->authorizeHistory();

        $query = $this->buildHistoryQuery($request);

        $passes = $query->get();

        return view('salidas.print', compact('passes'));
    }

    public function clearHistory()
    {
        $this->authorizeHistory();

        HallPass::query()->delete();

        return response()->json(['message' => 'Historial vaciado correctamente.']);
    }

    /**
     * Alterna un grupo como favorito en el perfil del usuario autenticado.
     */
    public function toggleFavoriteGroup(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $request->validate([
            'group_id' => 'required',
        ]);

        $groupId = (string) $request->input('group_id');
        $favorites = $user->favorite_groups ?? [];
        if (!is_array($favorites)) {
            $favorites = [];
        }
        $favorites = array_map('strval', $favorites);

        $index = array_search($groupId, $favorites);
        if ($index !== false) {
            array_splice($favorites, $index, 1);
            $isFavorite = false;
        } else {
            $favorites[] = $groupId;
            $isFavorite = true;
        }

        try {
            $user->update(['favorite_groups' => array_values($favorites)]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not persist favorite_groups to users table: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'favorites' => array_values($favorites),
        ]);
    }

    /**
     * Edita el tiempo/duración de una salida autorizada por el profesor autenticado o jefatura/admin.
     */
    public function updatePassTime(Request $request, HallPass $hallPass)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $canEdit = ($hallPass->teacher_id === $user->id) || $user->hasRole(['admin', 'jefatura', 'directiva', 'director']);
        if (!$canEdit) {
            return response()->json(['error' => 'Solo el profesor que autorizó la salida puede editar el tiempo.'], 403);
        }

        $request->validate([
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ]);

        $passDate = $hallPass->date ? \Carbon\Carbon::parse($hallPass->date)->toDateString() : now()->toDateString();

        if ($request->filled('start_time')) {
            $hallPass->start_time = \Carbon\Carbon::parse($passDate . ' ' . $request->input('start_time'));
        }

        if ($request->filled('duration_minutes')) {
            $minutes = (int) $request->input('duration_minutes');
            $baseStart = $hallPass->start_time ? \Carbon\Carbon::parse($hallPass->start_time) : now();
            $hallPass->end_time = (clone $baseStart)->addMinutes($minutes);
        } elseif ($request->filled('end_time')) {
            $hallPass->end_time = \Carbon\Carbon::parse($passDate . ' ' . $request->input('end_time'));
        }

        if ($hallPass->start_time && $hallPass->end_time) {
            $sTime = \Carbon\Carbon::parse($hallPass->start_time);
            $eTime = \Carbon\Carbon::parse($hallPass->end_time);
            if ($eTime->lessThan($sTime)) {
                return response()->json(['error' => 'La hora de regreso no puede ser anterior a la hora de salida.'], 422);
            }
        }

        $hallPass->save();

        return response()->json([
            'message' => 'Tiempo de salida actualizado correctamente.',
            'pass' => $hallPass->fresh(['teacher', 'student']),
        ]);
    }
}
