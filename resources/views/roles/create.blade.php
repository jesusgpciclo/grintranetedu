@extends('layouts.app')

@section('title', 'Crear Nuevo Rol')

@section('content')
<div class="space-y-6 max-w-5xl">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-1">
                <span>Administración</span>
                <span>/</span>
                <a href="{{ route('roles.index') }}" class="hover:text-sky-600 dark:hover:text-sky-400">Roles</a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-200 font-semibold">Nuevo Rol</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                <span>✨</span> Crear Nuevo Rol y Configurar Permisos
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Define un nuevo rol de usuario y asígnale los permisos necesarios organizados por módulo.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('roles.matrix') }}" class="btn inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
                <span>🛡️</span> Matriz General
            </a>
            <a href="{{ route('roles.index') }}" class="btn inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
                ← Volver
            </a>
        </div>
    </div>

    <form action="{{ route('roles.store') }}" method="POST" id="createRoleForm" class="space-y-6">
        @csrf

        <!-- Basic Data Card -->
        <div class="card p-5">
            <div class="form-group mb-0 max-w-md">
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Nombre del Rol *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Ej: coordinador, orientador, pas..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-500/40 focus:outline-hidden">
                @error('name') 
                    <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        <!-- Global Select/Deselect Bar -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200 uppercase tracking-wider">
                    Permisos Seleccionados:
                </span>
                <span id="selectedCountBadge" class="px-2.5 py-0.5 rounded-full text-xs font-black bg-sky-500/15 text-sky-600 dark:text-sky-400">
                    <span id="selectedCount">0</span> de <span id="totalCount">0</span>
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleAllGlobal(true)" class="px-3 py-1.5 rounded-lg text-xs font-bold text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/40 border border-sky-200 dark:border-sky-800/80 transition">
                    ✓ Marcar Todos
                </button>
                <button type="button" onclick="toggleAllGlobal(false)" class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700 transition">
                    ✕ Desmarcar Todos
                </button>
            </div>
        </div>

        <!-- Modular Permissions Cards -->
        <div class="space-y-4">
            @foreach($catalog as $moduleKey => $module)
                <div class="card p-5 module-card" data-module-key="{{ $moduleKey }}">
                    <!-- Module Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">{{ $module['icon'] }}</span>
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-900 dark:text-white uppercase tracking-wider">
                                    {{ $module['title'] }}
                                </h3>
                                <p class="text-xs text-slate-400 dark:text-slate-500">
                                    {{ $module['description'] }}
                                </p>
                            </div>
                        </div>

                        <!-- Module actions -->
                        <div class="flex items-center gap-2 self-start sm:self-auto">
                            <span class="text-[11px] font-semibold text-slate-400 module-counter" data-module="{{ $moduleKey }}">
                                0 / {{ count($module['permissions']) }}
                            </span>
                            <button type="button" onclick="toggleModuleGroup('{{ $moduleKey }}', true)" class="px-2 py-0.5 rounded text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/30 border border-sky-200 dark:border-sky-800/60">
                                Todos
                            </button>
                            <button type="button" onclick="toggleModuleGroup('{{ $moduleKey }}', false)" class="px-2 py-0.5 rounded text-[11px] font-bold text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                Ninguno
                            </button>
                        </div>
                    </div>

                    <!-- Permission Items Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($module['permissions'] as $permKey => $perm)
                            @php
                                $oldPerms = old('permissions', []);
                                $isChecked = in_array($permKey, $oldPerms);
                            @endphp
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-sky-300 dark:hover:border-sky-700/60 hover:bg-slate-50/50 dark:hover:bg-slate-800/40 cursor-pointer transition select-none">
                                <input type="checkbox" 
                                    name="permissions[]" 
                                    value="{{ $permKey }}"
                                    data-module="{{ $moduleKey }}"
                                    class="perm-item-checkbox mt-1 w-4 h-4 text-sky-600 rounded border-slate-300 dark:border-slate-700 focus:ring-sky-500 dark:focus:ring-sky-600 dark:bg-slate-800"
                                    {{ $isChecked ? 'checked' : '' }}
                                    onchange="updateCounters()">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">
                                            {{ $perm['label'] }}
                                        </span>
                                        <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 shrink-0">
                                            {{ $permKey }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 leading-snug">
                                        {{ $perm['description'] }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Sticky Floating Footer -->
        <div class="sticky bottom-4 z-20 p-4 rounded-2xl bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-200 dark:border-slate-800 shadow-lg flex items-center justify-between gap-4">
            <a href="{{ route('roles.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-xs shadow-md shadow-sky-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Guardar Nuevo Rol</span>
            </button>
        </div>
    </form>
</div>

<script>
    function updateCounters() {
        const allCheckboxes = document.querySelectorAll('.perm-item-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.perm-item-checkbox:checked');

        const selectedCount = document.getElementById('selectedCount');
        const totalCount = document.getElementById('totalCount');
        if (selectedCount && totalCount) {
            selectedCount.textContent = checkedCheckboxes.length;
            totalCount.textContent = allCheckboxes.length;
        }

        // Update module-level counters
        document.querySelectorAll('.module-counter').forEach(counter => {
            const modKey = counter.getAttribute('data-module');
            const modTotal = document.querySelectorAll(`.perm-item-checkbox[data-module="${modKey}"]`).length;
            const modChecked = document.querySelectorAll(`.perm-item-checkbox[data-module="${modKey}"]:checked`).length;
            counter.textContent = `${modChecked} / ${modTotal}`;
        });
    }

    function toggleModuleGroup(modKey, targetChecked) {
        document.querySelectorAll(`.perm-item-checkbox[data-module="${modKey}"]`).forEach(cb => {
            cb.checked = targetChecked;
        });
        updateCounters();
    }

    function toggleAllGlobal(targetChecked) {
        document.querySelectorAll('.perm-item-checkbox').forEach(cb => {
            cb.checked = targetChecked;
        });
        updateCounters();
    }

    document.addEventListener('DOMContentLoaded', updateCounters);
</script>
@endsection
