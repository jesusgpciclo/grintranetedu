@extends('layouts.app')

@section('title', 'Editar Alumno')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Editar Alumno</h1>
            <p style="color: var(--text-muted);">Modificar los datos de {{ $student->name }} {{ $student->last_name }}</p>
        </div>
        <a href="{{ route('students.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Volver al listado</a>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('students.update', $student) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $student->name) }}" required>
                @error('name')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="last_name" class="form-label">Apellidos</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name', $student->last_name) }}" required>
                @error('last_name')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $student->email) }}" required>
                @error('email')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="group_id" class="form-label">Grupo (Opcional)</label>
                <select name="group_id" id="group_id" class="form-control">
                    <option value="">Seleccione un grupo...</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ (old('group_id') ?? $student->group_id) == $group->id ? 'selected' : '' }}>
                            {{ $group->course }} - {{ $group->name }}
                        </option>
                    @endforeach
                </select>
                @error('group_id')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea name="observaciones" id="observaciones" rows="3" class="form-control" placeholder="Notas psicopedagógicas, adaptaciones o anotaciones sobre el alumno...">{{ old('observaciones', $student->observaciones) }}</textarea>
                @error('observaciones')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Nueva Contraseña (Opcional)</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
                    <button type="button" 
                            onclick="togglePasswordVisibility('password', this)" 
                            class="password-toggle-btn" 
                            title="Ver contraseña" 
                            aria-label="Ver contraseña">
                        <svg class="eye-show-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="eye-hide-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('students.index') }}" class="btn" style="background: rgba(255,255,255,0.1); color: var(--text-heading);">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Alumno</button>
            </div>
        </form>
    </div>
@endsection