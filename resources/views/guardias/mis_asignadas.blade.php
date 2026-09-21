@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="card p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/25 shrink-0 text-xl font-black">
                    🛡️
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Mis Guardias Asignadas
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        Consulta y gestiona las guardias que tienes encomendadas hoy y tu historial acumulado.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('guardias.parte') }}" class="btn btn-primary text-xs font-extrabold px-3.5 py-2 rounded-xl flex items-center gap-2">
                    <span>📋</span>
                    <span>Ver Parte Diario en Vivo</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Guardias de Hoy -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                <span>⚡ Guardias para hoy</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-extrabold {{ $guardiasHoy->isNotEmpty() ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                    {{ $guardiasHoy->count() }}
                </span>
            </h2>
            <span class="text-xs text-slate-400 font-medium">{{ \Carbon\Carbon::parse($today)->isoFormat('dddd, D [de] MMMM') }}</span>
        </div>

        @if($guardiasHoy->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($guardiasHoy as $g)
                    <div class="card p-5 bg-white dark:bg-slate-900 border-2 border-sky-400/60 dark:border-sky-600/50 rounded-2xl shadow-sm flex flex-col justify-between gap-4">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="px-2.5 py-1 rounded-xl text-xs font-black bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300">
                                    {{ $g->timeSlot->name ?? 'Hora' }} ({{ \Carbon\Carbon::parse($g->timeSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($g->timeSlot->end_time)->format('H:i') }})
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[0.68rem] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    ✓ Asignada
                                </span>
                            </div>

                            <div class="text-base font-black text-slate-900 dark:text-white mb-1">
                                🏫 {{ $g->group ? ($g->group->course . ' ' . $g->group->name) : 'Sin grupo' }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-1.5">
                                <span>📍 Aula:</span>
                                <strong class="text-slate-700 dark:text-slate-300">{{ $g->zona->nombre ?? 'Aula habitual' }}</strong>
                            </div>

                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-xs">
                                <div class="text-[0.68rem] font-bold uppercase text-slate-400 mb-0.5">Sustituyendo a:</div>
                                <div class="font-extrabold text-slate-800 dark:text-slate-200">
                                    {{ $g->user->name ?? 'Docente ausente' }} {{ $g->user->last_name ?? '' }}
                                </div>
                                @if($g->tarea)
                                    <div class="mt-2 pt-2 border-t border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 italic">
                                        "{{ Str::limit($g->tarea, 90) }}"
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                            <a href="{{ route('guardias.parte', ['slot' => $g->time_slot_id]) }}" class="btn btn-secondary text-xs font-bold py-1.5 px-3 rounded-xl">
                                Ver en el Parte &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-center">
                <div class="text-3xl mb-2">🎉</div>
                <h3 class="font-bold text-slate-900 dark:text-white text-sm">No tienes guardias asignadas para hoy</h3>
                <p class="text-xs text-slate-400 mt-1">Si se produce alguna ausencia en tus horas de guardia, aparecerá reflejada aquí.</p>
            </div>
        @endif
    </div>

    <!-- Historial de Guardias Realizadas -->
    <div class="space-y-3 pt-4">
        <h2 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
            <span>📜 Histórico de Guardias Realizadas</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                {{ $historialGuardias->total() }}
            </span>
        </h2>

        <div class="card p-0 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="table-responsive">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-black text-[0.7rem] tracking-wider">
                        <tr>
                            <th class="p-3.5 pl-5">Fecha</th>
                            <th class="p-3.5">Hora</th>
                            <th class="p-3.5">Docente Sustituido</th>
                            <th class="p-3.5">Grupo / Aula</th>
                            <th class="p-3.5">Dificultad</th>
                            <th class="p-3.5 pr-5 text-right">Confirmación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($historialGuardias as $hist)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="p-3.5 pl-5 font-bold text-slate-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($hist->fecha)->format('d/m/Y') }}
                                </td>
                                <td class="p-3.5">
                                    {{ $hist->timeSlot->name ?? 'Hora' }}
                                </td>
                                <td class="p-3.5">
                                    {{ $hist->user->name ?? 'Docente' }} {{ $hist->user->last_name ?? '' }}
                                </td>
                                <td class="p-3.5">
                                    <span class="font-bold text-slate-900 dark:text-white">
                                        {{ $hist->group ? ($hist->group->course . ' ' . $hist->group->name) : 'Sin grupo' }}
                                    </span>
                                    @if($hist->zona)
                                        <span class="text-xs text-slate-400">({{ $hist->zona->nombre }})</span>
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-black bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $hist->group->dificultad ?? $hist->zona->dificultad ?? 1 }} pts
                                    </span>
                                </td>
                                <td class="p-3.5 pr-5 text-right">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                        ✓ Realizada
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 text-xs">
                                    Aún no has cubierto ninguna guardia en el curso escolar activo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($historialGuardias->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $historialGuardias->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
