@extends('layouts.app')

@section('title', 'Gestión de Roles')

@section('content')
<div class="space-y-6">

    <!-- Header & Navigation Tabs -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <span>Administración</span>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-200 font-semibold">Roles</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <span>👥</span> Roles y Permisos
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Administra los perfiles de acceso del sistema y configura sus permisos por módulo.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Tabs -->
            <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700/80">
                <span class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-white dark:bg-slate-900 text-sky-600 dark:text-sky-400 shadow-sm border border-slate-200/60 dark:border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Roles</span>
                </span>
                <a href="{{ route('roles.matrix') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">
                    <span>🛡️</span>
                    <span>Matriz de Permisos</span>
                </a>
            </div>

            <!-- Actions -->
            <a href="{{ route('roles.matrix') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 text-sky-700 dark:text-sky-300 font-bold text-xs hover:bg-sky-100 dark:hover:bg-sky-900/50 transition">
                <span>🛡️</span>
                <span>Gestor de Permisos</span>
            </a>
            <a href="{{ route('roles.create') }}" class="btn btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs shadow-xs">
                <span>+</span>
                <span>Nuevo Rol</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-200">✕</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/80 text-rose-800 dark:text-rose-300 text-xs font-semibold">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Information Callout -->
    <div class="p-4 rounded-2xl bg-sky-50/80 dark:bg-sky-950/30 border border-sky-200/80 dark:border-sky-800/60 text-sky-900 dark:text-sky-200 text-xs flex items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-2.5">
            <span class="text-base">💡</span>
            <div>
                <span class="font-extrabold uppercase tracking-wide">Control de Módulos y Menús:</span>
                Los permisos asignados a cada rol definen con exactitud qué módulos y menús (Guardias, Ausencias, Salidas, Centro, Cuaderno, TIC, Documentación, etc.) puede ver y utilizar cada usuario en la barra de navegación.
            </div>
        </div>
        <a href="{{ route('roles.matrix') }}" class="shrink-0 px-3 py-1.5 rounded-xl font-bold bg-sky-600 hover:bg-sky-500 text-white text-xs transition">
            Abrir Matriz
        </a>
    </div>

    <!-- Search Bar -->
    <div class="card" style="padding: 1.25rem;">
        <form action="{{ route('roles.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center;">
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
            
            <div style="flex: 1;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar rol por nombre..." style="width: 100%;">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.55rem 1.25rem;">Buscar</button>
            @if(request('search'))
                <a href="{{ route('roles.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="card table-container">
        <table class="smart-table">
            <thead>
                <tr>
                    <th><x-sort-header column="name" label="Nombre Rol" route="roles.index" /></th>
                    <th>Permisos Asignados</th>
                    <th>Usuarios Asignados</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                <tr>
                    <td style="font-weight: 700;">
                        <span class="badge badge-role" style="font-size: 0.85rem;">{{ ucfirst($role->name) }}</span>
                    </td>
                    <td>
                        @php
                            $permCount = $role->permissions->count();
                        @endphp
                        @if($role->name === 'admin' || $permCount >= ($totalPermissions ?? 60))
                            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; font-weight: 700; font-size: 0.75rem;">
                                ✓ Todos los permisos ({{ $permCount }})
                            </span>
                        @elseif($permCount > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center;">
                                <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #0284c7; font-weight: 700; font-size: 0.75rem;">
                                    {{ $permCount }} permisos activos
                                </span>
                                @foreach($role->permissions->take(4) as $permission)
                                    <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted); font-size: 0.7rem; font-family: monospace;">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                                @if($permCount > 4)
                                    <a href="{{ route('roles.edit', $role) }}" class="badge" style="background: rgba(255, 255, 255, 0.1); color: var(--text-heading); font-size: 0.7rem;" title="Ver todos los permisos de este rol">
                                        +{{ $permCount - 4 }} más
                                    </a>
                                @endif
                            </div>
                        @else
                            <span style="color: var(--text-muted); font-size: 0.8rem; font-style: italic;">Sin permisos asignados</span>
                        @endif
                    </td>
                    <td style="color: var(--text-muted); font-weight: 600;">
                        {{ $role->users()->count() }} usuarios
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <a href="{{ route('roles.edit', $role) }}" class="btn" style="background: rgba(255, 255, 255, 0.1); padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                Configurar Permisos
                            </a>
                            
                            @if($role->name !== 'admin')
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('¿Estás seguro? Esto quitará el rol a todos los usuarios que lo tengan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Eliminar</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 1.25rem;">
            {{ $roles->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
