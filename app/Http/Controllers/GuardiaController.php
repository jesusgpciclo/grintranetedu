<?php

namespace App\Http\Controllers;

use App\Models\Ausencia;
use App\Models\Group;
use App\Models\ScheduleSelection;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\Zona;
use App\Models\Aula;
use App\Models\ScheduleTemplate;
use App\Models\Setting;
use App\Models\SchoolYear;
use App\Models\UserSchedule;
use App\Models\Guardia;
use App\Services\GuardiaEquityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuardiaController extends Controller
{
    protected GuardiaEquityService $equityService;

    public function __construct(GuardiaEquityService $equityService)
    {
        $this->equityService = $equityService;
    }

    /**
     * Live Real-Time Parte de Guardia.
     */
    public function parte(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $carbonDate = Carbon::parse($date);
        $activeSchoolYearId = session('active_school_year_id');

        $timeSlots = ScheduleTemplate::getActiveTimeSlots();

        // Detect if the viewed date is today and find current active time slot
        $isToday = $carbonDate->isToday();
        $currentTime = Carbon::now()->format('H:i:s');
        $currentSlotId = null;

        if ($isToday) {
            foreach ($timeSlots as $slot) {
                if ($currentTime >= $slot->start_time && $currentTime <= $slot->end_time) {
                    $currentSlotId = $slot->id;
                    break;
                }
            }
        }

        // Fetch all absences for this date
        $ausencias = Ausencia::whereDate('fecha', $date)
            ->with(['user', 'guardiaUser', 'timeSlot', 'group', 'zona'])
            ->get()
            ->groupBy('time_slot_id');

        // For each time slot, fetch available teachers with equity recommendation and absent guard teachers
        $disponiblesPorTramo = [];
        $ausentesPorTramo = [];
        foreach ($timeSlots as $slot) {
            $disponiblesPorTramo[$slot->id] = $this->equityService->getAvailableGuardiasForSlot($date, $slot->id);
            $ausentesPorTramo[$slot->id] = $this->equityService->getAbsentGuardiasForSlot($date, $slot->id);
        }

        // Data for "Apuntar en el parte" modal
        $grupos = Group::when($activeSchoolYearId, fn($q) => $q->where('school_year_id', $activeSchoolYearId))
            ->orderBy('course')
            ->orderBy('name')
            ->get();
        $aulas = Aula::orderBy('nombre')->get();
        $rolesToCheck = \Spatie\Permission\Models\Role::whereIn('name', ['profesor', 'admin', 'directiva'])->pluck('name')->toArray();
        $docentes = count($rolesToCheck) > 0 ? User::role($rolesToCheck)->orderBy('name')->get() : User::orderBy('name')->get();

        // Quick statistics for the day
        $allDayAusencias = Ausencia::whereDate('fecha', $date)->get();
        $totalDay = $allDayAusencias->count();
        $cubiertasDay = $allDayAusencias->filter(fn($a) => $a->isCubierta())->count();
        $pendientesDay = $totalDay - $cubiertasDay;

        return view('guardias.parte', compact(
            'date',
            'carbonDate',
            'isToday',
            'timeSlots',
            'currentSlotId',
            'ausencias',
            'disponiblesPorTramo',
            'ausentesPorTramo',
            'grupos',
            'aulas',
            'docentes',
            'totalDay',
            'cubiertasDay',
            'pendientesDay'
        ));
    }

    /**
     * 1-Tap Confirmation for covering a guardia.
     */
    public function confirmar(Request $request, Ausencia $ausencia)
    {
        $user = Auth::user();
        $guardiaUserId = $user->id;

        // Check if teacher is scheduled for guard duty in this slot or is admin/directiva
        $availableTeachers = $this->equityService->getAvailableGuardiasForSlot($ausencia->fecha->format('Y-m-d'), $ausencia->time_slot_id);
        $isTeacherOnGuard = $availableTeachers->contains('user_id', $user->id);
        $canAssignOthers = $user->hasAnyRole(['admin', 'directiva']) || $isTeacherOnGuard;

        if ($canAssignOthers && $request->filled('guardia_user_id')) {
            $guardiaUserId = (int)$request->input('guardia_user_id');
        }

        $ausencia->update([
            'guardia_user_id' => $guardiaUserId,
            'guardia_confirmed_at' => Carbon::now(),
            'observaciones_guardia' => $request->input('observaciones_guardia', $ausencia->observaciones_guardia),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            $ausencia->load(['guardiaUser', 'user', 'group', 'zona']);
            return response()->json([
                'success' => true,
                'message' => 'Guardia confirmada correctamente.',
                'ausencia' => $ausencia,
                'guardia_user_name' => $ausencia->guardiaUser ? $ausencia->guardiaUser->name : '',
            ]);
        }

        return back()->with('success', '¡Guardia confirmada correctamente!');
    }

    /**
     * Cancel / Undo guardia confirmation.
     */
    public function desconfirmar(Request $request, Ausencia $ausencia)
    {
        $user = Auth::user();

        // Permission check: only the teacher who confirmed it or admin/directiva can cancel
        if ($ausencia->guardia_user_id !== $user->id && !$user->hasRole('admin') && !$user->hasRole('directiva')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para desconfirmar esta guardia.'], 403);
            }
            return back()->with('error', 'No tienes permiso para desconfirmar esta guardia.');
        }

        $ausencia->update([
            'guardia_user_id' => null,
            'guardia_confirmed_at' => null,
            'observaciones_guardia' => null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Confirmación de guardia cancelada.',
            ]);
        }

        return back()->with('success', 'Confirmación de guardia cancelada.');
    }

    /**
     * Personal Guardias Hub ("Mis Horas de Guardia" / Horario de guardias con alumnos).
     */
    public function misHoras(Request $request)
    {
        $authUser = Auth::user();
        $isDirectiva = $authUser->hasRole('admin') || $authUser->hasRole('directiva');

        // Target user: if directiva/admin and user_id is passed, allow inspecting that teacher
        $targetUserId = ($isDirectiva && $request->filled('user_id')) ? (int)$request->input('user_id') : $authUser->id;
        $user = User::find($targetUserId) ?? $authUser;

        // Active template and time slots
        $template = ScheduleTemplate::getActiveTemplate();
        $timeSlots = ScheduleTemplate::getActiveTimeSlots();

        $activeSchoolYearId = session('active_school_year_id') ?? SchoolYear::where('is_active', true)->value('id') ?? 1;

        // Days of week (Lunes a Viernes)
        $days = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
        ];

        // Find UserSchedule (Horario personal) for this teacher
        $guardiasMap = []; // [slot_id][day_num] = ['has_guardia' => true, 'tipo' => string]
        $personalSchedule = null;

        $userSchedule = UserSchedule::where('user_id', $user->id)
            ->where('school_year_id', $activeSchoolYearId)
            ->when($template, fn($q) => $q->where('schedule_template_id', $template->id))
            ->with(['selections.guardia', 'selections.group'])
            ->first();

        if (!$userSchedule) {
            $userSchedule = UserSchedule::where('user_id', $user->id)
                ->where('school_year_id', $activeSchoolYearId)
                ->with(['selections.guardia', 'selections.group'])
                ->latest()
                ->first();
        }

        if (!$userSchedule) {
            $userSchedule = UserSchedule::where('user_id', $user->id)
                ->with(['selections.guardia', 'selections.group'])
                ->latest()
                ->first();
        }

        if ($userSchedule) {
            $personalSchedule = $userSchedule;
            $selections = $userSchedule->selections->filter(function ($sel) {
                return !empty($sel->guardia_id)
                    || !empty($sel->is_convivencia)
                    || str_contains(strtolower($sel->value ?? ''), 'guardia')
                    || str_contains(strtolower($sel->type ?? ''), 'guardia')
                    || str_contains(strtolower($sel->value ?? ''), 'convivencia');
            });

            foreach ($selections as $sel) {
                $dayKey = (int)$sel->day;
                if ($dayKey === 0) {
                    $normalized = GuardiaEquityService::normalizeDay($sel->day);
                    $dayKey = array_search(ucfirst($normalized), $days) ?: 1;
                }

                $tipoTexto = 'Guardia con alumnos';
                if (!empty($sel->guardia?->name)) {
                    $tipoTexto = $sel->guardia->name;
                } elseif (!empty($sel->is_convivencia)) {
                    $tipoTexto = 'Aula Convivencia';
                } elseif (!empty($sel->value)) {
                    $tipoTexto = $sel->value;
                }

                $guardiasMap[$sel->time_slot_id][$dayKey] = [
                    'has_guardia' => true,
                    'tipo' => $tipoTexto,
                    'grupo' => $sel->group ? ($sel->group->course . ' ' . $sel->group->name) : null,
                ];
            }
        }

        // List of teachers for Directiva selector
        $docentes = collect();
        if ($isDirectiva) {
            $rolesToCheck = \Spatie\Permission\Models\Role::whereIn('name', ['profesor', 'admin', 'directiva'])->pluck('name')->toArray();
            $docentes = count($rolesToCheck) > 0 ? User::role($rolesToCheck)->orderBy('name')->get() : User::orderBy('name')->get();
        }

        // Historical data of guardias completed by the target user
        $guardiasRealizadas = Ausencia::where('guardia_user_id', $user->id)
            ->with(['user', 'timeSlot', 'group', 'zona'])
            ->orderByDesc('fecha')
            ->orderByDesc('time_slot_id')
            ->get();

        // Reagrupación rule: multiple groups in the same slot count as 1 guardia hour
        $totalGuardias = $guardiasRealizadas->groupBy(function ($g) {
            return ($g->fecha ? $g->fecha->format('Y-m-d') : '') . '_' . $g->time_slot_id;
        })->count();

        $puntuacionTotal = $guardiasRealizadas->sum(function ($g) {
            return $g->group->dificultad ?? $g->zona->dificultad ?? 1;
        });

        // Group by month for history overview
        $guardiasPorMes = $guardiasRealizadas->groupBy(function ($g) {
            return $g->fecha->format('F Y');
        });

        return view('guardias.mis_horas', compact(
            'user',
            'authUser',
            'isDirectiva',
            'docentes',
            'timeSlots',
            'days',
            'guardiasMap',
            'guardiasRealizadas',
            'totalGuardias',
            'puntuacionTotal',
            'guardiasPorMes',
            'personalSchedule'
        ));
    }

    /**
     * Interactive toggle for guardia slot in Mis Horas (AJAX).
     */
    public function toggleHora(Request $request)
    {
        $authUser = Auth::user();
        $isDirectiva = $authUser->hasRole('admin') || $authUser->hasRole('directiva');

        $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'day' => 'required|integer|between:1,5',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $targetUserId = $authUser->id;
        if ($request->filled('user_id') && (int)$request->input('user_id') !== $authUser->id) {
            if (!$isDirectiva) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para modificar el horario de otro profesor.'
                ], 403);
            }
            $targetUserId = (int)$request->input('user_id');
        }

        $template = ScheduleTemplate::getActiveTemplate();
        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'No hay una plantilla de horarios activa en el centro.'
            ], 422);
        }

        $activeSchoolYearId = session('active_school_year_id') ?? SchoolYear::where('is_active', true)->value('id') ?? 1;

        $userSchedule = UserSchedule::firstOrCreate([
            'user_id' => $targetUserId,
            'schedule_template_id' => $template->id,
            'school_year_id' => $activeSchoolYearId,
        ]);

        $timeSlotId = (int)$request->input('time_slot_id');
        $day = (int)$request->input('day');
        $dayString = (string)$day;
        $dayName = GuardiaEquityService::normalizeDay($day);

        // Find existing selection
        $selection = ScheduleSelection::where('user_schedule_id', $userSchedule->id)
            ->where('time_slot_id', $timeSlotId)
            ->where(function ($q) use ($dayString, $dayName) {
                $q->where('day', $dayString)
                  ->orWhereRaw('LOWER(day) = ?', [$dayName]);
            })
            ->first();

        $isGuardia = false;

        if ($selection && ($selection->guardia_id !== null || str_contains(strtolower($selection->value ?? ''), 'guardia'))) {
            // Already has guardia: unmark / toggle off
            if (empty($selection->subject) && empty($selection->group_id)) {
                $selection->delete();
            } else {
                $selection->update([
                    'guardia_id' => null,
                    'value' => null,
                ]);
            }
            $isGuardia = false;
            $message = 'Guardia desmarcada correctamente.';
        } else {
            // Mark as guardia: toggle on
            $guardiaRecord = Guardia::firstOrCreate(['name' => 'Guardia con alumnos']);
            if ($selection) {
                $selection->update([
                    'guardia_id' => $guardiaRecord->id,
                    'value' => 'Guardia',
                    'type' => 'guardia',
                ]);
            } else {
                ScheduleSelection::create([
                    'user_schedule_id' => $userSchedule->id,
                    'time_slot_id' => $timeSlotId,
                    'day' => $dayString,
                    'value' => 'Guardia',
                    'type' => 'guardia',
                    'guardia_id' => $guardiaRecord->id,
                ]);
            }
            $isGuardia = true;
            $message = 'Guardia marcada correctamente.';
        }

        return response()->json([
            'success' => true,
            'is_guardia' => $isGuardia,
            'message' => $message,
            'time_slot_id' => $timeSlotId,
            'day' => $day,
        ]);
    }

    /**
     * Weekly Cuadrante Matrix of Guardias Availability.
     */
    public function cuadrante(Request $request)
    {
        $timeSlots = ScheduleTemplate::getActiveTimeSlots();
        $days = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
        ];

        // Fetch all guardia selections
        $selections = ScheduleSelection::whereNotNull('guardia_id')
            ->orWhere('value', 'LIKE', '%Guardia%')
            ->orWhere('is_convivencia', true)
            ->with(['userSchedule.user', 'guardia', 'timeSlot'])
            ->get();

        // Organize into matrix [slot_id][day_number] => Collection of selections
        $matrix = [];
        foreach ($timeSlots as $slot) {
            $matrix[$slot->id] = [];
            foreach ($days as $dayNum => $dayName) {
                $matrix[$slot->id][$dayNum] = $selections->filter(function ($sel) use ($slot, $dayNum, $dayName) {
                    if ($sel->time_slot_id != $slot->id) return false;
                    $selDay = GuardiaEquityService::normalizeDay($sel->day);
                    $targetDay = GuardiaEquityService::normalizeDay($dayNum);
                    return $selDay === $targetDay;
                });
            }
        }

        return view('guardias.cuadrante', compact('timeSlots', 'days', 'matrix'));
    }

    /**
     * Directivo / Admin Statistics & BI KPIs.
     */
    public function estadisticas(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $stats = $this->equityService->getDirectivoStats($startDate, $endDate);

        return view('guardias.estadisticas', compact('stats', 'startDate', 'endDate'));
    }

    /**
     * Directivo / Admin - Control de Justificaciones de Ausencias.
     */
    public function justificaciones(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status', 'all');

        $query = Ausencia::query()
            ->select('user_id', 'fecha')
            ->selectRaw('COUNT(*) as total_clases')
            ->selectRaw('SUM(CASE WHEN justificada = 1 THEN 1 ELSE 0 END) as justificada_count')
            ->when($startDate, fn($q) => $q->whereDate('fecha', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('fecha', '<=', $endDate))
            ->when($search, function($q) use ($search) {
                $q->whereHas('user', function($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('departamento', 'like', "%{$search}%");
                });
            })
            ->groupBy('user_id', 'fecha');

        if ($status === 'pending') {
            $query->havingRaw('justificada_count < total_clases');
        } elseif ($status === 'justified') {
            $query->havingRaw('justificada_count = total_clases');
        }

        $daysList = $query->orderByDesc('fecha')
            ->orderBy('user_id')
            ->paginate(25)
            ->withQueryString();

        $userIds = $daysList->pluck('user_id')->unique();
        $fechas = $daysList->pluck('fecha')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->unique();

        $ausenciasGrouped = Ausencia::whereIn('user_id', $userIds)
            ->whereIn('fecha', $fechas)
            ->with(['user', 'timeSlot', 'group', 'zona'])
            ->orderBy('time_slot_id')
            ->get()
            ->groupBy(function($item) {
                return $item->user_id . '_' . $item->fecha->format('Y-m-d');
            });

        $totalDaysCount = Ausencia::distinct()->count('fecha');
        $pendingCount = Ausencia::where('justificada', false)->count();
        $justifiedCount = Ausencia::where('justificada', true)->count();

        return view('guardias.justificaciones', compact(
            'daysList',
            'ausenciasGrouped',
            'search',
            'startDate',
            'endDate',
            'status',
            'totalDaysCount',
            'pendingCount',
            'justifiedCount'
        ));
    }

    /**
     * Directivo / Admin Guardias & Equity Configuration.
     */
    public function configuracion()
    {
        $activeSchoolYearId = session('active_school_year_id');
        $grupos = Group::when($activeSchoolYearId, fn($q) => $q->where('school_year_id', $activeSchoolYearId))
            ->orderBy('course')
            ->orderBy('name')
            ->get();

        // Guard zones (specific for guardias configuration)
        $zonas = Zona::orderBy('nombre')->get();

        // All institute spaces/classrooms available as base templates when copying
        $instituteZones = Aula::orderBy('nombre')->get();

        // Convivencia assignments in active schedules
        $convivenciaSelections = ScheduleSelection::where('is_convivencia', true)
            ->with(['userSchedule.user', 'timeSlot'])
            ->get();

        $templates = ScheduleTemplate::orderBy('name')->get();
        $activeTemplateId = Setting::get('guardias_schedule_template_id', ScheduleTemplate::first()?->id);

        return view('guardias.configuracion', compact('grupos', 'zonas', 'instituteZones', 'convivenciaSelections', 'templates', 'activeTemplateId'));
    }

    /**
     * Save configuration changes for groups and zones.
     */
    public function updateConfiguracion(Request $request)
    {
        if ($request->filled('guardias_schedule_template_id')) {
            Setting::set('guardias_schedule_template_id', (int)$request->input('guardias_schedule_template_id'));
        }

        if ($request->has('grupos')) {
            foreach ($request->input('grupos') as $groupId => $data) {
                Group::where('id', $groupId)->update([
                    'dificultad' => (int)($data['dificultad'] ?? 1),
                ]);
            }
        }

        if ($request->has('zonas')) {
            foreach ($request->input('zonas') as $zonaId => $data) {
                $updateData = [
                    'dificultad' => (int)($data['dificultad'] ?? 1),
                    'aforo' => isset($data['aforo']) && $data['aforo'] !== '' ? (int)$data['aforo'] : null,
                ];
                if (!empty($data['nombre'])) {
                    $updateData['nombre'] = trim($data['nombre']);
                }
                if (array_key_exists('planta', $data)) {
                    $updateData['planta'] = trim($data['planta']);
                }

                Zona::where('id', $zonaId)->update($updateData);
            }
        }

        return back()->with('success', 'Configuración de guardias, dificultades y zonas actualizada.');
    }

    /**
     * Create a new Zona / Espacio Común from configuration screen
     */
    public function storeZona(Request $request)
    {
        $request->validate([
            'origin' => 'required|in:new,copy',
            'base_zona_id' => 'nullable|required_if:origin,copy',
            'nombre' => 'required|string|max:255',
            'planta' => 'nullable|string|max:255',
            'dificultad' => 'required|integer',
            'aforo' => 'nullable|integer|min:0',
        ]);

        $dificultad = (int)$request->input('dificultad', 1);
        $aforo = $request->filled('aforo') ? (int)$request->input('aforo') : null;
        $planta = $request->input('planta');

        if ($request->origin === 'copy' && $request->filled('base_zona_id')) {
            $base = Aula::find($request->base_zona_id) ?? Zona::find($request->base_zona_id);
            if ($base) {
                if (!$request->filled('dificultad') || $request->input('dificultad') == 1) {
                    $dificultad = $base->dificultad ?? 1;
                }
                if (!$request->filled('aforo')) {
                    $aforo = $base->aforo ?? $base->capacidad;
                }
                if (!$request->filled('planta')) {
                    $planta = $base->planta ?? $base->ubicacion;
                }
            }
        }

        Zona::create([
            'nombre' => trim($request->nombre),
            'planta' => $planta ? trim($planta) : null,
            'dificultad' => $dificultad,
            'aforo' => $aforo,
        ]);

        return back()->with('success', 'Nueva zona de guardia "' . trim($request->nombre) . '" creada correctamente.');
    }

    /**
     * Delete a Zona from configuration screen
     */
    public function destroyZona(Zona $zona)
    {
        $nombre = $zona->nombre;
        $zona->delete();
        return back()->with('success', 'Zona de guardia "' . $nombre . '" eliminada correctamente.');
    }

    /**
     * Toggle Justification for an individual absence (Directivo/Admin only).
     */
    public function toggleJustificada(Request $request, Ausencia $ausencia)
    {
        $newState = $request->has('justificada') ? filter_var($request->justificada, FILTER_VALIDATE_BOOLEAN) : !$ausencia->justificada;

        $ausencia->update([
            'justificada' => $newState,
            'justificacion_nota' => $request->input('justificacion_nota', $ausencia->justificacion_nota),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'justificada' => $ausencia->justificada,
                'message' => $ausencia->justificada ? 'Ausencia justificada.' : 'Ausencia pendiente.',
            ]);
        }

        return back()->with('success', 'Estado de justificación actualizado.');
    }

    /**
     * Toggle Justification for all absences of a teacher on a given date (Directivo/Admin only).
     */
    public function toggleJustificarDia(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'fecha' => 'required|date',
            'justificada' => 'required',
        ]);

        $justificada = filter_var($request->justificada, FILTER_VALIDATE_BOOLEAN);

        Ausencia::where('user_id', $request->user_id)
            ->whereDate('fecha', $request->fecha)
            ->update([
                'justificada' => $justificada,
            ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'justificada' => $justificada,
                'message' => $justificada ? 'Día marcado como justificado.' : 'Día marcado como pendiente.',
            ]);
        }

        return back()->with('success', 'Estado de justificación del día actualizado correctamente.');
    }

    /**
     * Mis Guardias Asignadas: Vista personal de las guardias que el docente tiene asignadas hoy y su histórico.
     */
    public function misAsignadas(Request $request)
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $guardiasHoy = Ausencia::where('guardia_user_id', $user->id)
            ->whereDate('fecha', $today)
            ->with(['user', 'timeSlot', 'group', 'zona'])
            ->get();

        $historialGuardias = Ausencia::where('guardia_user_id', $user->id)
            ->whereNotNull('guardia_confirmed_at')
            ->with(['user', 'timeSlot', 'group', 'zona'])
            ->orderBy('fecha', 'desc')
            ->paginate(15);

        return view('guardias.mis_asignadas', compact('guardiasHoy', 'historialGuardias', 'today'));
    }
}
