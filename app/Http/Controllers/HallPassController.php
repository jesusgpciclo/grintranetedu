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
        // Teacher Dashboard
        // Get all groups
        $groups = Group::orderBy('name')->get();

        // Get students (Users with role 'alumno') ordered by Group then Name
        // We need to eager load groupRel (from User model)
        $students = User::role('alumno')
            ->with('groupRel')
            ->get()
            ->sortBy([
                ['groupRel.course', 'asc'],
                ['groupRel.name', 'asc'],
                ['name', 'asc']
            ]);

        // Active passes
        $activePasses = HallPass::whereNull('end_time')->with(['student.groupRel'])->get();

        // Today's passes for history stats per student
        $todayPassesByStudent = HallPass::whereDate('date', now()->toDateString())
            ->orderBy('start_time', 'desc')
            ->get()
            ->groupBy('user_id');

        // Statistics
        $stats = [
            'active_count' => $activePasses->count(),
            'today_count' => HallPass::whereDate('date', now()->toDateString())->count(),
        ];

        return view('salidas.dashboard', compact('students', 'activePasses', 'groups', 'stats', 'todayPassesByStudent'));
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

        // Si es el profesor que expidió el pase desde su aula, siempre puede finalizarlo
        $isTeacherOfPass = ($user && $hallPass->teacher_id === $user->id);

        // Si tiene permiso para regresar alumnos en el monitor o gestionar salidas
        $canReturnInMonitor = $user && (
            $user->can('salidas.return_monitor') ||
            $user->can('salidas.manage') ||
            $user->hasRole('admin')
        );

        if (!$isTeacherOfPass && !$canReturnInMonitor) {
            return response()->json([
                'error' => 'No tienes permisos para marcar el regreso de alumnos en el monitor de pasillos.'
            ], 403);
        }

        $hallPass->update([
            'end_time' => now(),
        ]);

        return response()->json($hallPass);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
    }

    public function monitor()
    {
        // Monitor de Pasillo
        $activePasses = HallPass::whereNull('end_time')
            ->with(['student.groupRel', 'teacher'])
            ->orderBy('start_time', 'desc')
            ->get();

        $todayPassesByStudent = HallPass::whereDate('date', now()->toDateString())
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
            $user->hasRole('admin')
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

    public function history(Request $request)
    {
        $query = HallPass::with(['student.groupRel', 'teacher']);

        // Search filter
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
                $query->orderByRaw('TIMESTAMPDIFF(MINUTE, start_time, end_time) ' . $direction);
                break;
            case 'profesor':
                $query->join('users as teachers', 'hall_passes.teacher_id', '=', 'teachers.id')
                    ->select('hall_passes.*')
                    ->orderBy('teachers.name', $direction);
                break;
            case 'fecha':
            default:
                $query->orderBy('date', $direction)
                    ->orderBy('start_time', $direction);
                break;
        }

        $passes = $query->paginate(20)->withQueryString();

        return view('salidas.history', compact('passes'));
    }

    public function exportCsv(Request $request)
    {
        $query = HallPass::with(['student.groupRel', 'teacher']);

        // Search filter
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
                    ->orderBy('students.name', $direction);
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
                $query->orderByRaw('TIMESTAMPDIFF(MINUTE, start_time, end_time) ' . $direction);
                break;
            case 'profesor':
                $query->join('users as teachers', 'hall_passes.teacher_id', '=', 'teachers.id')
                    ->select('hall_passes.*')
                    ->orderBy('teachers.name', $direction);
                break;
            case 'fecha':
            default:
                $query->orderBy('date', $direction)
                    ->orderBy('start_time', $direction);
                break;
        }

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
                $duration = $pass->end_time ? (int) $pass->start_time->diffInMinutes($pass->end_time) : 'Activo';
                fputcsv($file, [
                    $pass->date->format('d/m/Y'),
                    $pass->start_time->format('H:i'),
                    $pass->end_time ? $pass->end_time->format('H:i') : '-',
                    ($pass->student?->name ?? '') . ' ' . ($pass->student?->last_name ?? ''),
                    ($pass->student?->groupRel?->course ?? '') . ' ' . ($pass->student?->groupRel?->name ?? ''),
                    $pass->reason,
                    $duration,
                    $pass->teacher?->name ?? 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printHistory(Request $request)
    {
        $query = HallPass::with(['student.groupRel', 'teacher']);

        // Search filter
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
                    ->orderBy('students.name', $direction);
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
                $query->orderByRaw('TIMESTAMPDIFF(MINUTE, start_time, end_time) ' . $direction);
                break;
            case 'profesor':
                $query->join('users as teachers', 'hall_passes.teacher_id', '=', 'teachers.id')
                    ->select('hall_passes.*')
                    ->orderBy('teachers.name', $direction);
                break;
            case 'fecha':
            default:
                $query->orderBy('date', $direction)
                    ->orderBy('start_time', $direction);
                break;
        }

        $passes = $query->get();

        return view('salidas.print', compact('passes'));
    }

    public function clearHistory()
    {
        HallPass::query()->delete();

        return response()->json(['message' => 'Historial vaciado correctamente.']);
    }
}
