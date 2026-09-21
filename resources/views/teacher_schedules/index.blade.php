@extends('layouts.app')

@section('title', 'Gestión de Horarios del Profesorado')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestión de Horarios del Profesorado</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">
                Administra, importa y exporta los horarios de todos los docentes del centro educativo
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <!-- Selector de Curso Escolar -->
            <form method="GET" action="{{ route('teacher-schedules.index') }}" id="schoolYearForm" style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <select name="school_year_id" class="form-control" style="width: auto; padding: 0.45rem 0.85rem; font-weight: 600;" onchange="this.form.submit()">
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}" {{ ($selectedSchoolYear && $selectedSchoolYear->id == $sy->id) ? 'selected' : '' }}>
                            🗓️ {{ $sy->name }} {{ $sy->is_active ? '(Activo)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Exportar Todos Dropdown -->
            <div style="position: relative; display: inline-block;">
                <button type="button" onclick="document.getElementById('dropdown-export-all').classList.toggle('hidden')" class="btn btn-secondary">
                    ⬇️ Exportar Todos
                </button>
                <div id="dropdown-export-all" class="hidden" style="position: absolute; right: 0; top: 110%; background: var(--bg-card); border: 1px solid var(--border); border-radius: 0.75rem; box-shadow: var(--shadow-lg); min-width: 170px; z-index: 50; overflow: hidden;">
                    <a href="{{ route('teacher-schedules.export-all', ['format' => 'csv', 'school_year_id' => $selectedSchoolYear?->id]) }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; color: var(--text-main); text-decoration: none; border-bottom: 1px solid var(--border); font-size: 0.85rem;" class="nav-item-hover">
                        📄 Exportar CSV
                    </a>
                    <a href="{{ route('teacher-schedules.export-all', ['format' => 'json', 'school_year_id' => $selectedSchoolYear?->id]) }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; color: var(--text-main); text-decoration: none; font-size: 0.85rem;" class="nav-item-hover">
                        📦 Exportar JSON
                    </a>
                </div>
            </div>

            <!-- Importación Masiva -->
            <button onclick="document.getElementById('modal-import-bulk').classList.remove('hidden')" class="btn btn-secondary">
                📥 Importación Masiva
            </button>

            <!-- Crear Horario Directo -->
            <a href="{{ route('personal-schedules.create') }}" class="btn btn-primary">
                + Crear Horario
            </a>
        </div>
    </div>

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

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Barra de Búsqueda y Estadísticas -->
    <div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <form method="GET" action="{{ route('teacher-schedules.index') }}" style="display: flex; align-items: center; gap: 0.5rem; flex: 1; max-width: 420px; margin: 0;">
                <input type="hidden" name="school_year_id" value="{{ $selectedSchoolYear?->id }}">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, apellidos o email..." class="form-control" style="margin: 0; padding: 0.5rem 0.85rem;">
                <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 0.85rem;">🔍</button>
                @if(request('search'))
                    <a href="{{ route('teacher-schedules.index', ['school_year_id' => $selectedSchoolYear?->id]) }}" class="btn btn-secondary" style="padding: 0.5rem 0.85rem;" title="Limpiar filtro">✕</a>
                @endif
            </form>

            @php
                $totalTeachers = $teachers->count();
                $withScheduleCount = $teachers->filter(fn($t) => $t->schedules->isNotEmpty())->count();
                $withoutScheduleCount = $totalTeachers - $withScheduleCount;
            @endphp
            <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-muted);">
                <div>Total Docentes: <strong style="color: var(--text-heading);">{{ $totalTeachers }}</strong></div>
                <div>&bull;</div>
                <div>Con horario: <strong style="color: #10b981;">{{ $withScheduleCount }}</strong></div>
                <div>&bull;</div>
                <div>Sin horario: <strong style="color: #f59e0b;">{{ $withoutScheduleCount }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Listado de Profesores -->
    <div class="card" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr>
                    <th style="padding: 1rem 1.25rem; text-align: left;">Profesor / Docente</th>
                    <th style="padding: 1rem 1.25rem; text-align: left;">Email</th>
                    <th style="padding: 1rem 1.25rem; text-align: left;">Curso Escolar</th>
                    <th style="padding: 1rem 1.25rem; text-align: left;">Plantilla Base</th>
                    <th style="padding: 1rem 1.25rem; text-align: center;">Horas Asignadas</th>
                    <th style="padding: 1rem 1.25rem; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $teacher)
                    @php
                        $schedule = $teacher->schedules->first();
                        $hasSchedule = !empty($schedule);
                        $selectionsCount = $hasSchedule ? $schedule->selections->count() : 0;
                    @endphp
                    <tr class="table-row-hover" style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem 1.25rem; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem; border: 1px solid var(--primary-border);">
                                    {{ strtoupper(substr($teacher->name, 0, 1) . substr($teacher->last_name ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--text-heading); font-size: 0.95rem;">
                                        {{ $teacher->name }} {{ $teacher->last_name }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; color: var(--text-muted); font-size: 0.85rem;">
                            {{ $teacher->email }}
                        </td>
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; font-size: 0.85rem;">
                            <span class="badge badge-primary">
                                {{ $selectedSchoolYear ? $selectedSchoolYear->name : 'General' }}
                            </span>
                        </td>
                        <td style="padding: 1rem 1.25rem; vertical-align: middle; font-size: 0.85rem;">
                            @if($hasSchedule)
                                <span style="font-weight: 600; color: var(--text-main);">
                                    {{ $schedule->scheduleTemplate->name ?? 'Plantilla' }}
                                </span>
                            @else
                                <span style="color: var(--text-muted); opacity: 0.6; font-style: italic;">Sin asignar</span>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: center; vertical-align: middle;">
                            @if($hasSchedule)
                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-weight: 700;">
                                    ✓ {{ $selectionsCount }} tramos
                                </span>
                            @else
                                <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">
                                    Sin horario
                                </span>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.25rem; text-align: right; vertical-align: middle;">
                            <div style="display: flex; gap: 0.35rem; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                                @if($hasSchedule)
                                    <a href="{{ route('personal-schedules.show', $schedule) }}" class="btn btn-secondary btn-sm" title="Ver Horario" style="padding: 0.4rem 0.65rem;">
                                        👁️ Ver
                                    </a>
                                    <a href="{{ route('personal-schedules.edit', $schedule) }}" class="btn btn-secondary btn-sm" title="Editar Horario" style="padding: 0.4rem 0.65rem;">
                                        ✏️ Editar
                                    </a>
                                    <a href="{{ route('personal-schedules.print', $schedule) }}" target="_blank" class="btn btn-secondary btn-sm" title="Exportar / Imprimir PDF" style="padding: 0.4rem 0.65rem; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.2);">
                                        🖨️ PDF
                                    </a>
                                    <a href="{{ route('personal-schedules.export', [$schedule, 'csv']) }}" class="btn btn-secondary btn-sm" title="Exportar CSV" style="padding: 0.4rem 0.65rem;">
                                        ⬇ CSV
                                    </a>
                                    <a href="{{ route('personal-schedules.export', [$schedule, 'json']) }}" class="btn btn-secondary btn-sm" title="Exportar JSON" style="padding: 0.4rem 0.65rem;">
                                        ⬇ JSON
                                    </a>
                                    <form action="{{ route('personal-schedules.destroy', $schedule) }}" method="POST" style="margin: 0;" onsubmit="return confirm('¿Estás seguro de eliminar el horario de {{ $teacher->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar Horario" style="padding: 0.4rem 0.65rem;">
                                            🗑️
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('personal-schedules.create', ['user_id' => $teacher->id, 'school_year_id' => $selectedSchoolYear?->id]) }}" class="btn btn-primary btn-sm" style="padding: 0.4rem 0.75rem;">
                                        + Asignar Horario
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                            No se encontraron profesores registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal de Importación Masiva -->
    <div id="modal-import-bulk" class="hidden" style="position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(4px); z-index: 99; display: flex; align-items: center; justify-content: center; padding: 1rem;">
        <div class="card" style="width: 100%; max-width: 550px; margin: 0; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--text-heading);">
                    📥 Importación Masiva de Horarios Docentes
                </h3>
                <button type="button" onclick="document.getElementById('modal-import-bulk').classList.add('hidden')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem;">✕</button>
            </div>

            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                Sube un archivo CSV o JSON con los horarios de múltiples profesores. El sistema vinculará cada horario usando el <strong>email</strong> de cada profesor.
            </p>

            <form action="{{ route('teacher-schedules.import-bulk') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label for="bulk_school_year_id">Curso Escolar por Defecto</label>
                    <select name="school_year_id" id="bulk_school_year_id" required class="form-control">
                        @foreach($schoolYears as $sy)
                            <option value="{{ $sy->id }}" {{ ($selectedSchoolYear && $selectedSchoolYear->id == $sy->id) ? 'selected' : '' }}>
                                {{ $sy->name }} {{ $sy->is_active ? '(Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="bulk_schedule_template_id">Plantilla de Horario por Defecto</label>
                    <select name="schedule_template_id" id="bulk_schedule_template_id" required class="form-control">
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="bulk_file">Archivo de Horarios (CSV o JSON)</label>
                    <input type="file" name="file" id="bulk_file" accept=".csv,.json,.txt" required class="form-control">
                </div>

                <div style="margin-bottom: 1.5rem; font-size: 0.8rem; color: var(--text-muted); background: var(--bg-hover); padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                    💡 Descargar plantillas de importación masiva:
                    <a href="{{ route('teacher-schedules.template-bulk', 'csv') }}" style="font-weight: 700; color: var(--primary);">Plantilla CSV</a> |
                    <a href="{{ route('teacher-schedules.template-bulk', 'json') }}" style="font-weight: 700; color: var(--primary);">Plantilla JSON</a>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('modal-import-bulk').classList.add('hidden')" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Iniciar Importación Masiva</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .hidden { display: none !important; }
        .table-row-hover:hover { background: var(--bg-hover); }
        .nav-item-hover:hover { background: var(--bg-hover); }
    </style>
@endsection
