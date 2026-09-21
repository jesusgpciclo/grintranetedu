<div class="parte-container max-w-7xl mx-auto space-y-6" wire:poll.15s>
    
    <!-- Top Header Card con Livewire -->
    <div class="card p-5 sm:p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            
            <!-- Title & Live Indicator -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/25 shrink-0 text-xl font-black">
                    🛡️
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Parte de Guardia</h1>
                        @if($isToday)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black uppercase bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                EN VIVO
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        {{ ucfirst($carbonDate->isoFormat('dddd, D [de] MMMM [de] YYYY')) }}
                    </p>
                </div>
            </div>

            <!-- Polling Status & Actions -->
            <div class="flex items-center gap-2 flex-wrap">
                <div wire:loading class="text-xs text-sky-500 dark:text-sky-400 font-semibold flex items-center gap-1.5 px-2 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800">
                    <svg class="animate-spin h-3.5 w-3.5 text-sky-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Sincronizando...
                </div>

                <button type="button" onclick="openApuntarModal()" class="btn btn-primary text-xs font-extrabold px-3 py-2 rounded-xl bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-md shadow-sky-500/20 flex items-center gap-1.5">
                    <span>📝</span>
                    <span>Apuntar en el parte</span>
                </button>
            </div>
        </div>

        <!-- Metrics Grid (Actualización en tiempo real vía Livewire) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-6 pt-5 border-t border-slate-100 dark:border-slate-800/80">
            
            <div class="p-4 rounded-2xl border border-sky-200/60 dark:border-sky-900/50 bg-sky-50/40 dark:bg-sky-950/20 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Total Ausencias</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $totalDay }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-100 dark:bg-sky-900/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg">
                    📋
                </div>
            </div>

            <div class="p-4 rounded-2xl border border-emerald-200/60 dark:border-emerald-900/50 bg-emerald-50/40 dark:bg-emerald-950/20 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Cubiertas</span>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $cubiertasDay }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    ✓
                </div>
            </div>

            <div class="p-4 rounded-2xl border border-rose-200/60 dark:border-rose-900/50 bg-rose-50/40 dark:bg-rose-950/20 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider">Sin Cubrir</span>
                    <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">{{ $sinCubrirDay }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                    ⏳
                </div>
            </div>

        </div>
    </div>

    <!-- Navegador y Filtro Reactivo por Horas -->
    <div class="card p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-x-auto">
        <div class="flex items-center gap-2 min-w-max">
            <span class="text-xs font-black uppercase text-slate-400 dark:text-slate-500 mr-1 shrink-0">Filtrar Hora:</span>

            <button type="button" 
                    wire:click="filtrarHora('all')" 
                    class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all {{ $filterSlotId === 'all' ? 'bg-sky-600 text-white shadow-sm shadow-sky-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} flex items-center gap-2">
                <span>Todas las horas</span>
                <span class="px-1.5 py-0.5 rounded-full text-[0.65rem] {{ $filterSlotId === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                    {{ $totalDay }}
                </span>
            </button>

            @foreach($timeSlots as $s)
                @php
                    $sCount = ($ausencias->get($s->id, collect()))->count();
                    $sIsCurrent = ($currentSlotId === $s->id);
                @endphp
                <button type="button" 
                        wire:click="filtrarHora({{ $s->id }})" 
                        class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all {{ $filterSlotId == $s->id ? 'bg-sky-600 text-white shadow-sm shadow-sky-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} flex items-center gap-1.5">
                    <span>{{ $s->name }}</span>
                    @if($sIsCurrent)
                        <span class="text-amber-400 text-xs">●</span>
                    @endif
                    <span class="px-1.5 py-0.5 rounded-full text-[0.65rem] {{ $sCount > 0 ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                        {{ $sCount }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Lista de Tramos Horarios (Acordeón Inteligente) -->
    <div class="space-y-4">
        @forelse($timeSlots as $slot)
            @php
                if ($filterSlotId !== 'all' && $filterSlotId != $slot->id) {
                    continue;
                }

                $slotAusencias = $ausencias->get($slot->id, collect());
                $disponibles = $this->getAvailableGuardiasForSlot($slot->id);
                $slotAusentes = $this->getAbsentGuardiasForSlot($slot->id);
                $isCurrentSlot = ($currentSlotId === $slot->id);
                $hasPending = $slotAusencias->contains(fn($a) => !$a->isCubierta());
                $pendingCount = $slotAusencias->filter(fn($a) => !$a->isCubierta())->count();
                $isOpen = $openSlots[$slot->id] ?? ($isCurrentSlot || $slotAusencias->isNotEmpty());
            @endphp

            <div class="border rounded-2xl overflow-hidden transition-all duration-200 {{ $isCurrentSlot ? 'ring-2 ring-blue-500/80 dark:ring-blue-400 bg-blue-50/20 dark:bg-blue-950/15 border-blue-300 dark:border-blue-700 shadow-md shadow-blue-500/10' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}">
                
                <!-- Acordeón Header (Trigger de 1 sola línea) -->
                <button type="button" 
                        wire:click="toggleSlotAccordion({{ $slot->id }})"
                        class="w-full text-left p-4 sm:p-5 flex items-center justify-between gap-3 transition-colors hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                    
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">
                            {{ $slot->name }}
                        </span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700">
                            {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                        </span>
                        @if($isCurrentSlot)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black bg-blue-600 text-white shadow-sm shadow-blue-500/30">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                                </span>
                                HORA ACTUAL
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        @if($slotAusencias->isEmpty())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                ✓ 0 ausencias
                            </span>
                        @elseif($hasPending)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60 shadow-sm shadow-rose-500/10">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-600"></span>
                                </span>
                                {{ $pendingCount }} sin cubrir
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                ✓ Todas cubiertas ({{ $slotAusencias->count() }})
                            </span>
                        @endif

                        <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            👥 {{ $disponibles->count() }} disp.
                        </span>

                        <span class="text-slate-400 transition-transform duration-200 {{ $isOpen ? 'rotate-180' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </div>
                </button>

                <!-- Acordeón Content (Ausencias y Claustro) -->
                @if($isOpen)
                    <div class="p-5 pt-2 border-t border-slate-100 dark:border-slate-800/80 space-y-6">
                        
                        <!-- Tarjetas de Ausencia en esta hora -->
                        @if($slotAusencias->isNotEmpty())
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                @foreach($slotAusencias as $ausencia)
                                    @php
                                        $isCovered = $ausencia->isCubierta();
                                        $diff = $ausencia->group->dificultad ?? 1;
                                        $isExempt = ($diff < 0);
                                    @endphp
                                    <div class="rounded-2xl p-4 sm:p-5 border transition-all duration-200 {{ $isCovered ? 'bg-emerald-50/20 dark:bg-emerald-950/15 border-emerald-200/70 dark:border-emerald-800/50 shadow-sm' : 'bg-white dark:bg-slate-900 border-rose-200/80 dark:border-rose-900/40 shadow-sm' }} flex flex-col justify-between gap-4">
                                        
                                        <!-- Header de la ausencia: Grupo/Aula + Estado + Papelera -->
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                @if($ausencia->es_guardia)
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black tracking-wide uppercase bg-purple-100 text-purple-800 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                        🛡️ GUARDIA DE RECREO / AULA
                                                    </span>
                                                @else
                                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-sky-50 text-sky-900 dark:bg-sky-950/50 dark:text-sky-200 border border-sky-200 dark:border-sky-800 font-extrabold text-sm sm:text-base">
                                                        <span>🏫 {{ $ausencia->group ? ($ausencia->group->course . ' ' . $ausencia->group->name) : 'Grupo Sin Asignar' }}</span>
                                                        <span class="text-sky-400">·</span>
                                                        <span class="font-bold text-xs sm:text-sm text-sky-700 dark:text-sky-300">
                                                            📍 {{ $ausencia->zona ? $ausencia->zona->nombre : 'Aula habitual' }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-2">
                                                @if($isCovered)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                                        ✓ CUBIERTA
                                                    </span>
                                                @elseif($isExempt)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                                        NO REQUIERE
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-black bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-700">
                                                        <span class="relative flex h-2 w-2">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-500 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-600"></span>
                                                        </span>
                                                        SIN CUBRIR
                                                    </span>
                                                @endif

                                                @if($ausencia->canBeDeletedBy(Auth::user()))
                                                    <button type="button" 
                                                            wire:click="eliminarAusencia({{ $ausencia->id }})" 
                                                            wire:confirm="¿Seguro que deseas borrar esta ausencia del parte?"
                                                            class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900 border border-rose-200 dark:border-rose-800 flex items-center justify-center transition-transform hover:scale-105" 
                                                            title="Borrar ausencia">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Docente Ausente + Ver Tarea -->
                                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/60">
                                            <div class="flex items-center gap-3 overflow-hidden">
                                                @if($ausencia->user->avatar_url)
                                                    <img src="{{ $ausencia->user->avatar_url }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0 border border-slate-200 dark:border-slate-700">
                                                @else
                                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-rose-500 to-amber-500 text-white font-extrabold text-xs flex items-center justify-center shrink-0">
                                                        {{ strtoupper(substr($ausencia->user->name ?? 'P', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div class="overflow-hidden">
                                                    <div class="text-[0.68rem] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Docente Ausente</div>
                                                    <div class="font-extrabold text-sm text-slate-900 dark:text-white truncate">
                                                        {{ $ausencia->user->name ?? 'Docente' }} {{ $ausencia->user->last_name ?? '' }}
                                                    </div>
                                                    @if($ausencia->user->departamento)
                                                        <div class="text-[0.72rem] text-slate-500 dark:text-slate-400 truncate">
                                                            {{ $ausencia->user->departamento }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <button type="button" 
                                                    onclick="openTaskModal('{{ addslashes($ausencia->user->name . ' ' . ($ausencia->user->last_name ?? '')) }}', '{{ addslashes($ausencia->group ? ($ausencia->group->course . ' ' . $ausencia->group->name) : 'Grupo') }}', '{{ addslashes($ausencia->tarea ?? 'Sin tarea especificada.') }}', '{{ addslashes($ausencia->enlace_tarea ?? '') }}')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-extrabold text-xs bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200 dark:border-slate-600 shadow-sm shrink-0">
                                                <span>📄</span>
                                                <span>Ver Tarea</span>
                                                @if($ausencia->enlace_tarea)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                @endif
                                            </button>
                                        </div>

                                        <!-- Asignación Reactiva / Cobertura -->
                                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80">
                                            @if($isCovered)
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold text-sm">✓ Cubierta por:</span>
                                                        <strong class="text-slate-900 dark:text-white text-sm">{{ $ausencia->guardiaUser->name ?? 'Docente' }}</strong>
                                                    </div>

                                                    @if($ausencia->guardia_user_id === Auth::id() || Auth::user()->hasAnyRole(['admin', 'directiva']))
                                                        <button type="button" 
                                                                wire:click="desconfirmarGuardia({{ $ausencia->id }})" 
                                                                wire:confirm="¿Deseas liberar esta guardia?"
                                                                class="px-2.5 py-1 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-lg">
                                                            Liberar
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                @php
                                                    $recommendedDocente = $disponibles->firstWhere('is_recommended', true);
                                                    $isUserOnGuardSlot = $disponibles->contains('user_id', Auth::id());
                                                    $canAssignGuardia = Auth::user()->hasAnyRole(['admin', 'directiva']) || $isUserOnGuardSlot;
                                                @endphp

                                                @if($canAssignGuardia && $disponibles->isNotEmpty())
                                                    <div class="space-y-3">
                                                        <div>
                                                            <label class="block text-[0.68rem] font-black uppercase text-slate-400 dark:text-slate-500 mb-1">
                                                                Profesor asignado (orden de equidad):
                                                            </label>
                                                            <select wire:model="guardiaAssignments.{{ $ausencia->id }}" class="w-full text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 py-2 px-3 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                                                @foreach($disponibles as $disp)
                                                                    <option value="{{ $disp['user_id'] }}">
                                                                        {{ $disp['name'] }} {{ !empty($disp['departamento']) ? '(' . $disp['departamento'] . ')' : '' }} — {{ $disp['guardias_count'] }} h.{{ $disp['is_recommended'] ? ' ⭐ Recomendado' : '' }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    wire:click="confirmarGuardia({{ $ausencia->id }})" 
                                                                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-black text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                                                <span>✓</span>
                                                                <span>Confirmar Guardia</span>
                                                            </button>

                                                            @if($isUserOnGuardSlot)
                                                                <button type="button" 
                                                                        wire:click="asignarmeYo({{ $ausencia->id }})" 
                                                                        class="py-2.5 px-3 rounded-xl text-xs font-extrabold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 border border-blue-200 dark:border-blue-800 transition-all shrink-0"
                                                                        title="Asignarme inmediatamente a mí">
                                                                    🛡️ Yo
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @elseif($canAssignGuardia && $disponibles->isEmpty())
                                                    <button type="button" 
                                                            wire:click="confirmarGuardia({{ $ausencia->id }})" 
                                                            class="w-full py-2.5 px-4 rounded-xl text-xs font-black text-white bg-gradient-to-r from-emerald-600 to-teal-600 shadow-md">
                                                        ✓ Confirmar Guardia
                                                    </button>
                                                @else
                                                    <div class="text-xs text-slate-500 dark:text-slate-400 text-center py-2 px-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700/60 font-medium">
                                                        ℹ️ Solo directivos o profesores de guardia a esta hora pueden confirmar.
                                                    </div>
                                                @endif
                                            @endif
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Claustro de guardia en esta hora (ordenado por equidad) -->
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/60">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-2">
                                    <span>⚖️ Claustro de guardia en esta hora</span>
                                    <span class="text-[0.65rem] font-normal text-slate-400">(Priorizado por equidad)</span>
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @foreach($disponibles as $disp)
                                    <div class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-xl border text-xs font-medium {{ $disp['is_recommended'] ? 'bg-sky-50/80 text-sky-900 dark:bg-sky-950/40 dark:text-sky-200 border-sky-300 dark:border-sky-700/80 shadow-sm shadow-sky-500/10' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                                        <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-black text-[0.65rem] flex items-center justify-center">
                                            {{ strtoupper(substr($disp['name'], 0, 1)) }}
                                        </div>
                                        <span class="font-bold">{{ $disp['name'] }}</span>
                                        @if(!empty($disp['departamento']))
                                            <span class="text-[0.7rem] text-slate-400 dark:text-slate-500">({{ $disp['departamento'] }})</span>
                                        @endif
                                        <span class="text-[0.68rem] text-slate-400 dark:text-slate-500">({{ $disp['guardias_count'] }} h.)</span>
                                        @if($disp['is_recommended'])
                                            <span class="text-[0.65rem] font-black uppercase px-1.5 py-0.5 rounded-md bg-sky-500 text-white shadow-xs">
                                                ⭐ Recomendado
                                            </span>
                                        @endif
                                    </div>
                                @endforeach

                                @foreach($slotAusentes as $ausente)
                                    <div class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-xl border border-rose-300 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 text-xs font-medium" title="Este docente tiene turno de guardia pero ha registrado ausencia">
                                        <div class="w-5 h-5 rounded-full bg-rose-200 dark:bg-rose-900 text-rose-700 dark:text-rose-200 font-black text-[0.65rem] flex items-center justify-center">
                                            ✕
                                        </div>
                                        <span class="font-bold line-through">{{ $ausente['name'] }}</span>
                                        @if(!empty($ausente['departamento']))
                                            <span class="text-[0.7rem] no-underline">({{ $ausente['departamento'] }})</span>
                                        @endif
                                        <span class="text-[0.65rem] font-black uppercase px-1.5 py-0.5 rounded-md bg-rose-600 text-white">
                                            🔴 Ausente
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                @endif
            </div>
        @empty
            <div class="p-8 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
                <div class="text-3xl mb-2">🕒</div>
                <h3 class="font-bold text-slate-900 dark:text-white">No hay tramos horarios configurados</h3>
                <p class="text-slate-500 text-xs mt-1">Crea una plantilla de horarios en el panel del centro.</p>
            </div>
        @endforelse
    </div>

</div>
