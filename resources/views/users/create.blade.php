@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Crear Usuario</h1>
        <a href="{{ route('users.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Volver</a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                @error('name') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="last_name">Apellidos</label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}">
                @error('last_name') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" id="dept-field">
                <label for="departamento">Departamento</label>
                <input type="text" name="departamento" id="departamento" value="{{ old('departamento') }}" placeholder="Ej: Matemáticas, Lengua, Informática...">
                @error('departamento') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
                @error('email') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" id="student-only-fields" style="display: none;">
                <label for="group_id">Asignar a Grupo</label>
                <select name="group_id" id="group_id">
                    <option value="">Seleccionar Grupo</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->course }} - {{ $group->name }} (Tutor: {{ $group->tutor->name ?? 'Sin asignar' }})
                        </option>
                    @endforeach
                </select>
                @error('group_id') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea name="observaciones" id="observaciones" rows="3" placeholder="Notas u observaciones sobre el usuario...">{{ old('observaciones') }}</textarea>
                @error('observaciones') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    function toggleRoles() {
                        const alumnoChecked = Array.from(document.querySelectorAll('.role-checkbox:checked'))
                            .some(cb => cb.value === 'alumno');
                        document.getElementById('student-only-fields').style.display = alumnoChecked ? 'block' : 'none';
                        const deptField = document.getElementById('dept-field');
                        if (deptField) {
                            deptField.style.display = alumnoChecked ? 'none' : 'block';
                        }
                    }

                    document.querySelectorAll('.role-checkbox').forEach(cb => {
                        cb.addEventListener('change', toggleRoles);
                    });
                    toggleRoles(); // Initial state
                });
            </script>

            <div class="form-group">
                <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Roles Asignados</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 0.75rem; padding: 0.75rem; background: var(--bg-hover); border-radius: 8px; border: 1px solid var(--border);">
                    @foreach($roles as $role)
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-heading); font-size: 0.9rem;">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="role-checkbox"
                                {{ (is_array(old('roles')) && in_array($role->name, old('roles'))) ? 'checked' : '' }}>
                            {{ ucfirst($role->name) }}
                        </label>
                    @endforeach
                </div>
                @error('roles') <span style="color: var(--danger); font-size: 0.8rem; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required>
                @error('password') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary">Guardar Usuario</button>
            </div>
        </form>
    </div>
@endsection