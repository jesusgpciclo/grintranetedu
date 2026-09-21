@extends('layouts.app')

@section('title', 'Editar Curso Escolar')

@section('content')
    <div class="page-header" style="margin-bottom: 2rem;">
        <h1 class="page-title">Editar Curso Escolar</h1>
        <p style="color: var(--text-muted);">Modifica el nombre o el estado del curso escolar.</p>
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
        <form action="{{ route('school-years.update', $schoolYear) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Nombre del Curso Escolar *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $schoolYear->name) }}" required>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $schoolYear->is_active) ? 'checked' : '' }} {{ $schoolYear->is_active ? 'disabled' : '' }} style="width: auto;">
                <label for="is_active" style="margin-bottom: 0; cursor: pointer;">Establecer como curso activo</label>
                @if($schoolYear->is_active)
                    <input type="hidden" name="is_active" value="1">
                    <small style="color: var(--text-muted); margin-left: 0.5rem;">(Ya es el curso activo y no puede desactivarse directamente)</small>
                @endif
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('school-years.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1); color: #fff;">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
