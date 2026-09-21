<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'groupRel']);

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('departamento', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%");
            });
        }

        // Role Filter
        if ($request->has('role') && $request->role != '') {
            $query->role($request->role);
        }

        // Group Filter (Entity based)
        if ($request->has('group_id') && $request->group_id != '') {
            $query->where('group_id', $request->group_id);
        }

        // Sort
        if ($request->has('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->input('sort_by');
            
            // Allow sorting by Valid columns only
            if (in_array($sortBy, ['name', 'last_name', 'email', 'departamento', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc'); // Default sort
        }

        $users = $query->paginate($request->input('per_page', 25));
        $roles = Role::all();
        $groups = Group::all();
        return view('users.index', compact('users', 'roles', 'groups'));
    }

    public function create()
    {
        $roles = Role::all();
        $groups = Group::all();
        return view('users.create', compact('roles', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'group_id' => 'nullable|exists:groups,id',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $roles = $request->roles;
        $isAlumno = in_array('alumno', $roles);

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'departamento' => !$isAlumno ? $request->departamento : null,
            'observaciones' => $request->observaciones,
            'group_id' => $isAlumno ? $request->group_id : null,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($roles);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $groups = Group::all();
        return view('users.edit', compact('user', 'roles', 'groups'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'departamento' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'group_id' => 'nullable|exists:groups,id',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $roles = $request->roles;
        $isAlumno = in_array('alumno', $roles);

        $user->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'departamento' => !$isAlumno ? $request->departamento : null,
            'observaciones' => $request->observaciones,
            'group_id' => $isAlumno ? $request->group_id : null,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
