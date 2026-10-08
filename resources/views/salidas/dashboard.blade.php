@extends('layouts.app')

@section('title', 'Gestor de salidas - Dashboard')

@section('module_title')
Gestor de <span class="text-blue-500">salidas</span>
@endsection

@section('content')
    <style>
        /* Modern Modal Styles */
        #custom-modal-overlay, #student-details-modal-overlay {
            backdrop-filter: blur(8px);
            background-color: rgba(15, 23, 42, 0.7);
            transition: all 0.3s ease;
        }
        .modal-content, .modal-card {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            background-color: var(--bg-card-solid);
            border-color: var(--border);
            color: var(--text-color);
        }
        #custom-modal-overlay.active .modal-content,
        #student-details-modal-overlay.active .modal-card {
            transform: scale(1);
            opacity: 1;
        }

        /* Mobile Bottom Sheet Styles */
        @media (max-width: 639px) {
            .modal-card {
                transform: translateY(100%);
            }
            #student-details-modal-overlay.active .modal-card {
                transform: translateY(0);
            }
        }

        /* Mobile list item styling */
        @media (max-width: 639px) {
            .student-card {
                padding: 0.55rem 0.75rem !important;
                border-radius: 0.95rem !important;
                gap: 0.5rem !important;
            }
            .student-card .student-name-mobile {
                font-size: 1.05rem !important; /* Aumentado para mayor legibilidad en móviles */
                font-weight: 700 !important;
                line-height: 1.3 !important;
            }
            .student-card .student-course-mobile {
                font-size: 0.80rem !important;
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

        /* Search & Class Selector Layout (Bulletproof Desktop/Mobile Sizing) */
        .search-filter-container {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1 1 0%;
            min-width: 0;
        }

        .student-search-box {
            width: 100%;
        }

        .class-selector-box {
            width: 100%;
        }

        @media (min-width: 640px) {
            .search-filter-container {
                flex-direction: row;
                align-items: center;
                justify-content: flex-end;
                gap: 0.625rem;
            }

            .student-search-box {
                width: 200px !important;
                max-width: 200px !important;
                flex: 0 0 200px !important;
            }

            .class-selector-box {
                width: 320px !important;
                min-width: 260px !important;
                max-width: 360px !important;
                flex: 0 0 320px !important;
            }
        }

        @media (min-width: 1024px) {
            .student-search-box {
                width: 220px !important;
                max-width: 220px !important;
                flex: 0 0 220px !important;
            }

            .class-selector-box {
                width: 360px !important;
                min-width: 300px !important;
                max-width: 420px !important;
                flex: 0 0 360px !important;
            }
        }
    </style>

    <div class="py-2 sm:py-4">
        <div class="max-w-7xl mx-auto">

            <!-- Statistics & Search -->
            <div class="card p-3 sm:p-5 mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                    <!-- Stats (Hidden on mobile, visible on desktop) -->
                    <div class="hidden sm:flex gap-2 sm:gap-3 shrink-0">
                        <div class="bg-blue-500/10 border border-blue-500/25 rounded-xl px-3 sm:px-4 py-2 flex items-center justify-between sm:block">
                            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest sm:block mb-0.5">Activos</span>
                            <span class="text-lg sm:text-xl font-black text-[var(--text-heading)] leading-none">{{ $stats['active_count'] }}</span>
                        </div>
                        <div class="bg-indigo-500/10 border border-indigo-500/25 rounded-xl px-3 sm:px-4 py-2 flex items-center justify-between sm:block">
                            <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest sm:block mb-0.5">Hoy</span>
                            <span class="text-lg sm:text-xl font-black text-[var(--text-heading)] leading-none">{{ $stats['today_count'] }}</span>
                        </div>
                    </div>

                    <!-- Search & Filters -->
                    <div class="search-filter-container">
                        <div class="student-search-box relative">
                            <input type="text" id="student-search" placeholder="Buscar alumno..."
                                class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2 pl-9 pr-4 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-[var(--text-muted)]">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <div class="flex items-center gap-2 flex-1 sm:flex-initial min-w-0">
                            <div class="class-selector-box flex-1 min-w-0 flex items-center bg-[var(--bg-input)] border border-[var(--border)] rounded-xl focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 transition-all">
                                <select id="class-selector"
                                    class="w-full min-w-0 bg-transparent border-0 text-sm text-[var(--text-color)] font-semibold py-2 pl-3.5 pr-1 focus:ring-0 focus:outline-none cursor-pointer appearance-none truncate"
                                    style="border: none; outline: none; box-shadow: none; background: transparent;">
                                    <option value="" selected>Seleccionar clase...</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">
                                            {{ trim(($group->course ? $group->course . ' ' : '') . $group->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" id="favorite-class-btn" onclick="toggleCurrentClassFavorite(event)"
                                    class="p-1.5 mr-1.5 shrink-0 text-slate-400 hover:text-amber-500 hover:scale-110 active:scale-95 transition-all focus:outline-none flex items-center justify-center"
                                    title="Marcar o desmarcar como favorita">
                                    <svg id="favorite-star-icon" class="w-5 h-5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                    </svg>
                                </button>
                            </div>

                            <div class="flex gap-1 shrink-0">
                                <a href="{{ route('salidas.monitor') }}"
                                    class="p-2 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all"
                                    title="Monitor">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                @if(auth()->user()->can('salidas.manage') || auth()->user()->hasRole(['admin', 'jefatura', 'directiva', 'director']))
                                <a href="{{ route('salidas.history') }}"
                                    class="p-2 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all"
                                    title="Historial">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </a>
                                @endif
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

            <!-- Student Grid / List (Unified for PC and Mobile) -->
            <div id="student-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2 sm:gap-2.5 w-full">
                @foreach($students as $student)
                    @php
                        $activePass = $activePasses->firstWhere('user_id', $student->id);
                        $studentTodayPasses = $todayPassesByStudent->get($student->id, collect());
                        $todayCount = $studentTodayPasses->count();
                        $lastPass = $studentTodayPasses->first();
                        $studentDisplayName = $student->last_name ? trim($student->last_name . ', ' . $student->name) : $student->name;
                        $studentFullName = trim($student->name . ' ' . ($student->last_name ?? ''));
                        $courseName = trim(($student->groupRel?->course ? $student->groupRel->course . ' ' : '') . ($student->groupRel?->name ?? ''));
                    @endphp
                    <div class="student-card group relative rounded-2xl p-2.5 sm:p-3 shadow-xs hover:shadow-md transition-all duration-200 border flex items-center justify-between gap-2.5 w-full"
                        style="background: var(--bg-card); border-color: {{ $activePass ? 'rgba(245, 158, 11, 0.6)' : ($todayCount >= 3 ? 'rgba(239, 68, 68, 0.4)' : 'var(--border)') }};"
                        data-group-id="{{ $student->group_id }}" data-id="{{ $student->id }}"
                        data-student-name="{{ $studentDisplayName }}"
                        data-student-course="{{ $courseName }}"
                        data-today-count="{{ $todayCount }}"
                        data-last-exit="{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}"
                        data-search="{{ strtolower(($student->last_name ?? '') . ' ' . $student->name . ' ' . $student->name . ' ' . ($student->last_name ?? '')) }}"
                        style="display: none;">

                        <!-- Left: Avatar + Name + Course + Exits Today -->
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div class="h-9 w-9 sm:h-10 sm:w-10 shrink-0 rounded-full bg-gradient-to-tr {{ $activePass ? 'from-amber-400 to-amber-600' : ($todayCount >= 3 ? 'from-amber-500 to-rose-500' : 'from-blue-500 to-indigo-600') }} flex items-center justify-center text-white font-black text-xs sm:text-sm shadow-xs">
                                {{ substr($student->last_name ?? $student->name, 0, 1) }}{{ substr($student->name, 0, 1) }}
                            </div>
                            <div class="overflow-hidden min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <h3 class="student-name student-name-mobile text-base sm:text-lg font-bold text-[var(--text-heading)] truncate leading-tight"
                                        title="{{ $studentDisplayName }}">
                                        {{ $studentDisplayName }}
                                    </h3>
                                    @if($todayCount > 0 && !$activePass)
                                        <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-md {{ $todayCount >= 3 ? 'bg-rose-500/15 text-rose-500 border border-rose-500/30' : 'bg-amber-500/15 text-amber-500 border border-amber-500/30' }}" title="{{ $todayCount }} salidas hoy">
                                            {{ $todayCount }}
                                        </span>
                                    @endif
                                </div>
                                <div class="student-course-mobile flex items-center gap-1 text-[11px] text-[var(--text-muted)] leading-tight mt-0.5">
                                    <span class="truncate">{{ $courseName }}</span>
                                    @if($activePass)
                                        <span class="text-amber-500 font-extrabold flex items-center gap-1 shrink-0 ml-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                            <span>Fuera: {{ $activePass->reason }}</span>
                                        </span>
                                    @elseif($todayCount > 0)
                                        <span class="text-[10px] text-[var(--text-muted)] truncate hidden xs:inline">• Última: {{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Actions (Active state has Timer + Regresar; Inactive state has ONLY Servicio icon and More [+] icon) -->
                        @if($activePass)
                            <div class="flex items-center gap-1.5 shrink-0">
                                <div class="px-2 py-1 bg-amber-500/15 border border-amber-500/30 rounded-xl text-center">
                                    <span class="text-[11px] font-mono font-black text-amber-500 timer block leading-none" data-start="{{ $activePass->start_time->timestamp }}">00:00</span>
                                </div>
                                <button onclick="endPass({{ $activePass->id }})" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl font-bold text-xs flex items-center gap-1 shadow-xs transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Regresar</span>
                                </button>
                            </div>
                        @else
                            <div class="flex items-center gap-1 shrink-0">
                                <!-- 1. Icono de servicio (Baño / Aseo / WC) -->
                                <button type="button"
                                    onclick="createPass({{ $student->id }}, 'Baño', {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                                    title="Salida a servicio / baño" aria-label="Baño"
                                    class="flex items-center justify-center w-8 h-8 rounded-xl bg-blue-500/15 hover:bg-blue-500/25 active:scale-95 text-blue-600 dark:text-blue-400 border border-blue-500/30 transition shadow-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4zM7 8h10M7 12h10" />
                                    </svg>
                                </button>

                                <!-- 2. Icono de más (+) -->
                                <button type="button"
                                    onclick="openStudentDetailsModal({{ $student->id }})"
                                    title="Más opciones de salida y detalles" aria-label="Más opciones"
                                    class="flex items-center justify-center w-8 h-8 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 active:scale-95 text-slate-700 dark:text-slate-300 border border-slate-500/30 transition shadow-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                        @endif
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

    <!-- Student Details Modal (Opened by the Plus [+] icon) -->
    <div id="student-details-modal-overlay" class="fixed inset-0 z-[100] hidden items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="modal-card w-full sm:max-w-xl border-t sm:border rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden p-5 sm:p-7 bg-[var(--bg-card-solid)] border-[var(--border)] max-h-[92vh] flex flex-col">
            <!-- Mobile Sheet handle -->
            <div class="w-12 h-1.5 bg-[var(--border)] rounded-full mx-auto mb-3 sm:hidden"></div>

            <!-- Header: Nombre del alumno en letra más grande -->
            <div class="flex items-start justify-between gap-3 pb-3.5 border-b border-[var(--border)]">
                <div>
                    <h2 id="modal-student-name" class="text-2xl sm:text-3xl font-black text-[var(--text-heading)] leading-tight tracking-tight"></h2>
                    <p id="modal-student-course" class="text-sm sm:text-base text-[var(--text-muted)] font-bold mt-1"></p>
                </div>
                <button type="button" onclick="closeStudentDetailsModal()" class="text-[var(--text-muted)] hover:text-[var(--text-heading)] p-2.5 rounded-2xl bg-[var(--bg-hover)] border border-[var(--border)] transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto space-y-5 py-4 flex-1 pr-0.5">
                <!-- 1. Botones de acceso rápido de salida -->
                <div>
                    <p class="text-sm font-extrabold text-[var(--text-muted)] uppercase tracking-wider mb-2.5">Acceso rápido de salida:</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <button type="button" onclick="selectModalReason('Baño')"
                            class="p-3.5 sm:p-4 rounded-2xl bg-blue-500/10 hover:bg-blue-500/20 active:scale-95 text-blue-600 dark:text-blue-400 border border-blue-500/30 transition flex flex-col items-center justify-center gap-2 shadow-xs">
                            <div class="p-2.5 bg-blue-500/20 rounded-2xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4zM7 8h10M7 12h10" /></svg>
                            </div>
                            <span class="text-sm sm:text-base font-extrabold">Baño</span>
                        </button>
                        <button type="button" onclick="selectModalReason('Agua')"
                            class="p-3.5 sm:p-4 rounded-2xl bg-cyan-500/10 hover:bg-cyan-500/20 active:scale-95 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30 transition flex flex-col items-center justify-center gap-2 shadow-xs">
                            <div class="p-2.5 bg-cyan-500/20 rounded-2xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" /></svg>
                            </div>
                            <span class="text-sm sm:text-base font-extrabold">Agua</span>
                        </button>
                        <button type="button" onclick="selectModalReason('Enfermedad')"
                            class="p-3.5 sm:p-4 rounded-2xl bg-orange-500/10 hover:bg-orange-500/20 active:scale-95 text-orange-600 dark:text-orange-400 border border-orange-500/30 transition flex flex-col items-center justify-center gap-2 shadow-xs">
                            <div class="p-2.5 bg-orange-500/20 rounded-2xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3zM12 8v8m-4-4h8" /></svg>
                            </div>
                            <span class="text-sm sm:text-base font-extrabold">Enfermedad</span>
                        </button>
                        <button type="button" onclick="selectModalReason('Taquilla/Material')"
                            class="p-3.5 sm:p-4 rounded-2xl bg-purple-500/10 hover:bg-purple-500/20 active:scale-95 text-purple-600 dark:text-purple-400 border border-purple-500/30 transition flex flex-col items-center justify-center gap-2 shadow-xs">
                            <div class="p-2.5 bg-purple-500/20 rounded-2xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" /></svg>
                            </div>
                            <span class="text-sm sm:text-base font-extrabold">Material</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Cuadro de texto para otro motivo con un botón de otros -->
                <div>
                    <p class="text-sm font-extrabold text-[var(--text-muted)] uppercase tracking-wider mb-2.5">Otro motivo personalizado:</p>
                    <div class="flex items-center gap-2.5">
                        <input type="text" id="modal-custom-reason-input" placeholder="Escribe otro motivo (ej: Taquilla, Biblioteca...)"
                            class="flex-1 bg-[var(--bg-input)] border border-[var(--border)] rounded-2xl py-3 px-4 text-sm sm:text-base font-semibold text-[var(--text-color)] focus:ring-2 focus:ring-blue-500 transition-all placeholder:text-sm placeholder:text-[var(--text-muted)]">
                        <button type="button" onclick="submitModalCustomReason()"
                            class="px-5 py-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-extrabold text-sm sm:text-base rounded-2xl shrink-0 transition shadow-md shadow-blue-500/25 flex items-center gap-2"
                            style="background-color: #2563eb !important; color: #ffffff !important;">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <span>Otro motivo</span>
                        </button>
                    </div>
                </div>

                <!-- 3. Listado de las horas a las que ha salido el alumno en el día y quién les ha dejado salir -->
                <div class="pt-4 border-t border-[var(--border)]">
                    <div class="flex items-center justify-between mb-2.5">
                        <p class="text-sm font-extrabold text-[var(--text-muted)] uppercase tracking-wider">Historial de salidas de hoy:</p>
                        <span id="modal-history-count" class="text-sm font-black text-blue-600 dark:text-blue-400"></span>
                    </div>

                    <div id="modal-history-list" class="space-y-2.5 max-h-60 overflow-y-auto">
                        <!-- Populated dynamically by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-3.5 border-t border-[var(--border)] flex justify-end">
                <button type="button" onclick="closeStudentDetailsModal()" class="w-full sm:w-auto px-6 py-3 bg-[var(--bg-hover)] hover:bg-[var(--border)] text-[var(--text-heading)] font-extrabold text-sm sm:text-base rounded-2xl transition">
                    Cerrar
                </button>
            </div>
        </div>
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
        // Passes history map per student from server
        let todayPassesMap = @json($todayPassesJson ?? []);
        const initialDbFavorites = @json($userFavorites ?? []);

        // State for student details modal
        let currentModalStudent = null;

        function openStudentDetailsModal(studentId) {
            const card = document.querySelector(`.student-card[data-id="${studentId}"]`);
            if (!card) return;

            const studentName = card.dataset.studentName || '';
            const studentCourse = card.dataset.studentCourse || '';
            const todayCount = parseInt(card.dataset.todayCount || '0', 10);
            const lastExit = card.dataset.lastExit || '';

            currentModalStudent = {
                id: studentId,
                name: studentName,
                course: studentCourse,
                todayCount: todayCount,
                lastExit: lastExit
            };

            const modalOverlay = document.getElementById('student-details-modal-overlay');
            const nameEl = document.getElementById('modal-student-name');
            const courseEl = document.getElementById('modal-student-course');
            const customInput = document.getElementById('modal-custom-reason-input');

            if (nameEl) nameEl.textContent = studentName;
            if (courseEl) courseEl.textContent = studentCourse;
            if (customInput) customInput.value = '';

            renderModalTodayHistory(studentId);

            if (modalOverlay) {
                modalOverlay.classList.remove('hidden');
                modalOverlay.classList.add('flex');
                setTimeout(() => modalOverlay.classList.add('active'), 10);
            }
        }

        function closeStudentDetailsModal() {
            const modalOverlay = document.getElementById('student-details-modal-overlay');
            if (!modalOverlay) return;
            modalOverlay.classList.remove('active');
            setTimeout(() => {
                modalOverlay.classList.add('hidden');
                modalOverlay.classList.remove('flex');
                currentModalStudent = null;
            }, 200);
        }

        function selectModalReason(reason) {
            if (!currentModalStudent) return;
            const { id, todayCount, lastExit } = currentModalStudent;
            closeStudentDetailsModal();
            setTimeout(() => {
                createPass(id, reason, todayCount, lastExit);
            }, 150);
        }

        function submitModalCustomReason() {
            if (!currentModalStudent) return;
            const input = document.getElementById('modal-custom-reason-input');
            const reason = input ? input.value.trim() : '';
            if (!reason) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Por favor, escribe un motivo para la salida', 'info');
                }
                if (input) input.focus();
                return;
            }

            const { id, todayCount, lastExit } = currentModalStudent;
            closeStudentDetailsModal();
            setTimeout(() => {
                createPass(id, reason, todayCount, lastExit);
            }, 150);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function renderModalTodayHistory(studentId) {
            const historyContainer = document.getElementById('modal-history-list');
            const countBadge = document.getElementById('modal-history-count');
            if (!historyContainer) return;

            const passes = (todayPassesMap && todayPassesMap[studentId]) ? todayPassesMap[studentId] : [];
            if (countBadge) {
                countBadge.textContent = passes.length === 1 ? '1 salida' : `${passes.length} salidas`;
            }

            if (passes.length === 0) {
                historyContainer.innerHTML = `
                    <div class="p-3 text-center rounded-xl bg-[var(--bg-hover)] text-[var(--text-muted)] text-xs italic">
                        Sin salidas registradas hoy
                    </div>
                `;
                return;
            }

            let html = '';
            passes.forEach(pass => {
                const timeDisplay = pass.end_time 
                    ? `${pass.start_time} - ${pass.end_time}`
                    : `${pass.start_time} - En curso`;

                const editBtnHtml = pass.can_edit_time ? `
                    <button type="button" onclick="promptEditPassTime(${pass.id}, ${pass.duration_minutes || 5}, '${pass.start_time || ''}', '${pass.end_time || ''}')"
                        class="px-3 py-1.5 text-xs sm:text-sm font-bold rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 border border-blue-500/30 transition shrink-0 active:scale-95 shadow-xs flex items-center gap-1"
                        title="Editar duración o tiempo de salida">
                        ✏️ Editar tiempo
                    </button>
                ` : '';

                html += `
                    <div id="pass-history-row-${pass.id}" class="p-3 sm:p-3.5 rounded-2xl border border-[var(--border)] bg-[var(--bg-input)] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 transition hover:border-blue-500/30">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="text-sm sm:text-base font-mono font-bold text-[var(--text-heading)] bg-[var(--bg-card)] px-2.5 py-1 rounded-xl border border-[var(--border)] shadow-xs">
                                    🕒 ${timeDisplay}
                                </span>
                                <span class="text-sm sm:text-base font-extrabold text-blue-600 dark:text-blue-400">
                                    ${escapeHtml(pass.reason)}
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1.5 truncate">
                                Autorizado por: <strong class="text-[var(--text-heading)] font-bold">${escapeHtml(pass.teacher_name)}</strong>
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                            ${editBtnHtml}
                        </div>
                    </div>
                `;
            });

            historyContainer.innerHTML = html;
        }

        async function promptEditPassTime(passId, currentMinutes, startTime, endTime) {
            const newDurationStr = await customModal({
                title: 'Editar Tiempo de Salida',
                description: `Indica la duración en minutos de la salida (actual: ${currentMinutes || 5} min):`,
                input: true,
                confirmText: 'Guardar tiempo',
                type: 'question'
            });

            if (newDurationStr === null || String(newDurationStr).trim() === '') return;

            const newMinutes = parseInt(String(newDurationStr).trim(), 10);
            if (isNaN(newMinutes) || newMinutes <= 0 || newMinutes > 480) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Por favor introduce un número válido de minutos (1 - 480).', 'error');
                }
                return;
            }

            try {
                const url = "{{ url('/salidas/pass') }}/" + passId + "/time";
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ duration_minutes: newMinutes })
                });

                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.error || 'Error al actualizar el tiempo');
                }

                // Update in local todayPassesMap
                if (currentModalStudent && todayPassesMap[currentModalStudent.id]) {
                    const p = todayPassesMap[currentModalStudent.id].find(x => x.id === passId);
                    if (p) {
                        p.duration_minutes = newMinutes;
                        if (data.pass && data.pass.end_time) {
                            const endD = new Date(data.pass.end_time);
                            const endH = String(endD.getHours()).padStart(2, '0');
                            const endM = String(endD.getMinutes()).padStart(2, '0');
                            p.end_time = `${endH}:${endM}`;
                        }
                    }
                    renderModalTodayHistory(currentModalStudent.id);
                }

                if (typeof window.showToast === 'function') {
                    window.showToast('Tiempo de salida actualizado correctamente', 'success');
                }
            } catch (e) {
                if (typeof window.showToast === 'function') {
                    window.showToast(e.message, 'error');
                }
            }
        }

        // Available groups for favorite handling
        const availableGroups = @json($groups->map(fn($g) => [
            'id' => (string) $g->id,
            'name' => trim(($g->course ? $g->course . ' ' : '') . $g->name)
        ]));

        const FAVORITES_STORAGE_KEY = 'salidas_favorite_groups';

        function getFavoriteGroupIds() {
            try {
                const stored = localStorage.getItem(FAVORITES_STORAGE_KEY);
                let localFavs = stored ? JSON.parse(stored) : [];
                if (!Array.isArray(localFavs)) localFavs = [];

                // Sincronizar con favoritos de base de datos del usuario
                if (Array.isArray(initialDbFavorites) && initialDbFavorites.length > 0) {
                    initialDbFavorites.forEach(id => {
                        const strId = String(id);
                        if (!localFavs.map(String).includes(strId)) {
                            localFavs.push(strId);
                        }
                    });
                }
                return localFavs;
            } catch (e) {
                return (initialDbFavorites || []).map(String);
            }
        }

        function saveFavoriteGroupIds(ids) {
            try {
                localStorage.setItem(FAVORITES_STORAGE_KEY, JSON.stringify(ids));
            } catch (e) {
                console.error('Error saving favorite groups:', e);
            }
        }

        function isGroupFavorite(groupId) {
            if (!groupId) return false;
            return getFavoriteGroupIds().map(String).includes(String(groupId));
        }

        function updateFavoriteStarState() {
            const selector = document.getElementById('class-selector');
            const starBtn = document.getElementById('favorite-class-btn');
            const starIcon = document.getElementById('favorite-star-icon');
            if (!selector || !starBtn || !starIcon) return;

            const currentVal = selector.value;
            if (!currentVal) {
                starBtn.classList.add('opacity-30', 'cursor-not-allowed');
                starBtn.classList.remove('cursor-pointer');
                starBtn.title = 'Selecciona una clase para marcarla como favorita';
                starIcon.setAttribute('fill', 'none');
                starIcon.setAttribute('stroke', 'currentColor');
                starIcon.setAttribute('stroke-width', '2');
                starIcon.className = 'w-5 h-5 text-slate-400 dark:text-slate-500 transition-all';
                return;
            }

            starBtn.classList.remove('opacity-30', 'cursor-not-allowed');
            starBtn.classList.add('cursor-pointer');
            const isFav = isGroupFavorite(currentVal);

            if (isFav) {
                starBtn.title = 'Quitar de favoritos';
                starIcon.setAttribute('fill', '#facc15');
                starIcon.setAttribute('stroke', '#0f172a');
                starIcon.setAttribute('stroke-width', '1.8');
                starIcon.className = 'w-5 h-5 transition-transform drop-shadow-xs scale-105';
            } else {
                starBtn.title = 'Marcar como favorita';
                starIcon.setAttribute('fill', 'none');
                starIcon.setAttribute('stroke', 'currentColor');
                starIcon.setAttribute('stroke-width', '2');
                starIcon.className = 'w-5 h-5 text-slate-400 dark:text-slate-500 hover:text-amber-500 transition-all';
            }
        }

        function renderClassSelector(selectedId = null) {
            const selector = document.getElementById('class-selector');
            if (!selector) return;

            const currentVal = selectedId !== null ? String(selectedId) : String(selector.value || '');
            const favorites = getFavoriteGroupIds().map(String);

            // Separate favorites and other groups
            const favGroups = availableGroups.filter(g => favorites.includes(String(g.id)));
            const otherGroups = availableGroups.filter(g => !favorites.includes(String(g.id)));

            selector.innerHTML = '';

            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Seleccionar clase...';
            selector.appendChild(defaultOpt);

            if (favGroups.length > 0) {
                const favGroupEl = document.createElement('optgroup');
                favGroupEl.label = '⭐ Favoritos';
                favGroups.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g.id;
                    opt.textContent = g.name;
                    favGroupEl.appendChild(opt);
                });
                selector.appendChild(favGroupEl);

                const otherGroupEl = document.createElement('optgroup');
                otherGroupEl.label = 'Otras clases';
                otherGroups.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g.id;
                    opt.textContent = g.name;
                    otherGroupEl.appendChild(opt);
                });
                selector.appendChild(otherGroupEl);
            } else {
                availableGroups.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g.id;
                    opt.textContent = g.name;
                    selector.appendChild(opt);
                });
            }

            if (currentVal && availableGroups.some(g => String(g.id) === currentVal)) {
                selector.value = currentVal;
            } else {
                selector.value = '';
            }

            updateFavoriteStarState();
        }

        window.toggleCurrentClassFavorite = async function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const selector = document.getElementById('class-selector');
            if (!selector) return;

            const currentVal = selector.value;
            if (!currentVal) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Selecciona primero una clase para marcarla como favorita', 'info');
                }
                return;
            }

            let favorites = getFavoriteGroupIds().map(String);
            const index = favorites.indexOf(String(currentVal));
            const group = availableGroups.find(g => String(g.id) === String(currentVal));
            const groupName = group ? group.name : 'Clase';

            let isAdding = false;
            if (index >= 0) {
                favorites.splice(index, 1);
                isAdding = false;
            } else {
                favorites.push(String(currentVal));
                isAdding = true;
            }

            saveFavoriteGroupIds(favorites);
            renderClassSelector(currentVal);

            if (typeof window.showToast === 'function') {
                window.showToast(isAdding ? `⭐ ${groupName} añadida a favoritos` : `${groupName} eliminada de favoritos`, isAdding ? 'success' : 'info');
            }

            // Sincronizar en base de datos para persistencia multidispositivo
            try {
                await fetch('{{ route("salidas.toggle-favorite") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ group_id: currentVal })
                });
            } catch (err) {
                console.warn('Could not sync favorite to server:', err);
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('student-search');
            const selector = document.getElementById('class-selector');
            const cards = document.querySelectorAll('.student-card');
            const emptyState = document.getElementById('empty-state');
            const returnAllBtn = document.getElementById('return-all-btn');

            // Close student details modal on backdrop click
            const studentModalOverlay = document.getElementById('student-details-modal-overlay');
            if (studentModalOverlay) {
                studentModalOverlay.addEventListener('click', (e) => {
                    if (e.target === studentModalOverlay) closeStudentDetailsModal();
                });
            }

            // Enter key support for modal custom reason input
            const modalCustomInput = document.getElementById('modal-custom-reason-input');
            if (modalCustomInput) {
                modalCustomInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        submitModalCustomReason();
                    }
                });
            }

            // Escape key support
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && studentModalOverlay && !studentModalOverlay.classList.contains('hidden')) {
                    closeStudentDetailsModal();
                }
            });

            // Restore saved class or first favorite if only 1 favorite and no saved class
            const savedClass = localStorage.getItem('selected_class');
            const favorites = getFavoriteGroupIds().map(String);
            let initialClass = savedClass;
            if (!initialClass && favorites.length === 1) {
                initialClass = favorites[0];
            }
            renderClassSelector(initialClass);

            function applyFilters() {
                const selectedGroup = selector.value;
                const searchTerm = searchInput.value?.toLowerCase().trim() || '';
                let counter = 0;

                cards.forEach(card => {
                    const matchesGroup = !selectedGroup || String(card.dataset.groupId) === String(selectedGroup);
                    const matchesSearch = !searchTerm || card.dataset.search.includes(searchTerm);
                    const hasActiveFilter = Boolean(selectedGroup || searchTerm);

                    if (hasActiveFilter && matchesGroup && matchesSearch) {
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
                updateFavoriteStarState();
                applyFilters();
            });
            searchInput.addEventListener('input', applyFilters);

            // Initial call
            applyFilters();
            updateFavoriteStarState();

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