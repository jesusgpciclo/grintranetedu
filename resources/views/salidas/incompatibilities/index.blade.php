@extends('layouts.app')

@section('title', 'Incompatibilidades - Pasillos')

@section('module_title')
Incompatibilidades de <span class="text-rose-500">pasillos</span>
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
                        <div class="p-2 bg-rose-500/10 text-rose-500 rounded-xl shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-[var(--text-heading)]">Incompatibilidades en Pasillos</h1>
                    </div>
                    <p class="text-[var(--text-muted)] text-xs sm:text-sm font-medium mt-1">
                        Define grupos de alumnos que no deben coincidir simultáneamente fuera del aula.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(auth()->user()->can('salidas.notices') || auth()->user()->hasRole('admin'))
                    <a href="{{ route('salidas.notices.index') }}"
                        class="p-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] hover:text-amber-500 border border-[var(--border)] rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shrink-0"
                        title="Ver avisos especiales de salidas">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="hidden md:inline">Avisos Salidas</span>
                    </a>
                @endif
                <a href="{{ route('salidas.incompatibilities.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white rounded-xl font-bold text-sm shadow-md shadow-rose-500/20 transform hover:-translate-y-0.5 active:translate-y-0 transition-all shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Nuevo Grupo</span>
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

        <!-- Search Bar -->
        <div class="bg-[var(--bg-card)] p-3 sm:p-4 rounded-2xl border border-[var(--border)] shadow-sm">
            <form action="{{ route('salidas.incompatibilities.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="flex-1 relative">
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Buscar por nombre de grupo, descripción o alumno..."
                        class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 pl-10 pr-4 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all placeholder:text-[var(--text-muted)]">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="px-4 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-1.5 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Buscar</span>
                    </button>
                    @if($search !== '')
                        <a href="{{ route('salidas.incompatibilities.index') }}"
                            class="px-3.5 py-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] hover:text-[var(--text-color)] border border-[var(--border)] rounded-xl text-sm font-semibold transition-all">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Groups Listing -->
        @if($groups->isEmpty())
            <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] p-12 text-center shadow-sm">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-[var(--text-heading)]">No se encontraron grupos de incompatibilidad</h3>
                <p class="text-[var(--text-muted)] text-sm mt-1.5 max-w-md mx-auto">
                    @if($search !== '')
                        No hay resultados para la búsqueda "{{ $search }}". Intenta con otro término.
                    @else
                        Todavía no se ha configurado ningún grupo de incompatibilidad para los pasillos.
                    @endif
                </p>
                @if($search === '')
                    <div class="mt-6">
                        <a href="{{ route('salidas.incompatibilities.create') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-bold text-sm shadow-md shadow-rose-500/20 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Crear el primer grupo</span>
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($groups as $group)
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                        <div>
                            <!-- Header of Card -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="text-lg font-extrabold text-[var(--text-heading)] truncate" title="{{ $group->name }}">
                                            {{ $group->name }}
                                        </h2>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/15 text-rose-500 border border-rose-500/30">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            {{ $group->students_count }} {{ $group->students_count === 1 ? 'alumno' : 'alumnos' }}
                                        </span>
                                    </div>
                                    @if($group->description)
                                        <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1.5 line-clamp-2" title="{{ $group->description }}">
                                            {{ $group->description }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <a href="{{ route('salidas.incompatibilities.edit', $group) }}"
                                        class="p-2 text-[var(--text-muted)] hover:text-blue-500 hover:bg-blue-500/10 rounded-xl transition-all"
                                        title="Editar grupo">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button type="button" onclick="confirmDelete({{ $group->id }}, '{{ addslashes($group->name) }}')"
                                        class="p-2 text-[var(--text-muted)] hover:text-rose-500 hover:bg-rose-500/10 rounded-xl transition-all"
                                        title="Eliminar grupo">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- List of Students in Group -->
                            <div class="mt-4 pt-3 border-t border-[var(--border)]">
                                <h4 class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-wider mb-2.5">
                                    Alumnos en este grupo:
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="group-students-list-{{ $group->id }}">
                                    @php
                                        $hasStudentPagination = $group->students->count() > 40;
                                        $totalStudentPages = $hasStudentPagination ? ceil($group->students->count() / 40) : 1;
                                    @endphp
                                    @foreach($group->students as $student)
                                        @php
                                            $courseName = trim(($student->groupRel?->course ? $student->groupRel->course . ' ' : '') . ($student->groupRel?->name ?? ''));
                                            $fullName = trim(($student->last_name ?? '') . ', ' . $student->name);
                                            $studentPage = floor($loop->index / 40) + 1;
                                            $isInitialHidden = $hasStudentPagination && $loop->index >= 40;
                                        @endphp
                                        <div class="group-student-item-{{ $group->id }} flex items-center gap-2 p-2 rounded-xl bg-[var(--bg-input)] border border-[var(--border)] {{ $isInitialHidden ? 'hidden' : '' }}"
                                            data-student-page="{{ $studentPage }}">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-white font-black text-xs shrink-0 shadow-xs">
                                                {{ $student->initials }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-bold text-[var(--text-heading)] truncate" title="{{ $fullName }}">
                                                    {{ $fullName }}
                                                </div>
                                                @if($courseName)
                                                    <div class="text-[10px] text-[var(--text-muted)] truncate">
                                                        {{ $courseName }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if($hasStudentPagination)
                                    <!-- Controles de paginación para el grupo si supera 40 alumnos -->
                                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-2.5 mt-2.5 border-t border-[var(--border)] text-xs text-[var(--text-muted)]">
                                        <span id="group-{{ $group->id }}-page-info" class="font-medium text-[11px]">
                                            Mostrando <span class="font-bold text-[var(--text-heading)]">1 - 40</span> de <span class="font-bold text-[var(--text-heading)]">{{ $group->students->count() }}</span> alumnos
                                        </span>
                                        <div class="flex items-center gap-1">
                                            <button type="button"
                                                id="group-{{ $group->id }}-prev-btn"
                                                onclick="changeGroupStudentPage({{ $group->id }}, -1, {{ $totalStudentPages }}, {{ $group->students->count() }})"
                                                disabled
                                                class="px-2 py-1 rounded-lg border border-[var(--border)] bg-[var(--bg-input)] text-[var(--text-color)] opacity-40 cursor-not-allowed pointer-events-none text-xs font-semibold flex items-center gap-1 transition-all"
                                                title="Alumnos anteriores">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                </svg>
                                                <span>Ant.</span>
                                            </button>
                                            <span class="px-2 font-bold text-[var(--text-heading)] text-xs" id="group-{{ $group->id }}-curr-page">
                                                1 / {{ $totalStudentPages }}
                                            </span>
                                            <button type="button"
                                                id="group-{{ $group->id }}-next-btn"
                                                onclick="changeGroupStudentPage({{ $group->id }}, 1, {{ $totalStudentPages }}, {{ $group->students->count() }})"
                                                class="px-2 py-1 rounded-lg border border-[var(--border)] bg-[var(--bg-input)] text-[var(--text-color)] hover:bg-[var(--bg-hover)] hover:text-rose-500 text-xs font-semibold flex items-center gap-1 transition-all"
                                                title="Alumnos siguientes">
                                                <span>Sig.</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Card Footer Info -->
                        <div class="mt-4 pt-3 border-t border-[var(--border)] flex items-center justify-between text-[11px] text-[var(--text-muted)]">
                            <span>Actualizado: {{ $group->updated_at->diffForHumans() }}</span>
                            <a href="{{ route('salidas.incompatibilities.edit', $group) }}"
                                class="text-rose-500 hover:text-rose-600 font-bold flex items-center gap-1 hover:underline">
                                <span>Gestionar alumnos</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $groups->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Eliminar -->
<div id="delete-modal-overlay" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity">
    <div class="bg-[var(--bg-card)] border border-[var(--border)] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-[var(--text-heading)]">¿Eliminar grupo de incompatibilidad?</h3>
            <p class="text-sm text-[var(--text-muted)] mt-1.5" id="delete-modal-msg">
                Esta acción eliminará el grupo y los alumnos asociados podrán coincidir en el pasillo sin aviso.
            </p>
        </div>
        <form id="delete-form" method="POST" action="" class="flex items-center gap-3 pt-2">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()"
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
    function confirmDelete(id, name) {
        const form = document.getElementById('delete-form');
        form.action = `/salidas/incompatibilities/${id}`;
        document.getElementById('delete-modal-msg').textContent = `¿Seguro que deseas eliminar el grupo "${name}"? Los alumnos no generarán conflicto al salir.`;
        document.getElementById('delete-modal-overlay').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal-overlay').classList.add('hidden');
    }

    const groupStudentPages = {};

    function changeGroupStudentPage(groupId, delta, totalPages, totalCount) {
        if (!groupStudentPages[groupId]) {
            groupStudentPages[groupId] = 1;
        }
        const newPage = groupStudentPages[groupId] + delta;
        if (newPage < 1 || newPage > totalPages) return;

        groupStudentPages[groupId] = newPage;

        const items = document.querySelectorAll('.group-student-item-' + groupId);
        items.forEach(el => {
            const page = parseInt(el.getAttribute('data-student-page'), 10);
            if (page === newPage) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });

        const startItem = (newPage - 1) * 40 + 1;
        const endItem = Math.min(newPage * 40, totalCount);

        const infoEl = document.getElementById('group-' + groupId + '-page-info');
        if (infoEl) {
            infoEl.innerHTML = `Mostrando <span class="font-bold text-[var(--text-heading)]">${startItem} - ${endItem}</span> de <span class="font-bold text-[var(--text-heading)]">${totalCount}</span> alumnos`;
        }

        const currEl = document.getElementById('group-' + groupId + '-curr-page');
        if (currEl) {
            currEl.textContent = `${newPage} / ${totalPages}`;
        }

        const prevBtn = document.getElementById('group-' + groupId + '-prev-btn');
        const nextBtn = document.getElementById('group-' + groupId + '-next-btn');

        if (prevBtn) {
            if (newPage === 1) {
                prevBtn.disabled = true;
                prevBtn.classList.add('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                prevBtn.classList.remove('hover:bg-[var(--bg-hover)]', 'hover:text-rose-500');
            } else {
                prevBtn.disabled = false;
                prevBtn.classList.remove('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                prevBtn.classList.add('hover:bg-[var(--bg-hover)]', 'hover:text-rose-500');
            }
        }

        if (nextBtn) {
            if (newPage === totalPages) {
                nextBtn.disabled = true;
                nextBtn.classList.add('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                nextBtn.classList.remove('hover:bg-[var(--bg-hover)]', 'hover:text-rose-500');
            } else {
                nextBtn.disabled = false;
                nextBtn.classList.remove('opacity-40', 'cursor-not-allowed', 'pointer-events-none');
                nextBtn.classList.add('hover:bg-[var(--bg-hover)]', 'hover:text-rose-500');
            }
        }
    }
</script>
@endsection
