@extends('layouts.app')

@section('title', 'Cuadrante Semanal de Guardias')

@section('content')
<div class="cuadrante-container max-w-7xl mx-auto space-y-6 pb-12">
    
    <!-- Top Header -->
    <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm dark:shadow-2xl transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20 shrink-0">
                    <svg class="w-6 h-6 !text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Cuadrante Semanal de Guardias</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Distribución general de profesores disponibles por día y tramo horario</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('guardias.parte') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-white rounded-xl text-xs font-bold border border-slate-300 dark:border-slate-700 transition">
                    Ver Parte Diario
                </a>
            </div>
        </div>
    </div>

    <!-- Matrix Table (Responsive) -->
    <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                        <th class="py-4 px-4 w-40">Tramo Horario</th>
                        @foreach($days as $dayNum => $dayName)
                            <th class="py-4 px-4 text-center border-l border-slate-200 dark:border-slate-800/80">
                                <span class="text-slate-900 dark:text-white">{{ $dayName }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 text-sm">
                    @forelse($timeSlots as $slot)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/20 transition">
                            <td class="py-4 px-4 bg-slate-50/50 dark:bg-slate-950/40 font-bold text-slate-800 dark:text-slate-200">
                                <div>{{ $slot->name }}</div>
                                <div class="text-[11px] font-normal text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                </div>
                            </td>

                            @foreach($days as $dayNum => $dayName)
                                @php
                                    $selections = $matrix[$slot->id][$dayNum] ?? collect();
                                @endphp
                                <td class="py-3 px-3 border-l border-slate-200 dark:border-slate-800/60 align-top">
                                    @if($selections->isNotEmpty())
                                        <div class="space-y-1.5">
                                            @foreach($selections as $sel)
                                              @php $user = $sel->userSchedule->user ?? null; @endphp
                                              @if($user)
                                                  <div class="p-2 rounded-xl text-xs flex items-center justify-between gap-1.5
                                                       {{ $sel->is_convivencia ? 'bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-500/30 text-purple-700 dark:text-purple-200' : 'bg-slate-100 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700/60 text-slate-800 dark:text-slate-200' }}">
                                                      <div class="flex items-center gap-1.5 overflow-hidden">
                                                          <span class="w-1.5 h-1.5 rounded-full {{ $sel->is_convivencia ? 'bg-purple-500 dark:bg-purple-400' : 'bg-sky-500 dark:bg-sky-400' }}"></span>
                                                          <span class="font-semibold truncate">{{ $user->name }}</span>
                                                      </div>
                                                      @if($sel->is_convivencia)
                                                          <span class="px-1 py-0.2 rounded text-[9px] font-black bg-purple-500 !text-white" title="Aula de Convivencia">C</span>
                                                      @elseif($sel->guardia)
                                                          <span class="text-[9px] text-slate-500 dark:text-slate-400 truncate">{{ $sel->guardia->name }}</span>
                                                      @endif
                                                  </div>
                                              @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-2 text-slate-400 dark:text-slate-600 text-xs italic">
                                            -
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">No hay tramos horarios configurados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
