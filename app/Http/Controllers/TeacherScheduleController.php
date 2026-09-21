<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolYear;
use App\Models\ScheduleTemplate;
use App\Models\UserSchedule;
use App\Models\ScheduleSelection;
use App\Models\Guardia;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class TeacherScheduleController extends Controller
{
    private function authorizeManager(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->can('manage teacher schedules') && !$user->hasAnyRole(['admin', 'directiva']))) {
            abort(403, 'No tienes permiso para gestionar los horarios del profesorado.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeManager();

        $schoolYears = SchoolYear::orderBy('id', 'desc')->get();
        $selectedSchoolYearId = $request->get('school_year_id');
        
        if ($selectedSchoolYearId) {
            $selectedSchoolYear = SchoolYear::find($selectedSchoolYearId) ?? $schoolYears->first();
        } else {
            $selectedSchoolYear = SchoolYear::where('is_active', true)->first() ?? $schoolYears->first();
        }

        $templates = ScheduleTemplate::all();

        // Get teachers query
        $teachersQuery = User::whereHas('roles', function($q) {
            $q->where('name', 'profesor');
        });

        // Fallback if no user has 'profesor' role
        if ($teachersQuery->count() === 0) {
            $teachersQuery = User::query();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $teachersQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teachers = $teachersQuery->with(['schedules' => function($q) use ($selectedSchoolYear) {
            if ($selectedSchoolYear) {
                $q->where('school_year_id', $selectedSchoolYear->id);
            }
            $q->with(['scheduleTemplate.timeSlots', 'selections.group', 'selections.guardia']);
        }])->orderBy('name')->get();

        return view('teacher_schedules.index', compact(
            'teachers',
            'schoolYears',
            'selectedSchoolYear',
            'templates'
        ));
    }

    /**
     * Bulk export teacher schedules to CSV or JSON
     */
    public function exportAll(Request $request, $format = 'csv')
    {
        $this->authorizeManager();

        $schoolYearId = $request->get('school_year_id');
        $schoolYear = $schoolYearId ? SchoolYear::find($schoolYearId) : (SchoolYear::where('is_active', true)->first() ?? SchoolYear::first());

        $query = UserSchedule::with(['user', 'scheduleTemplate.timeSlots', 'selections.group', 'selections.guardia', 'selections.timeSlot', 'schoolYear']);
        
        if ($schoolYear) {
            $query->where('school_year_id', $schoolYear->id);
        }

        $schedules = $query->get();
        $format = strtolower($format);

        if ($format === 'json') {
            $exportData = [
                'meta' => [
                    'school_year' => $schoolYear ? $schoolYear->name : 'Todos',
                    'exported_at' => now()->toIso8601String(),
                    'total_teachers_with_schedule' => $schedules->count(),
                ],
                'schedules' => $schedules->map(function ($sch) {
                    return [
                        'profesor_id' => $sch->user_id,
                        'profesor_nombre' => $sch->user ? ($sch->user->name . ' ' . $sch->user->last_name) : 'N/A',
                        'profesor_email' => $sch->user ? $sch->user->email : '',
                        'curso_escolar' => $sch->schoolYear ? $sch->schoolYear->name : '',
                        'curso_escolar_id' => $sch->school_year_id,
                        'plantilla' => $sch->scheduleTemplate ? $sch->scheduleTemplate->name : '',
                        'plantilla_id' => $sch->schedule_template_id,
                        'selections' => $sch->selections->map(function ($s) {
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
                })
            ];

            $fileName = 'horarios_profesorado_' . ($schoolYear ? str_replace('/', '-', $schoolYear->name) : 'general') . '.json';
            return Response::make(json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]);
        }

        // CSV Bulk Export
        $fileName = 'horarios_profesorado_' . ($schoolYear ? str_replace('/', '-', $schoolYear->name) : 'general') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($schedules) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'Profesor_Email',
                'Profesor_Nombre',
                'Curso_Escolar',
                'Plantilla_ID',
                'Plantilla_Nombre',
                'Dia',
                'Tramo_ID',
                'Tramo_Nombre',
                'Tipo',
                'Asignatura_o_Texto',
                'Grupo_ID',
                'Grupo_Nombre',
                'Guardia_ID'
            ], ';');

            foreach ($schedules as $sch) {
                $teacherEmail = $sch->user ? $sch->user->email : '';
                $teacherName = $sch->user ? ($sch->user->name . ' ' . $sch->user->last_name) : '';
                $schoolYearName = $sch->schoolYear ? $sch->schoolYear->name : '';
                $templateName = $sch->scheduleTemplate ? $sch->scheduleTemplate->name : '';
                $templateId = $sch->schedule_template_id;

                foreach ($sch->selections as $s) {
                    $type = $s->type ?: ($s->guardia_id ? 'guardia' : 'texto');
                    $groupName = $s->group ? ($s->group->course . ' ' . $s->group->name) : '';
                    $textValue = $type === 'clase' ? $s->subject : $s->value;

                    fputcsv($file, [
                        $teacherEmail,
                        $teacherName,
                        $schoolYearName,
                        $templateId,
                        $templateName,
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
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk import schedules for multiple teachers from CSV or JSON
     */
    public function importBulk(Request $request)
    {
        $this->authorizeManager();

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,json',
            'school_year_id' => 'required|exists:school_years,id',
            'schedule_template_id' => 'required|exists:schedule_templates,id',
        ]);

        $defaultSchoolYearId = $request->school_year_id;
        $defaultTemplateId = $request->schedule_template_id;

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $content = file_get_contents($file->getRealPath());

        DB::beginTransaction();
        try {
            $importedSchedulesCount = 0;
            $importedSelectionsCount = 0;

            if ($ext === 'json') {
                $data = json_decode($content, true);
                $schedulesList = $data['schedules'] ?? $data;

                if (!is_array($schedulesList)) {
                    throw new \Exception('Formato JSON de importación no válido.');
                }

                foreach ($schedulesList as $item) {
                    $teacherEmail = trim($item['profesor_email'] ?? ($item['email'] ?? ''));
                    $teacherId = $item['profesor_id'] ?? ($item['user_id'] ?? null);

                    $teacher = null;
                    if ($teacherEmail) {
                        $teacher = User::where('email', $teacherEmail)->first();
                    } elseif ($teacherId) {
                        $teacher = User::find($teacherId);
                    }

                    if (!$teacher) {
                        continue;
                    }

                    $schoolYearId = !empty($item['curso_escolar_id']) ? $item['curso_escolar_id'] : $defaultSchoolYearId;
                    $templateId = !empty($item['plantilla_id']) ? $item['plantilla_id'] : $defaultTemplateId;

                    $schedule = UserSchedule::firstOrCreate([
                        'user_id' => $teacher->id,
                        'school_year_id' => $schoolYearId,
                        'schedule_template_id' => $templateId,
                    ]);

                    $importedSchedulesCount++;

                    $selections = $item['selections'] ?? [];
                    foreach ($selections as $row) {
                        if (empty($row['day']) || empty($row['time_slot_id'])) continue;

                        $type = $row['type'] ?? 'texto';
                        $subject = $row['subject'] ?? null;
                        $groupId = $row['group_id'] ?? null;
                        $value = $row['value'] ?? ($subject ?? '');
                        $guardiaId = $row['guardia_id'] ?? null;

                        ScheduleSelection::updateOrCreate(
                            [
                                'user_schedule_id' => $schedule->id,
                                'time_slot_id' => $row['time_slot_id'],
                                'day' => $row['day'],
                            ],
                            [
                                'type' => $type,
                                'subject' => $subject,
                                'group_id' => $groupId,
                                'value' => $value,
                                'guardia_id' => $guardiaId,
                                'is_convivencia' => false,
                            ]
                        );
                        $importedSelectionsCount++;
                    }
                }
            } else {
                // CSV bulk parsing
                $lines = explode("\n", str_replace("\r", "", $content));
                $delimiter = strpos($lines[0], ';') !== false ? ';' : ',';

                $scheduleCache = [];

                foreach ($lines as $index => $line) {
                    if (empty(trim($line))) continue;

                    $cols = str_getcsv($line, $delimiter);
                    if ($index === 0) {
                        // Skip header
                        if (str_contains(strtolower($cols[0]), 'profesor') || str_contains(strtolower($cols[0]), 'email')) {
                            continue;
                        }
                    }

                    if (count($cols) >= 6) {
                        $teacherEmail = trim($cols[0]);
                        $templateId = (!empty($cols[3]) && is_numeric($cols[3])) ? intval($cols[3]) : $defaultTemplateId;
                        $day = trim($cols[5] ?? $cols[0]);
                        $slotId = intval(trim($cols[6] ?? $cols[1]));
                        $type = isset($cols[8]) ? trim($cols[8]) : (isset($cols[3]) ? trim($cols[3]) : 'texto');
                        $textValue = isset($cols[9]) ? trim($cols[9]) : (isset($cols[4]) ? trim($cols[4]) : '');
                        $groupId = isset($cols[10]) && is_numeric($cols[10]) ? intval($cols[10]) : null;
                        $guardiaId = isset($cols[12]) && is_numeric($cols[12]) ? intval($cols[12]) : null;

                        if (empty($teacherEmail)) continue;

                        $teacher = User::where('email', $teacherEmail)->first();
                        if (!$teacher) continue;

                        $cacheKey = $teacher->id . '_' . $defaultSchoolYearId . '_' . $templateId;
                        if (!isset($scheduleCache[$cacheKey])) {
                            $schedule = UserSchedule::firstOrCreate([
                                'user_id' => $teacher->id,
                                'school_year_id' => $defaultSchoolYearId,
                                'schedule_template_id' => $templateId,
                            ]);
                            $scheduleCache[$cacheKey] = $schedule;
                            $importedSchedulesCount++;
                        } else {
                            $schedule = $scheduleCache[$cacheKey];
                        }

                        if (!empty($day) && !empty($slotId)) {
                            ScheduleSelection::updateOrCreate(
                                [
                                    'user_schedule_id' => $schedule->id,
                                    'time_slot_id' => $slotId,
                                    'day' => $day,
                                ],
                                [
                                    'type' => $type,
                                    'subject' => $type === 'clase' ? $textValue : null,
                                    'group_id' => $groupId,
                                    'value' => $textValue,
                                    'guardia_id' => $guardiaId,
                                    'is_convivencia' => false,
                                ]
                            );
                            $importedSelectionsCount++;
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('teacher-schedules.index', ['school_year_id' => $defaultSchoolYearId])
                ->with('success', "¡Importación masiva completada! Se procesaron $importedSchedulesCount horarios y $importedSelectionsCount tramos asignados.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al importar horarios masivos: ' . $e->getMessage());
        }
    }

    /**
     * Download example bulk import templates
     */
    public function downloadBulkTemplate($format = 'csv')
    {
        $this->authorizeManager();

        if ($format === 'json') {
            $data = [
                'meta' => [
                    'description' => 'Plantilla de importación masiva de horarios de profesores',
                ],
                'schedules' => [
                    [
                        'profesor_email' => 'carlos.garcia@example.com',
                        'plantilla_id' => 1,
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
                            ]
                        ]
                    ],
                    [
                        'profesor_email' => 'maria.lopez@example.com',
                        'plantilla_id' => 1,
                        'selections' => [
                            [
                                'day' => '2',
                                'time_slot_id' => 1,
                                'type' => 'clase',
                                'subject' => 'Lengua Castellana',
                                'group_id' => 2,
                                'value' => 'Lengua (2º ESO B)'
                            ]
                        ]
                    ]
                ]
            ];

            return Response::make(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="plantilla_masiva_horarios.json"'
            ]);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_masiva_horarios.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'Profesor_Email',
                'Profesor_Nombre',
                'Curso_Escolar',
                'Plantilla_ID',
                'Plantilla_Nombre',
                'Dia',
                'Tramo_ID',
                'Tramo_Nombre',
                'Tipo',
                'Asignatura_o_Texto',
                'Grupo_ID',
                'Grupo_Nombre',
                'Guardia_ID'
            ], ';');

            fputcsv($file, [
                'carlos.garcia@example.com',
                'Carlos García',
                '2026/2027',
                '1',
                'Plantilla General ESO',
                '1',
                '1',
                '1ª Hora',
                'clase',
                'Matemáticas',
                '1',
                '1º ESO A',
                ''
            ], ';');

            fputcsv($file, [
                'carlos.garcia@example.com',
                'Carlos García',
                '2026/2027',
                '1',
                'Plantilla General ESO',
                '1',
                '2',
                '2ª Hora',
                'guardia',
                'Guardia',
                '',
                '',
                '1'
            ], ';');

            fputcsv($file, [
                'maria.lopez@example.com',
                'María López',
                '2026/2027',
                '1',
                'Plantilla General ESO',
                '2',
                '1',
                '1ª Hora',
                'clase',
                'Lengua',
                '2',
                '2º ESO B',
                ''
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
