@extends('layouts.app')

@section('title', 'Crear Grupo')

@section('content')
    <div class="page-header" style="margin-bottom: 2rem;">
        <h1 class="page-title">Crear Nuevo Grupo</h1>
        <a href="{{ route('groups.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Volver</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="max-width: 600px;">
        <form action="{{ route('groups.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="school_year_id">Curso Escolar</label>
                <select name="school_year_id" id="school_year_id">
                    <option value="">-- Seleccionar curso escolar --</option>
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}" {{ old('school_year_id', $activeSchoolYearId) == $sy->id ? 'selected' : '' }}>
                            {{ $sy->name }}{{ $sy->is_active ? ' (Activo)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('school_year_id') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="course">Ciclo / Etapa Educativa</label>
                <input type="text" name="course" id="course" value="{{ old('course') }}" required
                    placeholder="Ej: ESO, Bachillerato, DAW...">
                @error('course') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="name">Nombre del Grupo</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Ej: 1º A, 2º B...">
                @error('name') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="tutor_id">Tutor Asignado</label>
                <select name="tutor_id" id="tutor_id">
                    <option value="">Sin tutor asignado</option>
                    @foreach($tutors as $tutor)
                        <option value="{{ $tutor->id }}" {{ old('tutor_id') == $tutor->id ? 'selected' : '' }}>
                            {{ $tutor->name }} {{ $tutor->last_name }} ({{ $tutor->email }})
                        </option>
                    @endforeach
                </select>
                @error('tutor_id') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Crear Grupo</button>
                <a href="{{ route('groups.index') }}" class="btn" style="background: rgba(255,255,255,0.1); color: #fff;">Cancelar</a>
            </div>
        </form>
    </div>
@endsection