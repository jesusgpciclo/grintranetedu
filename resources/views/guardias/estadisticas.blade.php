@extends('layouts.app')

@section('title', 'Estadísticas y Equidad de Guardias')

@section('content')
<div class="estadisticas-container max-w-7xl mx-auto space-y-6 pb-12">
    
    <!-- Top Header & Date Filter -->
    <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm dark:shadow-2xl transition-colors">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-purple-500/20 shrink-0">
                    <svg class="w-6 h-6 !text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Estadísticas y Equidad</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Métricas de absentismo, cobertura y balance de equidad en el claustro</p>
                </div>
            </div>

            <!-- Date Range Filter Form -->
            <form action="{{ route('guardias.estadisticas') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Desde:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent text-slate-900 dark:text-white text-xs outline-none font-medium">
                </div>
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700">
                    <span class="text-xs text-slate-500 dark:text-slate-400">Hasta:</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent text-slate-900 dark:text-white text-xs outline-none font-medium">
                </div>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 !text-white font-bold text-xs rounded-xl transition shadow-md shadow-purple-600/20 cursor-pointer">
                    <span class="!text-white">Filtrar</span>
                </button>
                @if($startDate || $endDate)
                    <a href="{{ route('guardias.estadisticas') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs rounded-xl transition font-medium">
                        Limpiar
                    </a>
                @endif
            </form>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6 pt-5 border-t border-slate-200 dark:border-slate-800/80">
            <div class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-4 border border-slate-200 dark:border-slate-800">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Ausencias</div>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1">{{ $stats['total_ausencias'] }}</div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">{{ $stats['pendientes'] }} pendientes</div>
            </div>

            <div class="bg-emerald-500/10 rounded-2xl p-4 border border-emerald-500/20">
                <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Tasa de Cobertura</div>
                <div class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['tasa_cobertura'] }}%</div>
                <div class="text-[11px] text-emerald-600/80 dark:text-emerald-400/80 mt-1">{{ $stats['cubiertas'] }} cubiertas</div>
            </div>

            <div class="bg-sky-500/10 rounded-2xl p-4 border border-sky-500/20">
                <div class="text-xs font-semibold text-sky-600 dark:text-sky-400 uppercase tracking-wider">Justificaciones</div>
                <div class="text-2xl sm:text-3xl font-black text-sky-600 dark:text-sky-400 mt-1">{{ $stats['tasa_justificacion'] }}%</div>
                <div class="text-[11px] text-sky-600/80 dark:text-sky-400/80 mt-1">{{ $stats['justificadas'] }} justificadas</div>
            </div>

            <div class="bg-amber-500/10 rounded-2xl p-4 border border-amber-500/20">
                <div class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Media Guardias/Profesor</div>
                <div class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $stats['promedio_guardias'] }}</div>
                <div class="text-[11px] text-amber-600/80 dark:text-amber-400/80 mt-1">Límite desbalance: > 2h</div>
            </div>
        </div>
    </div>

    <!-- Charts Row: Weekdays & TimeSlots Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Absences by Day of Week -->
        <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-xl transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <span>📅 Ausencias por Día de la Semana</span>
            </h2>
            <div class="space-y-3">
                @php $maxDay = max(array_values($stats['dias_semana']) ?: [1]); @endphp
                @foreach($stats['dias_semana'] as $dia => $count)
                    @php $pct = $maxDay > 0 ? round(($count / $maxDay) * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-slate-600 dark:text-slate-300">{{ $dia }}</span>
                            <span class="text-slate-900 dark:text-white font-bold">{{ $count }} ausencia(s)</span>
                        </div>
                        <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full transition-all duration-500" style="width: {{ max($pct, 3) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Absences by Time Slot -->
        <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-xl transition-colors">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <span>⏰ Ausencias por Tramo Horario</span>
            </h2>
            <div class="space-y-3">
                @php $maxSlot = max(array_values($stats['ausencias_por_tramo']) ?: [1]); @endphp
                @foreach($stats['ausencias_por_tramo'] as $slotName => $count)
                    @php $pct = $maxSlot > 0 ? round(($count / $maxSlot) * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1">
                            <span class="text-slate-600 dark:text-slate-300">{{ $slotName }}</span>
                            <span class="text-slate-900 dark:text-white font-bold">{{ $count }}</span>
                        </div>
                        <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-sky-500 to-blue-500 rounded-full transition-all duration-500" style="width: {{ max($pct, 3) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Equity Ranking Table -->
    <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl transition-colors">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>⚖️ Balance y Ranking de Equidad del Claustro</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Control de reparto justo de horas para evitar sobrecarga y desequilibrios</p>
            </div>
            <span class="text-xs text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-full border border-slate-200 dark:border-slate-700 font-medium">
                Media: {{ $stats['promedio_guardias'] }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/60 dark:bg-slate-800/30 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3.5 px-5">Profesor</th>
                        <th class="py-3.5 px-4 text-center">Guardias Realizadas</th>
                        <th class="py-3.5 px-4 text-center">Puntos Carga ($P$)</th>
                        <th class="py-3.5 px-4 text-center">Desviación s/ Media</th>
                        <th class="py-3.5 px-5 text-right">Estado Equidad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                    @forelse($stats['ranking_equidad'] as $item)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/20 transition">
                            <td class="py-3.5 px-5 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($item['name'], 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $item['name'] }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-extrabold text-slate-800 dark:text-slate-200">
                                {{ $item['guardias_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-extrabold text-sky-600 dark:text-sky-400">
                                {{ $item['puntuacion'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center text-xs font-semibold {{ $item['diferencia_media'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }}">
                                {{ $item['diferencia_media'] > 0 ? '+' : '' }}{{ $item['diferencia_media'] }}
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                @if($item['desbalance'])
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/20">
                                        ⚠️ Desbalance > 2h
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                                        ✓ Equilibrado
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">No hay registros de profesores.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
