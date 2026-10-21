<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\HallPassNotice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HallPassNoticeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role_or_permission:admin|salidas.notices'),
        ];
    }

    /**
     * Display a listing of the hall pass notices.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');
        $groupId = $request->input('group_id', '');

        $query = HallPassNotice::with(['student.groupRel', 'creator'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($groupId !== '') {
            $query->whereHas('student', function ($sq) use ($groupId) {
                $sq->where('group_id', $groupId);
            });
        }

        $notices = $query->paginate(15)->withQueryString();
        $classGroups = Group::orderBy('course')->orderBy('name')->get();

        return view('salidas.notices.index', compact('notices', 'search', 'status', 'groupId', 'classGroups'));
    }

    /**
     * Show the form for creating a new notice.
     */
    public function create()
    {
        $students = $this->getSortedStudents();
        $classGroups = Group::orderBy('course')->orderBy('name')->get();

        return view('salidas.notices.form', [
            'notice' => new HallPassNotice(['is_active' => true]),
            'students' => $students,
            'classGroups' => $classGroups,
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created notice in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'is_active' => 'nullable',
        ], [
            'user_id.required' => 'Debes seleccionar un alumno.',
            'user_id.exists' => 'El alumno seleccionado no existe.',
            'message.required' => 'Debes escribir el texto del aviso de salida.',
            'message.max' => 'El mensaje no puede superar los 1000 caracteres.',
        ]);

        HallPassNotice::create([
            'user_id' => $validated['user_id'],
            'message' => trim($validated['message']),
            'is_active' => $request->has('is_active'),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('salidas.notices.index')
            ->with('success', 'Aviso de salida registrado correctamente.');
    }

    /**
     * Show the form for editing the specified notice.
     */
    public function edit(HallPassNotice $notice)
    {
        $students = $this->getSortedStudents();
        $classGroups = Group::orderBy('course')->orderBy('name')->get();

        return view('salidas.notices.form', [
            'notice' => $notice,
            'students' => $students,
            'classGroups' => $classGroups,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified notice in storage.
     */
    public function update(Request $request, HallPassNotice $notice)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'is_active' => 'nullable',
        ], [
            'user_id.required' => 'Debes seleccionar un alumno.',
            'user_id.exists' => 'El alumno seleccionado no existe.',
            'message.required' => 'Debes escribir el texto del aviso de salida.',
            'message.max' => 'El mensaje no puede superar los 1000 caracteres.',
        ]);

        $notice->update([
            'user_id' => $validated['user_id'],
            'message' => trim($validated['message']),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('salidas.notices.index')
            ->with('success', 'Aviso de salida actualizado correctamente.');
    }

    /**
     * Remove the specified notice from storage.
     */
    public function destroy(HallPassNotice $notice)
    {
        $notice->delete();

        return redirect()->route('salidas.notices.index')
            ->with('success', 'Aviso de salida eliminado correctamente.');
    }

    /**
     * Toggle active status of a notice.
     */
    public function toggle(HallPassNotice $notice)
    {
        $notice->update(['is_active' => !$notice->is_active]);

        $statusText = $notice->is_active ? 'activado' : 'desactivado';

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $notice->is_active,
                'message' => "Aviso {$statusText} correctamente."
            ]);
        }

        return redirect()->back()
            ->with('success', "Aviso {$statusText} correctamente.");
    }

    /**
     * Get sorted list of students.
     */
    private function getSortedStudents()
    {
        return User::role('alumno')
            ->with('groupRel')
            ->get()
            ->sort(function ($a, $b) {
                $aGroup = mb_strtolower(trim($a->groupRel?->name ?? ''));
                $bGroup = mb_strtolower(trim($b->groupRel?->name ?? ''));
                if ($aGroup !== $bGroup) {
                    return strcoll($aGroup, $bGroup);
                }

                $aLastName = mb_strtolower(trim($a->last_name ?? ''));
                $bLastName = mb_strtolower(trim($b->last_name ?? ''));
                $cmpLast = strcoll($aLastName, $bLastName);
                if ($cmpLast !== 0) {
                    return $cmpLast;
                }

                $aName = mb_strtolower(trim($a->name));
                $bName = mb_strtolower(trim($b->name));
                return strcoll($aName, $bName);
            })
            ->values();
    }
}
