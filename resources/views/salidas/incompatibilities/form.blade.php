@extends('layouts.app')

@section('title', ($isEdit ? 'Editar Grupo' : 'Nuevo Grupo') . ' de Incompatibilidad - Pasillos')

@section('module_title')
{{ $isEdit ? 'Editar' : 'Nuevo' }} Grupo de <span class="text-rose-500">Incompatibilidad</span>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Top Header Card -->
        <div class="flex items-center justify-between gap-4 bg-[var(--bg-card)] p-5 rounded-2xl border border-[var(--border)] shadow-sm">
            <div class="flex items-center gap-3.5 sm:gap-5">
                <a href="{{ route('salidas.incompatibilities.index') }}"
                    class="p-2 sm:p-2.5 bg-[var(--bg-input)] text-[var(--text-muted)] border border-[var(--border)] rounded-xl hover:text-[var(--primary)] hover:border-[var(--primary)] transition-all group shrink-0"
                    title="Volver al listado de incompatibilidades">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-rose-500/10 text-rose-500 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </span>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-[var(--text-heading)]">
                            {{ $isEdit ? 'Editar Grupo de Incompatibilidad' : 'Crear Grupo de Incompatibilidad' }}
                        </h1>
                    </div>
                    <p class="text-[var(--text-muted)] text-xs sm:text-sm font-medium mt-1">
                        Los alumnos seleccionados generarán un aviso si intentan salir al pasillo cuando otro alumno del grupo ya está fuera.
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

        <form action="{{ $isEdit ? route('salidas.incompatibilities.update', $incompatibility) : route('salidas.incompatibilities.store') }}" method="POST" id="incompatibility-form" class="space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Datos Generales -->
            <div class="bg-[var(--bg-card)] p-5 rounded-2xl border border-[var(--border)] shadow-sm space-y-4">
                <h3 class="text-base font-bold text-[var(--text-heading)] border-b border-[var(--border)] pb-3">
                    Información del Grupo
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold text-[var(--text-heading)] uppercase tracking-wider mb-1.5">
                            Nombre del grupo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required
                            value="{{ old('name', $incompatibility->name) }}"
                            placeholder="Ej. Conflicto 2º ESO A, Pareja incompatible..."
                            class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 px-3.5 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all placeholder:text-[var(--text-muted)] @error('name') border-rose-500 @enderror">
                        @error('name')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-bold text-[var(--text-heading)] uppercase tracking-wider mb-1.5">
                            Motivo / Observaciones (opcional)
                        </label>
                        <input type="text" name="description" id="description"
                            value="{{ old('description', $incompatibility->description) }}"
                            placeholder="Ej. Incidentes previos en el recreo o pasillo..."
                            class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 px-3.5 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all placeholder:text-[var(--text-muted)] @error('description') border-rose-500 @enderror">
                    </div>
                </div>
            </div>

            <!-- Selección de Alumnos -->
            <div class="bg-[var(--bg-card)] p-5 rounded-2xl border border-[var(--border)] shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[var(--border)] pb-3">
                    <div>
                        <h3 class="text-base font-bold text-[var(--text-heading)] flex items-center gap-2">
                            <span>Selección de Alumnos</span>
                            <span id="selected-badge" class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-rose-500/15 text-rose-500 border border-rose-500/30">
                                <span id="selected-count">{{ count($selectedStudentIds) }}</span> seleccionados
                            </span>
                        </h3>
                        <p class="text-[var(--text-muted)] text-xs mt-0.5">
                            Marca al menos 2 alumnos para formar el grupo de incompatibilidad.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="selectAllVisible()"
                            class="px-3 py-1.5 text-xs font-semibold bg-[var(--bg-input)] hover:bg-[var(--bg-hover)] text-[var(--text-color)] border border-[var(--border)] rounded-xl transition-all">
                            Seleccionar visibles
                        </button>
                        <button type="button" onclick="clearAllSelections()"
                            class="px-3 py-1.5 text-xs font-semibold bg-[var(--bg-input)] hover:bg-[var(--bg-hover)] text-rose-500 border border-[var(--border)] rounded-xl transition-all">
                            Deseleccionar todos
                        </button>
                    </div>
                </div>

                <!-- Filtros para la lista de alumnos -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Búsqueda por texto -->
                    <div class="sm:col-span-2 relative">
                        <input type="text" id="student-search-input" oninput="filterStudents()"
                            placeholder="Filtrar alumnos por nombre o apellidos..."
                            class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 pl-10 pr-4 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all placeholder:text-[var(--text-muted)]">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Filtro por Clase / Grupo -->
                    <div>
                        <select id="group-filter-select" onchange="filterStudents()"
                            class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 px-3 text-sm text-[var(--text-color)] focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition-all">
                            <option value="">Todas las clases</option>
                            @foreach($classGroups as $cg)
                                <option value="{{ $cg->id }}">{{ $cg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Lista de Alumnos (Grid) -->
                <div class="max-h-[500px] overflow-y-auto pr-1">
                    <div id="students-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                        @foreach($students as $student)
                            @php
                                $courseName = trim(($student->groupRel?->course ? $student->groupRel->course . ' ' : '') . ($student->groupRel?->name ?? ''));
                                $displayName = $student->last_name ? trim($student->last_name . ', ' . $student->name) : $student->name;
                                $searchString = mb_strtolower(($student->last_name ?? '') . ' ' . $student->name . ' ' . $student->name . ' ' . ($student->last_name ?? ''));
                                $isSelected = in_array($student->id, $selectedStudentIds);
                            @endphp
                            <label class="student-select-card flex items-center gap-3 p-3 rounded-2xl border transition-all cursor-pointer select-none {{ $isSelected ? 'bg-rose-500/10 border-rose-500 shadow-xs' : 'bg-[var(--bg-input)] border-[var(--border)] hover:border-slate-400 dark:hover:border-slate-600' }}"
                                data-student-id="{{ $student->id }}"
                                data-group-id="{{ $student->group_id }}"
                                data-search="{{ $searchString }}">
                                
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                    class="student-checkbox w-4 h-4 rounded text-rose-600 border-slate-300 focus:ring-rose-500 transition-all shrink-0"
                                    {{ $isSelected ? 'checked' : '' }}
                                    onchange="handleStudentCheck(this)">

                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-400 to-slate-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                    {{ substr($student->last_name ?? $student->name, 0, 1) }}{{ substr($student->name, 0, 1) }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="text-xs sm:text-sm font-bold text-[var(--text-heading)] truncate" title="{{ $displayName }}">
                                        {{ $displayName }}
                                    </div>
                                    @if($courseName)
                                        <div class="text-[11px] text-[var(--text-muted)] truncate">
                                            {{ $courseName }}
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div id="no-students-match" class="hidden py-8 text-center text-[var(--text-muted)] text-sm">
                        No hay alumnos que coincidan con los filtros aplicados.
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('salidas.incompatibilities.index') }}"
                    class="py-2.5 px-5 bg-[var(--bg-input)] hover:bg-[var(--bg-hover)] text-[var(--text-color)] border border-[var(--border)] rounded-xl font-bold text-sm transition-all">
                    Cancelar
                </a>
                <button type="submit" id="submit-btn"
                    class="py-2.5 px-6 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white rounded-xl font-bold text-sm shadow-md shadow-rose-500/20 transform hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $isEdit ? 'Guardar Cambios' : 'Crear Grupo de Incompatibilidad' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.student-checkbox:checked');
        const count = checked.length;
        document.getElementById('selected-count').textContent = count;
    }

    function handleStudentCheck(checkbox) {
        const card = checkbox.closest('.student-select-card');
        if (checkbox.checked) {
            card.classList.add('bg-rose-500/10', 'border-rose-500', 'shadow-xs');
            card.classList.remove('bg-[var(--bg-input)]', 'border-[var(--border)]');
        } else {
            card.classList.remove('bg-rose-500/10', 'border-rose-500', 'shadow-xs');
            card.classList.add('bg-[var(--bg-input)]', 'border-[var(--border)]');
        }
        updateSelectedCount();
    }

    function filterStudents() {
        const search = document.getElementById('student-search-input').value.toLowerCase().trim();
        const groupId = document.getElementById('group-filter-select').value;
        const cards = document.querySelectorAll('.student-select-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const cardSearch = card.getAttribute('data-search') || '';
            const cardGroupId = card.getAttribute('data-group-id') || '';

            const matchesSearch = search === '' || cardSearch.includes(search);
            const matchesGroup = groupId === '' || cardGroupId === groupId;

            if (matchesSearch && matchesGroup) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        const noMatchEl = document.getElementById('no-students-match');
        if (visibleCount === 0) {
            noMatchEl.classList.remove('hidden');
        } else {
            noMatchEl.classList.add('hidden');
        }
    }

    function selectAllVisible() {
        const visibleCards = document.querySelectorAll('.student-select-card:not(.hidden)');
        visibleCards.forEach(card => {
            const cb = card.querySelector('.student-checkbox');
            if (cb && !cb.checked) {
                cb.checked = true;
                handleStudentCheck(cb);
            }
        });
        updateSelectedCount();
    }

    function clearAllSelections() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                cb.checked = false;
                handleStudentCheck(cb);
            }
        });
        updateSelectedCount();
    }

    document.getElementById('incompatibility-form').addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.student-checkbox:checked');
        if (checked.length < 2) {
            e.preventDefault();
            alert('Debes seleccionar al menos 2 alumnos para que exista una incompatibilidad.');
        }
    });

    // Initialize on load
    document.addEventListener('DOMContentLoaded', updateSelectedCount);
</script>
@endsection
