<?php

namespace App\Services;

use App\Models\Ausencia;
use App\Models\ScheduleSelection;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\UserSchedule;
use App\Models\ScheduleTemplate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GuardiaEquityService
{
    /**
     * Map day numbers and english names to spanish day names.
     */
    protected static array $daysMap = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miércoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sábado',
        7 => 'domingo',
        'monday' => 'lunes',
        'tuesday' => 'martes',
        'wednesday' => 'miércoles',
        'thursday' => 'jueves',
        'friday' => 'viernes',
        'saturday' => 'sábado',
        'sunday' => 'domingo',
    ];

    /**
     * Normalize day string or number to standard Spanish lowercase name.
     */
    public static function normalizeDay($day): string
    {
        if (is_numeric($day)) {
            return self::$daysMap[(int)$day] ?? 'lunes';
        }
        $lower = strtolower(trim($day));
        return self::$daysMap[$lower] ?? $lower;
    }

    /**
     * Get available teachers on guard duty for a given date and time slot,
     * ranked by equity algorithm (lowest accumulated load score first).
     */
    public function getAvailableGuardiasForSlot(string $date, int $timeSlotId): Collection
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeekNumber = $carbonDate->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
        $dayOfWeekName = self::normalizeDay($dayOfWeekNumber);

        // 1. Find all schedule selections for this time slot and day where user has guardia or convivencia
        $selections = ScheduleSelection::where('time_slot_id', $timeSlotId)
            ->where(function ($query) use ($dayOfWeekNumber, $dayOfWeekName) {
                $query->where('day', (string)$dayOfWeekNumber)
                    ->orWhereRaw('LOWER(day) = ?', [$dayOfWeekName]);
            })
            ->where(function ($query) {
                $query->whereNotNull('guardia_id')
                    ->orWhere('value', 'LIKE', '%Guardia%')
                    ->orWhere('is_convivencia', true);
            })
            ->with(['userSchedule.user'])
            ->get();

        $userIds = $selections->pluck('userSchedule.user_id')->unique()->filter()->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        // 2. Exclude teachers who have an absence on that date and time slot
        $absentUserIds = Ausencia::whereDate('fecha', $date)
            ->where('time_slot_id', $timeSlotId)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->toArray();

        $eligibleUserIds = $userIds->diff($absentUserIds)->values();

        // 2b. Substitute inheritance: if a titular teacher is absent and has an assigned substitute,
        // the substitute inherits the guard slot unless the substitute is also absent (Page 7 of manual)
        $substitutes = User::whereIn('titular_user_id', $userIds)->with('titular')->get();
        if ($substitutes->isNotEmpty()) {
            $absentSubstituteIds = Ausencia::whereDate('fecha', $date)
                ->where('time_slot_id', $timeSlotId)
                ->whereIn('user_id', $substitutes->pluck('id'))
                ->pluck('user_id')
                ->toArray();

            foreach ($substitutes as $sub) {
                if (!in_array($sub->id, $absentSubstituteIds)) {
                    if (in_array($sub->titular_user_id, $absentUserIds)) {
                        $eligibleUserIds->push($sub->id);
                    }
                }
            }
        }

        if ($eligibleUserIds->isEmpty()) {
            return collect();
        }

        // 3. Calculate accumulated workload for each eligible teacher
        $teachers = User::whereIn('id', $eligibleUserIds)->with('titular')->get();

        $rankedTeachers = $teachers->map(function ($teacher) use ($selections, $timeSlotId) {
            // Check if this slot is convivencias for this teacher or their titular
            $isConvivencia = $selections->where(function ($s) use ($teacher) {
                return $s->userSchedule->user_id == $teacher->id || ($teacher->titular_user_id && $s->userSchedule->user_id == $teacher->titular_user_id);
            })->first()?->is_convivencia ?? false;

            // Compute guardias count grouping by distinct (fecha, time_slot_id) - reagrupación rule
            $guardiasCubiertas = $teacher->guardiasCubiertas()
                ->whereNotNull('guardia_confirmed_at')
                ->with(['group', 'zona'])
                ->get();

            $totalGuardias = $guardiasCubiertas->groupBy(function ($g) {
                return ($g->fecha ? $g->fecha->format('Y-m-d') : '') . '_' . $g->time_slot_id;
            })->count();

            // Accumulated difficulty score
            $puntuacion = $guardiasCubiertas->sum(function ($g) {
                return $g->group->dificultad ?? $g->zona->dificultad ?? 1;
            });

            // Most recent date of a covered guardia
            $ultimaGuardia = $guardiasCubiertas->sortByDesc('fecha')->first();
            $ultimaGuardiaFecha = $ultimaGuardia?->fecha ? $ultimaGuardia->fecha->format('Y-m-d') : null;

            // Effective score formula: S_eff = D_g + (N_g * 1.5)
            $effectiveScore = $puntuacion + ($totalGuardias * 1.5);

            $displayName = $teacher->name . ' ' . ($teacher->last_name ?? '');
            if ($teacher->titular_user_id && $teacher->titular) {
                $displayName .= ' (Sustituto de ' . $teacher->titular->name . ')';
            }

            return [
                'user' => $teacher,
                'user_id' => $teacher->id,
                'name' => $displayName,
                'departamento' => $teacher->departamento ?: ($teacher->titular->departamento ?? null),
                'avatar' => $teacher->avatar,
                'email' => $teacher->email,
                'is_convivencia' => $isConvivencia,
                'guardias_count' => $totalGuardias,
                'puntuacion' => $puntuacion,
                'effective_score' => $effectiveScore,
                'ultima_guardia_fecha' => $ultimaGuardiaFecha,
                'is_recommended' => false,
                'is_absent' => false,
            ];
        });

        // 1. Convivencia flag (false first)
        // 2. Total guardias cubiertas ascending ($docente->guardias_cubiertas_count)
        // 3. Tie-breaker: oldest last covered guard date (or null first for teachers who haven't covered any)
        // 4. Effective difficulty score ascending
        $sorted = $rankedTeachers->sort(function ($a, $b) {
            if ($a['is_convivencia'] !== $b['is_convivencia']) {
                return $a['is_convivencia'] ? 1 : -1;
            }
            if ($a['guardias_count'] !== $b['guardias_count']) {
                return $a['guardias_count'] <=> $b['guardias_count'];
            }
            // Tie-break: oldest last guardia date first (0 for never covered)
            $dateA = $a['ultima_guardia_fecha'] ? strtotime($a['ultima_guardia_fecha']) : 0;
            $dateB = $b['ultima_guardia_fecha'] ? strtotime($b['ultima_guardia_fecha']) : 0;
            if ($dateA !== $dateB) {
                return $dateA <=> $dateB;
            }
            return $a['effective_score'] <=> $b['effective_score'];
        })->values();

        // Mark the first available non-convivencia teacher as recommended
        $recommendedSet = false;
        $sorted = $sorted->map(function ($item) use (&$recommendedSet) {
            if (!$recommendedSet && !$item['is_convivencia']) {
                $item['is_recommended'] = true;
                $recommendedSet = true;
            } else {
                $item['is_recommended'] = false;
            }
            return $item;
        });

        return $sorted;
    }

    /**
     * Get teachers scheduled for guard duty in this slot who are absent on this date.
     */
    public function getAbsentGuardiasForSlot(string $date, int $timeSlotId): Collection
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeekNumber = $carbonDate->dayOfWeekIso;
        $dayOfWeekName = self::normalizeDay($dayOfWeekNumber);

        $selections = ScheduleSelection::where('time_slot_id', $timeSlotId)
            ->where(function ($query) use ($dayOfWeekNumber, $dayOfWeekName) {
                $query->where('day', (string)$dayOfWeekNumber)
                    ->orWhereRaw('LOWER(day) = ?', [$dayOfWeekName]);
            })
            ->where(function ($query) {
                $query->whereNotNull('guardia_id')
                    ->orWhere('value', 'LIKE', '%Guardia%')
                    ->orWhere('is_convivencia', true);
            })
            ->with(['userSchedule.user'])
            ->get();

        $userIds = $selections->pluck('userSchedule.user_id')->unique()->filter()->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        $absentUserIds = Ausencia::whereDate('fecha', $date)
            ->where('time_slot_id', $timeSlotId)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->toArray();

        if (empty($absentUserIds)) {
            return collect();
        }

        return User::whereIn('id', $absentUserIds)->get()->map(function ($teacher) {
            return [
                'user' => $teacher,
                'user_id' => $teacher->id,
                'name' => $teacher->name . ' ' . ($teacher->last_name ?? ''),
                'departamento' => $teacher->departamento,
                'avatar' => $teacher->avatar,
                'email' => $teacher->email,
                'is_convivencia' => false,
                'guardias_count' => 0,
                'puntuacion' => 0,
                'effective_score' => 9999,
                'is_recommended' => false,
                'is_absent' => true,
            ];
        });
    }

    /**
     * Get directivo KPIs and full statistical breakdown.
     */
    public function getDirectivoStats(?string $startDate = null, ?string $endDate = null): array
    {
        $query = Ausencia::query();

        if ($startDate) {
            $query->whereDate('fecha', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('fecha', '<=', $endDate);
        }

        $allAusencias = (clone $query)->with(['user', 'guardiaUser', 'timeSlot', 'group', 'zona'])->get();

        $totalAusencias = $allAusencias->count();
        $cubiertas = $allAusencias->filter(fn($a) => $a->isCubierta())->count();
        $pendientes = $totalAusencias - $cubiertas;
        $justificadas = $allAusencias->where('justificada', true)->count();
        $sinJustificar = $totalAusencias - $justificadas;

        $tasaCobertura = $totalAusencias > 0 ? round(($cubiertas / $totalAusencias) * 100, 1) : 100.0;
        $tasaJustificacion = $totalAusencias > 0 ? round(($justificadas / $totalAusencias) * 100, 1) : 0.0;

        // Breakdown by day of week
        $diasSemana = ['Lunes' => 0, 'Martes' => 0, 'Miércoles' => 0, 'Jueves' => 0, 'Viernes' => 0];
        foreach ($allAusencias as $ausencia) {
            $dayName = ucfirst(self::normalizeDay($ausencia->fecha->dayOfWeekIso));
            if (isset($diasSemana[$dayName])) {
                $diasSemana[$dayName]++;
            }
        }

        // Breakdown by time slot
        $timeSlots = ScheduleTemplate::getActiveTimeSlots();
        $ausenciasPorTramo = [];
        foreach ($timeSlots as $slot) {
            $ausenciasPorTramo[$slot->name] = $allAusencias->where('time_slot_id', $slot->id)->count();
        }

        // Equity ranking for all teachers who have performed guardias
        $guardiasRealizadasByUser = Ausencia::whereNotNull('guardia_user_id')
            ->when($startDate, fn($q) => $q->whereDate('fecha', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('fecha', '<=', $endDate))
            ->with(['guardiaUser', 'group', 'zona'])
            ->get()
            ->groupBy('guardia_user_id');

        // Get all teachers
        $rolesToCheck = \Spatie\Permission\Models\Role::whereIn('name', ['profesor', 'admin', 'directiva'])->pluck('name')->toArray();
        $teachers = count($rolesToCheck) > 0 ? User::role($rolesToCheck)->get() : collect();
        $rankingEquidad = $teachers->map(function ($teacher) use ($guardiasRealizadasByUser) {
            $guardias = $guardiasRealizadasByUser->get($teacher->id, collect());
            $count = $guardias->groupBy(function ($g) {
                return ($g->fecha ? $g->fecha->format('Y-m-d') : '') . '_' . $g->time_slot_id;
            })->count();
            $score = $guardias->sum(function ($g) {
                return $g->group->dificultad ?? $g->zona->dificultad ?? 1;
            });

            return [
                'user' => $teacher,
                'name' => $teacher->name . ' ' . ($teacher->last_name ?? ''),
                'departamento' => $teacher->departamento,
                'avatar' => $teacher->avatar,
                'guardias_count' => $count,
                'puntuacion' => $score,
            ];
        })->sortByDesc('puntuacion')->values();

        // Calculate average and standard deviation to detect imbalance (> 2 hours)
        $avgScore = $rankingEquidad->avg('puntuacion') ?: 0;
        $rankingEquidad = $rankingEquidad->map(function ($item) use ($avgScore) {
            $diff = $item['puntuacion'] - $avgScore;
            $item['desbalance'] = abs($diff) > 2; // MAOApp standard rule (> 2 hours deviation)
            $item['diferencia_media'] = round($diff, 1);
            return $item;
        });

        return [
            'total_ausencias' => $totalAusencias,
            'cubiertas' => $cubiertas,
            'pendientes' => $pendientes,
            'justificadas' => $justificadas,
            'sin_justificar' => $sinJustificar,
            'tasa_cobertura' => $tasaCobertura,
            'tasa_justificacion' => $tasaJustificacion,
            'dias_semana' => $diasSemana,
            'ausencias_por_tramo' => $ausenciasPorTramo,
            'ranking_equidad' => $rankingEquidad,
            'promedio_guardias' => round($avgScore, 1),
        ];
    }
}
