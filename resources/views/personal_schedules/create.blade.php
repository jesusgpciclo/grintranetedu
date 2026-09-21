@extends('layouts.app')

@section('title', 'Crear Horario Personal')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Crear Horario Personal</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">Selecciona el curso escolar y la plantilla horaria base</p>
        </div>
        <a href="{{ $canManage ? route('teacher-schedules.index') : route('personal-schedules.index') }}" class="btn btn-secondary">&larr; Volver</a>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('personal-schedules.store') }}" method="POST">
            @csrf

            @if($canManage && $teachers->isNotEmpty())
                <div class="form-group">
                    <label for="user_id">Profesor / Docente Asignado:</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ ($selectedUserId == $teacher->id) ? 'selected' : '' }}>
                                👤 {{ $teacher->name }} {{ $teacher->last_name }} ({{ $teacher->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="form-group">
                <label for="school_year_id">Curso Escolar:</label>
                <select name="school_year_id" id="school_year_id" class="form-control" required>
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}" {{ ($activeSchoolYear && $activeSchoolYear->id == $sy->id) ? 'selected' : '' }}>
                            {{ $sy->name }} {{ $sy->is_active ? '(Curso Activo)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="schedule_template_id">Plantilla de Horario Base:</label>
                <select name="schedule_template_id" id="schedule_template_id" class="form-control" required>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">
                            {{ $template->name }} ({{ count($template->active_days ?? []) }} días lectivos, {{ $template->timeSlots->count() }} tramos)
                        </option>
                    @endforeach
                </select>
                <small style="color: var(--text-muted); display: block; margin-top: 0.4rem;">
                    La plantilla define el cuadro horario de tramos y días lectivos del centro.
                </small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; margin-top: 1rem;">
                Crear e Iniciar Configuración &rarr;
            </button>
        </form>
    </div>
@endsection