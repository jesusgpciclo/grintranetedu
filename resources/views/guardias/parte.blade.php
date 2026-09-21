@extends('layouts.app')

@section('title', 'Parte de Guardia - ' . $carbonDate->format('d/m/Y'))

@section('content')
<style>
    .parte-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* 3-Column Metrics Grid (Desktop) */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    @media (max-width: 640px) {
        .metrics-grid {
            grid-template-columns: 1fr;
        }
    }

    .metric-card {
        padding: 1.25rem 1.5rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid var(--border);
        background: var(--bg-card);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .metric-total {
        border-color: rgba(56, 189, 248, 0.25);
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.08), rgba(15, 23, 42, 0.4));
    }

    .metric-covered {
        border-color: rgba(34, 197, 94, 0.25);
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.08), rgba(15, 23, 42, 0.4));
    }

    .metric-pending {
        border-color: rgba(239, 68, 68, 0.25);
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(15, 23, 42, 0.4));
    }

    .metric-val {
        font-size: 2.25rem;
        font-weight: 900;
        line-height: 1;
        margin-top: 0.25rem;
        letter-spacing: -0.03em;
    }

    /* Time Slot Card (Desktop) */
    .slot-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 1rem;
        overflow: hidden;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .slot-card.is-current {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px var(--primary-light), var(--shadow-lg);
    }

    .slot-header {
        padding: 1rem 1.5rem;
        background: var(--bg-hover);
        border-bottom: 1px solid var(--border);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    /* Absence Card in Grid */
    .absence-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 0.875rem;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
        transition: all 0.2s ease;
    }

    .absence-card.is-covered {
        border-color: rgba(34, 197, 94, 0.35);
        background: linear-gradient(to bottom right, rgba(34, 197, 94, 0.04), var(--bg-surface));
    }

    .absence-card.is-pending {
        border-color: rgba(239, 68, 68, 0.4);
        background: linear-gradient(to bottom right, rgba(239, 68, 68, 0.05), var(--bg-surface));
    }

    /* Teacher chip */
    .teacher-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.75rem;
        border-radius: 0.625rem;
        background: var(--bg-surface);
        border: 1px solid var(--border);
        font-size: 0.8125rem;
        color: var(--text-color);
        transition: all 0.15s ease;
    }

    .teacher-chip:hover {
        border-color: var(--primary);
        background: var(--bg-hover);
    }

    .teacher-chip.is-priority {
        border-color: rgba(56, 189, 248, 0.4);
        background: rgba(56, 189, 248, 0.08);
    }

    .btn-delete-ausencia {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #ef4444;
        border-radius: 0.5rem;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-delete-ausencia:hover {
        background: rgba(239, 68, 68, 0.25);
        border-color: #ef4444;
        transform: scale(1.05);
    }

    /* Modal Apuntar en el Parte */
    .apuntar-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .apuntar-modal-backdrop.is-open {
        display: flex;
    }

    .apuntar-modal-content {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 1.25rem;
        width: 100%;
        max-width: 580px;
        box-shadow: var(--shadow-xl);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        animation: modalScaleUp 0.2s ease-out;
    }

    /* Slot Filter Bar (Desktop) */
    .slot-filter-btn {
        padding: 0.5rem 0.85rem;
        font-weight: 700;
        font-size: 0.8rem;
        border-radius: 0.75rem;
        border: 1px solid var(--border);
        background: var(--bg-card);
        color: var(--text-secondary);
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
    }

    .slot-filter-btn:hover {
        background: var(--bg-hover);
        color: var(--text-heading);
        border-color: var(--primary);
    }

    .slot-filter-btn.active {
        background: linear-gradient(135deg, var(--primary), #4f46e5);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 12px var(--primary-light);
    }

    .slot-filter-badge {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.1rem 0.4rem;
        border-radius: 9999px;
    }

    /* Mobile-First Custom Utilities */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    
    .touch-target-44 {
        min-height: 44px;
        min-width: 44px;
    }

    .mobile-glass-pill {
        background: var(--bg-card);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--border);
    }
</style>

<div class="parte-container">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- =======================================================================
         VISTA MÓVIL REFACTORIZADA (block md:hidden) - Mobile-First & Thumb-Friendly
         ======================================================================= -->
    <div class="block md:hidden space-y-3" x-data="mobileParteState()">
        
        <!-- 1. Cabecera y Resumen Compacto Mobile -->
        <div class="p-3.5 rounded-2xl mobile-glass-pill shadow-sm space-y-3">
            
            <!-- Fila Superior: Título + Badge En Vivo + Selector Fecha Compacto -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white text-base shadow-sm shrink-0">
                        🛡️
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h1 class="text-base font-black text-[var(--text-heading)] tracking-tight leading-none">Parte de Guardia</h1>
                            @if($isToday)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[0.625rem] font-extrabold uppercase bg-emerald-500/15 text-emerald-500 border border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    HOY
                                </span>
                            @endif
                        </div>
                        <p class="text-[0.725rem] font-semibold text-[var(--text-muted)] mt-0.5 leading-tight">
                            {{ ucfirst($carbonDate->isoFormat('ddd, D [de] MMM')) }}
                        </p>
                    </div>
                </div>

                <!-- Selector de Día Compacto (Flechas + Hoy + Botón Apuntar) -->
                <div class="flex items-center gap-1 shrink-0">
                    <a href="{{ route('guardias.parte', ['date' => $carbonDate->copy()->subDay()->format('Y-m-d')]) }}" 
                       class="w-8 h-8 rounded-lg flex items-center justify-center bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-heading)] font-black text-sm active:scale-95 transition-transform"
                       title="Día anterior">
                        ‹
                    </a>

                    @if(!$isToday)
                        <a href="{{ route('guardias.parte', ['date' => date('Y-m-d')]) }}" 
                           class="px-2 h-8 rounded-lg flex items-center justify-center bg-sky-500/15 text-sky-500 border border-sky-500/30 text-xs font-black active:scale-95 transition-transform">
                            Hoy
                        </a>
                    @endif

                    <a href="{{ route('guardias.parte', ['date' => $carbonDate->copy()->addDay()->format('Y-m-d')]) }}" 
                       class="w-8 h-8 rounded-lg flex items-center justify-center bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-heading)] font-black text-sm active:scale-95 transition-transform"
                       title="Día siguiente">
                        ›
                    </a>

                    <!-- Botón Rápido Apuntar -->
                    <button type="button" onclick="openApuntarModal()" 
                            class="h-8 px-2.5 rounded-lg bg-gradient-to-r from-sky-600 to-indigo-600 text-white font-extrabold text-xs flex items-center gap-1 shadow-sm active:scale-95 transition-transform">
                        <span>📝</span>
                        <span class="text-[0.7rem]">Apuntar</span>
                    </button>
                </div>
            </div>

            <!-- Fila Inferior: Barra Horizontal Tipo "Pills" Compacta (Total | Cubiertas | Sin Cubrir) -->
            <div class="grid grid-cols-3 gap-1.5 pt-2.5 border-t border-[var(--border)]">
                <!-- Pill Total -->
                <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-sky-500/10 border border-sky-500/20">
                    <div class="text-[0.65rem] font-bold uppercase text-[var(--text-muted)]">Total</div>
                    <div class="text-sm font-black text-[var(--text-heading)]">{{ $totalDay }}</div>
                </div>

                <!-- Pill Cubiertas -->
                <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/25">
                    <div class="text-[0.65rem] font-bold uppercase text-emerald-500">Cubiertas</div>
                    <div class="text-sm font-black text-emerald-500">{{ $cubiertasDay }}</div>
                </div>

                <!-- Pill Sin Cubrir -->
                <div class="flex items-center justify-between px-2.5 py-1.5 rounded-xl {{ $pendientesDay > 0 ? 'bg-rose-500/15 border-rose-500/30' : 'bg-rose-500/5 border-rose-500/20' }}">
                    <div class="flex items-center gap-1 text-[0.65rem] font-bold uppercase text-rose-500">
                        @if($pendientesDay > 0)
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                        @endif
                        <span>Pendiente</span>
                    </div>
                    <div class="text-sm font-black text-rose-500">{{ $pendientesDay }}</div>
                </div>
            </div>

        </div>

        <!-- 2. Selector de Tramos Horarios (Scroll Horizontal Táctil con Píldoras) -->
        <div class="py-1">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar scroll-smooth snap-x py-1 px-0.5">
                
                <!-- Botón "Todas" -->
                <button type="button" 
                        @click="filterMobileSlot('all')" 
                        :class="activeSlotFilter === 'all' ? 'bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-md shadow-sky-500/25 border-transparent' : 'bg-[var(--bg-card)] text-[var(--text-secondary)] border-[var(--border)]'"
                        class="snap-start shrink-0 px-3 py-2 rounded-xl text-xs font-black border transition-all flex items-center gap-1.5 touch-target-44 active:scale-95">
                    <span>Todas</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[0.65rem] font-bold"
                          :class="activeSlotFilter === 'all' ? 'bg-white/25 text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]'">
                        {{ $totalDay }}
                    </span>
                </button>

                <!-- Píldora para cada Tramo -->
                @foreach($timeSlots as $slot)
                    @php
                        $sAusencias = $ausencias->get($slot->id, collect());
                        $sCount = $sAusencias->count();
                        $sPending = $sAusencias->filter(fn($a) => !$a->isCubierta())->count();
                        $sIsCurrent = ($currentSlotId === $slot->id);
                    @endphp
                    <button type="button" 
                            @click="filterMobileSlot({{ $slot->id }})" 
                            :class="activeSlotFilter == {{ $slot->id }} ? 'bg-gradient-to-r from-sky-600 to-indigo-600 text-white shadow-md shadow-sky-500/25 border-transparent ring-2 ring-sky-400/50' : '{{ $sIsCurrent ? 'bg-sky-500/10 border-sky-500/40 text-[var(--text-heading)]' : 'bg-[var(--bg-card)] text-[var(--text-secondary)] border-[var(--border)]' }}'"
                            class="snap-start shrink-0 px-3 py-2 rounded-xl text-xs font-black border transition-all flex items-center gap-1.5 touch-target-44 active:scale-95">
                        
                        <!-- Indicador de Hora Actual -->
                        @if($sIsCurrent)
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse" title="Hora actual en curso"></span>
                        @endif

                        <span>{{ $slot->name }}</span>

                        <!-- Dot de Estado: Rojo si hay pendientes, Verde si todo cubierto o sin ausencias -->
                        @if($sPending > 0)
                            <span class="w-2 h-2 rounded-full bg-rose-500" title="{{ $sPending }} sin cubrir"></span>
                        @elseif($sCount > 0)
                            <span class="w-2 h-2 rounded-full bg-emerald-500" title="Todas cubiertas"></span>
                        @else
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400/50"></span>
                        @endif

                        @if($sCount > 0)
                            <span class="text-[0.65rem] font-extrabold opacity-80">({{ $sCount }})</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <!-- 3. Tarjetas de Tramo en Formato Acordeón Mobile (Alpine.js) -->
        <div class="space-y-2.5">
            @forelse($timeSlots as $slot)
                @php
                    $slotAusencias = $ausencias->get($slot->id, collect());
                    $disponibles = $disponiblesPorTramo[$slot->id] ?? collect();
                    $isCurrentSlot = ($currentSlotId === $slot->id);
                    $hasPending = $slotAusencias->contains(fn($a) => !$a->isCubierta());
                    $pendingCount = $slotAusencias->filter(fn($a) => !$a->isCubierta())->count();
                    $isOpenDefault = $isCurrentSlot || $slotAusencias->isNotEmpty();
                    $recommendedDocente = $disponibles->firstWhere('is_recommended', true);
                    $slotAusentes = $ausentesPorTramo[$slot->id] ?? collect();
                @endphp

                <div x-show="activeSlotFilter === 'all' || activeSlotFilter == {{ $slot->id }}"
                     id="mobile-slot-{{ $slot->id }}"
                     class="rounded-2xl border transition-all duration-200 overflow-hidden {{ $isCurrentSlot ? 'bg-sky-500/5 border-sky-500/50 ring-1 ring-sky-500/30 shadow-md shadow-sky-500/5' : 'bg-[var(--bg-card)] border-[var(--border)] shadow-sm' }}">
                    
                    <!-- VISTA COLAPSADA (Header Acordeón en una sola fila táctil) -->
                    <button type="button" 
                            @click="toggleMobileSlot({{ $slot->id }})"
                            class="w-full text-left p-3.5 flex items-center justify-between gap-2 touch-target-44 active:bg-[var(--bg-hover)] transition-colors">
                        
                        <!-- Lado Izquierdo: Nombre + Horario + Badge Hora Actual -->
                        <div class="flex items-center gap-2 flex-wrap min-w-0">
                            <span class="text-sm font-black text-[var(--text-heading)] truncate">
                                {{ $slot->name }}
                            </span>
                            <span class="text-[0.7rem] font-semibold text-[var(--text-muted)] bg-[var(--bg-surface)] px-1.5 py-0.5 rounded-md border border-[var(--border)] shrink-0">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                            </span>
                            @if($isCurrentSlot)
                                <span class="px-1.5 py-0.5 rounded-full text-[0.6rem] font-black uppercase bg-sky-600 text-white tracking-wider animate-pulse shrink-0">
                                    AHORA
                                </span>
                            @endif
                        </div>

                        <!-- Lado Derecho: Badges de Estado + Disponibles + Chevron -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($slotAusencias->isEmpty())
                                <span class="text-[0.68rem] font-extrabold text-[var(--text-muted)] bg-[var(--bg-surface)] px-2 py-0.5 rounded-full border border-[var(--border)]">
                                    0 aus.
                                </span>
                            @elseif($hasPending)
                                <span class="text-[0.68rem] font-black text-rose-500 bg-rose-500/15 border border-rose-500/30 px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    {{ $pendingCount }} sin cubrir
                                </span>
                            @else
                                <span class="text-[0.68rem] font-black text-emerald-500 bg-emerald-500/15 border border-emerald-500/30 px-2 py-0.5 rounded-full">
                                    ✓ Cubiertas
                                </span>
                            @endif

                            <span class="text-[0.68rem] font-bold text-[var(--text-secondary)] bg-[var(--bg-surface)] px-2 py-0.5 rounded-full border border-[var(--border)]" title="Profesores disponibles">
                                👥 {{ $disponibles->count() }} disp.
                            </span>

                            <!-- Chevron indicador rotatorio -->
                            <svg class="w-4 h-4 text-[var(--text-muted)] transition-transform duration-200"
                                 :class="expandedSlots[{{ $slot->id }}] ? 'rotate-180' : 'rotate-0'"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    <!-- VISTA EXPANDIDA (Cuerpo del Acordeón) -->
                    <div x-show="expandedSlots[{{ $slot->id }}]"
                         x-collapse
                         class="p-3 border-t border-[var(--border)] space-y-3 bg-[var(--bg-surface)]/60">
                        
                        <!-- Lista de Ausencias (Cards Compactas Mobile-First) -->
                        @if($slotAusencias->isNotEmpty())
                            <div class="space-y-2.5">
                                @foreach($slotAusencias as $ausencia)
                                    @php
                                        $isCovered = $ausencia->isCubierta();
                                        $diff = $ausencia->group->dificultad ?? 1;
                                        $isExempt = ($diff < 0);
                                        $isUserOnGuardSlot = $disponibles->contains('user_id', Auth::id());
                                        $canAssignGuardia = Auth::user()->hasAnyRole(['admin', 'directiva']) || $isUserOnGuardSlot;
                                    @endphp

                                    <div class="p-3 rounded-xl border {{ $isCovered ? 'bg-emerald-500/5 border-emerald-500/30' : 'bg-rose-500/5 border-rose-500/30 ring-1 ring-rose-500/20' }} space-y-2.5 shadow-sm">
                                        
                                        <!-- Línea 1: Grupo + Aula Destacada + Badge de Estado -->
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                                @if($ausencia->es_guardia)
                                                    <span class="text-[0.7rem] font-black uppercase text-purple-500 bg-purple-500/10 px-2 py-0.5 rounded-md border border-purple-500/20">
                                                        🛡️ Guardia Recreo/Aula
                                                    </span>
                                                @else
                                                    <!-- Grupo en Negrita -->
                                                    <span class="text-sm font-black text-[var(--primary)] tracking-tight">
                                                        🏫 {{ $ausencia->group ? ($ausencia->group->course . ' ' . $ausencia->group->name) : 'Sin Grupo' }}
                                                    </span>
                                                    <span class="text-[var(--text-muted)] opacity-50">·</span>
                                                    <!-- Aula en Badge Destacado -->
                                                    <span class="text-xs font-black text-[var(--text-heading)] bg-[var(--bg-surface)] px-2 py-0.5 rounded-md border border-[var(--border)] shadow-xs">
                                                        📍 {{ $ausencia->zona ? $ausencia->zona->nombre : 'Aula habitual' }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-1 shrink-0">
                                                @if($isCovered)
                                                    <span class="text-[0.65rem] font-black text-emerald-500 bg-emerald-500/15 border border-emerald-500/30 px-2 py-0.5 rounded-full">
                                                        ✓ CUBIERTA
                                                    </span>
                                                @elseif($isExempt)
                                                    <span class="text-[0.65rem] font-bold text-[var(--text-muted)] bg-[var(--bg-hover)] px-2 py-0.5 rounded-full">
                                                        NO REQUIERE
                                                    </span>
                                                @else
                                                    <span class="text-[0.65rem] font-black text-rose-500 bg-rose-500/20 border border-rose-500/40 px-2 py-0.5 rounded-full flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                        SIN CUBRIR
                                                    </span>
                                                @endif

                                                @if($ausencia->canBeDeletedBy(Auth::user()))
                                                    <form action="{{ route('ausencias.destroy', $ausencia) }}" method="POST" onsubmit="return confirm('¿Borrar esta ausencia?')" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-500 border border-rose-500/20 flex items-center justify-center text-xs active:scale-95 transition-transform" title="Borrar">
                                                            ✕
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Línea 2: Docente Ausente + Botón "Ver Tarea" -->
                                        <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-[var(--bg-card)] border border-[var(--border)]">
                                            <div class="flex items-center gap-2 overflow-hidden">
                                                <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-rose-500 to-amber-500 text-white font-black text-xs flex items-center justify-center shrink-0">
                                                    {{ strtoupper(substr($ausencia->user->name ?? 'P', 0, 1)) }}
                                                </div>
                                                <div class="min-w-0 overflow-hidden">
                                                    <div class="text-xs font-extrabold text-[var(--text-heading)] truncate">
                                                        {{ $ausencia->user->name ?? 'Docente' }} {{ $ausencia->user->last_name ?? '' }}
                                                    </div>
                                                    @if($ausencia->user->departamento)
                                                        <div class="text-[0.65rem] text-[var(--text-muted)] truncate">
                                                            {{ $ausencia->user->departamento }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Botón Drawer Ver Tarea (Thumb-Friendly) -->
                                            <button type="button" 
                                                    @click="openMobileTaskDrawer('{{ addslashes($ausencia->user->name . ' ' . ($ausencia->user->last_name ?? '')) }}', '{{ addslashes($ausencia->group ? ($ausencia->group->course . ' ' . $ausencia->group->name) : 'Grupo') }}', '{{ addslashes($ausencia->tarea ?? 'Sin tarea especificada.') }}', '{{ addslashes($ausencia->enlace_tarea ?? '') }}')"
                                                    class="h-8 px-2.5 rounded-lg bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-heading)] font-extrabold text-xs flex items-center gap-1.5 shrink-0 active:scale-95 shadow-xs transition-transform">
                                                <span>📄</span>
                                                <span class="text-[0.7rem]">Ver tarea</span>
                                                @if($ausencia->enlace_tarea)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                                @endif
                                            </button>
                                        </div>

                                        <!-- Línea 3: Asignación Rápida & Acciones Táctiles Grandes (min 44px) -->
                                        @if($isCovered)
                                            <div class="flex items-center justify-between gap-2 pt-1 border-t border-[var(--border)]">
                                                <div class="text-xs font-black text-emerald-500 flex items-center gap-1 truncate">
                                                    <span>✓ Cubierta por:</span>
                                                    <span class="text-[var(--text-heading)] underline">{{ $ausencia->guardiaUser->name ?? 'Docente' }}</span>
                                                </div>
                                                @if($ausencia->guardia_user_id === Auth::id() || Auth::user()->hasRole('admin') || Auth::user()->hasRole('directiva'))
                                                    <form action="{{ route('guardias.desconfirmar', $ausencia) }}" method="POST" onsubmit="return confirm('¿Desconfirmar guardia?')" class="m-0">
                                                        @csrf
                                                        <button type="submit" class="h-8 px-2.5 rounded-lg bg-rose-500/10 text-rose-500 border border-rose-500/25 text-xs font-extrabold active:scale-95 transition-transform">
                                                            Liberar
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                            <form action="{{ route('guardias.confirmar', $ausencia) }}" method="POST" class="space-y-2 pt-1 border-t border-[var(--border)]">
                                                @csrf
                                                
                                                @if($canAssignGuardia && $disponibles->isNotEmpty())
                                                    <div>
                                                        <label class="block text-[0.625rem] font-extrabold uppercase text-[var(--text-muted)] mb-1">
                                                            Asignar profesor (orden de equidad):
                                                        </label>
                                                        <select id="mobile_select_guardia_{{ $ausencia->id }}" name="guardia_user_id" 
                                                                class="w-full text-xs font-bold rounded-xl bg-[var(--bg-card)] border border-[var(--border)] p-2.5 text-[var(--text-heading)] focus:ring-2 focus:ring-sky-500">
                                                            @foreach($disponibles as $disp)
                                                                <option value="{{ $disp['user_id'] }}" {{ ($recommendedDocente && $recommendedDocente['user_id'] == $disp['user_id']) ? 'selected' : '' }}>
                                                                    {{ $disp['name'] }} ({{ $disp['guardias_count'] }} h){{ $disp['is_recommended'] ? ' ⭐ Recomendado' : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif

                                                <div class="flex items-stretch gap-1.5 pt-0.5">
                                                    @if($canAssignGuardia || $disponibles->isEmpty())
                                                        <!-- Botón Principal Confirmar (44px min height para el pulgar) -->
                                                        <button type="submit" 
                                                                class="flex-1 h-11 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-md shadow-emerald-500/20 active:scale-[0.98] transition-transform">
                                                            <span>✓</span>
                                                            <span>Confirmar Guardia</span>
                                                        </button>

                                                        <!-- Botón Rápido "Asignarme a mí" si estoy disponible en este tramo -->
                                                        @if($isUserOnGuardSlot)
                                                            <button type="button" 
                                                                    onclick="const sel = document.getElementById('mobile_select_guardia_{{ $ausencia->id }}'); if(sel) sel.value = '{{ Auth::id() }}'; this.form.submit();"
                                                                    class="h-11 px-3 rounded-xl bg-sky-500/15 text-sky-500 border border-sky-500/30 font-black text-xs flex items-center justify-center gap-1 active:scale-95 transition-transform shrink-0">
                                                                <span>🛡️</span>
                                                                <span>Asignarme</span>
                                                            </button>
                                                        @endif
                                                    @else
                                                        <div class="w-full text-center py-2 text-[0.7rem] font-semibold text-[var(--text-muted)] bg-[var(--bg-card)] rounded-lg border border-[var(--border)]">
                                                            ℹ️ Solo directivos o docentes de guardia pueden confirmar.
                                                        </div>
                                                    @endif
                                                </div>
                                            </form>
                                        @endif

                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-3 text-center text-xs font-bold text-emerald-500 bg-emerald-500/5 rounded-xl border border-emerald-500/20">
                                ✓ No hay ausencias registradas en esta hora.
                            </div>
                        @endif

                        <!-- Claustro de Guardia (Desplegable Secundario Compacto para no saturar con avatares) -->
                        <div class="pt-1.5 border-t border-dashed border-[var(--border)]">
                            <div class="flex items-center justify-between">
                                <div class="text-[0.725rem] font-bold text-[var(--text-muted)] truncate">
                                    @if($recommendedDocente)
                                        👥 Docentes de guardia: <strong class="text-[var(--text-heading)]">{{ $recommendedDocente['name'] }}</strong> (Recomendado) 
                                        @if($disponibles->count() > 1)
                                            y {{ $disponibles->count() - 1 }} más
                                        @endif
                                    @elseif($disponibles->isNotEmpty())
                                        👥 {{ $disponibles->count() }} docentes de guardia disponibles
                                    @else
                                        👥 Sin docentes de guardia en plantilla
                                    @endif
                                </div>
                                
                                @if($disponibles->isNotEmpty() || $slotAusentes->isNotEmpty())
                                    <button type="button" 
                                            @click="toggleFaculty({{ $slot->id }})"
                                            class="text-[0.7rem] font-black text-sky-500 hover:underline shrink-0 ml-1">
                                        <span x-text="facultyExpanded[{{ $slot->id }}] ? 'Ocultar' : 'Ver todos ▾'"></span>
                                    </button>
                                @endif
                            </div>

                            <!-- Chips Horizontales Desplegados -->
                            <div x-show="facultyExpanded[{{ $slot->id }}]" 
                                 x-collapse
                                 class="flex flex-wrap gap-1.5 pt-2">
                                @foreach($disponibles as $disp)
                                    <div class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[0.7rem] font-bold border {{ $disp['is_recommended'] ? 'bg-sky-500/15 border-sky-500/40 text-sky-400 font-extrabold' : 'bg-[var(--bg-card)] border-[var(--border)] text-[var(--text-secondary)]' }}">
                                        <span>{{ $disp['name'] }}</span>
                                        <span class="text-[0.625rem] opacity-75">({{ $disp['guardias_count'] }} h)</span>
                                        @if($disp['is_recommended'])
                                            <span class="text-[0.6rem] bg-sky-500 text-white px-1 rounded">⭐</span>
                                        @endif
                                    </div>
                                @endforeach

                                @foreach($slotAusentes as $ausente)
                                    <div class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[0.7rem] font-bold border bg-rose-500/10 border-rose-500/30 text-rose-500 line-through">
                                        <span>{{ $ausente['name'] }}</span>
                                        <span class="text-[0.6rem] bg-rose-500/20 text-rose-500 px-1 rounded no-underline">🔴 Ausente</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="p-6 text-center rounded-2xl bg-[var(--bg-card)] border border-[var(--border)]">
                    <div class="text-3xl mb-2">🕒</div>
                    <div class="text-sm font-black text-[var(--text-heading)]">No hay tramos horarios configurados</div>
                </div>
            @endforelse
        </div>

        <!-- 4. Drawer / Bottom-Sheet Modal para "Ver Tarea" Pedagógica (Thumb-Friendly) -->
        <div x-show="taskDrawer.open" 
             x-cloak
             class="fixed inset-0 z-[99999] flex flex-col justify-end"
             role="dialog" aria-modal="true">
            
            <!-- Backdrop Oscuro -->
            <div x-show="taskDrawer.open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeMobileTaskDrawer()"
                 class="fixed inset-0 bg-black/70 backdrop-blur-xs"></div>

            <!-- Panel Bottom-Sheet Deslizable -->
            <div x-show="taskDrawer.open"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="relative bg-[var(--bg-card)] border-t border-[var(--border)] rounded-t-3xl shadow-2xl p-5 space-y-4 max-h-[85vh] overflow-y-auto z-10">
                
                <!-- Barra Indicadora de Arrastre -->
                <div class="w-12 h-1.5 bg-[var(--border)] rounded-full mx-auto -mt-1 opacity-80"></div>

                <!-- Cabecera del Drawer -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-sky-500/15 text-sky-500 flex items-center justify-center text-xl shrink-0">
                            📄
                        </div>
                        <div>
                            <h3 class="text-base font-black text-[var(--text-heading)] leading-tight">Tarea Pedagógica</h3>
                            <p class="text-xs font-bold text-[var(--text-muted)]" x-text="taskDrawer.group + ' · ' + taskDrawer.teacher"></p>
                        </div>
                    </div>
                    <button type="button" @click="closeMobileTaskDrawer()" class="w-8 h-8 rounded-full bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-muted)] flex items-center justify-center font-bold text-sm">
                        ✕
                    </button>
                </div>

                <!-- Contenido de Instrucciones Pedagógicas -->
                <div class="space-y-1.5">
                    <div class="text-[0.65rem] font-extrabold uppercase text-[var(--text-muted)] tracking-wider">
                        Instrucciones para el alumnado:
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[var(--bg-surface)] border border-[var(--border)] text-sm leading-relaxed text-[var(--text-color)] whitespace-pre-wrap max-h-56 overflow-y-auto"
                         x-text="taskDrawer.task">
                    </div>
                </div>

                <!-- Enlace Classroom / Material Online si existe -->
                <div x-show="taskDrawer.link && taskDrawer.link.trim() !== ''" class="space-y-1.5">
                    <div class="text-[0.65rem] font-extrabold uppercase text-[var(--text-muted)] tracking-wider">
                        Material Online / Classroom:
                    </div>
                    <a :href="taskDrawer.link" target="_blank" rel="noopener noreferrer"
                       class="h-12 px-4 rounded-xl bg-sky-500/10 border border-sky-500/25 text-sky-500 font-extrabold text-xs flex items-center justify-between active:scale-[0.98] transition-transform">
                        <span class="flex items-center gap-2 truncate">
                            <span>🔗</span>
                            <span class="truncate">Abrir enlace de material</span>
                        </span>
                        <span class="text-sm font-black">&rarr;</span>
                    </a>
                </div>

                <!-- Botón Cerrar Amplio al Alcance del Pulgar (48px) -->
                <button type="button" @click="closeMobileTaskDrawer()" 
                        class="w-full h-12 rounded-2xl bg-[var(--bg-surface)] border border-[var(--border)] text-[var(--text-heading)] font-black text-sm active:scale-[0.98] transition-transform shadow-xs">
                    Entendido / Cerrar
                </button>

            </div>
        </div>

    </div>

    <!-- =======================================================================
         VISTA DE ESCRITORIO EXISTENTE (hidden md:block) - Completamente Intacta
         ======================================================================= -->
    <div class="hidden md:block">
        
        <!-- Top Header Card -->
        <div class="card" style="margin-bottom: 0; padding: 1.5rem;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1.25rem;">
                
                <!-- Title & Live Badge -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), #4f46e5); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; box-shadow: 0 4px 12px var(--primary-light);">
                        🛡️
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <h1 style="font-size: 1.65rem; font-weight: 800; margin: 0; color: var(--text-heading); letter-spacing: -0.02em;">Parte de Guardia</h1>
                            @if($isToday)
                                <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); padding: 0.2rem 0.55rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
                                    EN VIVO HOY
                                </span>
                            @endif
                        </div>
                        <p style="margin: 0.2rem 0 0 0; font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">
                            {{ ucfirst($carbonDate->isoFormat('dddd, D [de] MMMM [de] YYYY')) }}
                        </p>
                    </div>
                </div>

                <!-- Date Navigation Controls & Quick Action -->
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                    <a href="{{ route('guardias.parte', ['date' => $carbonDate->copy()->subDay()->format('Y-m-d')]) }}" 
                       class="btn btn-secondary btn-sm" style="padding: 0.5rem 0.75rem;" title="Día anterior">
                        &larr;
                    </a>

                    @if(!$isToday)
                        <a href="{{ route('guardias.parte', ['date' => date('Y-m-d')]) }}" 
                           class="btn btn-secondary btn-sm" style="background: var(--primary-light); color: var(--primary); border-color: var(--primary-border);">
                            Hoy
                        </a>
                    @endif

                    <a href="{{ route('guardias.parte', ['date' => $carbonDate->copy()->addDay()->format('Y-m-d')]) }}" 
                       class="btn btn-secondary btn-sm" style="padding: 0.5rem 0.75rem;" title="Día siguiente">
                        &rarr;
                    </a>

                    <form action="{{ route('guardias.parte') }}" method="GET" style="margin: 0;">
                        <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                               class="form-control" style="width: auto; padding: 0.45rem 0.75rem; font-size: 0.85rem; cursor: pointer;">
                    </form>

                    <button type="button" onclick="openApuntarModal()" class="btn btn-primary btn-sm" style="padding: 0.5rem 1rem; background: linear-gradient(135deg, var(--primary), #4f46e5); font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 4px 12px var(--primary-light);">
                        <span>📝 Apuntar en el parte</span>
                    </button>

                    <a href="{{ route('ausencias.create', ['date' => $date]) }}" class="btn btn-secondary btn-sm" style="padding: 0.5rem 0.85rem;" title="Formulario para notificar varios tramos a la vez">
                        + Multitramo
                    </a>
                </div>
            </div>

            <!-- Primera Fila en 3 Columnas: Total Ausencias | Cubiertas | Sin Cubrir -->
            <div class="metrics-grid" style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border);">
                
                <!-- Columna 1: Total Ausencias -->
                <div class="metric-card metric-total">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Total Ausencias
                        </div>
                        <div class="metric-val" style="color: var(--text-heading);">
                            {{ $totalDay }}
                        </div>
                    </div>
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800;">
                        📋
                    </div>
                </div>

                <!-- Columna 2: Cubiertas -->
                <div class="metric-card metric-covered">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: 0.05em;">
                            Cubiertas
                        </div>
                        <div class="metric-val" style="color: #10b981;">
                            {{ $cubiertasDay }}
                        </div>
                    </div>
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: rgba(34, 197, 94, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800;">
                        ✓
                    </div>
                </div>

                <!-- Columna 3: Sin Cubrir -->
                <div class="metric-card metric-pending">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em;">
                            Sin Cubrir
                        </div>
                        <div class="metric-val" style="color: #ef4444;">
                            {{ $pendientesDay }}
                        </div>
                    </div>
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800;">
                        ⏳
                    </div>
                </div>

            </div>
        </div>

        <!-- Navegador por Horas (Desktop) -->
        <div class="card" style="padding: 0.85rem 1.25rem; margin-top: 1.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem; overflow-x: auto; padding-bottom: 0.2rem; width: 100%;">
                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-right: 0.25rem; flex-shrink: 0;">
                    Filtrar Hora:
                </span>

                <button type="button" onclick="filterSlot('all', this)" class="slot-filter-btn active" id="filter-slot-all">
                    <span>Todas las horas</span>
                    <span class="slot-filter-badge" style="background: rgba(255, 255, 255, 0.25); color: #fff;">
                        {{ $totalDay }}
                    </span>
                </button>

                @foreach($timeSlots as $s)
                    @php
                        $sCount = ($ausencias->get($s->id, collect()))->count();
                        $sIsCurrent = ($currentSlotId === $s->id);
                    @endphp
                    <button type="button" onclick="filterSlot({{ $s->id }}, this)" class="slot-filter-btn" id="filter-slot-{{ $s->id }}" data-slot-id="{{ $s->id }}">
                        <span>{{ $s->name }}</span>
                        @if($sIsCurrent)
                            <span style="font-size: 0.6rem; color: #f59e0b;" title="Hora actual en curso">●</span>
                        @endif
                        <span class="slot-filter-badge" style="background: {{ $sCount > 0 ? 'rgba(239, 68, 68, 0.2)' : 'var(--bg-hover)' }}; color: {{ $sCount > 0 ? '#ef4444' : 'var(--text-muted)' }};">
                            {{ $sCount }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Time Slots List (Desktop) -->
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @forelse($timeSlots as $slot)
                @php
                    $slotAusencias = $ausencias->get($slot->id, collect());
                    $disponibles = $disponiblesPorTramo[$slot->id] ?? collect();
                    $isCurrentSlot = ($currentSlotId === $slot->id);
                    $hasPending = $slotAusencias->contains(fn($a) => !$a->isCubierta());
                    $pendingCount = $slotAusencias->filter(fn($a) => !$a->isCubierta())->count();
                    $isOpenDefault = $isCurrentSlot || $slotAusencias->isNotEmpty();
                @endphp

                <div class="slot-card {{ $isCurrentSlot ? 'is-current ring-2 ring-blue-500/80 dark:ring-blue-400 bg-blue-50/30 dark:bg-blue-950/20 border-blue-300 dark:border-blue-700 shadow-md shadow-blue-500/10' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}" 
                     id="slot-card-{{ $slot->id }}"
                     data-slot-id="{{ $slot->id }}"
                     style="border-radius: 1rem; border-width: 1px; border-style: solid; overflow: hidden; transition: all 0.2s ease;">
                    
                    <!-- Time Slot Bar (Interactive Accordion Trigger) -->
                    <button type="button" 
                            onclick="toggleSlotAccordion({{ $slot->id }})"
                            id="slot-trigger-{{ $slot->id }}"
                            aria-expanded="{{ $isOpenDefault ? 'true' : 'false' }}"
                            aria-controls="slot-content-{{ $slot->id }}"
                            class="slot-header w-full text-left"
                            style="cursor: pointer; border: none; width: 100%; text-align: left; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.85rem 1.25rem; background: {{ $isCurrentSlot ? 'rgba(59, 130, 246, 0.06)' : 'var(--bg-hover)' }}; transition: background 0.15s ease;">
                        
                        <!-- Left: Slot title, hour badges & current slot tag -->
                        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                            <span style="font-size: 1.05rem; font-weight: 800; color: var(--text-heading); letter-spacing: -0.01em;">
                                {{ $slot->name }}
                            </span>
                            <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); background: var(--bg-surface); padding: 0.2rem 0.6rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                            </span>
                            @if($isCurrentSlot)
                                <span style="font-size: 0.7rem; font-weight: 800; background: #2563eb; color: #fff; padding: 0.2rem 0.6rem; border-radius: 9999px; letter-spacing: 0.04em; display: inline-flex; align-items: center; gap: 0.35rem; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);">
                                    <span style="position: relative; display: flex; height: 6px; width: 6px;">
                                        <span style="position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 50%; background: #ffffff; opacity: 0.75; animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;"></span>
                                        <span style="position: relative; display: inline-flex; border-radius: 50%; height: 6px; width: 6px; background: #ffffff;"></span>
                                    </span>
                                    HORA ACTUAL
                                </span>
                            @endif
                        </div>

                        <!-- Right: Badges & Accordion Chevron -->
                        <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                            @if($slotAusencias->isEmpty())
                                <span style="font-size: 0.75rem; font-weight: 700; color: #10b981; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                    ✓ 0 ausencias
                                </span>
                            @elseif($hasPending)
                                <span style="font-size: 0.75rem; font-weight: 800; color: #ef4444; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); padding: 0.25rem 0.65rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                                    {{ $pendingCount }} sin cubrir
                                </span>
                            @else
                                <span style="font-size: 0.75rem; font-weight: 700; color: #10b981; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                    ✓ Todas cubiertas ({{ $slotAusencias->count() }})
                                </span>
                            @endif

                            <!-- Available Teachers Count Badge -->
                            <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); background: var(--bg-surface); padding: 0.25rem 0.65rem; border-radius: 9999px; border: 1px solid var(--border);" title="Profesores de guardia en esta hora">
                                👥 {{ $disponibles->count() }} disp.
                            </span>

                            <!-- Rotary Chevron Icon -->
                            <span id="slot-chevron-{{ $slot->id }}" 
                                  style="display: inline-flex; align-items: center; justify-content: center; color: var(--text-muted); transition: transform 0.2s ease; transform: {{ $isOpenDefault ? 'rotate(180deg)' : 'rotate(0deg)' }};">
                                <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </div>
                    </button>

                    <!-- Slot Accordion Body -->
                    <div id="slot-content-{{ $slot->id }}" 
                         style="display: {{ $isOpenDefault ? 'block' : 'none' }}; padding: 1.25rem 1.5rem; border-top: 1px solid var(--border);">
                        
                        <!-- Absences to cover in this slot -->
                        @if($slotAusencias->isNotEmpty())
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                                @foreach($slotAusencias as $ausencia)
                                    @php
                                        $isCovered = $ausencia->isCubierta();
                                        $diff = $ausencia->group->dificultad ?? 1;
                                        $isExempt = ($diff < 0);
                                    @endphp
                                    <div class="absence-card {{ $isCovered ? 'is-covered' : 'is-pending' }}">
                                        
                                        <div>
                                            <!-- Header: Group / Location & Status -->
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.85rem;">
                                                <div>
                                                    @if($ausencia->es_guardia)
                                                        <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #a855f7; background: rgba(168, 85, 247, 0.1); padding: 0.25rem 0.55rem; border-radius: 0.5rem; border: 1px solid rgba(168, 85, 247, 0.25);">
                                                            🛡️ GUARDIA DE RECREO / AULA
                                                        </span>
                                                    @else
                                                        <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.65rem; background: rgba(2, 132, 199, 0.08); border: 1px solid rgba(2, 132, 199, 0.2); border-radius: 0.625rem;">
                                                            <span style="font-size: 0.95rem; font-weight: 900; color: var(--primary);">
                                                                🏫 {{ $ausencia->group ? ($ausencia->group->course . ' ' . $ausencia->group->name) : 'Grupo Sin Asignar' }}
                                                            </span>
                                                            <span style="color: var(--text-muted); opacity: 0.6;">·</span>
                                                            <span style="font-size: 0.825rem; font-weight: 700; color: var(--text-heading);">
                                                                📍 {{ $ausencia->zona ? $ausencia->zona->nombre : 'Aula habitual' }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                                    @if($isCovered)
                                                        <span style="font-size: 0.7rem; font-weight: 800; color: #10b981; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); padding: 0.25rem 0.55rem; border-radius: 9999px;">
                                                            ✓ CUBIERTA
                                                        </span>
                                                    @elseif($isExempt)
                                                        <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); background: var(--bg-hover); padding: 0.25rem 0.55rem; border-radius: 9999px;">
                                                            NO REQUIERE
                                                        </span>
                                                    @else
                                                        <span style="font-size: 0.7rem; font-weight: 900; color: #ef4444; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); padding: 0.25rem 0.55rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                                            <span style="width: 5px; height: 5px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                                                            SIN CUBRIR
                                                        </span>
                                                    @endif

                                                    @if($ausencia->canBeDeletedBy(Auth::user()))
                                                        <form action="{{ route('ausencias.destroy', $ausencia) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas borrar esta ausencia del parte?')" style="margin: 0;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-delete-ausencia" title="Borrar esta ausencia del parte">
                                                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Teacher profile & Quick Task Trigger -->
                                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.6rem; padding: 0.6rem 0.75rem; background: var(--bg-input); border-radius: 0.625rem; border: 1px solid var(--border); margin-bottom: 0.85rem;">
                                                <div style="display: flex; align-items: center; gap: 0.6rem; overflow: hidden;">
                                                    @if($ausencia->user->avatar_url)
                                                        <img src="{{ $ausencia->user->avatar_url }}" alt="" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border); flex-shrink: 0;">
                                                    @else
                                                        <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #f43f5e, #f59e0b); color: #fff; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                            {{ strtoupper(substr($ausencia->user->name ?? 'P', 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div style="overflow: hidden; flex: 1;">
                                                    <div style="font-size: 0.65rem; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Docente Ausente</div>
                                                    <div style="font-size: 0.875rem; font-weight: 800; color: var(--text-heading); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        {{ $ausencia->user->name ?? 'Docente' }} {{ $ausencia->user->last_name ?? '' }}
                                                    </div>
                                                    @if($ausencia->user->departamento)
                                                        <div style="font-size: 0.7rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                            {{ $ausencia->user->departamento }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <button type="button" 
                                                        onclick="openTaskModal('{{ addslashes($ausencia->user->name . ' ' . ($ausencia->user->last_name ?? '')) }}', '{{ addslashes($ausencia->group ? ($ausencia->group->course . ' ' . $ausencia->group->name) : 'Grupo') }}', '{{ addslashes($ausencia->tarea ?? 'Sin tarea especificada.') }}', '{{ addslashes($ausencia->enlace_tarea ?? '') }}')"
                                                        class="btn btn-secondary btn-sm"
                                                        style="padding: 0.4rem 0.65rem; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap; flex-shrink: 0;"
                                                        title="Ver instrucciones y tarea pedagógica">
                                                    <span>📄 Ver Tarea</span>
                                                    @if($ausencia->enlace_tarea)
                                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
                                                    @endif
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Bottom Action & Coverage Status -->
                                        <div style="padding-top: 0.75rem; border-top: 1px solid var(--border); margin-top: 0.25rem;">
                                            @if($isCovered)
                                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem;">
                                                    <div style="font-size: 0.825rem; color: #10b981; font-weight: 700;">
                                                        ✓ Cubierta por: <strong style="color: var(--text-heading);">{{ $ausencia->guardiaUser->name ?? 'Docente' }}</strong>
                                                    </div>

                                                    @if($ausencia->guardia_user_id === Auth::id() || Auth::user()->hasRole('admin') || Auth::user()->hasRole('directiva'))
                                                        <form action="{{ route('guardias.desconfirmar', $ausencia) }}" method="POST" onsubmit="return confirm('¿Deseas desconfirmar esta guardia?')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-secondary btn-sm" style="font-size: 0.7rem; padding: 0.25rem 0.5rem; color: #ef4444;" title="Cancelar confirmación">
                                                                Liberar
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @else
                                                <!-- Confirm Action -->
                                                @php
                                                    $recommendedDocente = $disponibles->firstWhere('is_recommended', true);
                                                    $isUserOnGuardSlot = $disponibles->contains('user_id', Auth::id());
                                                    $canAssignGuardia = Auth::user()->hasAnyRole(['admin', 'directiva']) || $isUserOnGuardSlot;
                                                @endphp

                                                <form action="{{ route('guardias.confirmar', $ausencia) }}" method="POST">
                                                    @csrf
                                                    @if($canAssignGuardia && $disponibles->isNotEmpty())
                                                        <div style="margin-bottom: 0.5rem;">
                                                            <label for="select_guardia_{{ $ausencia->id }}" style="display: block; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.25rem;">
                                                                Profesor que realiza la guardia (orden de equidad):
                                                            </label>
                                                            <select id="select_guardia_{{ $ausencia->id }}" name="guardia_user_id" class="form-control" style="padding: 0.4rem 0.6rem; font-size: 0.75rem; width: 100%;">
                                                                @foreach($disponibles as $disp)
                                                                    <option value="{{ $disp['user_id'] }}" {{ ($recommendedDocente && $recommendedDocente['user_id'] == $disp['user_id']) ? 'selected' : '' }}>
                                                                        {{ $disp['name'] }} {{ !empty($disp['departamento']) ? '(' . $disp['departamento'] . ')' : '' }} — {{ $disp['guardias_count'] }} h.{{ $disp['is_recommended'] ? ' ⭐ Recomendado' : '' }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endif

                                                    <div style="display: flex; gap: 0.4rem; align-items: center;">
                                                        @if($canAssignGuardia || $disponibles->isEmpty())
                                                            <button type="submit" class="btn btn-primary" style="flex: 1; font-size: 0.825rem; padding: 0.55rem; font-weight: 800; justify-content: center; background: linear-gradient(135deg, #10b981, #059669); border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                                                                ✓ Confirmar Guardia
                                                            </button>

                                                            @if($isUserOnGuardSlot)
                                                                <button type="button" 
                                                                        onclick="const s = document.getElementById('select_guardia_{{ $ausencia->id }}'); if(s) s.value = '{{ Auth::id() }}'; this.form.submit();"
                                                                        class="btn btn-secondary" 
                                                                        style="font-size: 0.75rem; padding: 0.55rem 0.75rem; font-weight: 800; color: var(--primary); border-color: var(--primary-border);"
                                                                        title="Asignarme directamente a mí y confirmar">
                                                                    🛡️ Asignarme
                                                                </button>
                                                            @endif
                                                        @else
                                                            <div style="font-size: 0.75rem; color: var(--text-muted); text-align: center; padding: 0.4rem; background: var(--bg-hover); border-radius: 0.5rem; border: 1px solid var(--border); width: 100%;">
                                                                ℹ️ Solo directivos o profesores de guardia a esta hora pueden confirmar.
                                                            </div>
                                                        @endif
                                                    </div>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Available Teachers on Guard duty during this slot -->
                        <div style="padding-top: 0.75rem; border-top: 1px dashed var(--border);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.65rem;">
                                <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>⚖️ Claustro de guardia en esta hora</span>
                                    <span style="font-size: 0.65rem; font-weight: 500; opacity: 0.7;">(Ordenado por equidad)</span>
                                </span>
                            </div>

                            @if($disponibles->isNotEmpty() || $slotAusentes->isNotEmpty())
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    @foreach($disponibles as $disp)
                                        <div class="teacher-chip {{ $disp['is_recommended'] ? 'is-priority' : '' }}">
                                            <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--bg-hover); color: var(--primary); font-weight: 800; font-size: 0.7rem; display: flex; align-items: center; justify-content: center;">
                                                {{ strtoupper(substr($disp['name'], 0, 1)) }}
                                            </div>
                                            <div style="font-weight: 600; color: var(--text-heading);">
                                                {{ $disp['name'] }}
                                                @if(!empty($disp['departamento']))
                                                    <span style="font-size: 0.7rem; font-weight: normal; color: var(--text-muted);">({{ $disp['departamento'] }})</span>
                                                @endif
                                            </div>
                                            <span style="font-size: 0.7rem; color: var(--text-muted);">
                                                ({{ $disp['guardias_count'] }} h.)
                                            </span>
                                            @if($disp['is_recommended'])
                                                <span style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase; background: var(--primary-light); color: var(--primary); padding: 0.1rem 0.35rem; border-radius: 4px;">
                                                    Recomendado
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach

                                    @foreach($slotAusentes as $ausente)
                                        <div class="teacher-chip" style="border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.08);" title="Este docente tiene turno de guardia en esta hora pero ha registrado ausencia">
                                            <div style="width: 22px; height: 22px; border-radius: 50%; background: rgba(239, 68, 68, 0.2); color: #ef4444; font-weight: 800; font-size: 0.7rem; display: flex; align-items: center; justify-content: center;">
                                                ✕
                                            </div>
                                            <div style="font-weight: 600; color: #ef4444; text-decoration: line-through;">
                                                {{ $ausente['name'] }}
                                                @if(!empty($ausente['departamento']))
                                                    <span style="font-size: 0.7rem; font-weight: normal; text-decoration: none; color: #ef4444;">({{ $ausente['departamento'] }})</span>
                                                @endif
                                            </div>
                                            <span style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase; background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 0.1rem 0.35rem; border-radius: 4px;">
                                                🔴 Ausente
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                                    No hay profesores con tramo de guardia configurado en su horario para esta hora.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <div class="card" style="padding: 3rem; text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 0.75rem;">🕒</div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.25rem;">No hay tramos horarios configurados</h3>
                    <p style="color: var(--text-muted); font-size: 0.875rem;">Crea una plantilla de horarios en el panel del centro educativo.</p>
                </div>
            @endforelse
        </div>

    </div>

</div>

<!-- Modal Apuntar en el Parte -->
<div id="apuntarParteModal" class="apuntar-modal-backdrop" onclick="handleBackdropClick(event)">
    <div class="apuntar-modal-content">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="font-size: 1.3rem;">📝</span>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: var(--text-heading);">Apuntar en el Parte de Guardia</h3>
            </div>
            <button type="button" onclick="closeApuntarModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer; padding: 0.2rem 0.5rem; border-radius: 0.5rem;">
                ✕
            </button>
        </div>

        <form action="{{ route('ausencias.store') }}" method="POST" style="display: flex; flex-direction: column; overflow-y: auto; padding: 1.5rem; gap: 1rem;">
            @csrf
            <input type="hidden" name="redirect_to" value="parte">

            <!-- Día / Fecha -->
            <div>
                <label for="modal_fecha" style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">
                    📅 Día de la Ausencia <span style="color: var(--danger);">*</span>
                </label>
                <input type="date" name="fecha" id="modal_fecha" value="{{ $date }}" required class="form-control" style="width: 100%; cursor: pointer;">
            </div>

            <!-- Profesor Ausente -->
            <div>
                <label for="modal_user_id" style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">
                    👤 Profesor Ausente <span style="color: var(--danger);">*</span>
                </label>
                <select name="user_id" id="modal_user_id" required class="form-control" style="width: 100%;">
                    <option value="{{ Auth::id() }}">Yo mismo ({{ Auth::user()->name }} {{ Auth::user()->last_name ?? '' }})</option>
                    @foreach($docentes as $docente)
                        @if($docente->id !== Auth::id())
                            <option value="{{ $docente->id }}">
                                {{ $docente->name }} {{ $docente->last_name ?? '' }} {{ !empty($docente->departamento) ? '(' . $docente->departamento . ')' : '' }} - {{ $docente->email }}
                            </option>
                        @endif
                    @endforeach
                </select>
                <span style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.2rem; display: block;">
                    Puedes registrar tu propia falta o seleccionar a otro compañero que haya avisado de su ausencia.
                </span>
            </div>

            <!-- Tramo Horario (Hª) -->
            <div>
                <label for="modal_time_slot_id" style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">
                    ⏰ Hora Lectiva (Hª) <span style="color: var(--danger);">*</span>
                </label>
                <select name="time_slot_id" id="modal_time_slot_id" required class="form-control" style="width: 100%;">
                    @foreach($timeSlots as $slot)
                        <option value="{{ $slot->id }}" {{ ($currentSlotId == $slot->id) ? 'selected' : '' }}>
                            {{ $slot->name }} ({{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Fila: Grupo y Aula -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div>
                    <label for="modal_group_id" style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">
                        👥 Grupo
                    </label>
                    <select name="group_id" id="modal_group_id" class="form-control" style="width: 100%;">
                        <option value="">-- Sin asignar / Otro --</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->course }} {{ $grupo->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="modal_zona_id" style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">
                        📍 Aula / Espacio
                    </label>
                    <select name="zona_id" id="modal_zona_id" class="form-control" style="width: 100%;">
                        <option value="">-- Aula habitual --</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Tarea para los alumnos -->
            <div>
                <label for="modal_tarea" style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">
                    📝 Tarea para el alumnado <span style="color: var(--danger);">*</span>
                </label>
                <textarea name="tarea" id="modal_tarea" rows="3" required class="form-control" 
                          placeholder="Indica qué actividades, ejercicios o lectura debe realizar el alumnado durante la guardia..." 
                          style="width: 100%; resize: vertical;"></textarea>
            </div>

            <!-- Enlace Classroom / Material (opcional) -->
            <div>
                <label for="modal_enlace_tarea" style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.3rem;">
                    🔗 Enlace a material online (opcional)
                </label>
                <input type="url" name="enlace_tarea" id="modal_enlace_tarea" class="form-control" placeholder="https://classroom.google.com/..." style="width: 100%;">
            </div>

            <!-- Checkbox: Es mi hora de guardia -->
            <div style="padding: 0.75rem; background: var(--bg-hover); border-radius: 0.65rem; border: 1px solid var(--border);">
                <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; font-weight: 700; color: var(--text-heading); cursor: pointer;">
                    <input type="checkbox" name="es_guardia" value="1" id="modal_es_guardia" style="width: 16px; height: 16px; accent-color: var(--primary);">
                    <span>🛡️ Es mi hora de Guardia (Guardia virtual que no requiere sustitución en aula)</span>
                </label>
            </div>

            <!-- Botones de Acción -->
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border); margin-top: 0.5rem;">
                <button type="button" onclick="closeApuntarModal()" class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-weight: 800; background: linear-gradient(135deg, var(--primary), #4f46e5);">
                    Guardar en el Parte
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rápido: Ver Tarea Pedagógica (Desktop) -->
<div id="taskModal" class="apuntar-modal-backdrop" onclick="handleTaskBackdropClick(event)">
    <div class="apuntar-modal-content" style="max-width: 520px;">
        
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(2, 132, 199, 0.12); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                    📄
                </div>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text-heading); margin: 0;">
                        Tarea Pedagógica
                    </h3>
                    <p id="taskModalSubtitle" style="font-size: 0.75rem; color: var(--text-muted); margin: 0.15rem 0 0 0; font-weight: 600;">
                        Grupo
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeTaskModal()" style="background: transparent; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; padding: 0.25rem 0.5rem; border-radius: 0.5rem;">
                ✕
            </button>
        </div>

        <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label style="display: block; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.4rem; letter-spacing: 0.05em;">
                    Instrucciones para el aula:
                </label>
                <div id="taskModalBody" style="padding: 1rem; border-radius: 0.75rem; background: var(--bg-input); border: 1px solid var(--border); font-size: 0.875rem; color: var(--text-color); line-height: 1.5; white-space: pre-wrap; max-height: 240px; overflow-y: auto;">
                    Sin tarea especificada.
                </div>
            </div>

            <div id="taskModalLinkContainer" style="display: none;">
                <label style="display: block; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.4rem; letter-spacing: 0.05em;">
                    Material Online / Classroom:
                </label>
                <a id="taskModalLink" href="#" target="_blank" rel="noopener noreferrer" 
                   style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; border-radius: 0.75rem; background: rgba(2, 132, 199, 0.08); border: 1px solid rgba(2, 132, 199, 0.2); color: var(--primary); font-size: 0.8rem; font-weight: 700; text-decoration: none; transition: background 0.15s ease;">
                    <span style="display: flex; align-items: center; gap: 0.5rem; overflow: hidden;">
                        <span>🔗</span>
                        <span id="taskModalLinkText" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Abrir enlace de material</span>
                    </span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>

        <div style="padding: 1rem 1.5rem; background: var(--bg-hover); border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeTaskModal()" class="btn btn-secondary" style="padding: 0.45rem 1rem; font-size: 0.8rem;">
                Cerrar
            </button>
        </div>

    </div>
</div>

<script>
    /* Estado reactivo Alpine.js para la vista móvil */
    function mobileParteState() {
        return {
            activeSlotFilter: 'all',
            expandedSlots: {
                @foreach($timeSlots as $slot)
                    @php
                        $slotAusencias = $ausencias->get($slot->id, collect());
                        $isCurrentSlot = ($currentSlotId === $slot->id);
                        $isOpenDefault = $isCurrentSlot || $slotAusencias->isNotEmpty();
                    @endphp
                    '{{ $slot->id }}': {{ $isOpenDefault ? 'true' : 'false' }},
                @endforeach
            },
            taskDrawer: {
                open: false,
                teacher: '',
                group: '',
                task: '',
                link: ''
            },
            facultyExpanded: {},
            filterMobileSlot(slotId) {
                this.activeSlotFilter = slotId;
                if (slotId !== 'all') {
                    this.expandedSlots[slotId] = true;
                    this.$nextTick(() => {
                        const el = document.getElementById('mobile-slot-' + slotId);
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                }
            },
            toggleMobileSlot(slotId) {
                this.expandedSlots[slotId] = !this.expandedSlots[slotId];
            },
            openMobileTaskDrawer(teacher, group, task, link) {
                this.taskDrawer = {
                    open: true,
                    teacher: teacher,
                    group: group,
                    task: task,
                    link: link
                };
                document.body.style.overflow = 'hidden';
            },
            closeMobileTaskDrawer() {
                this.taskDrawer.open = false;
                document.body.style.overflow = '';
            },
            toggleFaculty(slotId) {
                this.facultyExpanded[slotId] = !this.facultyExpanded[slotId];
            }
        };
    }

    /* Funciones para escritorio y compatibilidad */
    function toggleSlotAccordion(slotId) {
        const content = document.getElementById('slot-content-' + slotId);
        const trigger = document.getElementById('slot-trigger-' + slotId);
        const chevron = document.getElementById('slot-chevron-' + slotId);

        if (!content) return;

        const isHidden = (content.style.display === 'none');
        if (isHidden) {
            content.style.display = 'block';
            if (trigger) trigger.setAttribute('aria-expanded', 'true');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            content.style.display = 'none';
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    }

    function openTaskModal(teacherName, groupName, taskText, taskLink) {
        const modal = document.getElementById('taskModal');
        const subtitle = document.getElementById('taskModalSubtitle');
        const body = document.getElementById('taskModalBody');
        const linkContainer = document.getElementById('taskModalLinkContainer');
        const link = document.getElementById('taskModalLink');

        if (subtitle) subtitle.textContent = groupName + ' · ' + teacherName;
        if (body) body.textContent = taskText;

        if (linkContainer && link) {
            if (taskLink && taskLink.trim() !== '') {
                link.href = taskLink;
                linkContainer.style.display = 'block';
            } else {
                linkContainer.style.display = 'none';
            }
        }

        if (modal) {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeTaskModal() {
        const modal = document.getElementById('taskModal');
        if (modal) {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }

    function handleTaskBackdropClick(event) {
        if (event.target.id === 'taskModal') {
            closeTaskModal();
        }
    }

    function openApuntarModal(slotId = null) {
        const modal = document.getElementById('apuntarParteModal');
        if (modal) {
            if (slotId) {
                const slotSelect = document.getElementById('modal_time_slot_id');
                if (slotSelect) slotSelect.value = slotId;
            }
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeApuntarModal() {
        const modal = document.getElementById('apuntarParteModal');
        if (modal) {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }

    function handleBackdropClick(event) {
        if (event.target.id === 'apuntarParteModal') {
            closeApuntarModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeApuntarModal();
            closeTaskModal();
        }
    });

    function filterSlot(slotId, btn) {
        document.querySelectorAll('.slot-filter-btn').forEach(b => b.classList.remove('active'));
        if (btn) {
            btn.classList.add('active');
        } else {
            const activeBtn = document.getElementById('filter-slot-' + slotId);
            if (activeBtn) activeBtn.classList.add('active');
        }

        const allSlots = document.querySelectorAll('.slot-card[data-slot-id]');
        allSlots.forEach(card => {
            if (slotId === 'all' || card.getAttribute('data-slot-id') == slotId) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // If a specific slot is selected, open its accordion and preselect it in apuntar modal
        if (slotId !== 'all') {
            const content = document.getElementById('slot-content-' + slotId);
            const trigger = document.getElementById('slot-trigger-' + slotId);
            const chevron = document.getElementById('slot-chevron-' + slotId);
            if (content) {
                content.style.display = 'block';
                if (trigger) trigger.setAttribute('aria-expanded', 'true');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            }

            const modalSlot = document.getElementById('modal_time_slot_id');
            if (modalSlot) modalSlot.value = slotId;
        }
    }

    // Check if slot query parameter is passed (e.g. ?slot=3)
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const slotParam = urlParams.get('slot');
        if (slotParam) {
            const btn = document.getElementById('filter-slot-' + slotParam);
            if (btn) {
                filterSlot(slotParam, btn);
            }
        }
    });
</script>
@endsection
