@extends('layouts.app')

@section('title', 'Gestión de Ausencias - ' . \Carbon\Carbon::parse($date)->format('d/m/Y'))

@section('content')
<div class="ausencias-index-container max-w-7xl mx-auto space-y-6">

    <!-- Top Header Card -->
    <div class="card p-5 sm:p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/25 shrink-0 text-xl font-black">
                    📅
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Gestión de Ausencias
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        {{ ucfirst(\Carbon\Carbon::parse($date)->isoFormat('dddd, D [de] MMMM [de] YYYY')) }}
                    </p>
                </div>
            </div>

            <!-- Action Controls & Date Picker -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('guardias.parte', ['date' => $date]) }}" 
                   class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs border border-slate-200 dark:border-slate-700 transition flex items-center gap-2">
                    <span>🛡️</span>
                    <span>Parte de Guardia</span>
                </a>

                <form action="{{ route('ausencias.index') }}" method="GET" class="inline-flex items-center gap-2">
                    @if($isDirectiva)
                        <select name="filter" onchange="this.form.submit()" 
                                class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="all" {{ ($viewFilter ?? 'all') === 'all' ? 'selected' : '' }}>Todas las ausencias</option>
                            <option value="mine" {{ ($viewFilter ?? '') === 'mine' ? 'selected' : '' }}>Mis ausencias</option>
                        </select>
                    @endif
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                           class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl px-3 py-2 cursor-pointer outline-none focus:ring-2 focus:ring-sky-500">
                </form>

                <a href="{{ route('ausencias.create', ['date' => $date]) }}" 
                   class="btn btn-primary text-xs font-extrabold px-4 py-2 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-md shadow-sky-500/20 flex items-center gap-2 transition active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Nueva Ausencia</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="font-bold text-xs sm:text-sm">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-2xl flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span class="font-bold text-xs sm:text-sm">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Rejilla Dinámica por Horas -->
    <div class="space-y-4">
        @forelse($timeSlots as $slot)
            @php 
                $slotAusencias = $ausencias->get($slot->id, collect()); 
            @endphp

            <div class="flex flex-col lg:flex-row gap-4 items-stretch">
                
                <!-- Columna Izquierda: Tramo Horario -->
                <div class="lg:w-52 p-4 rounded-2xl border flex lg:flex-col items-center lg:items-start justify-between lg:justify-center shrink-0 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-xs">
                    <span class="text-base font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $slot->name }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                    </span>
                    <span class="mt-2 text-[10px] uppercase font-black px-2.5 py-0.5 rounded-full {{ $slotAusencias->isNotEmpty() ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700' }}">
                        {{ $slotAusencias->count() }} {{ $slotAusencias->count() === 1 ? 'ausencia' : 'ausencias' }}
                    </span>
                </div>

                <!-- Columna Derecha: Tarjetas de Ausencia en Rejilla Fluida (280px a 340px) -->
                <div class="flex-1">
                    @if($slotAusencias->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-[repeat(auto-fill,minmax(280px,340px))] gap-4">
                            @foreach($slotAusencias as $ausencia)
                                @php
                                    $isCovered = $ausencia->isCubierta();
                                    $hasGuardiaUser = !empty($ausencia->guardia_user_id);
                                    $isConfirmed = $isCovered && $hasGuardiaUser;

                                    // Detección de enlaces o archivos
                                    $link = $ausencia->enlace_tarea;
                                    $isClassroom = $link && (str_contains($link, 'classroom.google') || str_contains($link, 'drive.google'));
                                    $isFile = $link && (preg_match('/\.(pdf|docx?|xlsx?|zip|rar)$/i', $link) || str_contains($link, 'download'));
                                @endphp

                                <!-- Tarjeta de Ausencia Individual (Card) -->
                                <div class="card p-4 sm:p-5 rounded-2xl border transition-all duration-200 {{ $isCovered ? 'bg-emerald-50/15 dark:bg-emerald-950/10 border-emerald-200/80 dark:border-emerald-800/60' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }} shadow-sm hover:shadow-md flex flex-col justify-between gap-3.5">
                                    
                                    <div>
                                        <!-- Cabecera: Nombre del Grupo en negrita + Badge de Estado -->
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="overflow-hidden">
                                                @if($ausencia->es_guardia)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[0.68rem] font-black uppercase bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                        🛡️ Guardia de Recreo / Aula
                                                    </span>
                                                @else
                                                    <div class="font-black text-sm sm:text-base text-slate-900 dark:text-white leading-tight truncate">
                                                        {{ $ausencia->group ? ($ausencia->group->course . ' ' . $ausencia->group->name) : 'Sin grupo asignado' }}
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Badge de Estado -->
                                            <div class="shrink-0">
                                                @if($isConfirmed)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.68rem] font-black bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800/80">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                        Guardia Confirmada
                                                    </span>
                                                @elseif($isCovered)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.68rem] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80">
                                                        ✓ Cubierta
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.68rem] font-black bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80">
                                                        <span class="relative flex h-1.5 w-1.5">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-rose-500"></span>
                                                        </span>
                                                        Sin cubrir
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Ubicación: Icono de pin con Aula Asignada -->
                                        @if(!$ausencia->es_guardia)
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-sky-600 dark:text-sky-400 mb-3">
                                                <!-- Map Pin Icon -->
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>{{ $ausencia->zona ? $ausencia->zona->nombre : 'Aula habitual' }}</span>
                                            </div>
                                        @endif

                                        <!-- Docente: Avatar circular + Nombre completo -->
                                        <div class="flex items-center gap-2.5 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 mb-3">
                                            @if($ausencia->user->avatar_url)
                                                <img src="{{ $ausencia->user->avatar_url }}" alt="" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-700">
                                            @else
                                                <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-600 text-white font-extrabold text-[0.68rem] flex items-center justify-center shrink-0">
                                                    {{ strtoupper(substr($ausencia->user->name ?? 'P', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="overflow-hidden">
                                                <div class="text-[0.65rem] font-bold uppercase tracking-wider text-slate-400">Profesor ausente</div>
                                                <div class="font-extrabold text-xs text-slate-900 dark:text-white truncate">
                                                    {{ $ausencia->user->name ?? 'Docente' }} {{ $ausencia->user->last_name ?? '' }}
                                                </div>
                                                @if($ausencia->user->departamento)
                                                    <div class="text-[0.68rem] text-slate-500 dark:text-slate-400 truncate">
                                                        {{ $ausencia->user->departamento }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Sección Tarea: Caja interior con texto resumido a 2 líneas (line-clamp-2) y badges de enlaces -->
                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 text-xs">
                                            <div class="text-[0.68rem] font-extrabold uppercase text-slate-400 mb-1 flex items-center justify-between">
                                                <span>📝 Tarea:</span>
                                            </div>

                                            <div class="text-slate-700 dark:text-slate-300 font-medium" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; min-height: 2.8em;">
                                                {{ $ausencia->tarea ?: 'Sin tarea especificada.' }}
                                            </div>

                                            <!-- Badges pequeños si contiene enlaces o archivos adjuntos -->
                                            @if($ausencia->enlace_tarea)
                                                <div class="mt-2 pt-2 border-t border-slate-200/70 dark:border-slate-700/60 flex items-center gap-1.5 flex-wrap">
                                                    <a href="{{ $ausencia->enlace_tarea }}" target="_blank" rel="noopener noreferrer"
                                                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[0.68rem] font-bold bg-sky-50 text-sky-700 hover:bg-sky-100 dark:bg-sky-950/50 dark:text-sky-300 dark:hover:bg-sky-900/50 border border-sky-200 dark:border-sky-800 transition-colors">
                                                        @if($isClassroom)
                                                            <span>🔗</span>
                                                            <span>[🔗 Classroom]</span>
                                                        @elseif($isFile)
                                                            <span>📎</span>
                                                            <span>[📎 Archivo]</span>
                                                        @else
                                                            <span>🔗</span>
                                                            <span>[🔗 Enlace]</span>
                                                        @endif
                                                    </a>
                                                </div>
                                            @endif
                                        </div>

                                    </div>

                                    <!-- Pie de Card: Badge de Justificación + Botones de Acción (Editar / Borrar) -->
                                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                                        
                                        <!-- Badge de Justificación -->
                                        <div>
                                            @if($ausencia->justificada)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.68rem] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                                    ✓ Justificada
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[0.68rem] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                    ⏳ Justif. Pendiente
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Botones de Acción Accesibles -->
                                        @if($ausencia->canBeDeletedBy(Auth::user()))
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('ausencias.edit', $ausencia) }}" 
                                                   class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 dark:text-slate-400 dark:hover:text-sky-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" 
                                                   title="Editar ausencia"
                                                   aria-label="Editar ausencia de {{ $ausencia->user->name ?? 'docente' }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>

                                                <form action="{{ route('ausencias.destroy', $ausencia) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta ausencia del parte?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" 
                                                            title="Borrar ausencia"
                                                            aria-label="Eliminar ausencia de {{ $ausencia->user->name ?? 'docente' }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif

                                    </div>

                                </div>
                            @endforeach

                            <!-- Botón/Slot Punteado Compacto "AÑADIR EN ESTE TRAMO" al final de las tarjetas activas -->
                            <a href="{{ route('ausencias.create', ['date' => $date, 'time_slot_id' => $slot->id]) }}"
                               class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-sky-500 dark:hover:border-sky-500 rounded-2xl p-5 flex flex-col items-center justify-center gap-2 text-slate-500 hover:text-sky-600 dark:text-slate-400 dark:hover:text-sky-400 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-sky-50/40 dark:hover:bg-sky-950/20 transition-all duration-200 group text-center min-h-[220px]">
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 group-hover:text-sky-500 group-hover:scale-110 shadow-xs transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-black uppercase tracking-wider">Añadir en este tramo</span>
                                <span class="text-[0.68rem] text-slate-400 font-medium">+ Comunicar falta</span>
                            </a>

                        </div>
                    @else
                        <!-- Fila Colapsada y Elegante con estilo Dashed si no hay ausencias -->
                        <a href="{{ route('ausencias.create', ['date' => $date, 'time_slot_id' => $slot->id]) }}"
                           class="flex items-center justify-between w-full h-14 sm:h-16 px-4 sm:px-5 border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-sky-500/80 dark:hover:border-sky-500/80 rounded-2xl bg-slate-50/50 dark:bg-slate-900/30 hover:bg-sky-50/40 dark:hover:bg-sky-950/20 text-slate-500 hover:text-sky-600 dark:text-slate-400 dark:hover:text-sky-400 transition-all duration-200 group">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 group-hover:text-sky-500 flex items-center justify-center font-black text-sm transition-transform group-hover:scale-110">
                                    +
                                </div>
                                <div class="text-left">
                                    <span class="font-black text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300 group-hover:text-sky-600 dark:group-hover:text-sky-400">
                                        Sin ausencias en este tramo
                                    </span>
                                    <span class="hidden sm:inline text-xs text-slate-400 ml-2">· Pulsa para comunicar falta o tarea</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-sky-600 dark:text-sky-400 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                <span>Añadir falta</span>
                                <span>&rarr;</span>
                            </span>
                        </a>
                    @endif
                </div>

            </div>
        @empty
            <div class="card p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl">
                <div class="text-4xl mb-3">🕒</div>
                <h3 class="font-bold text-slate-900 dark:text-white text-base">No hay tramos horarios configurados</h3>
                <p class="text-slate-500 text-xs mt-1">Crea una plantilla de horarios en el panel del centro.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection