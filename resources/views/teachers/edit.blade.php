@extends('layouts.app')

@section('title', 'Editar Profesor')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Editar Profesor</h1>
            <p style="color: var(--text-muted);">Modificar los datos de {{ $teacher->name }} {{ $teacher->last_name }}</p>
        </div>
        <a href="{{ route('teachers.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Volver al listado</a>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $teacher->name) }}" required>
                @error('name')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="last_name" class="form-label">Apellidos</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $teacher->last_name) }}" required>
                @error('last_name')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="departamento" class="form-label">Departamento</label>
                <input type="text" name="departamento" id="departamento" class="form-control" value="{{ old('departamento', $teacher->departamento) }}" placeholder="Ej: Matemáticas, Lengua Castellana, Inglés, Tecnología...">
                @error('departamento')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $teacher->email) }}" required>
                @error('email')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea name="observaciones" id="observaciones" rows="3" class="form-control" placeholder="Notas, cargo docente u observaciones...">{{ old('observaciones', $teacher->observaciones) }}</textarea>
                @error('observaciones')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Nueva Contraseña (Opcional)</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Dejar en blanco para mantener la contraseña actual">
                @error('password')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('teachers.index') }}" class="btn" style="background: rgba(255,255,255,0.1); color: var(--text-heading);">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Profesor</button>
            </div>
        </form>
    </div>
@endsection
