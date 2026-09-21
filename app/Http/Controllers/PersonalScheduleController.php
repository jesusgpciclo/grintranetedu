<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ScheduleTemplate;
use App\Models\UserSchedule;
use App\Models\ScheduleSelection;
use App\Models\TimeSlot;
use App\Models\Guardia;
use App\Models\Ausencia;
use App\Models\Group;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PersonalScheduleController extends Controller
{
    private function canManageTeacherSchedules(): bool
    {
        $user = Auth::user();
        return $user && ($user->can('manage teacher schedules') || $user->hasAnyRole(['admin', 'directiva']));
    }

    private function authorizeScheduleAccess(UserSchedule $personal_schedule): void
    {
        if ($personal_schedule->user_id !== Auth::id() && !$this->canManageTeacherSchedules()) {
            abort(403, 'No tienes permiso para gestionar este horario.');
        }
    }

    public function index()
    {
        $schedules = UserSchedule::where('user_id', Auth::id())
            ->with(['scheduleTemplate', 'schoolYear'])
            ->get();

        $schoolYears = SchoolYear::orderBy('id', 'desc')->get();
        $templates = ScheduleTemplate::all();
        $canManageTeachers = $this->canManageTeacherSchedules();

        return view('personal_schedules.index', compact('schedules', 'schoolYears', 'templates', 'canManageTeachers'));
    }

    public function create(Request $request)
    {
        $schoolYears = SchoolYear::orderBy('id', 'desc')->get();
        $activeSchoolYear = SchoolYear::where('is_active', true)->first() ?? $schoolYears->first();
        $templates = ScheduleTemplate::all();

        $canManage = $this->canManageTeacherSchedules();
        $teachers = $canManage ? User::role('profesor')->orderBy('name')->get() : collect();
        if ($canManage && $teachers->isEmpty()) {
            $teachers = User::orderBy('name')->get();
        }
        $selectedUserId = $request->get('user_id', Auth::id());

        return view('personal_schedules.create', compact('schoolYears', 'activeSchoolYear', 'templates', 'canManage', 'teachers', 'selectedUserId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'school_year_id' => 'required|exists:school_years,id',
            'schedule_template_id' => 'required|exists:schedule_templates,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $targetUserId = Auth::id();
        if ($this->canManageTeacherSchedules() && $request->filled('user_id')) {
            $targetUserId = $request->user_id;
        }

        $personalSchedule = UserSchedule::firstOrCreate([
            'user_id' => $targetUserId,
            'school_year_id' => $request->school_year_id,
            'schedule_template_id' => $request->schedule_template_id,
        ]);

        return redirect()->route('personal-schedules.edit', $personalSchedule)
            ->with('success', 'Horario creado. ¡Ahora puedes rellenar tus asignaturas, clases y guardias!');
    }

    public function show(UserSchedule $personal_schedule)
    {
        $this->authorizeScheduleAccess($personal_schedule);

        $personal_schedule->load(['scheduleTemplate.timeSlots', 'selections.guardia', 'selections.group', 'schoolYear', 'user']);
        $template = $personal_schedule->scheduleTemplate;
        $slots = $template->timeSlots ?? collect();
        
        $todayEnglish = strtolower(now()->format('l'));
        $daysMap = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miércoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sábado',
            'sunday' => 'domingo'
        ];
        $todaySpanish = $daysMap[$todayEnglish] ?? 'lunes';
        $todayIndex = (string) now()->dayOfWeekIso; 

        $todaySelections = $personal_schedule->selections->filter(function ($s) use ($todaySpanish, $todayIndex) {
            $day = strtolower(trim($s->day));
            return $day == $todaySpanish || $day == $todayIndex;
        })->keyBy('time_slot_id');

        $todayStr = now()->toDateString();
        $absences = Ausencia::where('fecha', $todayStr)
            ->where('user_id', '!=', $personal_schedule->user_id)
            ->with(['user', 'timeSlot'])
            ->get()
            ->groupBy('time_slot_id');

        $canManage = $this->canManageTeacherSchedules();

        return view('personal_schedules.show', compact('personal_schedule', 'template', 'slots', 'todaySpanish', 'todaySelections', 'absences', 'canManage'));
    }

    public function edit(UserSchedule $personal_schedule)
    {
        $this->authorizeScheduleAccess($personal_schedule);

        $personal_schedule->load(['scheduleTemplate.timeSlots', 'selections.group', 'selections.guardia', 'schoolYear']);
        $template = $personal_schedule->scheduleTemplate;
        $slots = $template->timeSlots ?? collect();

        $dayNames = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        $days = [];
        foreach (($template->active_days ?? [1, 2, 3, 4, 5]) as $dayId) {
            $days[$dayId] = $dayNames[$dayId] ?? $dayId;
        }
        
        $selections = $personal_schedule->selections->groupBy(function($item) {
            return $item->time_slot_id . '-' . $item->day;
        })->map->first();

        $guardias = Guardia::all();

        // Get groups filtered by the schedule's school year if present, otherwise all groups
        if ($personal_schedule->school_year_id) {
            $groups = Group::where('school_year_id', $personal_schedule->school_year_id)->get();
            if ($groups->isEmpty()) {
                $groups = Group::all();
            }
        } else {
            $groups = Group::all();
        }

        return view('personal_schedules.edit', compact('personal_schedule', 'template', 'slots', 'days', 'selections', 'guardias', 'groups'));
    }

    public function update(Request $request, UserSchedule $personal_schedule)
    {
        $this->authorizeScheduleAccess($personal_schedule);

        $request->validate([
            'selections' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            if ($request->has('selections')) {
                foreach ($request->selections as $slotId => $daysData) {
                    foreach ($daysData as $day => $data) {
                        $type = $data['type'] ?? null;
                        
                        if (empty($type) || $type === 'none') {
                            ScheduleSelection::where('user_schedule_id', $personal_schedule->id)
                                ->where('time_slot_id', $slotId)
                                ->where('day', $day)
                                ->delete();
                            continue;
                        }

                        $saveData = [
                            'type' => $type,
                        ];

                        if ($type === 'clase') {
                            $subject = trim($data['subject'] ?? '');
                            $groupId = !empty($data['group_id']) ? $data['group_id'] : null;
                            
                            $group = $groupId ? Group::find($groupId) : null;
                            $groupLabel = $group ? ($group->course . ' ' . $group->name) : '';
                            
                            $saveData['subject'] = $subject;
                            $saveData['group_id'] = $groupId;
                            $saveData['guardia_id'] = null;
                            $saveData['is_convivencia'] = false;
                            $saveData['value'] = $subject . ($groupLabel ? ' (' . $groupLabel . ')' : '');
                        } elseif ($type === 'guardia') {
                            $guardiaId = !empty($data['guardia_id']) ? $data['guardia_id'] : null;
                            $saveData['guardia_id'] = $guardiaId;
                            $saveData['is_convivencia'] = false;
                            $saveData['subject'] = null;
                            $saveData['group_id'] = null;
                            
                            $guardiaObj = $guardiaId ? Guardia::find($guardiaId) : null;
                            $saveData['value'] = $guardiaObj ? 'Guardia: ' . $guardiaObj->name : 'Guardia';
                        } elseif ($type === 'texto') {
                            $textValue = trim($data['text_value'] ?? '');
                            $saveData['value'] = $textValue;
                            $saveData['subject'] = null;
                            $saveData['group_id'] = null;
                            $saveData['guardia_id'] = null;
                            $saveData['is_convivencia'] = false;
                        }

                        ScheduleSelection::updateOrCreate(
                            [
                                'user_schedule_id' => $personal_schedule->id,
                                'time_slot_id' => $slotId,
                                'day' => $day,
                            ],
                            $saveData
                        );
                    }
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Horario actualizado correctamente.']);
            }

            return redirect()->route('personal-schedules.index')
                ->with('success', 'Horario actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function destroy(UserSchedule $personal_schedule)
    {
        $this->authorizeScheduleAccess($personal_schedule);

        $personal_schedule->delete();

        return redirect()->route('personal-schedules.index')
            ->with('success', 'Horario personal eliminado.');
    }

    /**
     * Export personal schedule to CSV or JSON
     */
    public function export(Request $request, UserSchedule $personal_schedule, $format = 'csv')
    {
        $this->authorizeScheduleAccess($personal_schedule);

        $personal_schedule->load(['scheduleTemplate.timeSlots', 'selections.group', 'selections.guardia', 'schoolYear', 'user']);
        $template = $personal_schedule->scheduleTemplate;
        $schoolYear = $personal_schedule->schoolYear;
        $targetUser = $personal_schedule->user ?? Auth::user();

        $format = strtolower($format);

        if ($format === 'json') {
            $exportData = [
                'meta' => [
                    'school_year' => $schoolYear ? $schoolYear->name : 'N/A',
                    'template' => $template->name,
                    'user' => $targetUser->name . ' ' . $targetUser->last_name,
                    'user_email' => $targetUser->email,
                    'exported_at' => now()->toIso8601String(),
                ],
                'selections' => $personal_schedule->selections->map(function ($s) {
                    return [
                        'day' => $s->day,
                        'time_slot_id' => $s->time_slot_id,
                        'time_slot_name' => $s->timeSlot ? $s->timeSlot->name : '',
                        'type' => $s->type ?: ($s->guardia_id ? 'guardia' : 'texto'),
                        'subject' => $s->subject,
                        'group' => $s->group ? ($s->group->course . ' ' . $s->group->name) : null,
                        'group_id' => $s->group_id,
                        'value' => $s->value,
                        'guardia_id' => $s->guardia_id,
                    ];
                })
            ];

            $fileName = 'horario_' . ($targetUser->email ? explode('@', $targetUser->email)[0] . '_' : '') . ($schoolYear ? str_replace('/', '-', $schoolYear->name) : 'personal') . '.json';
            return Response::make(json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]);
        }

        // Default CSV export
        $fileName = 'horario_' . ($targetUser->email ? explode('@', $targetUser->email)[0] . '_' : '') . ($schoolYear ? str_replace('/', '-', $schoolYear->name) : 'personal') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($personal_schedule) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['Dia', 'Tramo_ID', 'Tramo_Nombre', 'Tipo', 'Asignatura_o_Texto', 'Grupo_ID', 'Grupo_Nombre', 'Guardia_ID'], ';');

            foreach ($personal_schedule->selections as $s) {
                $type = $s->type ?: ($s->guardia_id ? 'guardia' : 'texto');
                $groupName = $s->group ? ($s->group->course . ' ' . $s->group->name) : '';
                $textValue = $type === 'clase' ? $s->subject : $s->value;

                fputcsv($file, [
                    $s->day,
                    $s->time_slot_id,
                    $s->timeSlot ? $s->timeSlot->name : '',
                    $type,
                    $textValue,
                    $s->group_id ?? '',
                    $groupName,
                    $s->guardia_id ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import personal schedule from CSV or JSON
     */
    public function import(Request $request)
    {
        $request->validate([
            'school_year_id' => 'required|exists:school_years,id',
            'schedule_template_id' => 'required|exists:schedule_templates,id',
            'user_id' => 'nullable|exists:users,id',
            'file' => 'required|file|mimes:csv,txt,json',
        ]);

        $targetUserId = Auth::id();
        if ($this->canManageTeacherSchedules() && $request->filled('user_id')) {
            $targetUserId = $request->user_id;
        }

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $content = file_get_contents($file->getRealPath());

        DB::beginTransaction();
        try {
            $personalSchedule = UserSchedule::firstOrCreate([
                'user_id' => $targetUserId,
                'school_year_id' => $request->school_year_id,
                'schedule_template_id' => $request->schedule_template_id,
            ]);

            $importedCount = 0;

            if ($ext === 'json') {
                $data = json_decode($content, true);
                $selections = $data['selections'] ?? $data;

                if (!is_array($selections)) {
                    throw new \Exception('Formato JSON no válido.');
                }

                foreach ($selections as $row) {
                    if (empty($row['day']) || empty($row['time_slot_id'])) continue;

                    $type = $row['type'] ?? 'texto';
                    $subject = $row['subject'] ?? null;
                    $groupId = $row['group_id'] ?? null;
                    $value = $row['value'] ?? '';

                    ScheduleSelection::updateOrCreate(
                        [
                            'user_schedule_id' => $personalSchedule->id,
                            'time_slot_id' => $row['time_slot_id'],
                            'day' => $row['day'],
                        ],
                        [
                            'type' => $type,
                            'subject' => $subject,
                            'group_id' => $groupId,
                            'value' => $value,
                            'guardia_id' => $row['guardia_id'] ?? null,
                            'is_convivencia' => false,
                        ]
                    );
                    $importedCount++;
                }
            } else {
                // CSV parsing
                $lines = explode("\n", str_replace("\r", "", $content));
                $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';

                $header = null;
                foreach ($lines as $index => $line) {
                    if (empty(trim($line))) continue;

                    $cols = str_getcsv($line, $delimiter);
                    if ($index === 0) {
                        // Skip header row if matches 'Dia' or 'Day'
                        if (str_contains(strtolower($cols[0]), 'dia') || str_contains(strtolower($cols[0]), 'day')) {
                            continue;
                        }
                    }

                    if (count($cols) >= 3) {
                        $day = trim($cols[0]);
                        $slotId = intval(trim($cols[1]));
                        $type = isset($cols[3]) ? trim($cols[3]) : 'texto';
                        $textValue = isset($cols[4]) ? trim($cols[4]) : '';
                        $groupId = isset($cols[5]) && is_numeric($cols[5]) ? intval($cols[5]) : null;

                        if (empty($day) || empty($slotId)) continue;

                        ScheduleSelection::updateOrCreate(
                            [
                                'user_schedule_id' => $personalSchedule->id,
                                'time_slot_id' => $slotId,
                                'day' => $day,
                            ],
                            [
                                'type' => $type,
                                'subject' => $type === 'clase' ? $textValue : null,
                                'group_id' => $groupId,
                                'value' => $textValue,
                            ]
                        );
                        $importedCount++;
                    }
                }
            }

            DB::commit();

            return redirect()->route('personal-schedules.edit', $personalSchedule)
                ->with('success', "¡Horario importado con éxito! Se registraron $importedCount tramos horarias.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al importar horario: ' . $e->getMessage());
        }
    }

    /**
     * Download example import template
     */
    public function downloadImportTemplate($format = 'csv')
    {
        if ($format === 'json') {
            $data = [
                'meta' => [
                    'description' => 'Plantilla de importación de horario personal',
                ],
                'selections' => [
                    [
                        'day' => '1',
                        'time_slot_id' => 1,
                        'type' => 'clase',
                        'subject' => 'Matemáticas',
                        'group_id' => 1,
                        'value' => 'Matemáticas (1º ESO A)'
                    ],
                    [
                        'day' => '1',
                        'time_slot_id' => 2,
                        'type' => 'guardia',
                        'value' => 'Guardia'
                    ],
                    [
                        'day' => '2',
                        'time_slot_id' => 1,
                        'type' => 'texto',
                        'value' => 'Reunión de Departamento'
                    ]
                ]
            ];

            return Response::make(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="plantilla_horario.json"'
            ]);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_horario.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Dia', 'Tramo_ID', 'Tramo_Nombre', 'Tipo', 'Asignatura_o_Texto', 'Grupo_ID', 'Grupo_Nombre'], ';');
            fputcsv($file, ['1', '1', 'Primera Hora', 'clase', 'Matemáticas', '1', '1º ESO A'], ';');
            fputcsv($file, ['1', '2', 'Segunda Hora', 'guardia', 'Guardia', '', ''], ';');
            fputcsv($file, ['2', '1', 'Primera Hora', 'texto', 'Reunión de Departamento', '', ''], ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print personal schedule to PDF view
     */
    public function print(UserSchedule $personal_schedule)
    {
        $this->authorizeScheduleAccess($personal_schedule);

        $personal_schedule->load(['scheduleTemplate.timeSlots', 'selections.guardia', 'selections.group', 'schoolYear', 'user']);
        $template = $personal_schedule->scheduleTemplate;
        $slots = $template->timeSlots ?? collect();

        $dayNames = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        $days = [];
        foreach (($template->active_days ?? [1, 2, 3, 4, 5]) as $dayId) {
            $days[$dayId] = $dayNames[$dayId] ?? $dayId;
        }

        $selections = $personal_schedule->selections->groupBy(function($item) {
            return $item->time_slot_id . '-' . $item->day;
        })->map->first();

        return view('personal_schedules.print', compact('personal_schedule', 'template', 'slots', 'days', 'selections'));
    }
}
