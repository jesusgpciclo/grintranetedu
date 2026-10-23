@extends('layouts.app')

@section('title', ($isEdit ? 'Editar Aviso' : 'Nuevo Aviso') . ' de Salida - Pasillos')

@section('module_title')
{{ $isEdit ? 'Editar' : 'Nuevo' }} Aviso de <span class="text-amber-500">Salida</span>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header Card -->
        <div class="flex items-center justify-between gap-4 bg-[var(--bg-card)] p-5 rounded-2xl border border-[var(--border)] shadow-sm">
            <div class="flex items-center gap-3.5 sm:gap-5">
                <a href="{{ route('salidas.notices.index') }}"
                    class="p-2 sm:p-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all group shrink-0"
                    title="Volver al listado de avisos">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-amber-500/10 text-amber-500 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </span>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-[var(--text-heading)]">
                            {{ $isEdit ? 'Editar Aviso de Salida' : 'Crear Nuevo Aviso de Salida' }}
                        </h1>
                    </div>
                    <p class="text-[var(--text-muted)] text-xs sm:text-sm font-medium mt-1">
                        Este mensaje se mostrará en pantalla cada vez que un docente intente autorizar la salida de este alumno.
                    </p>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-600 dark:text-rose-400 text-sm">
                <div class="font-bold mb-1 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Por favor revisa los errores en el formulario:
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs sm:text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $isEdit ? route('salidas.notices.update', $notice) : route('salidas.notices.store') }}" method="POST" id="notice-form" class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- 1. Selección del Alumno -->
            <div class="bg-[var(--bg-card)] p-5 rounded-2xl border border-[var(--border)] shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[var(--border)] pb-3">
                    <h3 class="text-base font-bold text-[var(--text-heading)] flex items-center gap-2">
                        <span>1. Seleccionar Alumno</span>
                        <span class="text-xs text-rose-500">*</span>
                    </h3>
                    <div id="selected-student-indicator" class="text-xs font-semibold text-amber-500">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Buscador y filtro para alumnos -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 relative">
                        <input type="text" id="student-search-input" oninput="filterStudents()"
                            placeholder="Buscar alumno por nombre o apellidos..."
                            class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 pl-10 pr-4 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all placeholder:text-[var(--text-muted)]">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div>
                        <select id="group-filter-select" onchange="filterStudents()"
                            class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 px-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                            <option value="">Todas las clases</option>
                            @foreach($classGroups as $cg)
                                <option value="{{ $cg->id }}">{{ trim(($cg->course ? $cg->course . ' ' : '') . $cg->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Lista de selección de alumnos -->
                <div class="max-h-[380px] overflow-y-auto pr-1" id="students-scroll-area">
                    <div id="students-container" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @php
                            $selectedStudentId = old('user_id', $notice->user_id);
                        @endphp
                        @foreach($students as $student)
                            @php
                                $courseName = trim(($student->groupRel?->course ? $student->groupRel->course . ' ' : '') . ($student->groupRel?->name ?? ''));
                                $displayName = $student->last_name ? trim($student->last_name . ', ' . $student->name) : $student->name;
                                $searchString = mb_strtolower(($student->last_name ?? '') . ' ' . $student->name . ' ' . $student->name . ' ' . ($student->last_name ?? ''));
                                $isSelected = ((string)$student->id === (string)$selectedStudentId);
                            @endphp
                            <label class="student-radio-card flex items-center gap-3 p-2.5 rounded-xl border transition-all cursor-pointer select-none {{ $isSelected ? 'bg-amber-500/10 border-amber-500 shadow-xs' : 'bg-[var(--bg-input)] border-[var(--border)] hover:border-slate-400' }}"
                                data-student-id="{{ $student->id }}"
                                data-group-id="{{ $student->group_id }}"
                                data-name="{{ $displayName }}"
                                data-search="{{ $searchString }}">
                                <input type="radio" name="user_id" value="{{ $student->id }}" required
                                    class="w-4 h-4 text-amber-500 border-slate-300 focus:ring-amber-500 shrink-0"
                                    {{ $isSelected ? 'checked' : '' }}
                                    onchange="handleStudentSelect(this, '{{ addslashes($displayName) }}')">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-amber-500 to-amber-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ $student->initials }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs sm:text-sm font-bold text-[var(--text-heading)] truncate">
                                        {{ $displayName }}
                                    </div>
                                    @if($courseName)
                                        <div class="text-[10px] text-[var(--text-muted)] truncate">
                                            {{ $courseName }}
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div id="no-students-match" class="hidden py-6 text-center text-[var(--text-muted)] text-sm">
                        No hay alumnos que coincidan con la búsqueda.
                    </div>
                </div>

                <!-- Controles de paginación de alumnos (40 en 40) -->
                <div id="student-pagination" class="hidden flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-[var(--border)] text-xs text-[var(--text-muted)]">
                    <div id="student-pagination-info" class="font-medium"></div>
                    <div class="flex items-center gap-1 flex-wrap justify-center" id="student-pagination-controls"></div>
                </div>
                @error('user_id')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 2. Mensaje del Aviso -->
            <div class="bg-[var(--bg-card)] p-5 rounded-2xl border border-[var(--border)] shadow-sm space-y-4">
                <div class="border-b border-[var(--border)] pb-3">
                    <h3 class="text-base font-bold text-[var(--text-heading)] flex items-center gap-2">
                        <span>2. Mensaje del Aviso</span>
                        <span class="text-xs text-rose-500">*</span>
                    </h3>
                    <p class="text-[var(--text-muted)] text-xs mt-0.5">
                        Escribe el mensaje exacto que aparecerá en pantalla cuando el alumno pida salir.
                    </p>
                </div>

                <!-- Chips con ejemplos frecuentes -->
                <div>
                    <span class="text-xs font-semibold text-[var(--text-muted)] block mb-1.5">
                        Sugerencias rápidas (pulsa para usar):
                    </span>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setPreset('El alumno o alumna debe salir siempre acompañado/a.')"
                            class="px-2.5 py-1 rounded-lg bg-[var(--bg-input)] hover:bg-amber-500/10 hover:text-amber-600 border border-[var(--border)] hover:border-amber-500/40 text-xs font-medium text-[var(--text-color)] transition-all">
                            🤝 Debe salir acompañado/a
                        </button>
                        <button type="button" onclick="setPreset('Dejarle salir siempre que lo solicite (atención médica / especial).')"
                            class="px-2.5 py-1 rounded-lg bg-[var(--bg-input)] hover:bg-amber-500/10 hover:text-amber-600 border border-[var(--border)] hover:border-amber-500/40 text-xs font-medium text-[var(--text-color)] transition-all">
                            🩺 Dejar salir siempre que lo solicite
                        </button>
                        <button type="button" onclick="setPreset('Permitir salir únicamente a beber agua o servicio.')"
                            class="px-2.5 py-1 rounded-lg bg-[var(--bg-input)] hover:bg-amber-500/10 hover:text-amber-600 border border-[var(--border)] hover:border-amber-500/40 text-xs font-medium text-[var(--text-color)] transition-all">
                            💧 Solo agua o servicio
                        </button>
                        <button type="button" onclick="setPreset('Avisar a jefatura de estudios o tutor/a si solicita salir más de una vez.')"
                            class="px-2.5 py-1 rounded-lg bg-[var(--bg-input)] hover:bg-amber-500/10 hover:text-amber-600 border border-[var(--border)] hover:border-amber-500/40 text-xs font-medium text-[var(--text-color)] transition-all">
                            ⚠️ Avisar a jefatura/tutor si repite
                        </button>
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-xs font-bold text-[var(--text-heading)] uppercase tracking-wider mb-1.5">
                        Texto del aviso
                    </label>
                    <textarea name="message" id="message" rows="3" required
                        placeholder="Escribe aquí el texto que se le mostrará al profesorado..."
                        class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl p-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all placeholder:text-[var(--text-muted)] @error('message') border-rose-500 @enderror">{{ old('message', $notice->message) }}</textarea>
                    @error('message')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado Activo / Inactivo -->
                <div class="pt-2">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                            class="w-4 h-4 rounded text-amber-500 border-slate-300 focus:ring-amber-500"
                            {{ old('is_active', $notice->is_active ?? true) ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-[var(--text-heading)]">Aviso activo (mostrar de inmediato en pasillos)</span>
                    </label>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('salidas.notices.index') }}"
                    class="py-2.5 px-5 bg-[var(--bg-input)] hover:bg-[var(--bg-hover)] text-[var(--text-color)] border border-[var(--border)] rounded-xl font-bold text-sm transition-all">
                    Cancelar
                </a>
                <button type="submit"
                    class="py-2.5 px-6 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl font-bold text-sm shadow-md shadow-amber-500/20 transform hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $isEdit ? 'Guardar Cambios' : 'Registrar Aviso' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function setPreset(text) {
        const textarea = document.getElementById('message');
        textarea.value = text;
        textarea.focus();
    }

    function handleStudentSelect(radio, studentName) {
        document.querySelectorAll('.student-radio-card').forEach(card => {
            card.classList.remove('bg-amber-500/10', 'border-amber-500', 'shadow-xs');
            card.classList.add('bg-[var(--bg-input)]', 'border-[var(--border)]');
        });
        const card = radio.closest('.student-radio-card');
        card.classList.add('bg-amber-500/10', 'border-amber-500', 'shadow-xs');
        card.classList.remove('bg-[var(--bg-input)]', 'border-[var(--border)]');

        const indicator = document.getElementById('selected-student-indicator');
        if (indicator) {
            indicator.textContent = `Seleccionado: ${studentName}`;
        }
    }

    const STUDENTS_PER_PAGE = 40;
    let currentStudentPage = 1;
    let currentFilteredCards = [];

    function renderStudentPagination(totalCount) {
        const paginationContainer = document.getElementById('student-pagination');
        const paginationInfo = document.getElementById('student-pagination-info');
        const paginationControls = document.getElementById('student-pagination-controls');

        if (!paginationContainer || !paginationControls || !paginationInfo) return;

        if (totalCount <= STUDENTS_PER_PAGE) {
            paginationContainer.classList.add('hidden');
            return;
        }

        paginationContainer.classList.remove('hidden');

        const totalPages = Math.ceil(totalCount / STUDENTS_PER_PAGE);
        const startItem = (currentStudentPage - 1) * STUDENTS_PER_PAGE + 1;
        const endItem = Math.min(currentStudentPage * STUDENTS_PER_PAGE, totalCount);

        paginationInfo.innerHTML = `Mostrando <span class="font-bold text-[var(--text-heading)]">${startItem} - ${endItem}</span> de <span class="font-bold text-[var(--text-heading)]">${totalCount}</span> alumnos`;

        let html = '';

        // Botón Anterior
        const prevDisabled = currentStudentPage === 1;
        html += `
            <button type="button" onclick="goToStudentPage(${currentStudentPage - 1})"
                ${prevDisabled ? 'disabled' : ''}
                class="px-2.5 py-1.5 rounded-lg border border-[var(--border)] bg-[var(--bg-input)] text-[var(--text-color)] hover:bg-[var(--bg-hover)] hover:text-amber-500 font-semibold text-xs flex items-center gap-1 transition-all ${prevDisabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''}"
                title="Página anterior">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="hidden sm:inline">Anterior</span>
            </button>
        `;

        // Botones numéricos
        const pageNumbers = getPageNumbers(currentStudentPage, totalPages);
        pageNumbers.forEach(p => {
            if (p === '...') {
                html += `<span class="px-1.5 py-1 text-[var(--text-muted)] select-none">...</span>`;
            } else {
                const isActive = p === currentStudentPage;
                html += `
                    <button type="button" onclick="goToStudentPage(${p})"
                        class="min-w-[30px] h-7 px-2 flex items-center justify-center rounded-lg text-xs font-bold transition-all ${isActive ? 'bg-amber-500 text-white shadow-xs' : 'border border-[var(--border)] bg-[var(--bg-input)] text-[var(--text-color)] hover:bg-[var(--bg-hover)] hover:border-amber-500/50'}">
                        ${p}
                    </button>
                `;
            }
        });

        // Botón Siguiente
        const nextDisabled = currentStudentPage === totalPages;
        html += `
            <button type="button" onclick="goToStudentPage(${currentStudentPage + 1})"
                ${nextDisabled ? 'disabled' : ''}
                class="px-2.5 py-1.5 rounded-lg border border-[var(--border)] bg-[var(--bg-input)] text-[var(--text-color)] hover:bg-[var(--bg-hover)] hover:text-amber-500 font-semibold text-xs flex items-center gap-1 transition-all ${nextDisabled ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''}"
                title="Página siguiente">
                <span class="hidden sm:inline">Siguiente</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        `;

        paginationControls.innerHTML = html;
    }

    function getPageNumbers(current, total) {
        if (total <= 7) {
            return Array.from({ length: total }, (_, i) => i + 1);
        }
        if (current <= 4) {
            return [1, 2, 3, 4, 5, '...', total];
        }
        if (current >= total - 3) {
            return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
        }
        return [1, '...', current - 1, current, current + 1, '...', total];
    }

    function goToStudentPage(page) {
        const totalPages = Math.ceil(currentFilteredCards.length / STUDENTS_PER_PAGE);
        if (page < 1 || page > totalPages) return;
        currentStudentPage = page;
        applyStudentPageVisibility();
        renderStudentPagination(currentFilteredCards.length);
        const scrollArea = document.getElementById('students-scroll-area');
        if (scrollArea) {
            scrollArea.scrollTop = 0;
        }
    }

    function applyStudentPageVisibility() {
        const startIndex = (currentStudentPage - 1) * STUDENTS_PER_PAGE;
        const endIndex = startIndex + STUDENTS_PER_PAGE;

        currentFilteredCards.forEach((card, index) => {
            if (index >= startIndex && index < endIndex) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    function filterStudents(resetPage = true) {
        const search = document.getElementById('student-search-input').value.toLowerCase().trim();
        const groupId = document.getElementById('group-filter-select').value;
        const cards = Array.from(document.querySelectorAll('.student-radio-card'));

        currentFilteredCards = [];

        cards.forEach(card => {
            const cardSearch = card.getAttribute('data-search') || '';
            const cardGroupId = card.getAttribute('data-group-id') || '';

            const matchesSearch = search === '' || cardSearch.includes(search);
            const matchesGroup = groupId === '' || cardGroupId === groupId;

            if (matchesSearch && matchesGroup) {
                currentFilteredCards.push(card);
            } else {
                card.classList.add('hidden');
            }
        });

        const noMatchEl = document.getElementById('no-students-match');
        if (currentFilteredCards.length === 0) {
            noMatchEl.classList.remove('hidden');
        } else {
            noMatchEl.classList.add('hidden');
        }

        if (resetPage) {
            currentStudentPage = 1;
        } else {
            const totalPages = Math.ceil(currentFilteredCards.length / STUDENTS_PER_PAGE) || 1;
            if (currentStudentPage > totalPages) currentStudentPage = totalPages;
        }

        applyStudentPageVisibility();
        renderStudentPagination(currentFilteredCards.length);
    }

    // Inicializar indicador del alumno seleccionado y paginación
    document.addEventListener('DOMContentLoaded', function() {
        const checked = document.querySelector('input[name="user_id"]:checked');
        if (checked) {
            const card = checked.closest('.student-radio-card');
            const name = card.getAttribute('data-name');
            const indicator = document.getElementById('selected-student-indicator');
            if (indicator && name) {
                indicator.textContent = `Seleccionado: ${name}`;
            }

            filterStudents(false);

            // Si hay un alumno previamente seleccionado, ir a su página
            const index = currentFilteredCards.indexOf(card);
            if (index !== -1) {
                currentStudentPage = Math.floor(index / STUDENTS_PER_PAGE) + 1;
                applyStudentPageVisibility();
                renderStudentPagination(currentFilteredCards.length);
            }
        } else {
            filterStudents(true);
        }
    });
</script>
@endsection
