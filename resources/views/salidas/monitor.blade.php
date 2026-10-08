@extends('layouts.app')

@section('title', 'Gestor de salidas - Monitor Conserjería')

@section('module_title')
Monitor de <span class="text-blue-500">salidas</span>
@endsection

@php
    $canReturn = auth()->user() && (
        auth()->user()->can('salidas.return_monitor') || 
        auth()->user()->can('salidas.manage') || 
        auth()->user()->hasRole(['admin', 'jefatura', 'directiva', 'director'])
    );
@endphp

@section('content')
    <div class="py-1 sm:py-2">
        <div class="max-w-7xl mx-auto">
            <div class="card overflow-hidden">
                <!-- Header de la tarjeta con botón de volver y contador de alumnos fuera -->
                <div class="p-3 sm:p-4 bg-[var(--bg-surface)] border-b border-[var(--border)] flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        @if(auth()->user()->can('salidas.create') || auth()->user()->hasRole(['admin', 'jefatura', 'directiva', 'director', 'profesor']))
                        <a href="{{ route('salidas.index') }}" class="p-1.5 sm:p-2 bg-[var(--bg-card)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all group shrink-0 shadow-xs" title="Volver al Gestor">
                            <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        @endif
                        <div class="flex items-center gap-2">
                            <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[var(--text-muted)]">Alumnos fuera actualmente:</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs sm:text-sm font-extrabold bg-blue-500 text-white shadow-xs">
                                {{ $activePasses->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-[var(--text-muted)] hidden sm:block">
                        Vista en lista de alta capacidad
                    </div>

                    <!-- Indicador de accesibilidad para modo de supervisión/control -->
                    <span class="sr-only">
                        @if($canReturn)
                            Control de Pasillo Activo
                        @else
                            Modo Supervisión (Solo Lectura)
                        @endif
                    </span>
                </div>

                @if($activePasses->isEmpty())
                    <div class="text-center py-16 text-[var(--text-muted)]">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-[var(--text-heading)]">Pasillo despejado</h3>
                        <p class="text-sm text-[var(--text-muted)] mt-1">No hay alumnos en el pasillo ahora mismo.</p>
                    </div>
                @else
                    <!-- Desktop & Tablet Table List -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[var(--border)] bg-[var(--bg-card)] text-[11px] font-extrabold uppercase tracking-wider text-[var(--text-muted)]">
                                    <th class="py-3 px-4">Estado</th>
                                    <th class="py-3 px-4">Alumno</th>
                                    <th class="py-3 px-4">Grupo</th>
                                    <th class="py-3 px-4">Motivo</th>
                                    <th class="py-3 px-4">Tiempo Fuera</th>
                                    <th class="py-3 px-4">Profesor Autorizante</th>
                                    <th class="py-3 px-4 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                @foreach($activePasses as $pass)
                                    @php
                                        $studentTodayPasses = isset($todayPassesByStudent) ? $todayPassesByStudent->get($pass->user_id, collect()) : collect();
                                        $todayCount = $studentTodayPasses->count();
                                    @endphp
                                    <tr id="pass-card-{{ $pass->id }}" class="hover:bg-[var(--bg-hover)] transition-colors {{ $todayCount >= 3 ? 'bg-rose-500/5' : '' }}">
                                        <!-- Estado (Ping animado) -->
                                        <td class="py-3.5 px-4 w-12 text-center">
                                            <span class="flex h-3 w-3 relative mx-auto">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                            </span>
                                        </td>

                                        <!-- Alumno -->
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="h-11 w-11 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white font-black text-sm shadow-xs shrink-0">
                                                    {{ substr($pass->student?->last_name ?? $pass->student?->name ?? 'A', 0, 1) }}{{ substr($pass->student?->name ?? '', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-lg sm:text-xl font-black text-[var(--text-heading)] tracking-tight">
                                                            {{ $pass->student?->last_name ? trim($pass->student->last_name . ', ' . $pass->student->name) : ($pass->student?->name ?? 'Alumno') }}
                                                        </span>
                                                        @if($todayCount > 1)
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $todayCount >= 3 ? 'bg-rose-500/15 text-rose-500 border border-rose-500/30' : 'bg-amber-500/15 text-amber-500 border border-amber-500/30' }}">
                                                                {{ $todayCount }}ª salida hoy
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Grupo -->
                                        <td class="py-3.5 px-4">
                                            <span class="text-xs font-semibold text-[var(--text-muted)]">
                                                {{ $pass->student?->groupRel?->course ?? '' }} {{ $pass->student?->groupRel?->name ?? '' }}
                                            </span>
                                        </td>

                                        <!-- Motivo -->
                                        <td class="py-3.5 px-4">
                                            @php
                                                $reasonLower = strtolower($pass->reason);
                                                $reasonBadgeClass = match(true) {
                                                    str_contains($reasonLower, 'baño') || str_contains($reasonLower, 'aseo') || str_contains($reasonLower, 'servicio') => 'bg-blue-500/15 text-blue-600 dark:text-blue-400 border-blue-500/30',
                                                    str_contains($reasonLower, 'agua') => 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border-cyan-500/30',
                                                    str_contains($reasonLower, 'enferm') => 'bg-orange-500/15 text-orange-600 dark:text-orange-400 border-orange-500/30',
                                                    default => 'bg-purple-500/15 text-purple-600 dark:text-purple-400 border-purple-500/30',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $reasonBadgeClass }}">
                                                <span>{{ $pass->reason }}</span>
                                            </span>
                                        </td>

                                        <!-- Tiempo Fuera -->
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-baseline gap-2">
                                                <span class="text-base font-mono font-black text-blue-600 dark:text-blue-400 timer" data-start="{{ $pass->start_time->timestamp }}">
                                                    00:00
                                                </span>
                                                <span class="text-[11px] text-[var(--text-muted)]">
                                                    (desde {{ $pass->start_time->format('H:i') }})
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Profesor Autorizante -->
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2">
                                                <div class="p-1 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                </div>
                                                <span class="text-xs sm:text-sm font-bold text-[var(--text-heading)]">
                                                    {{ $pass->teacher ? trim($pass->teacher->name . ' ' . ($pass->teacher->last_name ?? '')) : 'Sin asignar' }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Acción -->
                                        <td class="py-3.5 px-4 text-right">
                                            @if($canReturn)
                                                <button onclick="endPass({{ $pass->id }})" id="btn-return-{{ $pass->id }}"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-xs transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Regresar alumno</span>
                                                </button>
                                            @else
                                                <span class="sr-only">Solo lectura</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile List Items (compact horizontal cards) -->
                    <div class="md:hidden divide-y divide-[var(--border)]">
                        @foreach($activePasses as $pass)
                            @php
                                $studentTodayPasses = isset($todayPassesByStudent) ? $todayPassesByStudent->get($pass->user_id, collect()) : collect();
                                $todayCount = $studentTodayPasses->count();
                            @endphp
                            <div id="pass-mobile-card-{{ $pass->id }}" class="p-3.5 sm:p-4 flex items-center justify-between gap-3 hover:bg-[var(--bg-hover)] transition-colors {{ $todayCount >= 3 ? 'bg-rose-500/5' : '' }}">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <span class="flex h-3.5 w-3.5 relative shrink-0">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500"></span>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-lg sm:text-xl font-black text-[var(--text-heading)] truncate leading-tight">
                                                {{ $pass->student?->last_name ? trim($pass->student->last_name . ', ' . $pass->student->name) : ($pass->student?->name ?? 'Alumno') }}
                                            </h4>
                                            @if($todayCount > 1)
                                                <span class="text-[10px] font-extrabold text-amber-500 shrink-0">({{ $todayCount }}ª)</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 text-sm sm:text-base text-[var(--text-muted)] truncate mt-1">
                                            <span class="font-bold text-[var(--text-heading)]">{{ $pass->student?->groupRel?->course ?? '' }} {{ $pass->student?->groupRel?->name ?? '' }}</span>
                                            <span class="text-[var(--text-muted)]">•</span>
                                            <span class="font-extrabold text-blue-600 dark:text-blue-400">{{ $pass->reason }}</span>
                                        </div>
                                        <div class="text-sm sm:text-base font-bold text-[var(--text-heading)] truncate mt-1">
                                            {{ $pass->teacher ? trim($pass->teacher->name . ' ' . ($pass->teacher->last_name ?? '')) : 'Sin asignar' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <div class="text-right">
                                        <span class="text-base sm:text-lg font-mono font-black text-blue-600 dark:text-blue-400 timer block leading-none" data-start="{{ $pass->start_time->timestamp }}">
                                            00:00
                                        </span>
                                        <span class="text-xs font-semibold text-[var(--text-muted)] leading-none mt-1 block">
                                            {{ $pass->start_time->format('H:i') }}
                                        </span>
                                    </div>

                                    @if($canReturn)
                                        <button onclick="endPass({{ $pass->id }})" id="btn-return-mobile-{{ $pass->id }}"
                                            class="p-2 sm:px-3 sm:py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition-all flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Regresar</span>
                                        </button>
                                    @else
                                        <span class="sr-only">Solo lectura</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Toast notification container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2"></div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        // Show Toast
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `p-4 rounded-xl shadow-xl flex items-center gap-3 text-white text-sm font-semibold transition-all transform duration-300 translate-y-2 opacity-0 ${
                type === 'success' ? 'bg-emerald-600 border border-emerald-400/30' : 'bg-rose-600 border border-rose-400/30'
            }`;
            
            toast.innerHTML = `
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${type === 'success' 
                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' 
                        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'}
                </svg>
                <span>${message}</span>
            `;

            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        @if($canReturn)
        // Return single student pass
        async function endPass(passId) {
            const btn = document.getElementById(`btn-return-${passId}`);
            const btnMobile = document.getElementById(`btn-return-mobile-${passId}`);
            const loadingHtml = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Guardando...</span>
            `;
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = loadingHtml;
            }
            if (btnMobile) {
                btnMobile.disabled = true;
                btnMobile.innerHTML = loadingHtml;
            }

            try {
                const url = "{{ route('salidas.update', ':id') }}".replace(':id', passId);
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'PATCH', source: 'monitor' })
                });

                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    throw new Error(data.error || 'Error al registrar el regreso');
                }

                showToast('Alumno regresado con éxito', 'success');
                
                const card = document.getElementById(`pass-card-${passId}`);
                if (card) {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => card.remove(), 400);
                }
                const mobileCard = document.getElementById(`pass-mobile-card-${passId}`);
                if (mobileCard) {
                    mobileCard.style.transition = 'all 0.4s ease';
                    mobileCard.style.opacity = '0';
                    mobileCard.style.transform = 'scale(0.95)';
                    setTimeout(() => mobileCard.remove(), 400);
                }
                setTimeout(() => {
                    const remaining = document.querySelectorAll('[id^="pass-card-"], [id^="pass-mobile-card-"]');
                    if (remaining.length === 0) {
                        window.location.reload();
                    }
                }, 450);
            } catch (e) {
                console.error(e);
                showToast(e.message || 'Error al finalizar pase', 'error');
                const resetBtnHtml = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Regresar alumno</span>
                `;
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = resetBtnHtml;
                }
                if (btnMobile) {
                    btnMobile.disabled = false;
                    btnMobile.innerHTML = `
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Regresar</span>
                    `;
                }
            }
        }
        @endif

        // Auto refresh
        setTimeout(() => {
            window.location.reload();
        }, 30000);

        // Timer Logic
        setInterval(() => {
            document.querySelectorAll('.timer').forEach(el => {
                const start = parseInt(el.dataset.start);
                const now = Math.floor(Date.now() / 1000);
                const diff = now - start;

                const minutes = Math.floor(diff / 60).toString().padStart(2, '0');
                const seconds = (diff % 60).toString().padStart(2, '0');
                el.textContent = `${minutes}:${seconds}`;

                // Highlight passes exceeding 7 minutes (420s)
                if (diff >= 420) {
                    el.style.color = '#ef4444';
                    const parentCard = el.closest('[id^="pass-card-"]') || el.closest('.p-5');
                    if (parentCard) {
                        parentCard.style.borderColor = 'rgba(239, 68, 68, 0.6)';
                        parentCard.style.background = 'rgba(239, 68, 68, 0.1)';
                    }
                    if (!el.parentNode.querySelector('.exceso-badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'exceso-badge text-[10px] font-black uppercase text-rose-500 bg-rose-500/15 px-2 py-0.5 rounded border border-rose-500/30 block mt-1 animate-pulse';
                        badge.textContent = '⚠️ EXCESO >7 MIN';
                        el.parentNode.appendChild(badge);
                    }
                }
            });
        }, 1000);
    </script>
@endsection