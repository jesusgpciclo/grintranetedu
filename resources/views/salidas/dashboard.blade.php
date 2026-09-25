@extends('layouts.app')

@section('title', 'Gestor de salidas - Dashboard')

@section('content')
    <style>
        /* Modern Modal Styles */
        #custom-modal-overlay, #mobile-reason-sheet-overlay {
            backdrop-filter: blur(8px);
            background-color: rgba(15, 23, 42, 0.7);
            transition: all 0.3s ease;
        }
        .modal-content {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            background-color: var(--bg-card-solid);
            border-color: var(--border);
            color: var(--text-color);
        }
        #custom-modal-overlay.active .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        /* Mobile Bottom Sheet Styles */
        .sheet-content {
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            background-color: var(--bg-card-solid);
            border-color: var(--border);
            color: var(--text-color);
        }
        #mobile-reason-sheet-overlay.active .sheet-content {
            transform: translateY(0);
        }

        @media (min-width: 640px) {
            .sheet-content {
                transform: scale(0.95) !important;
                opacity: 0;
                transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            }
            #mobile-reason-sheet-overlay.active .sheet-content {
                transform: scale(1) !important;
                opacity: 1 !important;
            }
        }

        /* Mobile list item styling */
        @media (max-width: 639px) {
            .student-card {
                padding: 0.45rem 0.65rem !important;
                border-radius: 0.85rem !important;
                gap: 0 !important;
            }
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
            background: var(--bg-card-solid);
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
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: currentColor;
            opacity: 0.3;
            width: 100%;
            border-radius: 0 0 0 1rem;
        }
    </style>

    <div class="py-2 sm:py-4">
        <div class="max-w-7xl mx-auto">

            <!-- Header, Statistics & Search -->
            <div class="card p-4 sm:p-5 mb-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
                    <!-- Title Section -->
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="flex p-2.5 sm:p-3 bg-blue-500/15 text-blue-500 rounded-xl">
                            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-3xl font-extrabold text-[var(--text-heading)] tracking-tight leading-none">
                                Gestor de <span class="text-blue-500">salidas</span>
                            </h1>
                            <p class="text-[var(--text-muted)] text-xs sm:text-sm font-medium mt-1">Control de pases al pasillo</p>
                        </div>
                    </div>

                    <!-- Actions & Stats Section -->
                    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 sm:gap-4">
                        <!-- Stats (Hidden on mobile, visible on desktop) -->
                        <div class="hidden sm:flex gap-2 sm:gap-3">
                            <div class="bg-blue-500/10 border border-blue-500/25 rounded-xl px-3 sm:px-4 py-2 flex-1 flex items-center justify-between sm:block">
                                <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest sm:block mb-0.5">Activos</span>
                                <span class="text-lg sm:text-xl font-black text-[var(--text-heading)] leading-none">{{ $stats['active_count'] }}</span>
                            </div>
                            <div class="bg-indigo-500/10 border border-indigo-500/25 rounded-xl px-3 sm:px-4 py-2 flex-1 flex items-center justify-between sm:block">
                                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest sm:block mb-0.5">Hoy</span>
                                <span class="text-lg sm:text-xl font-black text-[var(--text-heading)] leading-none">{{ $stats['today_count'] }}</span>
                            </div>
                        </div>

                        <!-- Search & Filters -->
                        <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-2">
                            <div class="relative flex-1 min-w-[180px]">
                                <input type="text" id="student-search" placeholder="Buscar alumno..."
                                    class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2 pl-9 pr-4 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-[var(--text-muted)]">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <div class="flex items-center gap-2">
                                <select id="class-selector"
                                    class="flex-1 bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2 px-3 text-sm text-[var(--text-color)] font-semibold focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer appearance-none min-w-[140px]">
                                    <option value="" selected>Seleccionar clase...</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">
                                            {{ $group->course }} {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="flex gap-1 shrink-0">
                                    <a href="{{ route('salidas.monitor') }}"
                                        class="p-2 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all"
                                        title="Monitor">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('salidas.history') }}"
                                        class="p-2 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all"
                                        title="Historial">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button id="return-all-btn" onclick="returnAll()"
                    class="hidden w-full mt-4 sm:mt-6 py-3.5 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white rounded-xl font-bold shadow-lg shadow-red-500/20 transform hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                    Regresar a todos los alumnos
                </button>
            </div>

            <!-- Student Grid / List -->
            <div id="student-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
                @foreach($students as $student)
                    @php
                        $activePass = $activePasses->firstWhere('user_id', $student->id);
                        $studentTodayPasses = $todayPassesByStudent->get($student->id, collect());
                        $todayCount = $studentTodayPasses->count();
                        $lastPass = $studentTodayPasses->first();
                        $studentFullName = $student->name . ' ' . ($student->last_name ?? '');
                    @endphp
                    <div class="student-card group relative rounded-2xl p-3 sm:p-5 shadow-sm hover:shadow-xl transition-all duration-300 border flex flex-col justify-between"
                        style="background: var(--bg-card); border-color: {{ $activePass ? 'rgba(245, 158, 11, 0.6)' : ($todayCount >= 3 ? 'rgba(239, 68, 68, 0.5)' : 'var(--border)') }};"
                        data-group-id="{{ $student->group_id }}" data-id="{{ $student->id }}"
                        data-student-name="{{ $studentFullName }}"
                        data-today-count="{{ $todayCount }}"
                        data-last-exit="{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}"
                        data-search="{{ strtolower($student->name . ' ' . $student->last_name) }}" style="display: none;">

                        <!-- Mobile View (sm:hidden): Ultra-compact Single Line Row matching user image -->
                        <div class="sm:hidden flex items-center justify-between gap-1.5 w-full">
                            <!-- Left: Avatar + Name + Course -->
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <div class="h-9 w-9 shrink-0 rounded-full bg-gradient-to-tr {{ $activePass ? 'from-amber-400 to-amber-600' : ($todayCount >= 3 ? 'from-amber-500 to-rose-500' : 'from-blue-400 to-blue-600') }} flex items-center justify-center text-white font-bold text-xs shadow-xs">
                                    {{ substr($student->name, 0, 1) }}{{ substr($student->last_name ?? '', 0, 1) }}
                                </div>
                                <div class="overflow-hidden min-w-0 flex-1">
                                    <h3 class="text-xs font-bold text-[var(--text-heading)] truncate leading-tight"
                                        title="{{ $studentFullName }}">
                                        {{ $student->name }} {{ $student->last_name }}
                                    </h3>
                                    <div class="flex items-center gap-1 text-[11px] text-[var(--text-muted)] leading-tight mt-0.5">
                                        <span class="truncate">{{ $student->groupRel?->course ?? '' }} {{ $student->groupRel?->name ?? '' }}</span>
                                        @if($todayCount > 0 && !$activePass)
                                            <span class="text-[10px] text-amber-500 font-bold shrink-0">({{ $todayCount }})</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Compact Inline Action Buttons -->
                            @if($activePass)
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <div class="px-2 py-0.5 bg-amber-500/15 border border-amber-500/30 rounded-lg text-center">
                                        <span class="text-[9px] font-bold text-amber-500 uppercase block leading-none">{{ $activePass->reason }}</span>
                                        <span class="text-[11px] font-mono font-bold text-amber-500 timer" data-start="{{ $activePass->start_time->timestamp }}">00:00</span>
                                    </div>
                                    <button onclick="endPass({{ $activePass->id }})" class="p-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-bold text-[11px] flex items-center gap-1 shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span>Regresar</span>
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <!-- Baño -->
                                    <button type="button" 
                                        onclick="createPass({{ $student->id }}, 'Baño', {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                                        title="Baño" aria-label="Baño"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-500/15 hover:bg-blue-500/25 active:scale-95 text-blue-600 dark:text-blue-400 border border-blue-500/30 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4zM7 8h10M7 12h10" />
                                        </svg>
                                    </button>

                                    <!-- Agua (Gota de agua) -->
                                    <button type="button" 
                                        onclick="createPass({{ $student->id }}, 'Agua', {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                                        title="Agua" aria-label="Agua"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg bg-cyan-500/15 hover:bg-cyan-500/25 active:scale-95 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
                                        </svg>
                                    </button>

                                    <!-- Enfermedad -->
                                    <button type="button" 
                                        onclick="createPass({{ $student->id }}, 'Enfermedad', {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                                        title="Enfermedad" aria-label="Enfermedad"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg bg-orange-500/15 hover:bg-orange-500/25 active:scale-95 text-orange-600 dark:text-orange-400 border border-orange-500/30 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3zM12 8v8m-4-4h8" />
                                        </svg>
                                    </button>

                                    <!-- Otro motivo -->
                                    <button type="button" 
                                        onclick="promptCustomReason({{ $student->id }}, {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                                        title="Otro motivo" aria-label="Otro motivo"
                                        class="flex items-center justify-center w-7 h-7 rounded-lg bg-slate-500/10 hover:bg-slate-500/20 active:scale-95 text-slate-700 dark:text-slate-300 border border-slate-400/30 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Desktop View (hidden sm:flex flex-col justify-between h-full) -->
                        <div class="hidden sm:flex flex-col justify-between h-full">
                            <!-- Card Header / List Info -->
                            <div class="flex items-center justify-between mb-2 sm:mb-3">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div
                                        class="h-10 w-10 shrink-0 rounded-full bg-gradient-to-tr {{ $activePass ? 'from-amber-400 to-amber-600' : ($todayCount >= 3 ? 'from-amber-500 to-rose-500' : 'from-blue-400 to-blue-600') }} flex items-center justify-center text-white font-bold text-sm shadow-md">
                                        {{ substr($student->name, 0, 1) }}{{ substr($student->last_name ?? '', 0, 1) }}
                                    </div>
                                    <div class="overflow-hidden flex-1">
                                        <h3 class="text-sm font-bold text-[var(--text-heading)] truncate"
                                            title="{{ $studentFullName }}">
                                            {{ $student->name }} {{ $student->last_name }}
                                        </h3>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-[var(--text-muted)] truncate block">
                                                {{ $student->groupRel?->course ?? '' }} {{ $student->groupRel?->name ?? '' }}
                                            </span>
                                            @if($todayCount > 0 && !$activePass)
                                                <span class="text-[10px] text-[var(--text-muted)] hidden xs:inline">• {{ $todayCount }} {{ $todayCount === 1 ? 'salida' : 'salidas' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                    @if($todayCount > 0)
                                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold {{ $todayCount >= 3 ? 'bg-rose-500/15 text-rose-500 border border-rose-500/30' : ($todayCount >= 2 ? 'bg-amber-500/15 text-amber-500 border border-amber-500/30' : 'bg-[var(--bg-hover)] text-[var(--text-muted)] border border-[var(--border)]') }}"
                                            title="Ha salido {{ $todayCount }} {{ $todayCount === 1 ? 'vez' : 'veces' }} hoy">
                                            {{ $todayCount }} {{ $todayCount === 1 ? 'salida' : 'salidas' }}
                                        </span>
                                    @endif
                                    @if($activePass)
                                        <span class="flex h-3 w-3 relative ml-1">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Daily Exit History Info Alert -->
                            @if($todayCount > 0 && !$activePass)
                                <div class="mb-2 sm:mb-3 px-2.5 py-1.5 rounded-lg bg-[var(--bg-input)] border border-[var(--border)] text-[11px] flex items-center justify-between gap-2 text-[var(--text-muted)]">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 {{ $todayCount >= 3 ? 'text-rose-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Última salida:</span>
                                    </span>
                                    <span class="font-semibold {{ $todayCount >= 3 ? 'text-rose-500 font-bold' : 'text-[var(--text-color)]' }}">
                                        {{ $lastPass->start_time ? $lastPass->start_time->format('H:i') : '--:--' }} ({{ $lastPass->reason }})
                                    </span>
                                </div>
                            @endif

                            <!-- Desktop Actions Area -->
                            <div class="mt-auto">
                                @if($activePass)
                                    <!-- Active State -->
                                    <div class="space-y-2 sm:space-y-3 pt-1">
                                        <div
                                            class="bg-amber-500/10 rounded-xl p-2.5 sm:p-3 border border-amber-500/30">
                                            <div class="flex justify-between items-center mb-1">
                                                <span
                                                    class="text-xs font-bold text-amber-500 uppercase tracking-wide">
                                                    {{ $activePass->reason }}
                                                </span>
                                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="text-xl sm:text-2xl font-mono font-bold text-amber-500 timer"
                                                data-start="{{ $activePass->start_time->timestamp }}">
                                                00:00
                                            </div>
                                        </div>
                                        <button onclick="endPass({{ $activePass->id }})"
                                            class="w-full py-2.5 sm:py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 transform active:scale-95 transition-all duration-200 flex items-center justify-center gap-2 text-sm sm:text-base">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Regresar alumno
                                        </button>
                                    </div>
                                @else
                                    <!-- Desktop Grid Actions -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <button onclick="createPass({{ $student->id }}, 'Baño', {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                                            class="group/btn p-3 bg-blue-500/10 hover:bg-blue-500/20 text-blue-500 rounded-xl border border-blue-500/30 transition-all duration-200 flex flex-col items-center gap-1">
                                            <svg class="w-6 h-6 opacity-80 group-hover/btn:scale-110 transition-transform" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4zM7 8h10M7 12h10">
                                                </path>
                                            </svg>
                                            <span class="text-xs font-semibold">Baño</span>
                                        </button>
                                        <button onclick="createPass({{ $student->id }}, 'Agua', {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                                            class="group/btn p-3 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-500 rounded-xl border border-cyan-500/30 transition-all duration-200 flex flex-col items-center gap-1">
                                            <svg class="w-6 h-6 opacity-80 group-hover/btn:scale-110 transition-transform" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                                            </svg>
                                            <span class="text-xs font-semibold">Agua</span>
                                        </button>
                                        <button onclick="createPass({{ $student->id }}, 'Enfermedad', {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                                            class="group/btn p-3 bg-orange-500/10 hover:bg-orange-500/20 text-orange-500 rounded-xl border border-orange-500/30 transition-all duration-200 flex flex-col items-center gap-1">
                                            <svg class="w-6 h-6 opacity-80 group-hover/btn:scale-110 transition-transform" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 3h18v18H3zM12 8v8m-4-4h8"></path>
                                            </svg>
                                            <span class="text-xs font-semibold">Enfermedad</span>
                                        </button>
                                        <button onclick="promptCustomReason({{ $student->id }}, {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                                            class="group/btn p-3 bg-[var(--bg-input)] hover:bg-[var(--bg-hover)] text-[var(--text-muted)] rounded-xl border border-[var(--border)] transition-all duration-200 flex flex-col items-center gap-1">
                                            <svg class="w-6 h-6 opacity-80 group-hover/btn:scale-110 transition-transform" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z">
                                                </path>
                                            </svg>
                                            <span class="text-xs font-semibold">Otro</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="empty-state" class="hidden flex flex-col items-center justify-center py-20 text-center">
                <div class="bg-[var(--bg-hover)] rounded-full p-6 mb-4 animate-pulse">
                    <svg class="w-16 h-16 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-[var(--text-heading)] mb-2">Selecciona una clase</h3>
                <p class="text-[var(--text-muted)] max-w-sm">Elige un grupo o busca un alumno para comenzar.</p>
            </div>
        </div>
    </div>

    <!-- Mobile Reason Picker Bottom Sheet / Modal -->
    <div id="mobile-reason-sheet-overlay" class="fixed inset-0 z-[100] hidden items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="sheet-content w-full sm:max-w-md border-t sm:border rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden p-5 sm:p-6">
            <!-- Sheet Handle for mobile -->
            <div class="w-12 h-1.5 bg-[var(--border)] rounded-full mx-auto mb-4 sm:hidden"></div>

            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 id="sheet-student-name" class="text-lg font-bold text-[var(--text-heading)] leading-tight"></h3>
                    <p id="sheet-exit-status" class="text-xs text-[var(--text-muted)] mt-0.5"></p>
                </div>
                <button onclick="closeMobileReasonSheet()" class="text-[var(--text-muted)] hover:text-[var(--text-heading)] p-2 rounded-xl bg-[var(--bg-hover)] border border-[var(--border)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="text-xs font-semibold text-[var(--text-muted)] uppercase tracking-wider mb-3">Selecciona motivo de salida:</p>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <button onclick="selectMobileReason('Baño')"
                    class="p-4 bg-blue-500/10 hover:bg-blue-500/20 active:scale-95 text-blue-500 rounded-2xl border border-blue-500/30 transition-all flex flex-col items-center justify-center gap-2">
                    <div class="p-2.5 bg-blue-500/20 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4zM7 8h10M7 12h10">
                            </path>
                        </svg>
                    </div>
                    <span class="text-sm font-bold">Baño</span>
                </button>

                <button onclick="selectMobileReason('Agua')"
                    class="p-4 bg-cyan-500/10 hover:bg-cyan-500/20 active:scale-95 text-cyan-500 rounded-2xl border border-cyan-500/30 transition-all flex flex-col items-center justify-center gap-2">
                    <div class="p-2.5 bg-cyan-500/20 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-bold">Agua</span>
                </button>

                <button onclick="selectMobileReason('Enfermedad')"
                    class="p-4 bg-orange-500/10 hover:bg-orange-500/20 active:scale-95 text-orange-500 rounded-2xl border border-orange-500/30 transition-all flex flex-col items-center justify-center gap-2">
                    <div class="p-2.5 bg-orange-500/20 rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h18v18H3zM12 8v8m-4-4h8"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-bold">Enfermedad</span>
                </button>

                <button onclick="selectMobileCustomReason()"
                    class="p-4 bg-[var(--bg-input)] hover:bg-[var(--bg-hover)] active:scale-95 text-[var(--text-color)] rounded-2xl border border-[var(--border)] transition-all flex flex-col items-center justify-center gap-2">
                    <div class="p-2.5 bg-[var(--bg-hover)] rounded-xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z">
                            </path>
                        </svg>
                    </div>
                    <span class="text-sm font-bold">Otro motivo</span>
                </button>
            </div>

            <button onclick="closeMobileReasonSheet()" class="w-full py-3 bg-[var(--bg-hover)] hover:bg-[var(--border)] text-[var(--text-muted)] hover:text-[var(--text-heading)] font-bold rounded-xl transition-all text-sm">
                Cancelar
            </button>
        </div>
    </div>

    <!-- Custom Modal -->
    <div id="custom-modal-overlay" class="fixed inset-0 z-[110] hidden items-center justify-center p-4">
        <div class="modal-content w-full max-w-md border rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-6">
                <div id="modal-icon-container" class="mb-4 flex justify-center"></div>
                <h3 id="modal-title" class="text-xl font-bold text-[var(--text-heading)] text-center mb-2"></h3>
                <p id="modal-description" class="text-[var(--text-muted)] text-center text-sm mb-6"></p>
                
                <div id="modal-input-container" class="hidden mb-6">
                    <input type="text" id="modal-input" 
                        class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-3 px-4 text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-[var(--text-muted)]"
                        placeholder="Escribe aquí...">
                </div>

                <div class="flex gap-3 mt-4">
                    <button id="modal-cancel" class="flex-1 py-3 bg-[var(--bg-hover)] hover:bg-[var(--border)] text-[var(--text-heading)] font-bold rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button id="modal-confirm" class="flex-2 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <script>
        // State for mobile reason sheet
        let currentSheetStudent = null;

        function openMobileReasonSheet(studentId, studentName, todayCount = 0, lastExit = '') {
            currentSheetStudent = {
                id: studentId,
                name: studentName,
                todayCount: todayCount,
                lastExit: lastExit
            };

            const sheetOverlay = document.getElementById('mobile-reason-sheet-overlay');
            const nameEl = document.getElementById('sheet-student-name');
            const statusEl = document.getElementById('sheet-exit-status');

            nameEl.textContent = studentName;
            if (todayCount > 0) {
                statusEl.textContent = `${todayCount} ${todayCount === 1 ? 'salida hoy' : 'salidas hoy'}${lastExit ? ' (última a las ' + lastExit + ')' : ''}`;
                statusEl.className = 'text-xs mt-0.5 ' + (todayCount >= 3 ? 'text-rose-500 font-bold' : (todayCount >= 2 ? 'text-amber-500 font-bold' : 'text-[var(--text-muted)]'));
            } else {
                statusEl.textContent = 'Sin salidas registradas hoy';
                statusEl.className = 'text-xs text-[var(--text-muted)] mt-0.5';
            }

            sheetOverlay.classList.remove('hidden');
            sheetOverlay.classList.add('flex');
            setTimeout(() => sheetOverlay.classList.add('active'), 10);
        }

        function closeMobileReasonSheet() {
            const sheetOverlay = document.getElementById('mobile-reason-sheet-overlay');
            sheetOverlay.classList.remove('active');
            setTimeout(() => {
                sheetOverlay.classList.add('hidden');
                sheetOverlay.classList.remove('flex');
                currentSheetStudent = null;
            }, 300);
        }

        function selectMobileReason(reason) {
            if (!currentSheetStudent) return;
            const { id, todayCount, lastExit } = currentSheetStudent;
            closeMobileReasonSheet();
            setTimeout(() => {
                createPass(id, reason, todayCount, lastExit);
            }, 150);
        }

        function selectMobileCustomReason() {
            if (!currentSheetStudent) return;
            const { id, todayCount, lastExit } = currentSheetStudent;
            closeMobileReasonSheet();
            setTimeout(() => {
                promptCustomReason(id, todayCount, lastExit);
            }, 150);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('student-search');
            const selector = document.getElementById('class-selector');
            const cards = document.querySelectorAll('.student-card');
            const emptyState = document.getElementById('empty-state');
            const returnAllBtn = document.getElementById('return-all-btn');

            // Close sheet on overlay backdrop click
            const sheetOverlay = document.getElementById('mobile-reason-sheet-overlay');
            if (sheetOverlay) {
                sheetOverlay.addEventListener('click', (e) => {
                    if (e.target === sheetOverlay) closeMobileReasonSheet();
                });
            }

            // Restore saved class from localStorage
            const savedClass = localStorage.getItem('selected_class');
            if (savedClass) {
                selector.value = savedClass;
            }

            function applyFilters() {
                const selectedGroup = selector.value;
                const searchTerm = searchInput.value?.toLowerCase().trim() || '';
                let counter = 0;

                cards.forEach(card => {
                    const matchesGroup = selectedGroup && String(card.dataset.groupId) === String(selectedGroup);
                    const matchesSearch = searchTerm && card.dataset.search.includes(searchTerm);

                    if (matchesGroup || (searchTerm && matchesSearch)) {
                        card.style.display = 'flex';
                        counter++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                // Empty state handling
                if (counter === 0 && (selectedGroup || searchTerm)) {
                    emptyState.innerHTML = `
                            <div class="bg-[var(--bg-hover)] rounded-full p-6 mb-4 animate-pulse">
                                <svg class="w-16 h-16 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-[var(--text-heading)] mb-2">No se han encontrado alumnos</h3>
                            <p class="text-[var(--text-muted)] max-w-sm">Prueba ajustando los filtros o el término de búsqueda.</p>
                        `;
                    emptyState.classList.remove('hidden');
                    returnAllBtn.classList.add('hidden');
                } else if (!selectedGroup && !searchTerm) {
                    emptyState.innerHTML = `
                            <div class="bg-[var(--bg-hover)] rounded-full p-6 mb-4 animate-pulse">
                                <svg class="w-16 h-16 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-[var(--text-heading)] mb-2">Selecciona una clase</h3>
                            <p class="text-[var(--text-muted)] max-w-sm">Elige un grupo o busca un alumno para comenzar.</p>
                        `;
                    emptyState.classList.remove('hidden');
                    returnAllBtn.classList.add('hidden');
                } else {
                    emptyState.classList.add('hidden');
                    const hasActiveInGroup = Array.from(cards).some(card =>
                        String(card.dataset.groupId) === String(selectedGroup) &&
                        card.querySelector('.timer')
                    );
                    if (selectedGroup && hasActiveInGroup) {
                        returnAllBtn.classList.remove('hidden');
                    } else {
                        returnAllBtn.classList.add('hidden');
                    }
                }
            }

            selector.addEventListener('change', () => {
                localStorage.setItem('selected_class', selector.value);
                applyFilters();
            });
            searchInput.addEventListener('input', applyFilters);

            // Initial call
            applyFilters();

            // Premium Modal System
            const modalOverlay = document.getElementById('custom-modal-overlay');
            const modalTitle = document.getElementById('modal-title');
            const modalDesc = document.getElementById('modal-description');
            const modalInput = document.getElementById('modal-input');
            const modalInputContainer = document.getElementById('modal-input-container');
            const modalConfirm = document.getElementById('modal-confirm');
            const modalCancel = document.getElementById('modal-cancel');
            const modalIconContainer = document.getElementById('modal-icon-container');

            window.customModal = function({ title, description, input = false, confirmText = 'Confirmar', type = 'info' }) {
                return new Promise((resolve) => {
                    modalTitle.textContent = title;
                    modalDesc.textContent = description;
                    modalConfirm.textContent = confirmText;
                    modalInput.value = '';
                    
                    if (input) {
                        modalInputContainer.classList.remove('hidden');
                        setTimeout(() => modalInput.focus(), 100);
                    } else {
                        modalInputContainer.classList.add('hidden');
                    }

                    // Icon based on type
                    let iconHtml = '';
                    if (type === 'question') iconHtml = '<div class="p-4 bg-blue-500/20 text-blue-500 rounded-full"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>';
                    if (type === 'warning') iconHtml = '<div class="p-4 bg-amber-500/20 text-amber-500 rounded-full"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>';
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

                    modalConfirm.onclick = () => close(input ? modalInput.value : true);
                    modalCancel.onclick = () => close(null);
                    modalOverlay.onclick = (e) => { if(e.target === modalOverlay) close(null); };
                    
                    modalInput.onkeyup = (e) => {
                        if (e.key === 'Enter') close(modalInput.value);
                        if (e.key === 'Escape') close(null);
                    };
                });
            };

            // Premium Toast System
            window.showToast = function (message, type = 'info') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast border-l-4 ${type === 'success' ? 'border-emerald-500' : (type === 'error' ? 'border-rose-500' : 'border-blue-500')}`;

                let icon = '';
                if (type === 'success') icon = '<div class="p-2 bg-emerald-500/20 rounded-lg text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>';
                if (type === 'error') icon = '<div class="p-2 bg-rose-500/20 rounded-lg text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>';
                if (type === 'info') icon = '<div class="p-2 bg-blue-500/20 rounded-lg text-blue-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>';

                toast.innerHTML = `
                        ${icon}
                        <div class="flex-1">
                            <p class="text-sm font-bold text-[var(--text-heading)]">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                            <p class="text-xs text-[var(--text-muted)]">${message}</p>
                        </div>
                        <div class="toast-progress"></div>
                    `;

                container.appendChild(toast);
                setTimeout(() => toast.classList.add('show'), 10);

                setTimeout(() => {
                    toast.classList.add('hide');
                    setTimeout(() => toast.remove(), 400);
                }, 4000);
            };

            // Timer Logic
            setInterval(() => {
                document.querySelectorAll('.timer').forEach(el => {
                    const start = parseInt(el.dataset.start);
                    const now = Math.floor(Date.now() / 1000);
                    const diff = now - start;
                    const minutes = Math.floor(diff / 60).toString().padStart(2, '0');
                    const seconds = (diff % 60).toString().padStart(2, '0');
                    el.textContent = `${minutes}:${seconds}`;
                });
            }, 1000);
        });

        // API Calls
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        async function createPass(studentId, reason, todayCount = 0, lastExitTime = '') {
            if (todayCount >= 2) {
                const confirmed = await customModal({
                    title: 'Aviso de Salidas Frecuentes',
                    description: `Este alumno ya ha salido ${todayCount} veces hoy${lastExitTime ? ' (última salida a las ' + lastExitTime + ')' : ''}. ¿Deseas autorizar una nueva salida?`,
                    confirmText: 'Autorizar salida',
                    type: 'warning'
                });
                if (!confirmed) return;
            }

            try {
                const res = await fetch('{{ route("salidas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ student_id: studentId, reason: reason })
                });
                if (!res.ok) {
                    const data = await res.json();
                    throw new Error(data.error || 'Error creating pass');
                }
                showToast('Pase creado correctamente', 'success');
                setTimeout(() => window.location.reload(), 500);
            } catch (e) {
                console.error(e);
                showToast(e.message, 'error');
            }
        }

        async function promptCustomReason(studentId, todayCount = 0, lastExitTime = '') {
            const reason = await customModal({
                title: 'Motivo Personalizado',
                description: 'Especifique el motivo de la salida del alumno:',
                input: true,
                confirmText: 'Crear Pase',
                type: 'question'
            });
            
            if (reason && reason.trim()) {
                createPass(studentId, reason.trim(), todayCount, lastExitTime);
            }
        }

        async function endPass(passId) {
            try {
                const url = "{{ route('salidas.update', ':id') }}".replace(':id', passId);
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'PATCH' })
                });

                if (!res.ok) throw new Error('Error al registrar el regreso');
                showToast('Pase finalizado', 'success');
                setTimeout(() => window.location.reload(), 500);
            } catch (e) {
                showToast(e.message, 'error');
            }
        }

        async function returnAll() {
            const selector = document.getElementById('class-selector');
            const groupId = selector.value;
            const groupName = selector.options[selector.selectedIndex].text.trim();

            if (!groupId) return;
            
            const confirmed = await customModal({
                title: 'Finalizar todos los pases',
                description: `¿Estás seguro de que quieres marcar el regreso de TODOS los alumnos de ${groupName}?`,
                confirmText: 'Sí, finalizar todos',
                type: 'warning'
            });

            if (!confirmed) return;

            try {
                const res = await fetch('{{ route("salidas.return-all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ group_id: groupId })
                });

                if (!res.ok) throw new Error('Error returning all');
                showToast('Todos los alumnos han regresado', 'success');
                setTimeout(() => window.location.reload(), 500);
            } catch (e) {
                showToast(e.message, 'error');
            }
        }
    </script>
@endsection