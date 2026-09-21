<?php

namespace App\Http\Controllers;

use App\Services\PermissionManagerService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Sort
        if ($request->has('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->input('sort_by');
            
            if (in_array($sortBy, ['name', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('name', 'asc'); // Default
        }

        $roles = $query->paginate($request->input('per_page', 25));
        $totalPermissions = Permission::count();

        return view('roles.index', compact('roles', 'totalPermissions'));
    }

    public function create()
    {
        $catalog = PermissionManagerService::getCatalog();
        $permissions = Permission::all();
        return view('roles.create', compact('permissions', 'catalog'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        $catalog = PermissionManagerService::getCatalog();
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'catalog', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]); // Quitar todos si no se envía ninguno
        }

        // Si es rol admin, asegurar que mantiene todos los permisos
        if ($role->name === 'admin') {
            $role->syncPermissions(Permission::all());
        }

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
             return back()->withErrors(['error' => 'No puedes eliminar el rol Admin.']);
        }
        
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }

    /**
     * Muestra la Matriz Interactiva de Permisos para todos los roles.
     */
    public function matrix()
    {
        $roles = Role::with('permissions')->orderBy('name', 'asc')->get();
        $catalog = PermissionManagerService::getCatalog();
        $allPermissions = Permission::all()->keyBy('name');

        return view('roles.matrix', compact('roles', 'catalog', 'allPermissions'));
    }

    /**
     * Guarda la asignación masiva de permisos de todos los roles desde la matriz.
     */
    public function updateMatrix(Request $request)
    {
        $matrix = $request->input('matrix', []);
        $roles = Role::all();

        foreach ($roles as $role) {
            // Si el rol viene en la matriz, sincronizamos sus permisos
            $rolePermissions = $matrix[$role->id] ?? [];
            
            if ($role->name === 'admin') {
                // Admin siempre conserva todos los permisos
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($rolePermissions);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.matrix')->with('success', 'Matriz de permisos actualizada correctamente.');
    }

    /**
     * Conmuta un permiso individual para un rol específico mediante AJAX.
     */
    public function togglePermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|string',
        ]);

        $role = Role::findOrFail($request->role_id);
        $permissionName = $request->permission;

        // Asegurar que el permiso existe
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);

        if ($role->name === 'admin' && in_array($permissionName, ['roles.view', 'roles.edit', 'permissions.manage'])) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes revocar permisos administrativos clave al rol Admin.'
            ], 422);
        }

        $attached = false;
        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
            $attached = false;
            $msg = "Permiso '{$permissionName}' revocado para el rol " . ucfirst($role->name);
        } else {
            $role->givePermissionTo($permission);
            $attached = true;
            $msg = "Permiso '{$permissionName}' concedido al rol " . ucfirst($role->name);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'attached' => $attached,
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permission' => $permissionName,
            'message' => $msg
        ]);
    }
}
