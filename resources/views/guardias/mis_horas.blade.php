@extends('layouts.app')

@section('title', 'Mis Horas de Guardia - ' . $user->name)

@section('content')
<style>
    /* Tab switcher */
    .nav-tab-btn {
        padding: 0.75rem 1.25rem;
        font-weight: 700;
        font-size: 0.875rem;
        border-radius: 0.875rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .nav-tab-btn.active {
        background: var(--bg-card);
        color: var(--text-heading);
        border-color: var(--border);
        box-shadow: var(--shadow-sm);
    }

    .nav-tab-btn:not(.active) {
        color: var(--text-muted);
    }

    .nav-tab-btn:not(.active):hover {
        color: var(--text-heading);
        background: var(--bg-hover);
    }
</style>

<div class="mis-horas-container max-w-6xl mx-auto space-y-6 pb-12">
    
    <!-- Top Header Banner -->
    <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm dark:shadow-2xl transition-colors">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/20 shrink-0 text-2xl font-black">
                    🛡️
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Horario de guardias con alumnos
                        </h1>
                        @if($user->id !== $authUser->id)
                            <span class="text-xs font-extrabold uppercase px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                Modo Directivo (Solo Lectura)
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Horas de guardia extraídas del horario personal oficial • Docente: <strong class="text-slate-800 dark:text-slate-200">{{ $user->name }} {{ $user->last_name ?? '' }}</strong>
                        @if(!empty($user->departamento))
                            <span class="text-slate-400">({{ $user->departamento }})</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Selector de Docente para Equipo Directivo -->
                @if($isDirectiva && $docentes->isNotEmpty())
                    <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800/60 p-2 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <label for="select_docente_filter" class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase shrink-0 pl-1">
                            Docente:
                        </label>
                        <select id="select_docente_filter" onchange="changeDocente(this.value)"
                                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-800 dark:text-white outline-none cursor-pointer">
                            @foreach($docentes as $doc)
                                <option value="{{ $doc->id }}" {{ $doc->id == $user->id ? 'selected' : '' }}>
                                    {{ $doc->name }} {{ $doc->last_name ?? '' }} {{ !empty($doc->departamento) ? '(' . $doc->departamento . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <a href="{{ route('personal-schedules.index') }}" 
                   class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-white font-semibold text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 transition">
                    <span>📅 Mi Horario Personal</span>
                </a>

                <a href="{{ route('guardias.parte') }}" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-500 hover:to-indigo-500 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-md shadow-sky-500/20 transition">
                    <span>Ir al Parte de Hoy</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs Switcher -->
        <div class="flex items-center gap-2 mt-6 pt-5 border-t border-slate-200 dark:border-slate-800/80">
            <button type="button" onclick="switchTab('config')" id="tab-btn-config" class="nav-tab-btn active">
                <span>🛡️ Horario Semanal de Guardias</span>
            </button>
            <button type="button" onclick="switchTab('history')" id="tab-btn-history" class="nav-tab-btn">
                <span>📋 Historial y Cómputo</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold">
                    {{ $totalGuardias }}
                </span>
            </button>
        </div>
    </div>

    <!-- TAB 1: Horario Semanal de Guardias (SOLO LECTURA - SIN MODIFICACIÓN) -->
    <div id="tab-content-config" class="space-y-4">
        
        <!-- Banner Informativo de Solo Lectura -->
        <div class="bg-sky-50 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800/60 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3 text-xs sm:text-sm text-sky-900 dark:text-sky-200">
                <span class="text-xl shrink-0">ℹ️</span>
                <div>
                    <strong>Horario sincronizado de solo lectura.</strong> Las horas de guardia se extraen directamente del <strong>horario personal</strong> oficial del profesorado. Cualquier cambio en las guardias asignadas debe realizarse en la configuración de horarios.
                </div>
            </div>
            <a href="{{ route('personal-schedules.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-black text-sky-700 hover:text-sky-800 dark:text-sky-300 dark:hover:text-sky-200 bg-white dark:bg-slate-800 border border-sky-200 dark:border-sky-700 px-3.5 py-2 rounded-xl shadow-xs transition shrink-0">
                <span>Gestionar Horario Personal</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Tabla Semanal de Guardias (Solo Lectura) -->
        <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-xs font-extrabold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                            <th class="py-4 px-4 w-44 text-left">Hª (Tramo Horario)</th>
                            @foreach($days as $dayNum => $dayName)
                                <th class="py-4 px-3 border-l border-slate-200 dark:border-slate-800/80">
                                    <span class="text-slate-900 dark:text-white">{{ $dayName }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 text-sm">
                        @forelse($timeSlots as $slot)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                                <!-- Columna de Hora / Tramo -->
                                <td class="py-3.5 px-4 text-left bg-slate-50/60 dark:bg-slate-950/40 font-bold text-slate-800 dark:text-slate-200">
                                    <div class="font-extrabold text-sm text-slate-900 dark:text-white">{{ $slot->name }}</div>
                                    <div class="text-[11px] font-normal text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                    </div>
                                </td>

                                <!-- Celdas de Lunes a Viernes (Sin botones de modificación) -->
                                @foreach($days as $dayNum => $dayName)
                                    @php
                                        $guardiaData = $guardiasMap[$slot->id][$dayNum] ?? null;
                                        $hasGuardia = !empty($guardiaData);
                                        $tipoTexto = is_array($guardiaData) ? ($guardiaData['tipo'] ?? 'Guardia') : 'Guardia';
                                    @endphp
                                    <td class="py-3.5 px-2 border-l border-slate-200 dark:border-slate-800/60 align-middle">
                                        <div class="flex items-center justify-center">
                                            @if($hasGuardia)
                                                <!-- Badge de Guardia No Modificable (Solo Lectura) -->
                                                <div data-is-guardia="1" 
                                                     class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl font-extrabold text-xs bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-sm shadow-sky-500/25 select-none"
                                                     title="Hora de guardia asignada en el horario personal">
                                                    <span>🛡️</span>
                                                    <span>{{ $tipoTexto }}</span>
                                                </div>
                                            @else
                                                <!-- Sin guardia: guión sutil sin botón editable -->
                                                <span class="text-slate-300 dark:text-slate-700 font-bold text-sm select-none">—</span>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">
                                    No hay tramos horarios configurados en la plantilla activa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(count($guardiasMap) === 0)
            <div class="p-6 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 rounded-2xl text-center space-y-2">
                <div class="text-2xl">📋</div>
                <div class="text-sm font-bold text-slate-800 dark:text-slate-200">
                    No se han detectado horas de guardia en tu horario personal.
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    Si tienes horas de guardia asignadas para este curso, asegúrate de añadirlas a tu horario semanal en la sección de horarios personales.
                </p>
                <a href="{{ route('personal-schedules.index') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 hover:text-sky-500 mt-2">
                    <span>Acceder a Mi Horario Personal</span>
                    <span>&rarr;</span>
                </a>
            </div>
        @endif

    </div>

    <!-- TAB 2: Historial y Cómputo de Guardias -->
    <div id="tab-content-history" class="space-y-6" style="display: none;">
        
        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-slate-900/90 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Guardias Realizadas</div>
                    <div class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ $totalGuardias }} <span class="text-base font-normal text-slate-500 dark:text-slate-400">sesiones</span></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Reagrupaciones computadas como 1 hora</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl">
                    🛡️
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900/90 rounded-3xl p-5 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Puntuación de Dificultad</div>
                    <div class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ $puntuacionTotal }} <span class="text-base font-normal text-slate-500 dark:text-slate-400">pts</span></div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Basado en la dificultad de grupos/aulas cubiertas</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl">
                    📊
                </div>
            </div>
        </div>

        <!-- History Log Table -->
        <div class="bg-white dark:bg-slate-900/90 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Registro Detallado de Guardias Cubiertas</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Orden cronológico inverso</span>
            </div>

            @if($guardiasRealizadas->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 font-bold uppercase">
                                <th class="py-3 px-4">Fecha</th>
                                <th class="py-3 px-4">Hora</th>
                                <th class="py-3 px-4">Profesor Ausente</th>
                                <th class="py-3 px-4">Grupo y Aula</th>
                                <th class="py-3 px-4">Dificultad</th>
                                <th class="py-3 px-4">Confirmada en</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($guardiasRealizadas as $g)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">
                                        {{ $g->fecha ? $g->fecha->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-medium">
                                        {{ $g->timeSlot?->name ?? 'Hora ' . $g->time_slot_id }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-800 dark:text-slate-200 font-semibold">
                                        {{ $g->user?->name ?? 'Docente' }} {{ $g->user?->last_name ?? '' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-sky-600 dark:text-sky-400">
                                            {{ $g->group ? ($g->group->course . ' ' . $g->group->name) : ($g->es_guardia ? 'Guardia Recreo' : 'Sin grupo') }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            📍 {{ $g->zona?->nombre ?? 'Aula' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ $g->group->dificultad ?? $g->zona->dificultad ?? 1 }} pt(s)
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                                        {{ $g->guardia_confirmed_at ? $g->guardia_confirmed_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center text-slate-400 space-y-2">
                    <div class="text-3xl">🛡️</div>
                    <div class="text-sm font-semibold">Aún no has cubierto ninguna guardia en este curso escolar.</div>
                    <div class="text-xs text-slate-500">Tus guardias confirmadas aparecerán registradas en este listado con su cómputo correspondiente.</div>
                </div>
            @endif
        </div>

    </div>

</div>

<script>
    function switchTab(tab) {
        const configTab = document.getElementById('tab-content-config');
        const historyTab = document.getElementById('tab-content-history');
        const btnConfig = document.getElementById('tab-btn-config');
        const btnHistory = document.getElementById('tab-btn-history');

        if (tab === 'config') {
            configTab.style.display = 'block';
            historyTab.style.display = 'none';
            btnConfig.classList.add('active');
            btnHistory.classList.remove('active');
        } else {
            configTab.style.display = 'none';
            historyTab.style.display = 'block';
            btnConfig.classList.remove('active');
            btnHistory.classList.add('active');
        }
    }

    function changeDocente(userId) {
        const url = new URL(window.location.href);
        url.searchParams.set('user_id', userId);
        window.location.href = url.toString();
    }
</script>
@endsection
