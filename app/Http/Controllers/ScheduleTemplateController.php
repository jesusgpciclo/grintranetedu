<?php

namespace App\Http\Controllers;

use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ScheduleTemplate::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Sort
        if ($request->has('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->input('sort_by');
            
            // Allow sorting by Valid columns only
            if (in_array($sortBy, ['name', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc'); // Default sort
        }

        $templates = $query->paginate($request->input('per_page', 25));
        return view('schedule_templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('schedule_templates.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active_days' => 'nullable|array',
            'slots' => 'nullable|array',
            'slots.*.name' => 'required|string|max:255',
            'slots.*.start_time' => 'required',
            'slots.*.end_time' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $template = ScheduleTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'active_days' => $request->active_days ?? [],
            ]);

            if ($request->has('slots')) {
                foreach ($request->slots as $index => $slotData) {
                    $template->timeSlots()->create([
                        'name' => $slotData['name'],
                        'start_time' => $slotData['start_time'],
                        'end_time' => $slotData['end_time'],
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'redirect' => route('schedule-templates.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $template = ScheduleTemplate::with('timeSlots')->findOrFail($id);
        return view('schedule_templates.form', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active_days' => 'nullable|array',
            'slots' => 'nullable|array',
            'slots.*.name' => 'required|string|max:255',
            'slots.*.start_time' => 'required',
            'slots.*.end_time' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $template = ScheduleTemplate::findOrFail($id);
            $template->update([
                'name' => $request->name,
                'description' => $request->description,
                'active_days' => $request->active_days ?? [],
            ]);

            // Sync slots: Delete all and recreate (easier for reordering) 
            // OR update existing. For simplicity and ordering, I'll delete and recreate 
            // but that changes IDs which might be bad if referenced. 
            // Better: Update existing ones where possible? 
            // For now, let's delete and recreate logic as it is a template editor.
            
            $template->timeSlots()->delete();

            if ($request->has('slots')) {
                foreach ($request->slots as $index => $slotData) {
                    $template->timeSlots()->create([
                        'name' => $slotData['name'],
                        'start_time' => $slotData['start_time'],
                        'end_time' => $slotData['end_time'],
                        'order' => $index,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'redirect' => route('schedule-templates.index')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $template = ScheduleTemplate::findOrFail($id);
        $template->delete();
        return redirect()->route('schedule-templates.index')->with('success', 'Template deleted successfully');
    }

    public function preview($id)
    {
        $template = ScheduleTemplate::with('timeSlots')->findOrFail($id);
        return view('schedule_templates.preview', compact('template'));
    }

    public function copy($id)
    {
        try {
            DB::beginTransaction();

            $template = ScheduleTemplate::with('timeSlots')->findOrFail($id);
            
            $newTemplate = $template->replicate();
            $newTemplate->name = 'COPIA ' . $template->name;
            $newTemplate->save();

            foreach ($template->timeSlots as $slot) {
                $newSlot = $slot->replicate();
                $newSlot->schedule_template_id = $newTemplate->id;
                $newSlot->save();
            }

            DB::commit();

            return redirect()->route('schedule-templates.index')->with('success', 'Plantilla copiada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('schedule-templates.index')->with('error', 'Error al copiar la plantilla: ' . $e->getMessage());
        }
    }
}
