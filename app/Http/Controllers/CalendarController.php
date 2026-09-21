<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $dateParam  = $request->get('date', date('Y-m-d'));
        $filterType = $request->get('type', 'all');
        $calendarId = $request->get('calendar_id');

        $currentDate = Carbon::parse($dateParam);

        $activeSchoolYearId = session('active_school_year_id');
        // Load all calendars for the selector, filtered by active school year
        $calendars = Calendar::where('school_year_id', $activeSchoolYearId)->orderByDesc('is_base')->orderBy('name')->get();
        $baseCalendar = $calendars->where('is_base', true)->first();

        // Determine active calendar
        $activeCalendar = null;
        if ($calendarId) {
            $activeCalendar = $calendars->firstWhere('id', (int)$calendarId) 
                ?? $calendars->firstWhere('id', (string)$calendarId) 
                ?? Calendar::find($calendarId);
        }
        if (!$activeCalendar) {
            $activeCalendar = $baseCalendar ?? Calendar::orderByDesc('is_base')->first();
        }

        if ($activeCalendar && !$calendars->contains('id', $activeCalendar->id)) {
            $calendars->push($activeCalendar);
        }

        // Load events (own + inherited from parent)
        $eventQuery = $activeCalendar
            ? $activeCalendar->allEvents()
            : collect();

        // Apply type filter
        if ($filterType !== 'all') {
            $eventQuery = $eventQuery->where('type', $filterType);
        }

        $events = $eventQuery->values();

        $view = $request->get('view', 'monthly');

        $months = [];

        if ($view === 'academic') {
            $academicStart = $activeCalendar && $activeCalendar->start_date 
                ? $activeCalendar->start_date->copy()->startOfMonth() 
                : Carbon::create($currentDate->year, 9, 1);
            
            if (!$activeCalendar || !$activeCalendar->start_date) {
                if ($currentDate->month <= 6) {
                    $academicStart->subYear();
                }
            }

            $monthsCount = 10;
            if ($activeCalendar && $activeCalendar->start_date && $activeCalendar->end_date) {
                $monthsCount = $activeCalendar->start_date->copy()->startOfMonth()->diffInMonths($activeCalendar->end_date->copy()->startOfMonth()) + 1;
            }

            for ($i = 0; $i < $monthsCount; $i++) {
                $monthObj = clone $academicStart;
                $months[] = [
                    'name' => $monthObj->locale('es')->isoFormat('MMMM YYYY'),
                    'month' => $monthObj->month,
                    'year' => $monthObj->year,
                    'days' => $this->buildMonthGrid($monthObj, $events),
                ];
                $academicStart->addMonth();
            }

            $prevDate = $currentDate->copy()->subYear()->format('Y-m-d');
            $nextDate = $currentDate->copy()->addYear()->format('Y-m-d');

        } elseif ($view === 'weekly') {
            $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
            $days = [];
            for ($i = 0; $i < 7; $i++) {
                $dayDate = clone $startOfWeek;
                $dateString = $dayDate->toDateString();
                $dayEvents = $events->filter(function ($e) use ($dateString) {
                    return $e->spansDate($dateString);
                })->values();

                $days[] = [
                    'day' => $dayDate->day,
                    'date' => $dateString,
                    'is_weekend' => $dayDate->isWeekend(),
                    'is_today' => $dayDate->isToday(),
                    'full_name' => $dayDate->locale('es')->isoFormat('dddd D'),
                    'events' => $dayEvents,
                ];
                $startOfWeek->addDay();
            }
            $months[] = [
                'name' => 'Semana del ' . $currentDate->copy()->startOfWeek(Carbon::MONDAY)->format('d/m/Y'),
                'days' => $days,
                'is_weekly' => true
            ];

            $prevDate = $currentDate->copy()->subWeek()->format('Y-m-d');
            $nextDate = $currentDate->copy()->addWeek()->format('Y-m-d');

        } elseif ($view === 'daily') {
            $dateString = $currentDate->toDateString();
            $dayEvents = $events->filter(function ($e) use ($dateString) {
                return $e->spansDate($dateString);
            })->values();

            $months[] = [
                'name' => ucfirst($currentDate->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY')),
                'days' => [[
                    'day'        => $currentDate->day,
                    'date'       => $dateString,
                    'is_weekend' => $currentDate->isWeekend(),
                    'is_today'   => $currentDate->isToday(),
                    'full_name'  => ucfirst($currentDate->locale('es')->isoFormat('dddd D')),
                    'events'     => $dayEvents,
                ]],
                'is_daily' => true
            ];

            $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
            $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        } else {
            $monthObj = clone $currentDate->startOfMonth();
            $months[] = [
                'name' => $monthObj->locale('es')->isoFormat('MMMM YYYY'),
                'month' => $monthObj->month,
                'year' => $monthObj->year,
                'days' => $this->buildMonthGrid($monthObj, $events),
            ];
            $prevDate = $currentDate->copy()->subMonth()->format('Y-m-d');
            $nextDate = $currentDate->copy()->addMonth()->format('Y-m-d');
        }

        $eventTypes = CalendarEvent::$types;
        $typeColors  = CalendarEvent::$typeColors;

        // Calculate teaching days stats for active calendar
        $stats = [
            'teaching_days' => 0,
            'holidays'      => 0,
            'evaluations'   => 0,
            'meetings'      => 0,
        ];

        if ($activeCalendar) {
            $allCalEvents = $activeCalendar->allEvents();
            $nonTeachingDates = collect();

            foreach ($allCalEvents as $ev) {
                if (in_array($ev->type, ['holiday', 'vacation', 'non_teaching'])) {
                    $s = $ev->start_date ? $ev->start_date->copy() : null;
                    $e = $ev->end_date ? $ev->end_date->copy() : ($s ? $s->copy() : null);
                    if ($s && $e) {
                        while ($s->lte($e)) {
                            $nonTeachingDates->push($s->format('Y-m-d'));
                            $s->addDay();
                        }
                    }
                }
            }
            $nonTeachingDates = $nonTeachingDates->unique();

            $calStart = $activeCalendar->start_date ? $activeCalendar->start_date->copy() : Carbon::create($currentDate->year, 9, 1);
            $calEnd   = $activeCalendar->end_date ? $activeCalendar->end_date->copy() : Carbon::create($currentDate->year + 1, 6, 30);

            if ($calStart && $calEnd && $calStart->lte($calEnd)) {
                $cur = $calStart->copy();
                while ($cur->lte($calEnd)) {
                    if ($cur->isWeekday() && !$nonTeachingDates->contains($cur->format('Y-m-d'))) {
                        $stats['teaching_days']++;
                    }
                    $cur->addDay();
                }
            }

            $stats['holidays']    = $allCalEvents->whereIn('type', ['holiday', 'vacation', 'non_teaching'])->count();
            $stats['evaluations'] = $allCalEvents->where('type', 'evaluation')->count();
            $stats['excursions']  = $allCalEvents->where('type', 'excursion')->count();
            $stats['meetings']    = $allCalEvents->whereIn('type', ['department_meeting', 'faculty_meeting'])->count();
        }

        return view('calendar.index', compact(
            'months',
            'view',
            'prevDate',
            'nextDate',
            'dateParam',
            'filterType',
            'events',
            'eventTypes',
            'typeColors',
            'calendars',
            'activeCalendar',
            'stats',
        ));
    }

    private function buildMonthGrid(Carbon $month, $events): array
    {
        $daysInMonth = $month->daysInMonth;
        $firstDayOfWeek = $month->copy()->startOfMonth()->dayOfWeekIso;

        $grid = [];

        for ($i = 1; $i < $firstDayOfWeek; $i++) {
            $grid[] = null;
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayDate = Carbon::create($month->year, $month->month, $day);
            $dateString = $dayDate->format('Y-m-d');

            $dayEvents = $events->filter(function ($e) use ($dateString) {
                return $e->spansDate($dateString);
            });

            $grid[] = [
                'day' => $day,
                'date' => $dateString,
                'is_weekend' => $dayDate->isWeekend(),
                'is_today' => $dayDate->isToday(),
                'events' => $dayEvents,
            ];
        }

        return $grid;
    }

    public function store(Request $request)
    {
        $request->validate([
            'calendar_id'     => 'required|exists:calendars,id',
            'title'           => 'required|string|max:255',
            'type'            => 'required|in:' . implode(',', array_keys(CalendarEvent::$types)),
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'description'     => 'nullable|string',
            'attachment_file' => 'nullable|file|max:10240',
            'color'           => 'nullable|string|max:7',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment_file')) {
            $attachmentPath = $request->file('attachment_file')->store('calendar_attachments', 'public');
        }

        CalendarEvent::create([
            'calendar_id' => $request->calendar_id,
            'title'       => $request->title,
            'type'        => $request->type,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date ?: null,
            'description' => $request->description,
            'attachment'  => $attachmentPath,
            'color'       => $request->color ?: null,
            'created_by'  => auth()->id(),
        ]);

        return redirect()->route('calendar.index', [
            'date'        => $request->get('active_date', date('Y-m-d')),
            'calendar_id' => $request->calendar_id,
        ])->with('success', 'Evento creado correctamente.');
    }

    public function update(Request $request, CalendarEvent $event)
    {
        $request->validate([
            'title'           => 'required|string|max:255',
            'type'            => 'required|in:' . implode(',', array_keys(CalendarEvent::$types)),
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'description'     => 'nullable|string',
            'attachment_file' => 'nullable|file|max:10240',
            'color'           => 'nullable|string|max:7',
        ]);

        $data = [
            'title'       => $request->title,
            'type'        => $request->type,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date ?: null,
            'description' => $request->description,
            'color'       => $request->color ?: null,
        ];

        if ($request->hasFile('attachment_file')) {
            if ($event->attachment && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->attachment)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($event->attachment);
            }
            $data['attachment'] = $request->file('attachment_file')->store('calendar_attachments', 'public');
        }

        $event->update($data);

        return redirect()->route('calendar.index', [
            'date'        => $request->get('active_date', date('Y-m-d')),
            'calendar_id' => $event->calendar_id,
        ])->with('success', 'Evento actualizado correctamente.');
    }

    public function downloadAttachment(CalendarEvent $event)
    {
        if (!$event->attachment || !\Illuminate\Support\Facades\Storage::disk('public')->exists($event->attachment)) {
            abort(404, 'El archivo adjunto no existe o fue eliminado.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($event->attachment);
    }

    public function destroy(Request $request, CalendarEvent $event)
    {
        $calendarId = $event->calendar_id;
        if ($event->attachment && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->attachment)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->attachment);
        }
        $event->delete();

        return redirect()->route('calendar.index', [
            'date'        => $request->get('active_date', date('Y-m-d')),
            'calendar_id' => $calendarId,
        ])->with('success', 'Evento eliminado correctamente.');
    }

    public function importIcs(Request $request)
    {
        $request->validate([
            'ics_file'        => 'required_without:ics_text|nullable|file',
            'ics_text'        => 'required_without:ics_file|nullable|string',
            'target_option'   => 'required|in:active,new,existing',
            'calendar_id'     => 'required_if:target_option,existing|nullable|exists:calendars,id',
            'new_cal_name'    => 'nullable|string|max:255',
            'clear_existing'  => 'nullable|boolean',
        ]);

        $activeSchoolYearId = session('active_school_year_id');

        $content = '';
        if ($request->hasFile('ics_file')) {
            $content = file_get_contents($request->file('ics_file')->getRealPath());
        } elseif ($request->filled('ics_text')) {
            $content = $request->ics_text;
        }

        if (empty(trim($content))) {
            return back()->with('error', 'El archivo o contenido ICS está vacío.');
        }

        $rawEvents = $this->parseIcsContent($content);

        if (empty($rawEvents)) {
            return back()->with('error', 'No se encontraron eventos válidos en el archivo ICS.');
        }

        // Determine or create target calendar
        $calendar = null;
        if ($request->target_option === 'active') {
            $activeCalId = $request->input('active_calendar_id');
            if ($activeCalId) {
                $calendar = Calendar::find($activeCalId);
            }
            if (!$calendar) {
                $calendar = Calendar::where('school_year_id', $activeSchoolYearId)->orderByDesc('is_base')->first();
            }
        } elseif ($request->target_option === 'existing' && $request->calendar_id) {
            $calendar = Calendar::find($request->calendar_id);
        }

        if (!$calendar || $request->target_option === 'new') {
            $calName = $request->new_cal_name ?: ('Calendario Importado ' . date('d/m/Y H:i'));
            $calendar = Calendar::create([
                'name'           => $calName,
                'description'    => 'Importado desde archivo ICS el ' . date('d/m/Y H:i'),
                'color'          => '#38bdf8',
                'user_id'        => auth()->id(),
                'is_base'        => false,
                'school_year_id' => $activeSchoolYearId,
            ]);
        }

        if ($request->boolean('clear_existing')) {
            CalendarEvent::where('calendar_id', $calendar->id)->delete();
        }

        $importedCount = 0;
        foreach ($rawEvents as $ev) {
            $title = $ev['SUMMARY'] ?? 'Evento importado';
            $description = $ev['DESCRIPTION'] ?? null;
            if (!empty($ev['LOCATION'])) {
                $description = ($description ? ($description . "\n") : '') . "Ubicación: " . $ev['LOCATION'];
            }

            $startDate = $this->parseIcsDate($ev['DTSTART'] ?? null);
            $endDate   = $this->parseIcsDate($ev['DTEND'] ?? null);

            if (!$startDate) {
                continue;
            }

            $type = $this->detectEventType($title, $description ?? '');

            CalendarEvent::create([
                'calendar_id' => $calendar->id,
                'title'       => $title,
                'type'        => $type,
                'start_date'  => $startDate,
                'end_date'    => ($endDate && $endDate !== $startDate) ? $endDate : null,
                'description' => $description,
                'created_by'  => auth()->id(),
            ]);

            $importedCount++;
        }

        return redirect()->route('calendar.index', [
            'calendar_id' => $calendar->id,
        ])->with('success', "Se han importado {$importedCount} eventos correctamente en el calendario \"{$calendar->name}\".");
    }

    private function parseIcsContent(string $content): array
    {
        // Unfold lines: RFC 5545 specifies lines broken by CRLF + space/tab
        $unfolded = preg_replace("/\r?\n[ \t]/", "", $content);
        $unfolded = str_replace("\r\n", "\n", $unfolded);
        $lines = explode("\n", $unfolded);

        $events = [];
        $currentEvent = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === 'BEGIN:VEVENT') {
                $currentEvent = [];
                continue;
            }

            if ($line === 'END:VEVENT') {
                if ($currentEvent && !empty($currentEvent['SUMMARY']) && !empty($currentEvent['DTSTART'])) {
                    $events[] = $currentEvent;
                }
                $currentEvent = null;
                continue;
            }

            if ($currentEvent !== null) {
                $colonPos = strpos($line, ':');
                if ($colonPos !== false) {
                    $propNameWithParams = substr($line, 0, $colonPos);
                    $value = substr($line, $colonPos + 1);

                    $semicolonPos = strpos($propNameWithParams, ';');
                    $propName = ($semicolonPos !== false) ? substr($propNameWithParams, 0, $semicolonPos) : $propNameWithParams;
                    $propName = strtoupper(trim($propName));

                    $value = str_replace(['\\\\', '\\;', '\\,', '\\N', '\\n'], ['\\', ';', ',', "\n", "\n"], $value);

                    $currentEvent[$propName] = trim($value);
                }
            }
        }

        return $events;
    }

    private function parseIcsDate(?string $rawDate): ?string
    {
        if (!$rawDate) return null;
        $rawDate = trim($rawDate);

        if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $rawDate, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        try {
            return Carbon::parse($rawDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function detectEventType(string $title, string $description = ''): string
    {
        $text = mb_strtolower($title . ' ' . $description);

        if (str_contains($text, 'festiv') || str_contains($text, 'fiesta')) {
            return 'holiday';
        }
        if (str_contains($text, 'vacacion')) {
            return 'vacation';
        }
        if (str_contains($text, 'no lectivo')) {
            return 'non_teaching';
        }
        if (str_contains($text, 'evaluaci')) {
            return 'evaluation';
        }
        if (str_contains($text, 'excursio') || str_contains($text, 'excursión') || str_contains($text, 'salida') || str_contains($text, 'visita') || str_contains($text, 'viaje')) {
            return 'excursion';
        }
        if (str_contains($text, 'departamento') || str_contains($text, 'reunió') || str_contains($text, 'reunion')) {
            return 'department_meeting';
        }
        if (str_contains($text, 'claustro')) {
            return 'faculty_meeting';
        }

        return 'other';
    }

    public function exportIcs(Request $request)
    {
        $calendarId = $request->get('calendar_id');
        $activeSchoolYearId = session('active_school_year_id');

        $calendar = $calendarId ? Calendar::find($calendarId) : null;
        if (!$calendar) {
            $calendar = Calendar::where('school_year_id', $activeSchoolYearId)->orderByDesc('is_base')->first();
        }

        if (!$calendar) {
            return back()->with('error', 'No se encontró ningún calendario activo para exportar.');
        }

        $icsContent = $this->buildIcsContent($calendar);
        $filename = 'calendario_' . \Illuminate\Support\Str::slug($calendar->name) . '_' . date('Ymd_His') . '.ics';

        return response($icsContent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportCsv(Request $request)
    {
        $calendarId = $request->get('calendar_id');
        $activeSchoolYearId = session('active_school_year_id');

        $calendar = $calendarId ? Calendar::find($calendarId) : null;
        if (!$calendar) {
            $calendar = Calendar::where('school_year_id', $activeSchoolYearId)->orderByDesc('is_base')->first();
        }

        if (!$calendar) {
            return back()->with('error', 'No se encontró ningún calendario para exportar.');
        }

        $events = $calendar->allEvents()->sortBy('start_date');
        $filename = 'calendario_' . \Illuminate\Support\Str::slug($calendar->name) . '_' . date('Ymd') . '.csv';

        $callback = function () use ($events) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Título', 'Tipo', 'Fecha Inicio', 'Fecha Fin', 'Descripción']);

            foreach ($events as $ev) {
                fputcsv($file, [
                    $ev->title,
                    $ev->type_label,
                    $ev->start_date ? $ev->start_date->format('d/m/Y') : '',
                    $ev->end_date ? $ev->end_date->format('d/m/Y') : '',
                    $ev->description ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function feed(string $token)
    {
        $calendar = Calendar::where('feed_token', $token)->first();

        if (!$calendar) {
            abort(404, 'Calendario no encontrado.');
        }

        $icsContent = $this->buildIcsContent($calendar);

        return response($icsContent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    public function printView(Request $request)
    {
        $activeSchoolYearId = session('active_school_year_id');
        $calendarId = $request->get('calendar_id');
        $calendar = $calendarId ? Calendar::find($calendarId) : Calendar::where('school_year_id', $activeSchoolYearId)->orderByDesc('is_base')->first();

        $events = $calendar ? $calendar->allEvents()->sortBy('start_date') : collect();

        // Calculate stats for print
        $stats = [
            'teaching_days' => 0,
            'holidays'      => 0,
            'evaluations'   => 0,
            'excursions'    => 0,
            'meetings'      => 0,
        ];

        if ($calendar) {
            $allCalEvents = $calendar->allEvents();
            $nonTeachingDates = collect();
            foreach ($allCalEvents as $ev) {
                if (in_array($ev->type, ['holiday', 'vacation', 'non_teaching'])) {
                    $s = $ev->start_date ? $ev->start_date->copy() : null;
                    $e = $ev->end_date ? $ev->end_date->copy() : ($s ? $s->copy() : null);
                    if ($s && $e) {
                        while ($s->lte($e)) {
                            $nonTeachingDates->push($s->format('Y-m-d'));
                            $s->addDay();
                        }
                    }
                }
            }
            $nonTeachingDates = $nonTeachingDates->unique();
            $calStart = $calendar->start_date ? $calendar->start_date->copy() : Carbon::create(date('Y'), 9, 1);
            $calEnd   = $calendar->end_date ? $calendar->end_date->copy() : Carbon::create(date('Y') + 1, 6, 30);
            if ($calStart && $calEnd && $calStart->lte($calEnd)) {
                $cur = $calStart->copy();
                while ($cur->lte($calEnd)) {
                    if ($cur->isWeekday() && !$nonTeachingDates->contains($cur->format('Y-m-d'))) {
                        $stats['teaching_days']++;
                    }
                    $cur->addDay();
                }
            }
            $stats['holidays']    = $allCalEvents->whereIn('type', ['holiday', 'vacation', 'non_teaching'])->count();
            $stats['evaluations'] = $allCalEvents->where('type', 'evaluation')->count();
            $stats['excursions']  = $allCalEvents->where('type', 'excursion')->count();
            $stats['meetings']    = $allCalEvents->whereIn('type', ['department_meeting', 'faculty_meeting'])->count();
        }

        return view('calendar.print', compact('calendar', 'events', 'stats'));
    }

    public function move(Request $request, CalendarEvent $event)
    {
        $request->validate([
            'new_date' => 'required|date',
        ]);

        $newDate = Carbon::parse($request->new_date);

        if ($event->end_date && $event->start_date) {
            $diffInDays = $event->start_date->diffInDays($event->end_date);
            $event->start_date = $newDate;
            $event->end_date = $newDate->copy()->addDays($diffInDays);
        } else {
            $event->start_date = $newDate;
            $event->end_date = null;
        }

        $event->save();

        return response()->json([
            'success' => true,
            'message' => 'Evento movido correctamente.',
        ]);
    }

    private function buildIcsContent(Calendar $calendar): string
    {
        $events = $calendar->allEvents();
        $lines = [];
        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:2.0';
        $lines[] = 'PRODID:-//GR Intranet EDU//Escolar//ES';
        $lines[] = 'CALSCALE:GREGORIAN';
        $lines[] = 'METHOD:PUBLISH';
        $lines[] = 'X-WR-CALNAME:' . $this->escapeIcsText($calendar->name);

        foreach ($events as $ev) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . md5($ev->id . '_' . $ev->created_at);
            $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');

            $startDateStr = $ev->start_date ? $ev->start_date->format('Ymd') : date('Ymd');
            $endDateStr = $ev->end_date ? $ev->end_date->format('Ymd') : $startDateStr;

            $lines[] = 'DTSTART;VALUE=DATE:' . $startDateStr;
            $lines[] = 'DTEND;VALUE=DATE:' . $endDateStr;
            $lines[] = 'SUMMARY:' . $this->escapeIcsText($ev->title);

            if ($ev->description) {
                $lines[] = 'DESCRIPTION:' . $this->escapeIcsText($ev->description);
            }

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines);
    }

    private function escapeIcsText(string $text): string
    {
        return str_replace(["\\", ";", ",", "\n", "\r"], ["\\\\", "\\;", "\\,", "\\n", ""], $text);
    }
}
