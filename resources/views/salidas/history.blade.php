@extends('layouts.app')

@section('title', 'Gestor de salidas - Historial')

@section('content')
    <style>
        /* Modern Modal Styles */
        #custom-modal-overlay {
            backdrop-filter: blur(8px);
            background-color: rgba(15, 23, 42, 0.6);
            transition: all 0.3s ease;
        }
        .modal-content {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        #custom-modal-overlay.active .modal-content {
            transform: scale(1);
            opacity: 1;
        }
        /* Modern Toast Styles */
        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .toast {
            min-width: 300px;
            background: var(--bg-card);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            color: var(--text-color);
            padding: 1rem;
            border-radius: 1rem;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        .toast.hide {
            transform: translateX(100%);
            opacity: 0;
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 bg-[var(--bg-card)] p-5 rounded-2xl border border-[var(--border)] shadow-sm">
                <div class="flex items-center gap-6">
                    <a href="{{ route('salidas.index') }}" class="p-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all group" title="Volver al Gestor">
                        <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-[var(--text-heading)] leading-none">Historial de Salidas</h1>
                        <p class="text-[var(--text-muted)] text-xs sm:text-sm font-medium mt-1">Registro completo de movimientos</p>
                    </div>
                </div>

                <!-- Search Filter -->
                <form action="{{ route('salidas.history') }}" method="GET" class="flex-1 max-w-md w-full">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por alumno, clase o motivo..."
                            class="w-full bg-[var(--bg-input)] border-[var(--border)] rounded-xl py-2.5 pl-10 pr-4 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)] transition-all placeholder:text-[var(--text-muted)]">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        @if(request('search'))
                            <a href="{{ route('salidas.history') }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--text-heading)] transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>
                    @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                    @if(request('direction')) <input type="hidden" name="direction" value="{{ request('direction') }}"> @endif
                </form>
            </div>

            <!-- Action Buttons Bar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 mb-6 bg-[var(--bg-card)] p-4 rounded-2xl border border-[var(--border)] shadow-sm">
                <!-- Export actions -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('salidas.history.export-csv', request()->all()) }}" 
                       class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 rounded-xl font-bold text-sm transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Descargar Excel
                    </a>
                    
                    <a href="{{ route('salidas.history.print', request()->all()) }}" target="_blank"
                       class="flex items-center gap-2 px-4 py-2.5 bg-sky-500/10 hover:bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-500/30 rounded-xl font-bold text-sm transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Descargar PDF
                    </a>
                </div>

                <!-- Danger / Delete actions -->
                <div>
                    <button onclick="confirmClearHistory()" 
                            class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 rounded-xl font-bold text-sm transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar todo el historial
                    </button>
                </div>
            </div>

            <!-- Main Table Card -->
            <div class="card p-0 overflow-hidden shadow-sm">
                <div class="p-4 sm:p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[var(--border)]">
                            <thead class="bg-[var(--bg-hover)]">
                                <tr>
                                    @foreach([
                                        'Fecha' => 'fecha',
                                        'Alumno' => 'alumno',
                                        'Clase' => 'clase',
                                        'Motivo' => 'motivo',
                                        'Duración' => 'duracion',
                                        'Profesor' => 'profesor'
                                    ] as $label => $column)
                                        @php
                                            $currentSort = request('sort', 'fecha');
                                            $currentDir = request('direction', 'desc');
                                            $isActive = $currentSort === $column;
                                            $nextDir = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';
                                            $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
                                        @endphp
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider cursor-pointer hover:bg-[var(--bg-active)] hover:text-[var(--primary)] transition-all group/head border-r border-[var(--border)] last:border-0">
                                            <a href="{{ $url }}" class="flex items-center justify-between gap-2">
                                                <span>{{ $label }}</span>
                                                <div class="flex flex-col opacity-50 group-hover/head:opacity-100 transition-opacity bg-[var(--bg-input)] p-1 rounded-md">
                                                    @if($isActive)
                                                        <svg class="w-3.5 h-3.5 text-[var(--primary)] {{ $currentDir === 'asc' ? '' : 'rotate-180' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                                        </svg>
                                                    @endif
                                                </div>
                                            </a>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                @forelse($passes as $pass)
                                    <tr class="hover:bg-[var(--bg-hover)] transition-colors group">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="font-bold text-[var(--text-heading)]">{{ $pass->date->format('d/m/Y') }}</div>
                                            <div class="text-[10px] text-[var(--text-muted)]">{{ $pass->start_time->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs border border-sky-500/20">
                                                    {{ strtoupper(substr($pass->student?->name, 0, 1)) }}
                                                </div>
                                                <span class="text-sm font-semibold text-[var(--text-heading)] group-hover:text-[var(--primary)] transition-colors">
                                                    {{ $pass->student?->name }} {{ $pass->student?->last_name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-lg bg-[var(--bg-hover)] text-[var(--text-muted)] text-xs font-bold border border-[var(--border)]">
                                                {{ $pass->student?->groupRel?->course ?? '' }} {{ $pass->student?->groupRel?->name ?? '' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-color)]">
                                            {{ $pass->reason }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($pass->end_time)
                                                <span class="text-[var(--text-muted)] font-medium">
                                                    {{ (int) $pass->start_time->diffInMinutes($pass->end_time) }} min
                                                </span>
                                            @else
                                                <span class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-black animate-pulse">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                    Activo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[var(--text-muted)] italic">
                                            {{ $pass->teacher?->name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-[var(--text-muted)]">
                                            <svg class="w-12 h-12 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            No se han encontrado registros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8 pagination-custom">
                        {{ $passes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Modal -->
    <div id="custom-modal-overlay" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="modal-content w-full max-w-md bg-[var(--bg-card)] border border-[var(--border)] rounded-3xl shadow-2xl overflow-hidden">
            <div class="p-6">
                <div id="modal-icon-container" class="mb-4 flex justify-center"></div>
                <h3 id="modal-title" class="text-xl font-bold text-[var(--text-heading)] text-center mb-2"></h3>
                <p id="modal-description" class="text-[var(--text-muted)] text-center text-sm mb-6"></p>
                
                <div class="flex gap-3 mt-4">
                    <button id="modal-cancel" class="flex-1 py-3 bg-[var(--bg-hover)] hover:bg-[var(--border)] text-[var(--text-color)] font-bold rounded-xl transition-all border border-[var(--border)]">
                        Cancelar
                    </button>
                    <button id="modal-confirm" class="flex-2 py-3 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl shadow-lg shadow-rose-500/30 transition-all">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <script>
        const csrfToken = '{{ csrf_token() }}';

        // Premium Modal System
        const modalOverlay = document.getElementById('custom-modal-overlay');
        const modalTitle = document.getElementById('modal-title');
        const modalDesc = document.getElementById('modal-description');
        const modalConfirm = document.getElementById('modal-confirm');
        const modalCancel = document.getElementById('modal-cancel');
        const modalIconContainer = document.getElementById('modal-icon-container');

        window.customModal = function({ title, description, confirmText = 'Confirmar', type = 'info' }) {
            return new Promise((resolve) => {
                modalTitle.textContent = title;
                modalDesc.textContent = description;
                modalConfirm.textContent = confirmText;
                
                // Icon based on type
                let iconHtml = '';
                if (type === 'question') iconHtml = '<div class="p-4 bg-sky-500/20 text-sky-500 rounded-full"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>';
                if (type === 'warning') iconHtml = '<div class="p-4 bg-rose-500/20 text-rose-500 rounded-full"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>';
                modalIconContainer.innerHTML = iconHtml;

                modalOverlay.classList.remove('hidden');
                modalOverlay.classList.add('flex');
                setTimeout(() => modalOverlay.classList.add('active'), 10);

                const close = (result) => {
                    modalOverlay.classList.remove('active');
                    setTimeout(() => {
                        modalOverlay.classList.add('hidden');
                        modalOverlay.classList.remove('flex');
                        resolve(result);
                    }, 300);
                };

                modalConfirm.onclick = () => close(true);
                modalCancel.onclick = () => close(null);
                modalOverlay.onclick = (e) => { if(e.target === modalOverlay) close(null); };
            });
        };

        // Premium Toast System
        window.showToast = function (message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast border-l-4 ${type === 'success' ? 'border-emerald-500' : (type === 'error' ? 'border-rose-500' : 'border-sky-500')}`;

            let icon = '';
            if (type === 'success') icon = '<div class="p-2 bg-emerald-500/20 rounded-lg text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>';
            if (type === 'error') icon = '<div class="p-2 bg-rose-500/20 rounded-lg text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></div>';

            toast.innerHTML = `
                ${icon}
                <div class="flex-1">
                    <p class="text-sm font-bold text-[var(--text-heading)]">${type.charAt(0).toUpperCase() + type.slice(1)}</p>
                    <p class="text-xs text-[var(--text-muted)]">${message}</p>
                </div>
            `;

            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);

            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        };

        async function confirmClearHistory() {
            const confirmed = await customModal({
                title: '¿Eliminar todo el historial?',
                description: '¿Estás seguro de que deseas eliminar permanentemente TODO el historial de salidas de alumnos? Esta acción no se puede deshacer.',
                confirmText: 'Sí, eliminar permanentemente',
                type: 'warning'
            });

            if (!confirmed) return;

            try {
                const res = await fetch('{{ route("salidas.history.clear") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                if (!res.ok) throw new Error('Error al vaciar el historial.');
                const data = await res.json();
                
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } catch (e) {
                showToast(e.message, 'error');
            }
        }
    </script>
@endsection