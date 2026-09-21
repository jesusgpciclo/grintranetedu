<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class ActiveSchoolYear
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle explicit switch of school year
        if ($request->has('set_school_year_id')) {
            $yearId = $request->input('set_school_year_id');
            $year = SchoolYear::find($yearId);
            if ($year) {
                Session::put('active_school_year_id', $year->id);
                Session::put('active_school_year_name', $year->name);
            }
            // Redirect back or to current path without the query parameter to clean the URL
            return redirect($request->url());
        }

        // Initialize active school year if not present in session
        if (!Session::has('active_school_year_id')) {
            $activeYear = SchoolYear::where('is_active', true)->first();
            if (!$activeYear) {
                $activeYear = SchoolYear::orderBy('name', 'desc')->first();
            }

            if ($activeYear) {
                Session::put('active_school_year_id', $activeYear->id);
                Session::put('active_school_year_name', $activeYear->name);
            }
        }

        // Share school year variables with all views globally
        $allSchoolYears = SchoolYear::orderBy('name', 'desc')->get();
        $activeSchoolYearId = Session::get('active_school_year_id');
        $activeSchoolYearName = Session::get('active_school_year_name');

        View::share('allSchoolYears', $allSchoolYears);
        View::share('activeSchoolYearId', $activeSchoolYearId);
        View::share('activeSchoolYearName', $activeSchoolYearName);

        return $next($request);
    }
}
