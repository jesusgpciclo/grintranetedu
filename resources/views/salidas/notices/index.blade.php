@extends('layouts.app')

@section('title', 'Avisos Especiales de Salidas - Pasillos')

@section('module_title')
Avisos de <span class="text-amber-500">salidas</span>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header Card -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[var(--bg-card)] p-5 rounded-2xl border border-[var(--border)] shadow-sm">
            <div class="flex items-center gap-3.5 sm:gap-5">
                <a href="{{ route('salidas.index') }}"
                    class="p-2 sm:p-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all group shrink-0"
                    title="Volver al Gestor de Pasillos">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-amber-500/10 text-amber-500 rounded-xl shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-[var(--text-heading)]">Avisos Especiales de Salidas</h1>
                    </div>
                    <p class="text-[var(--text-muted)] text-xs sm:text-sm font-medium mt-1">
                        Mensajes y condiciones que se mostrarán al profesor cuando un alumno o alumna solicite salir del aula.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(auth()->user()->can('salidas.incompatibilities') || auth()->user()->hasRole('admin'))
                    <a href="{{ route('salidas.incompatibilities.index') }}"
                        class="p-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] hover:text-rose-500 border border-[var(--border)] rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0"
                        title="Ver grupos de incompatibilidad">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span class="hidden md:inline">Incompatibilidades</span>
                    </a>
                @endif
                <a href="{{ route('salidas.notices.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl font-bold text-sm shadow-md shadow-amber-500/20 transform hover:-translate-y-0.5 active:translate-y-0 transition-all shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Nuevo Aviso</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 rounded-2xl text-sm font-semibold">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-rose-500/10 border border-rose-500/30 text-rose-600 dark:text-rose-400 rounded-2xl text-sm font-semibold">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="bg-[var(--bg-card)] p-4 rounded-2xl border border-[var(--border)] shadow-sm">
            <form action="{{ route('salidas.notices.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
                <!-- Search text -->
                <div class="sm:col-span-2 relative">
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Buscar por alumno o mensaje..."
                        class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 pl-10 pr-4 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all placeholder:text-[var(--text-muted)]">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Class Group Filter -->
                <div>
                    <select name="group_id" onchange="this.form.submit()"
                        class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 px-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                        <option value="">Todas las clases</option>
                        @foreach($classGroups as $cg)
                            <option value="{{ $cg->id }}" {{ $groupId == $cg->id ? 'selected' : '' }}>{{ trim(($cg->course ? $cg->course . ' ' : '') . $cg->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter & Actions -->
                <div class="flex items-center gap-2">
                    <select name="status" onchange="this.form.submit()"
                        class="flex-1 bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 px-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Todos los estados</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Solo Activos</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Solo Inactivos</option>
                    </select>

                    <button type="submit"
                        class="p-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold transition-all shrink-0"
                        title="Buscar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    @if($search !== '' || $status !== 'all' || $groupId !== '')
                        <a href="{{ route('salidas.notices.index') }}"
                            class="p-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] hover:text-[var(--text-color)] border border-[var(--border)] rounded-xl text-xs font-semibold transition-all shrink-0"
                            title="Limpiar filtros">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Notices Listing -->
        @if($notices->isEmpty())
            <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] p-12 text-center shadow-sm">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[var(--text-heading)]">No se encontraron avisos de salida</h3>
                <p class="text-[var(--text-muted)] text-sm mt-1.5 max-w-md mx-auto">
                    @if($search !== '' || $status !== 'all' || $groupId !== '')
                        No hay avisos que coincidan con los filtros aplicados.
                    @else
                        Aún no se ha registrado ningún aviso especial de salida para los alumnos.
                    @endif
                </p>
                <div class="mt-6">
                    <a href="{{ route('salidas.notices.create') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-sm shadow-md shadow-amber-500/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Crear nuevo aviso</span>
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($notices as $notice)
                    @php
                        $student = $notice->student;
                        $courseName = trim(($student?->groupRel?->course ? $student->groupRel->course . ' ' : '') . ($student?->groupRel?->name ?? ''));
                        $studentDisplayName = $student ? ($student->last_name ? trim($student->last_name . ', ' . $student->name) : $student->name) : 'Alumno desconocido';
                    @endphp
                    <div class="bg-[var(--bg-card)] rounded-2xl border transition-all p-5 shadow-sm hover:shadow-md flex flex-col justify-between {{ $notice->is_active ? 'border-amber-500/40 bg-amber-500/[0.02]' : 'border-[var(--border)] opacity-80' }}">
                        <div>
                            <!-- Header of card: Student info + Status badge -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-amber-500 to-amber-600 text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                                        {{ substr($student?->last_name ?? $student?->name ?? 'A', 0, 1) }}{{ substr($student?->name ?? '', 0, 1) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-sm sm:text-base font-bold text-[var(--text-heading)] truncate" title="{{ $studentDisplayName }}">
                                            {{ $studentDisplayName }}
                                        </h3>
                                        <p class="text-xs text-[var(--text-muted)] truncate">
                                            {{ $courseName ?: 'Sin clase asignada' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="shrink-0 flex items-center gap-1">
                                    <!-- Toggle Active form -->
                                    <form action="{{ route('salidas.notices.toggle', $notice) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold transition-all {{ $notice->is_active ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-slate-500/15 text-slate-500 border border-slate-500/30 hover:bg-slate-500/25' }}"
                                            title="Pulsar para {{ $notice->is_active ? 'desactivar' : 'activar' }} el aviso">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $notice->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            <span>{{ $notice->is_active ? 'Activo' : 'Inactivo' }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Notice Message box -->
                            <div class="p-3.5 rounded-xl bg-[var(--bg-input)] border border-[var(--border)] relative mt-2 text-sm text-[var(--text-color)] font-medium">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="break-words leading-relaxed whitespace-pre-line">{{ $notice->message }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="mt-4 pt-3 border-t border-[var(--border)] flex items-center justify-between text-xs text-[var(--text-muted)]">
                            <span class="truncate" title="Por {{ $notice->creator ? $notice->creator->name : 'Sistema' }}">
                                @if($notice->creator)
                                    Por: {{ $notice->creator->name }}
                                @else
                                    {{ $notice->created_at->format('d/m/Y') }}
                                @endif
                            </span>

                            <div class="flex items-center gap-1 shrink-0">
                                <a href="{{ route('salidas.notices.edit', $notice) }}"
                                    class="p-1.5 text-[var(--text-muted)] hover:text-blue-500 hover:bg-blue-500/10 rounded-lg transition-all"
                                    title="Editar aviso">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <button type="button" onclick="confirmDeleteNotice({{ $notice->id }}, '{{ addslashes($studentDisplayName) }}')"
                                    class="p-1.5 text-[var(--text-muted)] hover:text-rose-500 hover:bg-rose-500/10 rounded-lg transition-all"
                                    title="Eliminar aviso">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $notices->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Eliminar Aviso -->
<div id="delete-notice-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity">
    <div class="bg-[var(--bg-card)] border border-[var(--border)] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-[var(--text-heading)]">¿Eliminar aviso de salida?</h3>
            <p class="text-sm text-[var(--text-muted)] mt-1.5" id="delete-notice-msg">
                Esta acción eliminará el aviso y no se mostrará cuando el alumno solicite salir.
            </p>
        </div>
        <form id="delete-notice-form" method="POST" action="" class="flex items-center gap-3 pt-2">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteNoticeModal()"
                class="flex-1 py-2.5 px-4 bg-[var(--bg-input)] hover:bg-[var(--bg-hover)] text-[var(--text-color)] border border-[var(--border)] rounded-xl font-bold text-sm transition-all">
                Cancelar
            </button>
            <button type="submit"
                class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-sm shadow-md shadow-rose-600/20 transition-all">
                Eliminar
            </button>
        </form>
    </div>
</div>

<script>
    function confirmDeleteNotice(id, studentName) {
        const form = document.getElementById('delete-notice-form');
        form.action = `/salidas/notices/${id}`;
        document.getElementById('delete-notice-msg').textContent = `¿Seguro que deseas eliminar el aviso para ${studentName}?`;
        document.getElementById('delete-notice-modal').classList.remove('hidden');
    }

    function closeDeleteNoticeModal() {
        document.getElementById('delete-notice-modal').classList.add('hidden');
    }
</script>
@endsection
