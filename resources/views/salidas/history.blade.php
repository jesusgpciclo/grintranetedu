@extends('layouts.app')

@section('title', 'Historial de Pasillos')

@section('module_title')
Historial de <span class="text-blue-500">pasillos</span>
@endsection

@section('content')
    <style>
        /* Modern Modal Styles */
        #custom-modal-overlay {
            backdrop-filter: blur(8px);
            background-color: rgba(15, 23, 42, 0.6);
            transition: all 0.3s ease;
        }
        .modal-content {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        #custom-modal-overlay.active .modal-content {
            transform: scale(1);
            opacity: 1;
        }
        /* Modern Toast Styles */
        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .toast {
            min-width: 300px;
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            color: var(--text-color);
            padding: 1rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        .toast.hide {
            transform: translateX(100%);
            opacity: 0;
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 bg-[var(--bg-card)] p-5 rounded-2xl border border-[var(--border)] shadow-sm">
                <div class="flex items-center gap-4 sm:gap-6">
                    <a href="{{ route('salidas.index') }}" class="p-2 sm:p-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all group shrink-0" title="Volver a Pasillos">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-xl sm:text-3xl font-extrabold text-[var(--text-heading)] leading-none">Historial de Pasillos</h1>
                        <p class="text-[var(--text-muted)] text-xs sm:text-sm font-medium mt-1">Registro completo de movimientos y pases</p>
                    </div>
                </div>
            </div>

            <!-- Filters Card: Búsqueda, Fechas (una fecha o entre fechas) y Horas (entre horas) -->
            <div class="bg-[var(--bg-card)] p-4 sm:p-5 rounded-2xl border border-[var(--border)] shadow-sm mb-6">
                <form action="{{ route('salidas.history') }}" method="GET" id="history-filter-form" class="space-y-4">
                    @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                    @if(request('direction')) <input type="hidden" name="direction" value="{{ request('direction') }}"> @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 sm:gap-4 items-end">
                        <!-- Búsqueda rápida de texto -->
                        <div class="sm:col-span-2 lg:col-span-4">
                            <label for="search" class="block text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5">
                                Buscar
                            </label>
                            <div class="relative">
                                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Alumno, clase o motivo..."
                                    class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 pl-9 pr-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)] transition-all placeholder:text-[var(--text-muted)]">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Fecha Desde -->
                        <div class="lg:col-span-2">
                            <label for="date_from" class="block text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5">
                                Fecha Desde
                            </label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                                class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2 px-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-[var(--primary)] transition-all">
                        </div>

                        <!-- Fecha Hasta -->
                        <div class="lg:col-span-2">
                            <label for="date_to" class="block text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5">
                                Fecha Hasta <span class="text-[10px] lowercase text-[var(--text-muted)] font-normal">(opcional)</span>
                            </label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                                class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2 px-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-[var(--primary)] transition-all">
                        </div>

                        <!-- Hora Desde -->
                        <div class="lg:col-span-2">
                            <label for="time_from" class="block text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5">
                                Hora Desde
                            </label>
                            <input type="time" id="time_from" name="time_from" value="{{ request('time_from') }}"
                                class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2 px-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-[var(--primary)] transition-all">
                        </div>

                        <!-- Hora Hasta -->
                        <div class="lg:col-span-2">
                            <label for="time_to" class="block text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5">
                                Hora Hasta
                            </label>
                            <input type="time" id="time_to" name="time_to" value="{{ request('time_to') }}"
                                class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2 px-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-[var(--primary)] transition-all">
                        </div>
                    </div>

                    <!-- Botones de Acción de Filtros -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-[var(--border)]">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="submit"
                                class="px-4 py-2 bg-[var(--primary)] hover:bg-[var(--primary-hover,var(--primary))] text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                                <span>Filtrar</span>
                            </button>

                            <button type="button" onclick="filterToday()"
                                class="px-3.5 py-2 bg-[var(--bg-surface)] hover:bg-[var(--bg-hover)] text-[var(--text-heading)] border border-[var(--border)] rounded-xl text-xs sm:text-sm font-semibold transition-all">
                                📅 Solo Hoy
                            </button>

                            @if(request()->hasAny(['search', 'date_from', 'date_to', 'time_from', 'time_to']))
                            <a href="{{ route('salidas.history') }}"
                                class="px-3 py-2 text-rose-500 hover:text-rose-600 bg-rose-500/10 hover:bg-rose-500/15 border border-rose-500/20 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Limpiar filtros</span>
                            </a>
                            @endif
                        </div>

                        <div class="text-xs text-[var(--text-muted)] font-medium">
                            @if(request()->hasAny(['search', 'date_from', 'date_to', 'time_from', 'time_to']))
                                <span>Filtros activos: <strong>{{ $passes->total() }}</strong> salidas encontradas</span>
                            @else
                                <span>Total registros: <strong>{{ $passes->total() }}</strong></span>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Action Buttons Bar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 mb-6 bg-[var(--bg-card)] p-4 rounded-2xl border border-[var(--border)] shadow-sm">
                <!-- Export actions -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('salidas.history.export-csv', request()->all()) }}" 
                       class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 rounded-xl font-bold text-sm transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Descargar Excel
                    </a>
                    
                    <a href="{{ route('salidas.history.print', request()->all()) }}" target="_blank"
                       class="flex items-center gap-2 px-4 py-2.5 bg-sky-500/10 hover:bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-500/30 rounded-xl font-bold text-sm transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Descargar PDF
                    </a>
                </div>

                <!-- Danger / Delete actions -->
                <div class="flex items-center gap-2">
                    <button id="btn-delete-selected" onclick="confirmDeleteSelected()" 
                            class="hidden items-center justify-center gap-2 px-4 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-sm transition-all shadow-md shadow-rose-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar seleccionadas (<span id="selected-count">0</span>)
                    </button>

                    <button onclick="confirmClearHistory()" 
                            class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 rounded-xl font-bold text-sm transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar todo el historial
                    </button>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="card p-0 overflow-hidden shadow-sm">
                @if($passes->isEmpty())
                    <div class="p-8 sm:p-12 text-center text-[var(--text-muted)]">
                        <svg class="w-12 h-12 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-base font-bold text-[var(--text-heading)]">No se han encontrado registros</h3>
                        <p class="text-xs text-[var(--text-muted)] mt-1">Prueba a cambiar o limpiar los filtros seleccionados.</p>
                    </div>
                @else
                    <!-- Desktop & Tablet Table List -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-[var(--border)]">
                            <thead class="bg-[var(--bg-hover)]">
                                <tr>
                                    <th scope="col" class="px-4 py-4 text-center w-10 border-r border-[var(--border)]">
                                        <input type="checkbox" id="select-all-passes" onchange="toggleSelectAll(this)" 
                                               class="w-4 h-4 rounded border-[var(--border)] text-rose-600 focus:ring-rose-500 cursor-pointer" 
                                               title="Seleccionar todas las salidas de la página">
                                    </th>
                                    @foreach([
                                        'Fecha' => 'fecha',
                                        'Alumno' => 'alumno',
                                        'Clase' => 'clase',
                                        'Motivo' => 'motivo',
                                        'Duración' => 'duracion',
                                        'Profesor' => 'profesor'
                                    ] as $label => $column)
                                        @php
                                            $currentSort = request('sort', 'fecha');
                                            $currentDir = request('direction', 'desc');
                                            $isActive = $currentSort === $column;
                                            $nextDir = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';
                                            $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
                                        @endphp
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider cursor-pointer hover:bg-[var(--bg-active)] hover:text-[var(--primary)] transition-all group/head border-r border-[var(--border)]">
                                            <a href="{{ $url }}" class="flex items-center justify-between gap-2">
                                                <span>{{ $label }}</span>
                                                <div class="flex flex-col opacity-50 group-hover/head:opacity-100 transition-opacity bg-[var(--bg-input)] p-1 rounded-md">
                                                    @if($isActive)
                                                        <svg class="w-3.5 h-3.5 text-[var(--primary)] {{ $currentDir === 'asc' ? '' : 'rotate-180' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                                        </svg>
                                                    @endif
                                                </div>
                                            </a>
                                        </th>
                                    @endforeach
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider w-24">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                @foreach($passes as $pass)
                                    <tr id="pass-row-{{ $pass->id }}" class="hover:bg-[var(--bg-hover)] transition-colors group">
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <input type="checkbox" class="pass-checkbox w-4 h-4 rounded border-[var(--border)] text-rose-600 focus:ring-rose-500 cursor-pointer" 
                                                   value="{{ $pass->id }}" onchange="updateSelectedCount()">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="font-bold text-[var(--text-heading)]">{{ $pass->date->format('d/m/Y') }}</div>
                                            <div class="text-[10px] text-[var(--text-muted)]">{{ $pass->start_time->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs border border-sky-500/20">
                                                    {{ strtoupper(substr($pass->student?->last_name ?? $pass->student?->name ?? 'A', 0, 1)) }}
                                                </div>
                                                <span class="text-sm font-semibold text-[var(--text-heading)] group-hover:text-[var(--primary)] transition-colors">
                                                    {{ $pass->student?->last_name ? trim($pass->student->last_name . ', ' . $pass->student->name) : ($pass->student?->name ?? 'Alumno') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-lg bg-[var(--bg-hover)] text-[var(--text-muted)] text-xs font-bold border border-[var(--border)]">
                                                {{ $pass->student?->groupRel?->course ?? '' }} {{ $pass->student?->groupRel?->name ?? '' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-color)]">
                                            {{ $pass->reason }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($pass->end_time)
                                                <span class="text-[var(--text-muted)] font-medium">
                                                    {{ $pass->duration_formatted }}
                                                </span>
                                            @else
                                                <span class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-black animate-pulse">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                    Activo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-muted)] italic">
                                            {{ $pass->teacher_full_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                            <button onclick="confirmDeletePass({{ $pass->id }}, '{{ addslashes(($pass->student?->name ?? 'Alumno') . ' ' . ($pass->student?->last_name ?? '')) }}')"
                                                    class="inline-flex items-center justify-center p-2 text-rose-500 hover:text-white hover:bg-rose-500 rounded-xl transition-all border border-transparent hover:border-rose-600 shadow-sm group/btn"
                                                    title="Eliminar esta salida">
                                                <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards List (Optimized for mobile screens - full student info) -->
                    <div class="md:hidden divide-y divide-[var(--border)]">
                        @foreach($passes as $pass)
                            <div id="pass-card-{{ $pass->id }}" class="p-4 hover:bg-[var(--bg-hover)] transition-colors">
                                <!-- Fila 1: Checkbox + Avatar + Nombre Alumno + Botón Eliminar -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <input type="checkbox" class="pass-checkbox w-5 h-5 rounded border-[var(--border)] text-rose-600 focus:ring-rose-500 cursor-pointer shrink-0" 
                                               value="{{ $pass->id }}" onchange="updateSelectedCount()">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-xs">
                                            {{ strtoupper(substr($pass->student?->last_name ?? $pass->student?->name ?? 'A', 0, 1)) }}{{ strtoupper(substr($pass->student?->name ?? '', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-base font-black text-[var(--text-heading)] leading-snug truncate">
                                                {{ $pass->student?->last_name ? trim($pass->student->last_name . ', ' . $pass->student->name) : ($pass->student?->name ?? 'Alumno') }}
                                            </h3>
                                            <div class="flex items-center gap-1.5 text-xs text-[var(--text-muted)] mt-0.5">
                                                <span class="font-bold text-[var(--text-heading)]">
                                                    {{ $pass->student?->groupRel?->course ?? '' }} {{ $pass->student?->groupRel?->name ?? '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <button onclick="confirmDeletePass({{ $pass->id }}, '{{ addslashes(($pass->student?->name ?? 'Alumno') . ' ' . ($pass->student?->last_name ?? '')) }}')"
                                            class="p-2 text-rose-500 hover:text-white hover:bg-rose-500 rounded-xl transition-all shrink-0 active:scale-95 border border-transparent hover:border-rose-600"
                                            title="Eliminar esta salida" aria-label="Eliminar salida">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Fila 2: Badges de Motivo y Duración -->
                                <div class="flex flex-wrap items-center gap-2 mt-3 pl-8">
                                    @php
                                        $reasonLower = strtolower($pass->reason);
                                        $reasonBadgeClass = match(true) {
                                            str_contains($reasonLower, 'baño') || str_contains($reasonLower, 'aseo') || str_contains($reasonLower, 'servicio') => 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border-blue-500/30',
                                            str_contains($reasonLower, 'agua') => 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border-cyan-500/30',
                                            str_contains($reasonLower, 'enferm') => 'bg-orange-500/15 text-orange-600 dark:text-orange-400 border-orange-500/30',
                                            default => 'bg-purple-500/15 text-purple-600 dark:text-purple-400 border-purple-500/30',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $reasonBadgeClass }}">
                                        {{ $pass->reason }}
                                    </span>

                                    @if($pass->end_time)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-[var(--bg-hover)] text-[var(--text-heading)] border border-[var(--border)]">
                                            <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ $pass->duration_formatted }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 animate-pulse">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            <span>Activo</span>
                                        </span>
                                    @endif
                                </div>

                                <!-- Fila 3: Detalles completos (Fecha, Horas y Profesor) en grid adaptado a móvil -->
                                <div class="grid grid-cols-2 gap-2 mt-3 pt-2.5 border-t border-[var(--border)] text-xs pl-8">
                                    <div>
                                        <span class="font-bold text-[var(--text-heading)] block">
                                            📅 {{ $pass->date->format('d/m/Y') }}
                                        </span>
                                        <span class="text-[11px] font-medium text-[var(--text-muted)] block mt-0.5">
                                            ⏰ {{ $pass->start_time ? $pass->start_time->format('H:i') : '--:--' }} &rarr; {{ $pass->end_time ? $pass->end_time->format('H:i') : 'Activo' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-[var(--text-muted)] block">Profesor:</span>
                                        <span class="font-bold text-[var(--text-heading)] truncate block mt-0.5">
                                            👨‍🏫 {{ $pass->teacher_full_name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="p-4 sm:p-6 border-t border-[var(--border)] pagination-custom">
                    {{ $passes->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Modal -->
    <div id="custom-modal-overlay" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="modal-content w-full max-w-md bg-[var(--bg-card)] border border-[var(--border)] rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-6">
                <div id="modal-icon-container" class="mb-4 flex justify-center"></div>
                <h3 id="modal-title" class="text-xl font-bold text-[var(--text-heading)] text-center mb-2"></h3>
                <p id="modal-description" class="text-[var(--text-muted)] text-center text-sm mb-6"></p>
                
                <div class="flex gap-3 mt-4">
                    <button id="modal-cancel" class="flex-1 py-3 bg-[var(--bg-hover)] hover:bg-[var(--border)] text-[var(--text-color)] font-bold rounded-xl transition-all border border-[var(--border)]">
                        Cancelar
                    </button>
                    <button id="modal-confirm" class="flex-2 py-3 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/30 transition-all">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <script>
        const csrfToken = '{{ csrf_token() }}';

        // Premium Modal System
        const modalOverlay = document.getElementById('custom-modal-overlay');
        const modalTitle = document.getElementById('modal-title');
        const modalDesc = document.getElementById('modal-description');
        const modalConfirm = document.getElementById('modal-confirm');
        const modalCancel = document.getElementById('modal-cancel');
        const modalIconContainer = document.getElementById('modal-icon-container');

        window.customModal = function({ title, description, confirmText = 'Confirmar', type = 'info' }) {
            return new Promise((resolve) => {
                modalTitle.textContent = title;
                modalDesc.textContent = description;
                modalConfirm.textContent = confirmText;
                
                // Icon based on type
                let iconHtml = '';
                if (type === 'question') iconHtml = '<div class="p-4 bg-sky-500/20 text-sky-500 rounded-full"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>';
                if (type === 'warning') iconHtml = '<div class="p-4 bg-rose-500/20 text-rose-500 rounded-full"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>';
                modalIconContainer.innerHTML = iconHtml;

                modalOverlay.classList.remove('hidden');
                modalOverlay.classList.add('flex');
                setTimeout(() => modalOverlay.classList.add('active'), 10);

                const close = (result) => {
                    modalOverlay.classList.remove('active');
                    setTimeout(() => {
                        modalOverlay.classList.add('hidden');
                        modalOverlay.classList.remove('flex');
                        resolve(result);
                    }, 300);
                };

                modalConfirm.onclick = () => close(true);
                modalCancel.onclick = () => close(null);
                modalOverlay.onclick = (e) => { if(e.target === modalOverlay) close(null); };
            });
        };

        // Premium Toast System
        window.showToast = function (message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast border-l-4 ${type === 'success' ? 'border-emerald-500' : (type === 'error' ? 'border-rose-500' : 'border-sky-500')}`;

            let icon = '';
            if (type === 'success') icon = '<div class="p-2 bg-emerald-500/20 rounded-lg text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>';
            if (type === 'error') icon = '<div class="p-2 bg-rose-500/20 rounded-lg text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>';

            toast.innerHTML = `
                ${icon}
                <div class="flex-1">
                    <p class="text-sm font-bold text-[var(--text-heading)]">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                    <p class="text-xs text-[var(--text-muted)]">${message}</p>
                </div>
            `;

            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);

            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        };

        function toggleSelectAll(master) {
            const checkboxes = document.querySelectorAll('.pass-checkbox');
            checkboxes.forEach(cb => cb.checked = master.checked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.pass-checkbox:checked');
            const uniqueIds = new Set(Array.from(checkedBoxes).map(cb => cb.value));
            const count = uniqueIds.size;
            const btn = document.getElementById('btn-delete-selected');
            const countEl = document.getElementById('selected-count');
            const master = document.getElementById('select-all-passes');

            if (countEl) countEl.textContent = count;

            if (count > 0) {
                btn.classList.remove('hidden');
                btn.classList.add('flex');
            } else {
                btn.classList.add('hidden');
                btn.classList.remove('flex');
                if (master) master.checked = false;
            }
        }

        function filterToday() {
            const today = new Date().toISOString().split('T')[0];
            const df = document.getElementById('date_from');
            const dt = document.getElementById('date_to');
            if (df) df.value = today;
            if (dt) dt.value = '';
            document.getElementById('history-filter-form').submit();
        }

        async function confirmDeletePass(id, studentName) {
            const confirmed = await customModal({
                title: '¿Eliminar salida?',
                description: `¿Estás seguro de que deseas eliminar permanentemente la salida de ${studentName}? Esta acción no se puede deshacer.`,
                confirmText: 'Sí, eliminar',
                type: 'warning'
            });

            if (!confirmed) return;

            try {
                const res = await fetch(`/salidas/history/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                if (!res.ok) {
                    const errData = await res.json().catch(() => ({}));
                    throw new Error(errData.error || 'Error al eliminar la salida.');
                }

                const data = await res.json();
                showToast(data.message || 'Salida eliminada correctamente.', 'success');

                const row = document.getElementById(`pass-row-${id}`);
                const card = document.getElementById(`pass-card-${id}`);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        row.remove();
                        updateSelectedCount();
                    }, 300);
                }
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        card.remove();
                        updateSelectedCount();
                    }, 300);
                }
                if (!row && !card) {
                    setTimeout(() => window.location.reload(), 800);
                }
            } catch (e) {
                showToast(e.message, 'error');
            }
        }

        async function confirmDeleteSelected() {
            const checkedBoxes = document.querySelectorAll('.pass-checkbox:checked');
            const checked = Array.from(new Set(Array.from(checkedBoxes).map(cb => cb.value)));
            if (checked.length === 0) return;

            const confirmed = await customModal({
                title: '¿Eliminar salidas seleccionadas?',
                description: `¿Estás seguro de que deseas eliminar permanentemente las ${checked.length} salidas seleccionadas? Esta acción no se puede deshacer.`,
                confirmText: `Sí, eliminar (${checked.length})`,
                type: 'warning'
            });

            if (!confirmed) return;

            try {
                const res = await fetch('{{ route("salidas.history.bulk-delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: checked })
                });

                if (!res.ok) {
                    const errData = await res.json().catch(() => ({}));
                    throw new Error(errData.error || 'Error al eliminar las salidas seleccionadas.');
                }

                const data = await res.json();
                showToast(data.message || 'Salidas eliminadas correctamente.', 'success');

                checked.forEach(id => {
                    const row = document.getElementById(`pass-row-${id}`);
                    const card = document.getElementById(`pass-card-${id}`);
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'scale(0.95)';
                        setTimeout(() => row.remove(), 300);
                    }
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => card.remove(), 300);
                    }
                });

                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } catch (e) {
                showToast(e.message, 'error');
            }
        }

        async function confirmClearHistory() {
            const confirmed = await customModal({
                title: '¿Eliminar todo el historial?',
                description: '¿Estás seguro de que deseas eliminar permanentemente TODO el historial de pasillos de alumnos? Esta acción no se puede deshacer.',
                confirmText: 'Sí, eliminar permanentemente',
                type: 'warning'
            });

            if (!confirmed) return;

            try {
                const res = await fetch('{{ route("salidas.history.clear") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                if (!res.ok) throw new Error('Error al vaciar el historial.');
                const data = await res.json();
                
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        }
    </script>
@endsection