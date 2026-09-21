<?php

namespace App\Http\Controllers;

use App\Models\TicBooking;
use App\Models\TicResource;
use App\Models\TicResourceCategory;
use App\Models\TimeSlot;
use App\Models\ScheduleTemplate;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TicBookingController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('es');
        
        $categories = TicResourceCategory::with('resources')->get();
        $selectedResource = null;
        $bookings = [];

        $view = $request->get('view', 'week');
        $dateParam = $request->input('date', date('Y-m-d'));
        $carbonDate = Carbon::parse($dateParam);
        
        $holidays = Holiday::all();

        if ($request->has('resource_id') && $request->resource_id) {
            $selectedResource = TicResource::find($request->resource_id);
            if ($selectedResource) {
                // Fetch bookings for this resource based on the view
                $start = match($view) {
                    'day' => $carbonDate->copy()->startOfDay(),
                    'week' => $carbonDate->copy()->startOfWeek(),
                    'month' => $carbonDate->copy()->startOfMonth(),
                    'year' => $carbonDate->copy()->startOfYear(),
                    default => $carbonDate->copy()->startOfWeek(),
                };
                $end = match($view) {
                    'day' => $carbonDate->copy()->endOfDay(),
                    'week' => $carbonDate->copy()->endOfWeek(),
                    'month' => $carbonDate->copy()->endOfMonth(),
                    'year' => $carbonDate->copy()->endOfYear(),
                    default => $carbonDate->copy()->endOfWeek(),
                };

                $bookings = TicBooking::with(['user', 'timeSlot'])
                    ->where('tic_resource_id', $selectedResource->id)
                    ->whereBetween('date', [$start, $end])
                    ->get();
            }
        }

        // Get time slots from the first schedule template
        $template = ScheduleTemplate::first();
        $timeSlots = $template ? $template->timeSlots : collect();

        // Prepare days based on view
        $days = [];

        if ($view === 'day') {
            $isHoliday = $holidays->contains(fn ($h) => Carbon::parse($h->date)->isSameDay($carbonDate));
            $holidayData = $isHoliday ? $holidays->first(fn ($h) => Carbon::parse($h->date)->isSameDay($carbonDate)) : null;

            $days[] = [
                'date' => $carbonDate->format('Y-m-d'),
                'dayName' => $carbonDate->translatedFormat('l'),
                'dayNumber' => $carbonDate->format('d'),
                'is_today' => $carbonDate->isToday(),
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayData ? $holidayData->name : null,
            ];
        } elseif ($view === 'week') {
            $startOfWeek = $carbonDate->copy()->startOfWeek();
            for ($i = 0; $i < 5; $i++) { // Monday to Friday
                $currentDay = $startOfWeek->copy()->addDays($i);
                
                $isHoliday = $holidays->contains(fn ($h) => Carbon::parse($h->date)->isSameDay($currentDay));
                $holidayData = $isHoliday ? $holidays->first(fn ($h) => Carbon::parse($h->date)->isSameDay($currentDay)) : null;

                $days[] = [
                    'date' => $currentDay->format('Y-m-d'),
                    'dayName' => $currentDay->translatedFormat('l'),
                    'dayNumber' => $currentDay->format('d'),
                    'is_today' => $currentDay->isToday(),
                    'is_holiday' => $isHoliday,
                    'holiday_name' => $holidayData ? $holidayData->name : null,
                ];
            }
        } elseif ($view === 'month') {
            $daysInMonth = $carbonDate->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $currentDay = $carbonDate->copy()->day($i);
                
                $isHoliday = $holidays->contains(fn ($h) => Carbon::parse($h->date)->isSameDay($currentDay));
                $holidayData = $isHoliday ? $holidays->first(fn ($h) => Carbon::parse($h->date)->isSameDay($currentDay)) : null;

                $days[] = [
                    'date' => $currentDay->format('Y-m-d'),
                    'dayName' => $currentDay->translatedFormat('l'),
                    'dayNumber' => $currentDay->format('d'),
                    'is_today' => $currentDay->isToday(),
                    'is_weekend' => $currentDay->isWeekend(),
                    'dayOfWeek' => $currentDay->dayOfWeek, // 0 (Sun) - 6 (Sat)
                    'is_holiday' => $isHoliday,
                    'holiday_name' => $holidayData ? $holidayData->name : null,
                ];
            }
        } elseif ($view === 'year') {
            $academicStart = Carbon::create($carbonDate->year, 9, 1);
            if ($carbonDate->month <= 6) {
                $academicStart->subYear();
            }

            for ($m = 0; $m < 10; $m++) {
                $monthDate = $academicStart->copy()->addMonths($m)->startOfMonth();
                $monthDays = [];
                $daysInMonth = $monthDate->daysInMonth;
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $currentDay = $monthDate->copy()->day($i);
                    
                    $isHoliday = $holidays->contains(fn ($h) => Carbon::parse($h->date)->isSameDay($currentDay));
                    $holidayData = $isHoliday ? $holidays->first(fn ($h) => Carbon::parse($h->date)->isSameDay($currentDay)) : null;

                    $monthDays[] = [
                        'date' => $currentDay->format('Y-m-d'),
                        'dayNumber' => $currentDay->format('d'),
                        'is_today' => $currentDay->isToday(),
                        'is_weekend' => $currentDay->isWeekend(),
                        'dayOfWeek' => $currentDay->dayOfWeek,
                        'is_holiday' => $isHoliday,
                        'holiday_name' => $holidayData ? $holidayData->name : null,
                    ];
                }
                $days[] = [
                    'monthName' => $monthDate->translatedFormat('F Y'),
                    'days' => $monthDays,
                    'date' => $monthDate->format('Y-m-d'),
                    'firstDayOfWeek' => ($monthDate->dayOfWeek + 6) % 7 // Monday = 0
                ];
            }
        }

        return view('tic_bookings.index', compact('categories', 'selectedResource', 'bookings', 'timeSlots', 'days', 'dateParam', 'view'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tic_resource_id' => 'required|exists:tic_resources,id',
            'date' => 'required|date',
            'time_slot_id' => 'required|exists:time_slots,id',
            'observations' => 'nullable|string|max:500',
        ]);
        
        // Prevent booking on a holiday
        $isHoliday = Holiday::whereDate('date', $request->date)->exists();
        if ($isHoliday) {
            return back()->with('error', 'No se pueden hacer reservas en un día festivo.');
        }

        // Check for overlaps
        $exists = TicBooking::where('tic_resource_id', $request->tic_resource_id)
            ->where('date', $request->date)
            ->where('time_slot_id', $request->time_slot_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'El recurso ya está reservado en ese tramo horario.');
        }

        TicBooking::create([
            'tic_resource_id' => $request->tic_resource_id,
            'user_id' => Auth::id(),
            'date' => $request->date,
            'time_slot_id' => $request->time_slot_id,
            'observations' => $request->observations,
        ]);

        return back()->with('success', 'Reserva realizada con éxito.');
    }

    public function update(Request $request, TicBooking $ticBooking)
    {
        if ($ticBooking->user_id !== Auth::id() && !Auth::user()->hasRole('admin|directiva')) {
            abort(403);
        }

        $request->validate([
            'observations' => 'nullable|string|max:500',
        ]);

        $ticBooking->update([
            'observations' => $request->observations,
        ]);

        return back()->with('success', 'Reserva actualizada correctamente.');
    }

    public function destroy(TicBooking $ticBooking)
    {
        if ($ticBooking->user_id !== Auth::id() && !Auth::user()->hasRole('admin|directiva')) {
            abort(403);
        }

        $ticBooking->delete();

        return back()->with('success', 'Reserva cancelada correctamente.');
    }
}
