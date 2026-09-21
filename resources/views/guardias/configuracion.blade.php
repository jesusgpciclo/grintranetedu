@extends('layouts.app')

@section('title', 'Configuración de Guardias y Dificultades')

@section('content')
<div class="configuracion-container max-w-7xl mx-auto space-y-6 pb-16">
    
    <!-- Top Header -->
    <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-sm dark:shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/20 shrink-0">
                <svg class="w-7 h-7 !text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Configuración de Guardias</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Motor de equidad de guardias, coeficientes de dificultad ($D_g$) y aforos de espacios comunes</p>
            </div>
        </div>

        <button type="submit" form="guardias-config-form" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 !text-white font-bold text-sm rounded-2xl shadow-lg shadow-sky-500/25 transition active:scale-95 cursor-pointer">
            <svg class="w-4 h-4 !text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            <span class="!text-white">Guardar Configuración</span>
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="font-semibold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <form id="guardias-config-form" action="{{ route('guardias.configuracion.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Plantilla de Horario Activa -->
        <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl transition-colors">
            <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📅 Plantilla de Horario Activa</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Selecciona la plantilla con la que funcionará el cómputo de tramos y guardias.</p>
                </div>
            </div>

            <div class="p-6">
                <div class="max-w-xl">
                    <label for="guardias_schedule_template_id" class="block text-xs uppercase font-bold text-slate-500 dark:text-slate-400 mb-2">Plantilla Asignada:</label>
                    <select name="guardias_schedule_template_id" id="guardias_schedule_template_id" 
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white font-semibold focus:ring-2 focus:ring-sky-500 focus:border-transparent text-sm">
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" {{ $activeTemplateId == $template->id ? 'selected' : '' }}>
                                {{ $template->name }} ({{ count($template->timeSlots) }} tramos horarios)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Dificultad de Grupos ($D_g$) con Búsqueda y Organización por Niveles -->
        <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl transition-colors">
            <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>👥 Dificultad de Grupos ($D_g$)</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Define el peso de la guardia según ratio o conflictividad. Valores negativos (ej. <code class="text-sky-600 dark:text-sky-400 font-mono font-bold">-2</code>) para grupos exentos de guardia.
                    </p>
                </div>

                <!-- Buscador de Grupos en Tiempo Real -->
                <div class="relative w-full md:w-80">
                    <input type="text" id="group-search" placeholder="Filtrar por grupo, curso o tutor..." 
                           class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div class="p-6 space-y-5" id="grupos-container">
                @php
                    $gruposPorCurso = $grupos->groupBy('course');
                @endphp

                @forelse($gruposPorCurso as $cursoNombre => $listaGrupos)
                    @php
                        $cursoSlug = Str::slug($cursoNombre ?: 'sin-curso');
                    @endphp
                    <div class="curso-block bg-slate-50/60 dark:bg-slate-950/50 rounded-2xl border border-slate-200 dark:border-slate-800/80 overflow-hidden transition" data-curso-name="{{ strtolower($cursoNombre) }}">
                        
                        <!-- Cabecera del Curso / Nivel -->
                        <div class="px-5 py-3.5 bg-slate-100/80 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-600 dark:text-sky-400 text-xs font-bold tracking-wide">
                                    {{ $cursoNombre ?: 'Sin Curso Asignado' }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    {{ count($listaGrupos) }} {{ count($listaGrupos) === 1 ? 'grupo' : 'grupos' }}
                                </span>
                            </div>

                            <!-- Herramienta Bulk: Asignar Dificultad Masiva -->
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-slate-600 dark:text-slate-400 hidden sm:inline">Asignar a todo el curso:</span>
                                <div class="flex items-center gap-1.5">
                                    <input type="number" min="-5" max="10" step="1" value="1" id="bulk-input-{{ $cursoSlug }}" 
                                           class="w-14 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center text-slate-900 dark:text-white text-xs font-bold focus:ring-1 focus:ring-sky-500">
                                    <button type="button" onclick="applyBulkDifficulty('{{ $cursoSlug }}')" 
                                            class="px-2.5 py-1 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-white rounded-lg text-xs font-semibold border border-slate-300 dark:border-slate-700 shadow-sm transition active:scale-95 cursor-pointer">
                                        Aplicar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Filas de Grupos (Sin truncamiento, legibilidad 100%) -->
                        <div class="divide-y divide-slate-200 dark:divide-slate-800/60">
                            @foreach($listaGrupos as $grupo)
                                <div class="group-row px-5 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-white dark:hover:bg-slate-900/60 transition"
                                     data-group-text="{{ strtolower($grupo->course . ' ' . $grupo->name . ' ' . ($grupo->tutor->name ?? 'sin asignar')) }}">
                                    
                                    <!-- Información del Grupo y Tutor -->
                                    <div class="flex-1 min-w-0 pr-2">
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <span class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                                                {{ $grupo->course }} {{ $grupo->name }}
                                            </span>
                                            @if($grupo->aula)
                                                <span class="text-[11px] px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-medium">
                                                    Aula: {{ $grupo->aula->nombre }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 flex-wrap">
                                            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span>Tutor: <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $grupo->tutor->name ?? 'Sin asignar' }}</strong></span>
                                        </div>
                                    </div>

                                    <!-- Selector Numérico con Stepper Ergonómico -->
                                    <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                                        <label class="text-[11px] uppercase font-bold text-slate-500 dark:text-slate-400">Dificultad ($D_g$):</label>
                                        <div class="inline-flex items-center rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 shadow-sm overflow-hidden">
                                            <button type="button" onclick="adjustValue('group_dif_{{ $grupo->id }}', -1)" 
                                                    class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-sm border-r border-slate-300 dark:border-slate-700 transition select-none cursor-pointer">
                                                -
                                            </button>
                                            <input type="number" step="1" id="group_dif_{{ $grupo->id }}" 
                                                   name="grupos[{{ $grupo->id }}][dificultad]" 
                                                   value="{{ $grupo->dificultad ?? 1 }}" 
                                                   data-curso-target="{{ $cursoSlug }}"
                                                   class="w-16 h-8 bg-transparent text-slate-900 dark:text-white text-center text-sm font-bold focus:outline-none focus:bg-slate-50 dark:focus:bg-slate-950">
                                            <button type="button" onclick="adjustValue('group_dif_{{ $grupo->id }}', 1)" 
                                                    class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-sm border-l border-slate-300 dark:border-slate-700 transition select-none cursor-pointer">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-500 text-sm">
                        No hay grupos registrados en este curso escolar.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 3. Zonas y Espacios Comunes -->
        <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl transition-colors">
            <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📍 Zonas y Espacios Comunes (Reagrupamiento & Guardias)</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Configura las zonas de guardia, sus coeficientes de dificultad y sus aforos máximos permitidos.</p>
                </div>
                <button type="button" onclick="openCreateZonaModal()" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 !text-white font-bold text-xs rounded-xl shadow-lg shadow-sky-500/20 transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 !text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span class="!text-white">Añadir Zona / Espacio</span>
                </button>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($zonas as $zona)
                        <div class="bg-slate-50/70 dark:bg-slate-950/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col justify-between gap-3 hover:border-slate-300 dark:hover:border-slate-700 transition shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <label class="block text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 mb-1">Nombre de la Zona:</label>
                                    <input type="text" name="zonas[{{ $zona->id }}][nombre]" value="{{ $zona->nombre }}" 
                                           class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-3 py-1.5 text-slate-900 dark:text-white text-xs font-bold focus:ring-1 focus:ring-sky-500">
                                </div>
                                <button type="button" onclick="deleteZona({{ $zona->id }}, '{{ addslashes($zona->nombre) }}')" 
                                        class="mt-4 p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition cursor-pointer" title="Eliminar Zona">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-200 dark:border-slate-800/80">
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 mb-1">Planta:</label>
                                    <input type="text" name="zonas[{{ $zona->id }}][planta]" value="{{ $zona->planta }}" placeholder="PB" 
                                           class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-slate-900 dark:text-white text-center text-xs font-bold focus:ring-1 focus:ring-sky-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 mb-1">Dificultad:</label>
                                    <input type="number" step="1" name="zonas[{{ $zona->id }}][dificultad]" value="{{ $zona->dificultad ?? 1 }}" 
                                           class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-slate-900 dark:text-white text-center text-xs font-bold focus:ring-1 focus:ring-sky-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 mb-1">Aforo Máx:</label>
                                    <input type="number" min="0" step="1" name="zonas[{{ $zona->id }}][aforo]" value="{{ $zona->aforo }}" placeholder="N/A"
                                           class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-slate-900 dark:text-white text-center text-xs font-bold focus:ring-1 focus:ring-sky-500">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-8 text-slate-500 text-sm">
                            No hay zonas registradas. Haz clic en "Añadir Zona" para registrar una.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Botón Guardar Inferior -->
        <div class="flex justify-end pt-2">
            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 !text-white font-bold rounded-2xl shadow-lg shadow-sky-500/25 transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4 !text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span class="!text-white">Guardar Todos los Cambios</span>
            </button>
        </div>
    </form>
</div>

<!-- Modal para Crear Nueva Zona -->
<div id="modal-crear-zona" class="fixed inset-0 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden transition-colors">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>📍 Añadir Zona o Espacio Común</span>
            </h3>
            <button type="button" onclick="closeCreateZonaModal()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white p-1 cursor-pointer text-lg leading-none">✕</button>
        </div>

        <form action="{{ route('guardias.configuracion.zona.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <!-- Modo Origen -->
            <div>
                <label class="block text-xs uppercase font-bold text-slate-500 dark:text-slate-400 mb-2">Origen del espacio:</label>
                <div class="grid grid-cols-2 gap-3">
                    <label id="label-origin-new" class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-950 border border-sky-500 rounded-xl cursor-pointer hover:bg-sky-500/5 transition">
                        <input type="radio" name="origin" value="new" checked onchange="toggleZonaOriginMode()" class="text-sky-500 focus:ring-sky-500">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">🆕 Nombre nuevo</span>
                    </label>
                    <label id="label-origin-copy" class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800/50 transition">
                        <input type="radio" name="origin" value="copy" onchange="toggleZonaOriginMode()" class="text-sky-500 focus:ring-sky-500">
                        <span class="text-xs font-bold text-slate-900 dark:text-white">📋 Copiar de existente</span>
                    </label>
                </div>
            </div>

            <!-- Selector de Zona Base si es Copia -->
            <div id="container-base-zona" class="hidden">
                <label for="base_zona_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Seleccionar Espacio Base:</label>
                <select id="base_zona_id" name="base_zona_id" onchange="loadBaseZonaData()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white text-sm focus:ring-1 focus:ring-sky-500">
                    <option value="">-- Selecciona una zona base --</option>
                    @if(isset($instituteZones) && count($instituteZones) > 0)
                        <optgroup label="🏛️ Aulas y Espacios del Instituto">
                            @foreach($instituteZones as $iz)
                                <option value="{{ $iz->id }}" 
                                        data-nombre="{{ $iz->nombre }}" 
                                        data-planta="{{ $iz->ubicacion ?? $iz->planta }}" 
                                        data-dificultad="{{ $iz->dificultad ?? 1 }}" 
                                        data-aforo="{{ $iz->aforo ?? $iz->capacidad }}">
                                    📍 {{ $iz->nombre }} {{ $iz->ubicacion ? '('.$iz->ubicacion.')' : '' }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if(isset($zonas) && count($zonas) > 0)
                        <optgroup label="🛡️ Zonas de Guardia Existentes">
                            @foreach($zonas as $z)
                                <option value="{{ $z->id }}" 
                                        data-nombre="{{ $z->nombre }}" 
                                        data-planta="{{ $z->planta }}" 
                                        data-dificultad="{{ $z->dificultad ?? 1 }}" 
                                        data-aforo="{{ $z->aforo }}">
                                    🛡️ {{ $z->nombre }} (Dif: {{ $z->dificultad ?? 1 }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <!-- Nombre -->
            <div>
                <label for="new_zona_nombre" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre del Espacio *:</label>
                <input type="text" id="new_zona_nombre" name="nombre" required placeholder="Ej: Patio Norte, Cafetería, Biblioteca..." 
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white text-sm focus:ring-1 focus:ring-sky-500">
            </div>

            <!-- Planta / Ubicación -->
            <div>
                <label for="new_zona_planta" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Planta / Ubicación:</label>
                <input type="text" id="new_zona_planta" name="planta" placeholder="Ej: Planta Baja, P1, Exterior..." 
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white text-sm focus:ring-1 focus:ring-sky-500">
            </div>

            <!-- Dificultad y Aforo -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="new_zona_dificultad" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Dificultad ($D_g$) *:</label>
                    <input type="number" step="1" id="new_zona_dificultad" name="dificultad" value="1" required 
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white text-center font-bold text-sm focus:ring-1 focus:ring-sky-500">
                </div>
                <div>
                    <label for="new_zona_aforo" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Aforo Máximo:</label>
                    <input type="number" min="0" step="1" id="new_zona_aforo" name="aforo" placeholder="Ej: 50" 
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white text-center font-bold text-sm focus:ring-1 focus:ring-sky-500">
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closeCreateZonaModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-500 !text-white text-xs font-bold rounded-xl shadow-lg shadow-sky-500/20 transition cursor-pointer">
                    <span class="!text-white">Guardar Zona</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form oculto para eliminar zona -->
<form id="delete-zona-form" method="POST" action="" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // Buscador en vivo de grupos y tutores
    document.getElementById('group-search')?.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        const cursoBlocks = document.querySelectorAll('.curso-block');

        cursoBlocks.forEach(block => {
            let hasVisibleRow = false;
            const rows = block.querySelectorAll('.group-row');

            rows.forEach(row => {
                const text = row.getAttribute('data-group-text') || '';
                if (text.includes(query)) {
                    row.style.display = 'flex';
                    hasVisibleRow = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Ocultar el bloque de curso completo si no tiene filas coincidentes
            block.style.display = hasVisibleRow ? 'block' : 'none';
        });
    });

    // Ajuste incremental (+ / -)
    function adjustValue(inputId, delta) {
        const input = document.getElementById(inputId);
        if (input) {
            let val = parseInt(input.value) || 0;
            input.value = val + delta;
        }
    }

    // Aplicar valor masivo por nivel/curso
    function applyBulkDifficulty(cursoSlug) {
        const bulkInput = document.getElementById('bulk-input-' + cursoSlug);
        if (!bulkInput) return;
        const val = bulkInput.value;

        const inputs = document.querySelectorAll(`input[data-curso-target="${cursoSlug}"]`);
        inputs.forEach(input => {
            input.value = val;
        });
    }

    // Modales y gestión de zonas
    function openCreateZonaModal() {
        document.getElementById('modal-crear-zona').classList.remove('hidden');
    }

    function closeCreateZonaModal() {
        document.getElementById('modal-crear-zona').classList.add('hidden');
    }

    function toggleZonaOriginMode() {
        const originVal = document.querySelector('input[name="origin"]:checked').value;
        const containerBase = document.getElementById('container-base-zona');
        const labelNew = document.getElementById('label-origin-new');
        const labelCopy = document.getElementById('label-origin-copy');

        if (originVal === 'copy') {
            containerBase.classList.remove('hidden');
            labelCopy.classList.add('border-sky-500');
            labelCopy.classList.remove('border-slate-200', 'dark:border-slate-800');
            labelNew.classList.remove('border-sky-500');
            labelNew.classList.add('border-slate-200', 'dark:border-slate-800');
            loadBaseZonaData();
        } else {
            containerBase.classList.add('hidden');
            labelNew.classList.add('border-sky-500');
            labelNew.classList.remove('border-slate-200', 'dark:border-slate-800');
            labelCopy.classList.remove('border-sky-500');
            labelCopy.classList.add('border-slate-200', 'dark:border-slate-800');
        }
    }

    function loadBaseZonaData() {
        const select = document.getElementById('base_zona_id');
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption && selectedOption.value) {
            const nombre = selectedOption.getAttribute('data-nombre');
            const planta = selectedOption.getAttribute('data-planta');
            const dificultad = selectedOption.getAttribute('data-dificultad');
            const aforo = selectedOption.getAttribute('data-aforo');

            document.getElementById('new_zona_nombre').value = nombre ? nombre + ' (Copia)' : '';
            document.getElementById('new_zona_planta').value = planta || '';
            document.getElementById('new_zona_dificultad').value = dificultad || 1;
            document.getElementById('new_zona_aforo').value = aforo || '';
        }
    }

    function deleteZona(zonaId, zonaNombre) {
        if (confirm(`¿Estás seguro de que deseas eliminar la zona "${zonaNombre}"?`)) {
            const form = document.getElementById('delete-zona-form');
            form.action = '/guardias/configuracion/zona/' + zonaId;
            form.submit();
        }
    }
</script>
@endsection
