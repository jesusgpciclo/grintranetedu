@extends('layouts.app')
@section('title', 'Nueva Zona / Aula')
@section('content')
    <div class="page-header"><h1 class="page-title">Nueva Zona / Aula</h1></div>

    <div class="card" style="max-width: 700px;">
        <form method="POST" action="{{ route('aulas.store') }}">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label>Tipo *</label>
                    <select name="tipo" id="tipo-select" onchange="toggleCapacidad()" required
                        style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff; outline: none;">
                        <option value="aula" {{ old('tipo', 'aula') === 'aula' ? 'selected' : '' }}>Aula</option>
                        <option value="zona" {{ old('tipo') === 'zona' ? 'selected' : '' }}>Zona</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Identificación (Código / Siglas)</label>
                    <input type="text" name="identificacion" value="{{ old('identificacion') }}" placeholder="Ej: A-101, Z-01">
                </div>
            </div>

            <div class="form-group">
                <label>Nombre del espacio *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Aula de Informática, Biblioteca, Patio...">
            </div>

            <div class="grid-2">
                <div class="form-group" id="capacidad-group">
                    <label>Capacidad (opcional)</label>
                    <input type="number" name="capacidad" id="capacidad-input" value="{{ old('capacidad') }}" min="1" max="500" placeholder="Ej: 30">
                </div>
                <div class="form-group">
                    <label>Ubicación</label>
                    <input type="text" name="ubicacion" value="{{ old('ubicacion') }}" placeholder="Ej: Planta 1, Edificio Principal">
                </div>
            </div>

            <div class="form-group">
                <label>Equipamiento (selecciona los que apliquen)</label>
                @php $equipamientoOptions = ['Proyector', 'Pizarra digital', 'Pizarra convencional', 'Ordenador profesor', 'Ordenadores alumnos', 'Impresora', 'Sistema de sonido', 'Webcam', 'Aire acondicionado']; @endphp
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem; margin-top: 0.5rem;">
                    @foreach($equipamientoOptions as $equip)
                        <div class="form-check">
                            <input type="checkbox" name="equipamiento[]" value="{{ $equip }}" id="eq_{{ Str::slug($equip) }}"
                                {{ in_array($equip, old('equipamiento', [])) ? 'checked' : '' }}>
                            <label for="eq_{{ Str::slug($equip) }}" style="color: #fff; font-size: 0.9rem;">{{ $equip }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3" placeholder="Descripción adicional o detalles del espacio...">{{ old('descripcion') }}</textarea>
            </div>

            @if($errors->any())
                <div class="alert alert-error">
                    @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
                </div>
            @endif

            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Crear Espacio</button>
                <a href="{{ route('aulas.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        function toggleCapacidad() {
            const tipoSelect = document.getElementById('tipo-select');
            const capacidadGroup = document.getElementById('capacidad-group');
            const capacidadInput = document.getElementById('capacidad-input');
            
            if (tipoSelect.value === 'aula') {
                capacidadGroup.style.opacity = '1';
                capacidadGroup.style.pointerEvents = 'auto';
                capacidadInput.disabled = false;
            } else {
                capacidadGroup.style.opacity = '0.3';
                capacidadGroup.style.pointerEvents = 'none';
                capacidadInput.disabled = true;
                capacidadInput.value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            toggleCapacidad();
        });
    </script>
@endsection
