@extends('layouts.app')

@section('title', 'Matriz de Permisos por Rol')

@section('content')
<div class="space-y-6">

    <!-- Header with Breadcrumbs & Action Tabs -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <span>Administración</span>
                <span>/</span>
                <a href="{{ route('roles.index') }}" class="hover:text-sky-600 dark:hover:text-sky-400">Roles</a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-200 font-semibold">Gestor de Permisos</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <span>🛡️</span> Matriz de Permisos por Rol
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Configura los permisos de acceso y acciones sobre cada CRUD y módulo del centro para cada uno de los roles.
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700/80 self-start md:self-auto">
            <a href="{{ route('roles.index') }}" class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Roles ({{ $roles->count() }})</span>
            </a>
            <span class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black bg-white dark:bg-slate-900 text-sky-600 dark:text-sky-400 shadow-sm border border-slate-200/60 dark:border-slate-700">
                <span>🛡️</span>
                <span>Matriz de Permisos</span>
            </span>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-200">✕</button>
        </div>
    @endif

    <!-- Control Toolbar & Realtime Filter -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1 max-w-md relative">
            <input type="text" id="permissionFilter" placeholder="Filtrar por módulo, nombre o código técnico (ej. 'guardias', 'alumnos', 'create')..." 
                class="w-full pl-10 pr-4 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-sky-500/40 transition">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 hidden lg:inline">
                💡 Los cambios con los interruptores se guardan automáticamente por AJAX.
            </span>
            <form action="{{ route('roles.matrix.update') }}" method="POST" id="matrixBulkForm" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-xs hover:shadow-md transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span>Guardar Todo en Lote</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Matrix Table Container -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="matrixTable">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 sticky top-0 z-10 backdrop-blur-md">
                        <th class="py-3.5 px-4 w-[340px] min-w-[280px]">
                            Módulo / Permiso
                        </th>
                        @foreach($roles as $role)
                            <th class="py-3.5 px-3 text-center min-w-[110px]">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase tracking-wider
                                        {{ $role->name === 'admin' ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20' : '' }}
                                        {{ in_array($role->name, ['directiva', 'director']) ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                                        {{ in_array($role->name, ['profesor', 'jefe-de-departamento']) ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20' : '' }}
                                        {{ !in_array($role->name, ['admin', 'directiva', 'director', 'profesor', 'jefe-de-departamento']) ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' : '' }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                    <span class="text-[10px] font-semibold text-slate-400 lowercase">
                                        <span id="role-count-{{ $role->id }}">{{ $role->permissions->count() }}</span> permisos
                                    </span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                    @foreach($catalog as $moduleKey => $module)
                        <!-- Module Section Header -->
                        <tr class="bg-slate-100/70 dark:bg-slate-800/90 border-t-2 border-slate-200 dark:border-slate-700/80 module-header-row" data-module-key="{{ $moduleKey }}">
                            <td class="py-3 px-4" colspan="1">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">{{ $module['icon'] }}</span>
                                    <div>
                                        <div class="font-extrabold text-slate-900 dark:text-white uppercase tracking-wider text-xs">
                                            {{ $module['title'] }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">
                                            {{ $module['description'] }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @foreach($roles as $role)
                                <td class="py-3 px-2 text-center" colspan="1">
                                    @if($role->name !== 'admin')
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" 
                                                onclick="toggleModuleForRole('{{ $moduleKey }}', {{ $role->id }}, true)"
                                                class="px-1.5 py-0.5 rounded text-[10px] font-bold text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/40 border border-sky-200 dark:border-sky-800/60 transition"
                                                title="Marcar todos los permisos de este módulo para {{ $role->name }}">
                                                ✓ Todos
                                            </button>
                                            <button type="button" 
                                                onclick="toggleModuleForRole('{{ $moduleKey }}', {{ $role->id }}, false)"
                                                class="px-1.5 py-0.5 rounded text-[10px] font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition"
                                                title="Desmarcar todos en este módulo">
                                                ✕
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-[10px] font-bold text-rose-500 uppercase tracking-wider">Total</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>

                        <!-- Permissions Rows -->
                        @foreach($module['permissions'] as $permKey => $perm)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors permission-row" 
                                data-module="{{ $moduleKey }}"
                                data-search="{{ strtolower($module['title'] . ' ' . $perm['label'] . ' ' . $permKey . ' ' . $perm['description']) }}">
                                
                                <td class="py-2.5 px-4">
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-800 dark:text-slate-100">
                                                {{ $perm['label'] }}
                                            </span>
                                            <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                {{ $permKey }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 leading-tight">
                                            {{ $perm['description'] }}
                                        </div>
                                    </div>
                                </td>

                                @foreach($roles as $role)
                                    @php
                                        $hasPerm = $role->hasPermissionTo($permKey);
                                        $isAdmin = ($role->name === 'admin');
                                    @endphp
                                    <td class="py-2.5 px-3 text-center">
                                        <label class="inline-flex items-center justify-center cursor-pointer select-none p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                            <input type="checkbox" 
                                                name="matrix[{{ $role->id }}][]" 
                                                value="{{ $permKey }}"
                                                form="matrixBulkForm"
                                                class="perm-checkbox w-4 h-4 text-sky-600 bg-slate-100 border-slate-300 rounded focus:ring-sky-500 dark:focus:ring-sky-600 dark:ring-offset-slate-900 focus:ring-2 dark:bg-slate-800 dark:border-slate-700 transition"
                                                data-role-id="{{ $role->id }}"
                                                data-role-name="{{ $role->name }}"
                                                data-permission="{{ $permKey }}"
                                                data-module="{{ $moduleKey }}"
                                                {{ $hasPerm || $isAdmin ? 'checked' : '' }}
                                                {{ $isAdmin ? 'disabled' : '' }}
                                                onchange="handlePermToggle(this)">
                                        </label>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Floating Toast Notification -->
<div id="toastNotification" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
    <div class="px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xl border border-slate-700 dark:border-slate-200 text-xs font-bold flex items-center gap-3">
        <span id="toastIcon">🛡️</span>
        <span id="toastMessage">Permiso actualizado</span>
    </div>
</div>

<script>
    // AJAX Permission Toggle
    async function handlePermToggle(checkbox) {
        const roleId = checkbox.getAttribute('data-role-id');
        const roleName = checkbox.getAttribute('data-role-name');
        const permission = checkbox.getAttribute('data-permission');
        const originalState = !checkbox.checked;

        // Feedback inmediato en el contador
        updateRoleCount(roleId, checkbox.checked ? 1 : -1);

        try {
            const response = await fetch("{{ route('roles.matrix.toggle') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    role_id: roleId,
                    permission: permission
                })
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.attached ? '✓' : '✕', data.message, data.attached ? 'text-emerald-400' : 'text-amber-400');
            } else {
                // Revertir
                checkbox.checked = originalState;
                updateRoleCount(roleId, originalState ? 1 : -1);
                showToast('⚠️', data.message || 'Error al modificar permiso', 'text-rose-400');
            }
        } catch (error) {
            checkbox.checked = originalState;
            updateRoleCount(roleId, originalState ? 1 : -1);
            showToast('⚠️', 'Error de conexión con el servidor', 'text-rose-400');
        }
    }

    // Toggle all in module for a role
    function toggleModuleForRole(moduleKey, roleId, targetChecked) {
        const checkboxes = document.querySelectorAll(`input.perm-checkbox[data-module="${moduleKey}"][data-role-id="${roleId}"]:not(:disabled)`);
        checkboxes.forEach(cb => {
            if (cb.checked !== targetChecked) {
                cb.checked = targetChecked;
                handlePermToggle(cb);
            }
        });
    }

    // Helper to update counter on role header
    function updateRoleCount(roleId, diff) {
        const el = document.getElementById(`role-count-${roleId}`);
        if (el) {
            const current = parseInt(el.textContent, 10) || 0;
            el.textContent = Math.max(0, current + diff);
        }
    }

    // Toast display
    let toastTimeout = null;
    function showToast(icon, message, iconClass = '') {
        const toast = document.getElementById('toastNotification');
        const toastIcon = document.getElementById('toastIcon');
        const toastMessage = document.getElementById('toastMessage');

        toastIcon.textContent = icon;
        toastMessage.textContent = message;

        toast.classList.remove('translate-y-20', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');

        if (toastTimeout) clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
            toast.classList.remove('translate-y-0', 'opacity-100');
        }, 2800);
    }

    // Realtime search filter
    document.getElementById('permissionFilter').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.permission-row');
        const headers = document.querySelectorAll('.module-header-row');

        if (term === '') {
            rows.forEach(r => r.style.display = '');
            headers.forEach(h => h.style.display = '');
            return;
        }

        const visibleModules = new Set();

        rows.forEach(r => {
            const searchData = r.getAttribute('data-search') || '';
            if (searchData.includes(term)) {
                r.style.display = '';
                visibleModules.add(r.getAttribute('data-module'));
            } else {
                r.style.display = 'none';
            }
        });

        headers.forEach(h => {
            const modKey = h.getAttribute('data-module-key');
            if (visibleModules.has(modKey)) {
                h.style.display = '';
            } else {
                h.style.display = 'none';
            }
        });
    });
</script>
@endsection
