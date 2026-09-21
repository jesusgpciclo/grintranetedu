@extends('layouts.app')

@section('title', 'Nuevo Curso Escolar')

@section('content')
    <div class="page-header" style="margin-bottom: 2rem;">
        <h1 class="page-title">Nuevo Curso Escolar</h1>
        <p style="color: var(--text-muted);">Crea un nuevo año escolar o curso académico para segmentar el sistema.</p>
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
        <form action="{{ route('school-years.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Nombre del Curso Escolar *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Ejemplo: 2026/27" required>
                <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Formato recomendado: AAAA/AA o similar.</small>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }} style="width: auto;">
                <label for="is_active" style="margin-bottom: 0; cursor: pointer;">Establecer como curso activo</label>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('school-years.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1); color: #fff;">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
