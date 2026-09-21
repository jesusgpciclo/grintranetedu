@extends('layouts.app')

@section('title', 'Control de Justificaciones de Ausencias')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>📑 Control de Ausencias</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Seguimiento y control de justificaciones de ausencias del profesorado
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span id="kpi-pending">{{ $pendingCount }}</span> pendientes
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span id="kpi-justified">{{ $justifiedCount }}</span> justificadas
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-200">✕</button>
        </div>
    @endif

    <!-- Search & Filter Card (Floating Bar aesthetic) -->
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-3xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-xl transition-all">
        <form method="GET" action="{{ route('guardias.justificaciones') }}" class="flex flex-wrap items-center gap-3">
            
            <!-- Search Text -->
            <div class="flex-1 min-w-[220px]">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        🔍
                    </span>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           placeholder="Buscar por profesor, email, departamento..." 
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition">
                </div>
            </div>

            <!-- Date From -->
            <div class="flex items-center gap-2">
                <label for="start_date" class="text-xs font-semibold text-slate-600 dark:text-slate-400 whitespace-nowrap">Desde:</label>
                <input type="date" 
                       id="start_date"
                       name="start_date" 
                       value="{{ $startDate }}" 
                       class="px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-2xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary transition">
            </div>

            <!-- Date To -->
            <div class="flex items-center gap-2">
                <label for="end_date" class="text-xs font-semibold text-slate-600 dark:text-slate-400 whitespace-nowrap">Hasta:</label>
                <input type="date" 
                       id="end_date"
                       name="end_date" 
                       value="{{ $endDate }}" 
                       class="px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-2xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary transition">
            </div>

            <!-- Status Filter -->
            <div class="flex items-center gap-2">
                <select name="status" class="px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 rounded-2xl text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary transition">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Todos los estados</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Solo pendientes</option>
                    <option value="justified" {{ $status === 'justified' ? 'selected' : '' }}>Solo justificadas</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-2xl text-xs font-bold shadow-md shadow-primary/20 transition cursor-pointer">
                    Filtrar
                </button>
                @if($search || $startDate || $endDate || $status !== 'all')
                    <a href="{{ route('guardias.justificaciones') }}" class="px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-2xl text-xs font-semibold transition" title="Limpiar filtros">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    @php
        $diasSemana = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miércoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sábado',
            7 => 'domingo',
        ];
    @endphp

    <!-- Table Card (Matching Image 2 layout) -->
    <div class="bg-white dark:bg-slate-900/80 backdrop-blur-md rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm dark:shadow-xl transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/40 text-xs font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                        <th class="py-4 px-6">Prof.Ausente</th>
                        <th class="py-4 px-4">fecha</th>
                        <th class="py-4 px-4">dia_sem</th>
                        <th class="py-4 px-4 text-center">clases ausente</th>
                        <th class="py-4 px-6 text-center">justificado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($daysList as $day)
                        @php
                            $key = $day->user_id . '_' . \Carbon\Carbon::parse($day->fecha)->format('Y-m-d');
                            $ausenciasOfThisDay = $ausenciasGrouped->get($key, collect());
                            $user = $ausenciasOfThisDay->first()?->user;
                            $dateCarbon = \Carbon\Carbon::parse($day->fecha);
                            $dayName = $diasSemana[$dateCarbon->dayOfWeekIso] ?? '';
                            $isDayFullyJustified = ($day->justificada_count == $day->total_clases && $day->total_clases > 0);
                            $isPartiallyJustified = ($day->justificada_count > 0 && $day->justificada_count < $day->total_clases);
                        @endphp

                        <!-- Main Row -->
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/25 transition group">
                            
                            <!-- Prof. Ausente -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3.5">
                                    @if($user && $user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover shadow-sm border border-slate-200 dark:border-slate-700">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-primary text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                            {{ strtoupper(substr($user->name ?? 'P', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span>{{ $user->name ?? 'Profesor' }} {{ $user->last_name ?? '' }}</span>
                                            @if($user && $user->departamento)
                                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">({{ $user->departamento }})</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500">
                                            {{ $user->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Fecha -->
                            <td class="py-4 px-4 text-slate-700 dark:text-slate-300 font-mono text-xs">
                                {{ $dateCarbon->format('Y-m-d') }}
                            </td>

                            <!-- Día de la semana -->
                            <td class="py-4 px-4 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $dayName }}
                            </td>

                            <!-- Clases Ausente -->
                            <td class="py-4 px-4 text-center">
                                <button type="button" 
                                        onclick="toggleDetails('details-{{ $key }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500 hover:bg-emerald-600 !text-white shadow-sm transition cursor-pointer"
                                        title="Ver desglose de horas y grupos">
                                    <span>{{ $day->total_clases }} {{ $day->total_clases == 1 ? 'clase' : 'clases' }}</span>
                                    <svg class="w-3.5 h-3.5 transition-transform" id="chevron-{{ $key }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </td>

                            <!-- Justificado Toggle (Day Switch) -->
                            <td class="py-4 px-6 text-center">
                                <div class="flex flex-col items-center justify-center gap-1">
                                    <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">justificar día</span>
                                    
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               class="sr-only peer day-toggle-input" 
                                               data-user-id="{{ $day->user_id }}" 
                                               data-fecha="{{ $dateCarbon->format('Y-m-d') }}"
                                               data-group-key="{{ $key }}"
                                               {{ $isDayFullyJustified ? 'checked' : '' }}>
                                        
                                        <!-- Switch track & knob -->
                                        <div class="w-11 h-6 bg-slate-300 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    </label>
                                </div>
                            </td>
                        </tr>

                        <!-- Expandable Hour Breakdown Details -->
                        <tr id="details-{{ $key }}" class="hidden bg-slate-50/90 dark:bg-slate-950/40 border-b border-slate-200 dark:border-slate-800">
                            <td colspan="5" class="py-3 px-8">
                                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2 flex items-center justify-between">
                                    <span>Desglose horario del día ({{ $dateCarbon->format('d/m/Y') }}):</span>
                                    <span class="text-[11px] text-slate-400">Puedes justificar horas individuales si no se ausentó todo el día</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                    @foreach($ausenciasOfThisDay as $ausencia)
                                        <div class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shadow-xs">
                                            <div>
                                                <div class="font-bold text-slate-800 dark:text-slate-200">
                                                    {{ $ausencia->timeSlot ? $ausencia->timeSlot->name : 'Hora sin asignar' }}
                                                </div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                    @if($ausencia->es_guardia)
                                                        <span class="text-purple-600 dark:text-purple-400 font-semibold">Hora de Guardia</span>
                                                    @else
                                                        {{ $ausencia->group ? ($ausencia->group->course . ' ' . $ausencia->group->name) : 'Sin grupo' }}
                                                        @if($ausencia->zona)
                                                            <span class="text-slate-400">({{ $ausencia->zona->nombre }})</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                @if($ausencia->tarea)
                                                    <div class="text-[10px] text-slate-400 mt-0.5 truncate max-w-[180px]" title="{{ $ausencia->tarea }}">
                                                        Tarea: {{ $ausencia->tarea }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button type="button" 
                                                        onclick="toggleSingleAusencia({{ $ausencia->id }}, this, '{{ $key }}')" 
                                                        class="single-ausencia-btn px-2.5 py-1 rounded-xl text-[11px] font-semibold transition cursor-pointer {{ $ausencia->justificada ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300 dark:border-slate-700' }}"
                                                        data-justificada="{{ $ausencia->justificada ? '1' : '0' }}">
                                                    {{ $ausencia->justificada ? '✓ Justificada' : 'Pendiente' }}
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 dark:text-slate-500 text-sm">
                                No se encontraron registros de ausencias con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($daysList->hasPages())
            <div class="p-4 bg-slate-50/50 dark:bg-slate-950/40 border-t border-slate-200 dark:border-slate-800">
                {{ $daysList->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Toast notification helper -->
<div id="toast-notification" class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
    <div class="px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-2xl flex items-center gap-2.5 text-xs font-semibold">
        <span id="toast-icon">✓</span>
        <span id="toast-message">Mensaje</span>
    </div>
</div>

<script>
    function toggleDetails(rowId) {
        const row = document.getElementById(rowId);
        const key = rowId.replace('details-', '');
        const chevron = document.getElementById('chevron-' + key);
        
        if (row.classList.contains('hidden')) {
            row.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            row.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    function showToast(message, isSuccess = true) {
        const toast = document.getElementById('toast-notification');
        const msg = document.getElementById('toast-message');
        const icon = document.getElementById('toast-icon');
        
        msg.innerText = message;
        icon.innerText = isSuccess ? '✓' : '⚠️';
        
        toast.classList.remove('translate-y-20', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
        
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
            toast.classList.remove('translate-y-0', 'opacity-100');
        }, 2500);
    }

    // Toggle entire day
    document.querySelectorAll('.day-toggle-input').forEach(input => {
        input.addEventListener('change', async function() {
            const userId = this.dataset.userId;
            const fecha = this.dataset.fecha;
            const groupKey = this.dataset.groupKey;
            const isChecked = this.checked;
            const originalState = !isChecked;

            try {
                const response = await fetch("{{ route('guardias.justificar-dia') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        fecha: fecha,
                        justificada: isChecked ? 1 : 0
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showToast(data.message || 'Justificación actualizada.');
                    
                    // Update single buttons inside details row if open
                    const detailsRow = document.getElementById('details-' + groupKey);
                    if (detailsRow) {
                        detailsRow.querySelectorAll('.single-ausencia-btn').forEach(btn => {
                            btn.dataset.justificada = isChecked ? '1' : '0';
                            if (isChecked) {
                                btn.innerText = '✓ Justificada';
                                btn.className = 'single-ausencia-btn px-2.5 py-1 rounded-xl text-[11px] font-semibold transition cursor-pointer bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30';
                            } else {
                                btn.innerText = 'Pendiente';
                                btn.className = 'single-ausencia-btn px-2.5 py-1 rounded-xl text-[11px] font-semibold transition cursor-pointer bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300 dark:border-slate-700';
                            }
                        });
                    }
                } else {
                    this.checked = originalState;
                    showToast(data.message || 'Error al actualizar justificación.', false);
                }
            } catch (err) {
                console.error(err);
                this.checked = originalState;
                showToast('Error de conexión con el servidor.', false);
            }
        });
    });

    // Toggle individual ausencia inside expandable details
    async function toggleSingleAusencia(ausenciaId, btnElement, groupKey) {
        const currentJustificada = btnElement.dataset.justificada === '1';
        const nextState = !currentJustificada;

        try {
            const response = await fetch(`/guardias/justificar/${ausenciaId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    justificada: nextState ? 1 : 0
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                btnElement.dataset.justificada = data.justificada ? '1' : '0';
                if (data.justificada) {
                    btnElement.innerText = '✓ Justificada';
                    btnElement.className = 'single-ausencia-btn px-2.5 py-1 rounded-xl text-[11px] font-semibold transition cursor-pointer bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30';
                } else {
                    btnElement.innerText = 'Pendiente';
                    btnElement.className = 'single-ausencia-btn px-2.5 py-1 rounded-xl text-[11px] font-semibold transition cursor-pointer bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300 dark:border-slate-700';
                }

                // Check if all single buttons in this group are now justified
                const detailsRow = document.getElementById('details-' + groupKey);
                if (detailsRow) {
                    const allBtns = Array.from(detailsRow.querySelectorAll('.single-ausencia-btn'));
                    const allChecked = allBtns.every(b => b.dataset.justificada === '1');
                    const mainToggle = document.querySelector(`.day-toggle-input[data-group-key="${groupKey}"]`);
                    if (mainToggle) {
                        mainToggle.checked = allChecked;
                    }
                }

                showToast(data.message || 'Ausencia actualizada.');
            } else {
                showToast(data.message || 'Error al actualizar.', false);
            }
        } catch (err) {
            console.error(err);
            showToast('Error de conexión.', false);
        }
    }
</script>
@endsection
