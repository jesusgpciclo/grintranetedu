<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;

class CalendarManagerController extends Controller
{
    public function index()
    {
        $activeSchoolYearId = session('active_school_year_id');
        $calendars = Calendar::where('school_year_id', $activeSchoolYearId)
            ->with(['user', 'schoolYear'])
            ->withCount('events')
            ->orderByDesc('is_base')
            ->orderBy('name')
            ->get();

        $baseCalendar = $calendars->where('is_base', true)->first();
        $schoolYears = \App\Models\SchoolYear::orderBy('name', 'desc')->get();

        return view('calendar.manage', compact('calendars', 'baseCalendar', 'schoolYears', 'activeSchoolYearId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'color'          => 'nullable|string|max:7',
            'parent_id'      => 'nullable|exists:calendars,id',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'school_year_id' => 'required|exists:school_years,id',
        ]);

        Calendar::create([
            'name'           => $request->name,
            'description'    => $request->description,
            'color'          => $request->color ?: '#38bdf8',
            'user_id'        => auth()->id(),
            'is_base'        => false,
            'parent_id'      => $request->parent_id ?: Calendar::base()?->id,
            'school_year_id' => $request->school_year_id,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date ?: null,
        ]);

        return redirect()->route('calendars.index')
            ->with('success', 'Calendario creado correctamente.');
    }

    public function update(Request $request, Calendar $calendar)
    {
        $rules = [
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'color'          => 'nullable|string|max:7',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'school_year_id' => 'required|exists:school_years,id',
        ];

        $request->validate($rules);

        $calendar->update([
            'name'           => $request->name,
            'description'    => $request->description,
            'color'          => $request->color ?: $calendar->color,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date ?: null,
            'school_year_id' => $request->school_year_id,
        ]);

        return redirect()->route('calendars.index')
            ->with('success', 'Calendario actualizado correctamente.');
    }

    public function destroy(Calendar $calendar)
    {
        if ($calendar->is_base) {
            return back()->with('error', 'No se puede eliminar el calendario base.');
        }

        $calendar->delete();

        return redirect()->route('calendars.index')
            ->with('success', 'Calendario eliminado correctamente.');
    }
}
