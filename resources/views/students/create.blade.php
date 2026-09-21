@extends('layouts.app')

@section('title', 'Nuevo Alumno')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Nuevo Alumno</h1>
            <p style="color: var(--text-muted);">Registrar un nuevo alumno en el sistema</p>
        </div>
        <a href="{{ route('students.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Volver al listado</a>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('students.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                @error('name')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="last_name" class="form-label">Apellidos</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}" required>
                @error('last_name')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                @error('email')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="group_id" class="form-label">Grupo (Opcional)</label>
                <select name="group_id" id="group_id" class="form-control">
                    <option value="">Seleccione un grupo...</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
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
                <textarea name="observaciones" id="observaciones" rows="3" class="form-control" placeholder="Notas psicopedagógicas, adaptaciones o anotaciones sobre el alumno...">{{ old('observaciones') }}</textarea>
                @error('observaciones')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña (Opcional)</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Dejar en blanco para usar el email como contraseña">
                @error('password')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <a href="{{ route('students.index') }}" class="btn" style="background: rgba(255,255,255,0.1); color: var(--text-heading);">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Alumno</button>
            </div>
        </form>
    </div>
@endsection