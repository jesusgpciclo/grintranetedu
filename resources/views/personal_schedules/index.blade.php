@extends('layouts.app')

@section('title', 'Mis Horarios Personales')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Mis Horarios Personales</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">Crea, edita e importa tus horarios por curso escolar</p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            @if(!empty($canManageTeachers))
                <a href="{{ route('teacher-schedules.index') }}" class="btn btn-secondary" style="background: rgba(59, 130, 246, 0.15); color: #38bdf8; border-color: rgba(59, 130, 246, 0.3);">
                    👥 Horarios del Profesorado
                </a>
            @endif
            <button onclick="document.getElementById('modal-import-horario').classList.remove('hidden')" class="btn btn-secondary">
                📥 Importar Horario
            </button>
            <a href="{{ route('personal-schedules.create') }}" class="btn btn-primary">
                + Nuevo Horario
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

    @if($schedules->isEmpty())
        <div class="card text-center" style="padding: 4rem 2rem;">
            <div style="font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.8;">📅</div>
            <h2 style="margin-bottom: 0.5rem; color: var(--text-heading);">No tienes ningún horario creado aún</h2>
            <p style="margin-bottom: 2rem; color: var(--text-muted); max-width: 500px; margin-left: auto; margin-right: auto;">
                Crea tu horario asignando a cada hora si impartes clase a un grupo, realizas guardia o cualquier otra actividad.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('personal-schedules.create') }}" class="btn btn-primary">
                    + Crear Horario
                </a>
                <button onclick="document.getElementById('modal-import-horario').classList.remove('hidden')" class="btn btn-secondary">
                    📥 Importar desde Archivo
                </button>
            </div>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
            @foreach($schedules as $schedule)
                <div class="card card-hover" style="display: flex; flex-direction: column; justify-content: space-between; margin-bottom: 0;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                            <div>
                                <span class="badge badge-primary" style="margin-bottom: 0.35rem;">
                                    🗓️ {{ $schedule->schoolYear ? $schedule->schoolYear->name : 'Curso General' }}
                                </span>
                                <h3 style="font-weight: 800; font-size: 1.25rem; color: var(--text-heading); margin-top: 0.2rem;">
                                    {{ $schedule->scheduleTemplate->name ?? 'Horario Personal' }}
                                </h3>
                            </div>
                        </div>
                        
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                            {{ $schedule->scheduleTemplate->description ?? 'Plantilla base de tramos horarias' }}
                        </p>

                        <div style="display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.825rem; color: var(--text-muted); background: var(--bg-hover); padding: 0.75rem; border-radius: 0.625rem; border: 1px solid var(--border);">
                            <div><strong>Días lectivos:</strong> {{ implode(', ', $schedule->scheduleTemplate->active_days ?? []) }}</div>
                            <div><strong>Tramos configurados:</strong> {{ $schedule->selections->count() }} horas asignadas</div>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('personal-schedules.show', $schedule) }}" class="btn btn-primary" style="flex: 1; justify-content: center;">
                                Ver Horario
                            </a>
                            <a href="{{ route('personal-schedules.edit', $schedule) }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">
                                ✏️ Editar
                            </a>
                            <form action="{{ route('personal-schedules.destroy', $schedule) }}" method="POST" style="margin: 0;" onsubmit="return confirm('¿Estás seguro de eliminar este horario personal?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 0.6rem 0.85rem;" title="Eliminar Horario">
                                    🗑️
                                </button>
                            </form>
                        </div>
                        
                        <!-- Export Buttons -->
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('personal-schedules.print', $schedule) }}" target="_blank" class="btn btn-secondary btn-sm" style="flex: 1; justify-content: center; font-size: 0.775rem; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.2);">
                                🖨️ PDF
                            </a>
                            <a href="{{ route('personal-schedules.export', [$schedule, 'csv']) }}" class="btn btn-secondary btn-sm" style="flex: 1; justify-content: center; font-size: 0.775rem;">
                                ⬇ CSV
                            </a>
                            <a href="{{ route('personal-schedules.export', [$schedule, 'json']) }}" class="btn btn-secondary btn-sm" style="flex: 1; justify-content: center; font-size: 0.775rem;">
                                ⬇ JSON
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Import Modal -->
    <div id="modal-import-horario" class="hidden" style="position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(4px); z-index: 99; display: flex; align-items: center; justify-content: center; padding: 1rem;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 0; box-shadow: var(--shadow-lg);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--text-heading);">📥 Importar Horario</h3>
                <button onclick="document.getElementById('modal-import-horario').classList.add('hidden')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem;">✕</button>
            </div>

            <form action="{{ route('personal-schedules.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label for="import_school_year_id">Curso Escolar</label>
                    <select name="school_year_id" id="import_school_year_id" required class="form-control">
                        @foreach($schoolYears as $sy)
                            <option value="{{ $sy->id }}" {{ $sy->is_active ? 'selected' : '' }}>
                                {{ $sy->name }} {{ $sy->is_active ? '(Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="import_schedule_template_id">Plantilla de Horario</label>
                    <select name="schedule_template_id" id="import_schedule_template_id" required class="form-control">
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="import_file">Archivo de Horario (CSV o JSON)</label>
                    <input type="file" name="file" id="import_file" accept=".csv,.json,.txt" required class="form-control">
                </div>

                <div style="margin-bottom: 1.5rem; font-size: 0.8rem; color: var(--text-muted); background: var(--bg-hover); padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                    💡 Descargar plantillas de ejemplo:
                    <a href="{{ route('personal-schedules.template', 'csv') }}" style="font-weight: 700;">CSV</a> |
                    <a href="{{ route('personal-schedules.template', 'json') }}" style="font-weight: 700;">JSON</a>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('modal-import-horario').classList.add('hidden')" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Comenzar Importación</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .hidden { display: none !important; }
    </style>
@endsection