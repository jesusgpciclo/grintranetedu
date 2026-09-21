@extends('layouts.app')

@section('title', 'Gestor de salidas - Monitor Conserjería')

@php
    $canReturn = auth()->user() && (
        auth()->user()->can('salidas.return_monitor') || 
        auth()->user()->can('salidas.manage') || 
        auth()->user()->hasRole('admin')
    );
@endphp

@section('content')
    <div class="py-2 sm:py-4">
        <div class="max-w-7xl mx-auto">
            <div class="card flex flex-col sm:flex-row justify-between items-center mb-6 gap-4 p-5">
                <div class="flex items-center gap-4">
                    <a href="{{ route('salidas.index') }}" class="p-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all group" title="Volver al Gestor">
                        <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[var(--text-heading)] leading-none">Monitor de Pasillo</h1>
                        <p class="text-[var(--text-muted)] text-xs sm:text-sm font-medium mt-1">Control de seguridad en tiempo real</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if($canReturn)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shadow-xs">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Control de Pasillo Activo</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700" title="Conserjería y usuarios sin rol de control tienen acceso en modo solo consulta">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Modo Supervisión (Solo Lectura)</span>
                        </span>
                    @endif
                    <span class="flex h-2 w-2 ml-1">
                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    <span class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest">
                        Auto: 30s
                    </span>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div>
                    @if($activePasses->isEmpty())
                        <div class="text-center py-12 text-[var(--text-muted)]">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p class="text-lg font-semibold">No hay alumnos en el pasillo ahora mismo.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($activePasses as $pass)
                                @php
                                    $studentTodayPasses = isset($todayPassesByStudent) ? $todayPassesByStudent->get($pass->user_id, collect()) : collect();
                                    $todayCount = $studentTodayPasses->count();
                                    // Previous pass if any (excluding this active one)
                                    $previousPass = $studentTodayPasses->filter(fn($p) => $p->id !== $pass->id)->first();
                                @endphp
                                <div id="pass-card-{{ $pass->id }}"
                                    class="p-5 border rounded-2xl transition-all flex flex-col justify-between gap-4"
                                    style="background: var(--bg-surface); border-color: {{ $todayCount >= 3 ? 'rgba(239, 68, 68, 0.4)' : 'var(--border)' }};">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-3 w-3 relative shrink-0">
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                            </span>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h3 class="text-base sm:text-lg font-bold text-[var(--text-heading)] leading-tight">
                                                        {{ $pass->student?->name }} {{ $pass->student?->last_name }}
                                                    </h3>
                                                    @if($todayCount > 1)
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider {{ $todayCount >= 3 ? 'bg-rose-500/15 text-rose-500 border border-rose-500/30' : 'bg-amber-500/15 text-amber-500 border border-amber-500/30' }}"
                                                            title="Ha salido {{ $todayCount }} veces hoy">
                                                            {{ $todayCount }}ª salida hoy
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-[var(--text-muted)] mt-0.5">
                                                    {{ $pass->student?->groupRel?->course ?? '' }}
                                                    {{ $pass->student?->groupRel?->name ?? '' }}
                                                    &bull; <span class="font-bold text-[var(--text-color)]">{{ $pass->reason }}</span>
                                                </p>
                                                @if($pass->teacher)
                                                    <p class="text-xs text-[var(--text-muted)] mt-0.5">
                                                        Prof: {{ $pass->teacher->name }} {{ $pass->teacher->last_name ?? '' }}
                                                    </p>
                                                @endif
                                                @if($previousPass)
                                                    <p class="text-[11px] text-amber-500 mt-1 flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span>Salida anterior hoy: {{ $previousPass->start_time ? $previousPass->start_time->format('H:i') : '' }} ({{ $previousPass->reason }})</span>
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right flex flex-col items-end">
                                            <div class="text-2xl sm:text-3xl font-mono font-bold text-blue-500 timer"
                                                data-start="{{ $pass->start_time->timestamp }}">
                                                00:00
                                            </div>
                                            <span class="text-xs text-[var(--text-muted)] mt-1">Salida:
                                                {{ $pass->start_time->format('H:i') }}</span>
                                        </div>
                                    </div>

                                    <div class="pt-3 border-t border-[var(--border)] flex justify-end items-center">
                                        @if($canReturn)
                                            <button onclick="endPass({{ $pass->id }})" id="btn-return-{{ $pass->id }}"
                                                class="w-full sm:w-auto px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-500/20 transition-all duration-200 flex items-center justify-center gap-2 group">
                                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Regresar alumno</span>
                                            </button>
                                        @else
                                            <div class="w-full flex items-center justify-between text-xs text-[var(--text-muted)] bg-slate-50 dark:bg-slate-800/60 px-3.5 py-2 rounded-xl border border-[var(--border)]">
                                                <span class="flex items-center gap-1.5 font-semibold text-slate-600 dark:text-slate-300">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                    <span>En tránsito en pasillo</span>
                                                </span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-200/60 dark:bg-slate-700/60 px-2 py-0.5 rounded-md">Solo lectura</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
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
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Guardando...</span>
                `;
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
                    body: JSON.stringify({ _method: 'PATCH' })
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
                    setTimeout(() => {
                        card.remove();
                        const remaining = document.querySelectorAll('[id^="pass-card-"]');
                        if (remaining.length === 0) {
                            window.location.reload();
                        }
                    }, 400);
                }
            } catch (e) {
                console.error(e);
                showToast(e.message || 'Error al finalizar pase', 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Regresar alumno</span>
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