<?php

namespace App\Http\Controllers;

use App\Models\Ausencia;
use App\Models\Group;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\Zona;
use App\Models\Aula;
use App\Models\ScheduleTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AusenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $user = Auth::user();
        $isDirectiva = $user->hasRole('admin') || $user->hasRole('directiva');
        $viewFilter = $request->input('filter', 'all'); // 'all' or 'mine'

        $activeSchoolYearId = session('active_school_year_id');

        $query = Ausencia::whereDate('fecha', $date)
            ->with(['user', 'guardiaUser', 'timeSlot', 'group', 'zona']);

        if (!$isDirectiva || $viewFilter === 'mine') {
            $query->where('user_id', $user->id);
        }

        $ausencias = $query->get()->groupBy('time_slot_id');
        $timeSlots = ScheduleTemplate::getActiveTimeSlots();

        return view('ausencias.index', compact('ausencias', 'timeSlots', 'date', 'isDirectiva', 'viewFilter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $isDirectiva = $user->hasRole('admin') || $user->hasRole('directiva');

        $activeSchoolYearId = session('active_school_year_id');
        $tramos = ScheduleTemplate::getActiveTimeSlots();
        $grupos = Group::when($activeSchoolYearId, fn($q) => $q->where('school_year_id', $activeSchoolYearId))
            ->orderBy('course')
            ->orderBy('name')
            ->get();
        $zonas = Aula::orderBy('nombre')->get();
        $rolesToCheck = \Spatie\Permission\Models\Role::whereIn('name', ['profesor', 'admin', 'directiva'])->pluck('name')->toArray();
        $docentes = ($isDirectiva && count($rolesToCheck) > 0) ? User::role($rolesToCheck)->orderBy('name')->get() : collect();

        // Pre-selection from query params
        $selectedTimeSlot = $request->input('time_slot_id');
        $selectedDate = $request->input('date', date('Y-m-d'));

        return view('ausencias.create', compact('tramos', 'grupos', 'zonas', 'docentes', 'selectedTimeSlot', 'selectedDate', 'isDirectiva'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Support single time_slot_id (e.g., from quick modal) as well as array
        if ($request->filled('time_slot_id') && !$request->has('time_slot_ids')) {
            $request->merge(['time_slot_ids' => [(int)$request->input('time_slot_id')]]);
        }

        $request->validate([
            'fecha' => 'required|date',
            'time_slot_ids' => 'required|array|min:1',
            'time_slot_ids.*' => 'exists:time_slots,id',
            'user_id' => 'nullable|exists:users,id',
            'slots' => 'nullable|array',
        ]);

        // Teachers can report their own absence or a colleague's absence
        $targetUserId = ($request->filled('user_id')) ? (int)$request->user_id : $user->id;

        foreach ($request->time_slot_ids as $slotId) {
            $slotData = $request->input("slots.{$slotId}", []);
            $esGuardia = isset($slotData['es_guardia']) ? (bool)$slotData['es_guardia'] : $request->boolean('es_guardia');
            
            $groupId = (!$esGuardia && !empty($slotData['group_id'])) ? $slotData['group_id'] : ($esGuardia ? null : $request->input('group_id'));
            $zonaId = (!$esGuardia && !empty($slotData['zona_id'])) ? $slotData['zona_id'] : ($esGuardia ? null : $request->input('zona_id'));
            
            $tarea = !empty($slotData['tarea']) ? trim($slotData['tarea']) : ($request->input('tarea') ?: ($esGuardia ? 'Ausencia durante hora de guardia asignada.' : 'Sin tarea especificada.'));
            $enlaceTarea = !empty($slotData['enlace_tarea']) ? trim($slotData['enlace_tarea']) : $request->input('enlace_tarea');

            Ausencia::create([
                'user_id' => $targetUserId,
                'fecha' => $request->fecha,
                'time_slot_id' => $slotId,
                'group_id' => $groupId ?: null,
                'zona_id' => $zonaId ?: null,
                'tarea' => $tarea,
                'enlace_tarea' => $enlaceTarea ?: null,
                'es_guardia' => $esGuardia,
                'justificada' => false,
            ]);
        }

        if ($request->input('redirect_to') === 'parte') {
            return redirect()->route('guardias.parte', ['date' => $request->fecha])
                ->with('success', 'Ausencia apuntada en el parte correctamente.');
        }

        return redirect()->route('ausencias.index', ['date' => $request->fecha])
            ->with('success', 'Ausencia(s) registrada(s) correctamente.');
    }

    /**
     * Show form to edit an existing absence.
     */
    public function edit(Ausencia $ausencia)
    {
        $user = Auth::user();
        if (!$ausencia->canBeDeletedBy($user)) {
            return redirect()->route('ausencias.index')->with('error', 'No puedes editar esta ausencia porque ya ha comenzado.');
        }

        $activeSchoolYearId = session('active_school_year_id');
        $tramos = ScheduleTemplate::getActiveTimeSlots();
        $grupos = Group::when($activeSchoolYearId, fn($q) => $q->where('school_year_id', $activeSchoolYearId))
            ->orderBy('course')
            ->orderBy('name')
            ->get();
        $zonas = Zona::orderBy('nombre')->get();
        $isDirectiva = $user->hasRole('admin') || $user->hasRole('directiva');
        $rolesToCheck = \Spatie\Permission\Models\Role::whereIn('name', ['profesor', 'admin', 'directiva'])->pluck('name')->toArray();
        $docentes = ($isDirectiva && count($rolesToCheck) > 0) ? User::role($rolesToCheck)->orderBy('name')->get() : collect();

        return view('ausencias.edit', compact('ausencia', 'tramos', 'grupos', 'zonas', 'docentes', 'isDirectiva'));
    }

    /**
     * Update an existing absence.
     */
    public function update(Request $request, Ausencia $ausencia)
    {
        $user = Auth::user();
        if (!$ausencia->canBeDeletedBy($user)) {
            return redirect()->route('ausencias.index')->with('error', 'No puedes modificar esta ausencia.');
        }

        $isDirectiva = $user->hasRole('admin') || $user->hasRole('directiva');

        $request->validate([
            'fecha' => 'required|date',
            'time_slot_id' => 'required|exists:time_slots,id',
            'group_id' => 'nullable|exists:groups,id',
            'zona_id' => 'nullable|exists:aulas,id',
            'tarea' => 'required|string',
            'enlace_tarea' => 'nullable|url',
            'es_guardia' => 'nullable|boolean',
            'justificada' => 'nullable|boolean',
        ]);

        $esGuardia = $request->boolean('es_guardia');

        $data = [
            'fecha' => $request->fecha,
            'time_slot_id' => $request->time_slot_id,
            'group_id' => $esGuardia ? null : $request->group_id,
            'zona_id' => $esGuardia ? null : $request->zona_id,
            'tarea' => $request->tarea,
            'enlace_tarea' => $request->enlace_tarea,
            'es_guardia' => $esGuardia,
        ];

        if ($isDirectiva && $request->has('justificada')) {
            $data['justificada'] = $request->boolean('justificada');
        }

        $ausencia->update($data);

        return redirect()->route('ausencias.index', ['date' => $request->fecha])
            ->with('success', 'Ausencia actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ausencia $ausencia)
    {
        $user = Auth::user();

        if (!$ausencia->canBeDeletedBy($user)) {
            return back()->with('error', 'No puedes eliminar una ausencia que ya ha comenzado.');
        }

        $ausencia->delete();

        return back()->with('success', 'Ausencia eliminada correctamente.');
    }
}
