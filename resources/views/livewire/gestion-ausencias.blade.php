<div class="gestion-ausencias-livewire space-y-6">

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- 1. CABECERA Y FILTROS REACTIVOS                               -->
    <!-- ───────────────────────────────────────────────────────────── -->
    <div class="card p-5 sm:p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
            
            <!-- Título y Fecha -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/25 shrink-0 text-xl font-black">
                    📅
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Gestión de Ausencias
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        {{ ucfirst($carbonDate->isoFormat('dddd, D [de] MMMM [de] YYYY')) }}
                    </p>
                </div>
            </div>

            <!-- Navegación por Días y Controles Reactivos -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                
                <!-- Navegación Día Anterior / Hoy / Siguiente -->
                <div class="inline-flex items-center bg-slate-100 dark:bg-slate-800 p-1 rounded-2xl border border-slate-200 dark:border-slate-700">
                    <button type="button" 
                            wire:click="previousDay" 
                            class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-sky-600 dark:hover:text-sky-400 transition shadow-xs" 
                            title="Día anterior">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    <button type="button" 
                            wire:click="today" 
                            class="px-3 py-1 text-xs font-black uppercase text-slate-700 dark:text-slate-200 hover:text-sky-600 transition"
                            title="Ir al día actual">
                        Hoy
                    </button>

                    <button type="button" 
                            wire:click="nextDay" 
                            class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-sky-600 dark:hover:text-sky-400 transition shadow-xs" 
                            title="Día siguiente">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Input Fecha Reactivo -->
                <input type="date" 
                       wire:model.live="date" 
                       class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl px-3 py-2 cursor-pointer outline-none focus:ring-2 focus:ring-sky-500">

                <!-- Filtro de Estado Desplegable -->
                <select wire:model.live="filterStatus" 
                        class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="all">🔍 Todas las ausencias</option>
                    <option value="pendientes">⏳ Solo pendientes de cubrir</option>
                    <option value="justificadas">✓ Solo justificadas</option>
                    <option value="sin_justificar">⚠️ Solo sin justificar</option>
                </select>

                <!-- Filtro de Ámbito para Directiva -->
                @if($isDirectiva)
                    <select wire:model.live="viewScope" 
                            class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="all">👥 Todo el claustro</option>
                        <option value="mine">👤 Mis ausencias</option>
                    </select>
                @endif

                <!-- Enlace al Parte Diario en vivo -->
                <a href="{{ route('guardias.parte', ['date' => $date]) }}" 
                   class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs border border-slate-200 dark:border-slate-700 transition flex items-center gap-1.5">
                    <span>🛡️</span>
                    <span>Parte de Guardia</span>
                </a>
            </div>

        </div>
    </div>

    <!-- Alertas Flash -->
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

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- 2. REJILLA DINÁMICA POR HORAS                                 -->
    <!-- ───────────────────────────────────────────────────────────── -->
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

                <!-- Columna Derecha: Tarjetas de Ausencias en Rejilla Fluida (280px a 340px) -->
                <div class="flex-1">
                    @if($slotAusencias->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-[repeat(auto-fill,minmax(280px,340px))] gap-4">
                            @foreach($slotAusencias as $ausencia)
                                @php
                                    $isCovered = $ausencia->isCubierta();
                                    $hasGuardiaUser = !empty($ausencia->guardia_user_id);
                                    $isConfirmed = $isCovered && $hasGuardiaUser;

                                    $link = $ausencia->enlace_tarea;
                                    $isClassroom = $link && (str_contains($link, 'classroom.google') || str_contains($link, 'drive.google'));
                                    $isFile = $link && (preg_match('/\.(pdf|docx?|xlsx?|zip|rar)$/i', $link) || str_contains($link, 'download') || str_contains($link, 'storage'));
                                    $canDelete = $ausencia->canBeDeletedBy(Auth::user());
                                @endphp

                                <!-- Tarjeta de Ausencia Individual (Card) -->
                                <div class="card p-4 sm:p-5 rounded-2xl border transition-all duration-200 {{ $isCovered ? 'bg-emerald-50/15 dark:bg-emerald-950/10 border-emerald-200/80 dark:border-emerald-800/60' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }} shadow-sm hover:shadow-md flex flex-col justify-between gap-3.5">
                                    
                                    <div>
                                        <!-- Cabecera: Grupo en negrita + Badge de Estado -->
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
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span>{{ $ausencia->zona ? $ausencia->zona->nombre : 'Aula habitual' }}</span>
                                            </div>
                                        @endif

                                        <!-- Docente Ausente -->
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
                                                @if($ausencia->justificacion_nota)
                                                    <div class="text-[0.68rem] text-slate-500 dark:text-slate-400 truncate">
                                                        Motivo: {{ $ausencia->justificacion_nota }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Sección Tarea: Texto recortado a 2 líneas (line-clamp-2) y badges de adjuntos -->
                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/60 text-xs">
                                            <div class="text-[0.68rem] font-extrabold uppercase text-slate-400 mb-1 flex items-center justify-between">
                                                <span>📝 Tarea pedagógica:</span>
                                            </div>

                                            <div class="text-slate-700 dark:text-slate-300 font-medium" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; min-height: 2.8em;">
                                                {{ $ausencia->tarea ?: 'Sin tarea especificada.' }}
                                            </div>

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

                                    <!-- Pie de Card: Justificación + Acciones -->
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
                                        <div class="flex items-center gap-1">
                                            @if($canDelete)
                                                <a href="{{ route('ausencias.edit', $ausencia) }}" 
                                                   class="p-1.5 rounded-lg text-slate-500 hover:text-sky-600 dark:text-slate-400 dark:hover:text-sky-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" 
                                                   title="Editar ausencia"
                                                   aria-label="Editar ausencia">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>

                                                <button type="button" 
                                                        wire:click="confirmDelete({{ $ausencia->id }})" 
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors" 
                                                        title="Borrar ausencia"
                                                        aria-label="Eliminar ausencia">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @else
                                                <span class="text-[0.65rem] text-slate-400 italic" title="No modificable tras iniciar el tramo o confirmada la guardia">
                                                    🔒 Bloqueada
                                                </span>
                                            @endif
                                        </div>

                                    </div>

                                </div>
                            @endforeach

                            <!-- Slot Punteado "AÑADIR EN ESTE TRAMO" al final de las tarjetas activas -->
                            <button type="button" 
                                    wire:click="openCreateModal({{ $slot->id }})"
                                    class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-sky-500 dark:hover:border-sky-500 rounded-2xl p-5 flex flex-col items-center justify-center gap-2 text-slate-500 hover:text-sky-600 dark:text-slate-400 dark:hover:text-sky-400 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-sky-50/40 dark:hover:bg-sky-950/20 transition-all duration-200 group text-center min-h-[220px]">
                                <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 group-hover:text-sky-500 group-hover:scale-110 shadow-xs transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-black uppercase tracking-wider">Añadir en este tramo</span>
                                <span class="text-[0.68rem] text-slate-400 font-medium">+ Comunicar falta</span>
                            </button>

                        </div>
                    @else
                        <!-- Fila Colapsada con Estilo Dashed en tramo sin ausencias -->
                        <button type="button" 
                                wire:click="openCreateModal({{ $slot->id }})"
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
                        </button>
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

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- 3. MODAL DE CREACIÓN RÁPIDA POR TRAMO                         -->
    <!-- ───────────────────────────────────────────────────────────── -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
             x-data
             @keydown.escape.window="$wire.closeCreateModal()">
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-xl shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                
                <!-- Cabecera del Modal -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/40">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-sky-500 text-white flex items-center justify-center font-black text-sm">
                            +
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">
                                Nueva Ausencia
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">
                                {{ $selectedSlot ? $selectedSlot->name : 'Tramo horario' }} · {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>

                    <button type="button" 
                            wire:click="closeCreateModal" 
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        ✕
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit.prevent="saveAusencia" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                    
                    <!-- Selección de Docente -->
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1.5">
                            Docente Ausente <span class="text-rose-500">*</span>
                        </label>
                        @if($isDirectiva)
                            <div class="space-y-1.5">
                                <input type="text" 
                                       wire:model.live.debounce.300ms="searchTeacher" 
                                       placeholder="Buscar profesor por nombre o apellido..." 
                                       class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-sky-500">
                                
                                <select wire:model="user_id" 
                                        class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-sky-500">
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">
                                            {{ $teacher->name }} {{ $teacher->last_name }} ({{ $teacher->departamento ?: 'Docente' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs font-bold text-slate-800 dark:text-slate-200">
                                <span>{{ Auth::user()->name }} {{ Auth::user()->last_name }}</span>
                                <span class="text-[0.65rem] px-2 py-0.5 rounded-md bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300">Tú (Docente)</span>
                            </div>
                        @endif
                        @error('user_id') <span class="text-rose-500 text-[0.68rem] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Grupo y Aula (en fila) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1.5">
                                Grupo
                            </label>
                            <select wire:model="group_id" 
                                    class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-sky-500">
                                <option value="">-- Sin grupo / Guardia --</option>
                                @foreach($groups as $grp)
                                    <option value="{{ $grp->id }}">{{ $grp->course }} {{ $grp->name }}</option>
                                @endforeach
                            </select>
                            @error('group_id') <span class="text-rose-500 text-[0.68rem] font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1.5">
                                Aula / Zona
                            </label>
                            <select wire:model="zona_id" 
                                    class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-sky-500">
                                <option value="">-- Aula habitual / No definida --</option>
                                @foreach($aulas as $aula)
                                    <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                                @endforeach
                            </select>
                            @error('zona_id') <span class="text-rose-500 text-[0.68rem] font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Motivo de la Ausencia -->
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1.5">
                            Motivo de la Ausencia
                        </label>
                        <select wire:model="motivo" 
                                class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-sky-500">
                            @foreach($motivosComunes as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tarea Pedagógica -->
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1.5">
                            Tarea Pedagógica para el Aula
                        </label>
                        <textarea wire:model="tarea" 
                                  rows="3" 
                                  placeholder="Indica qué trabajo, ejercicios o instrucciones debe supervisar el profesor de guardia..." 
                                  class="w-full text-xs p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                        @error('tarea') <span class="text-rose-500 text-[0.68rem] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Enlace de Tarea (Classroom / Drive / Web) -->
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1.5">
                            Enlace de la Tarea (Classroom, Drive, etc.)
                        </label>
                        <input type="url" 
                               wire:model="enlace_tarea" 
                               placeholder="https://classroom.google.com/..." 
                               class="w-full text-xs p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-sky-500">
                        @error('enlace_tarea') <span class="text-rose-500 text-[0.68rem] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Subida de Archivo Opcional (PDF / Imagen) -->
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1.5">
                            Adjuntar Archivo (PDF o Imagen, máx. 10MB)
                        </label>
                        <input type="file" 
                               wire:model="archivo" 
                               class="w-full text-xs p-2 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 text-slate-600 dark:text-slate-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 dark:file:bg-sky-950 dark:file:text-sky-300 cursor-pointer">
                        
                        <div wire:loading wire:target="archivo" class="text-sky-600 text-xs font-bold mt-1">
                            ⏳ Subiendo archivo adjunto...
                        </div>
                        @error('archivo') <span class="text-rose-500 text-[0.68rem] font-bold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Botones de Acción del Modal -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                        <button type="button" 
                                wire:click="closeCreateModal" 
                                class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Cancelar
                        </button>
                        
                        <button type="submit" 
                                wire:loading.attr="disabled" 
                                class="px-5 py-2.5 rounded-xl text-xs font-extrabold bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-md shadow-sky-500/25 hover:from-sky-500 hover:to-indigo-500 transition active:scale-95 flex items-center gap-2">
                            <span wire:loading.remove wire:target="saveAusencia">Guardar Ausencia</span>
                            <span wire:loading wire:target="saveAusencia">Guardando...</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- 4. MODAL DE CONFIRMACIÓN DE BORRADO                          -->
    <!-- ───────────────────────────────────────────────────────────── -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
             x-data
             @keydown.escape.window="$wire.set('showDeleteModal', false)">
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4 animate-in fade-in zoom-in-95 duration-200">
                
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center text-xl font-bold mx-auto">
                    ⚠️
                </div>

                <div class="text-center space-y-1">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">
                        ¿Eliminar esta ausencia?
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Esta acción retirará la falta del cuadrante de guardias del centro. Recuerda que no podrás eliminarla una vez iniciada la franja horaria.
                    </p>
                </div>

                <div class="pt-2 flex items-center justify-center gap-3">
                    <button type="button" 
                            wire:click="$set('showDeleteModal', false)" 
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancelar
                    </button>

                    <button type="button" 
                            wire:click="deleteAusencia" 
                            class="px-5 py-2.5 rounded-xl text-xs font-extrabold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-500/25 transition active:scale-95">
                        Sí, eliminar ausencia
                    </button>
                </div>

            </div>
        </div>
    @endif

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- 5. SCRIPT DE INTEGRACIÓN SWEETALERT2 / ALPINE.JS               -->
    <!-- ───────────────────────────────────────────────────────────── -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('swal:alert', (data) => {
                const payload = Array.isArray(data) ? data[0] : data;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: payload.type || 'info',
                        title: payload.title || '',
                        text: payload.text || '',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                    });
                }
            });
        });
    </script>

</div>
