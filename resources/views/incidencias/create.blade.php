@extends('layouts.app')

@section('title', 'Reportar Incidencia')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Reportar Incidencia</h1>
            <div style="color: var(--text-muted);">
                Describe el problema para que podamos solucionarlo.
            </div>
        </div>
        <a href="{{ route('incidencias.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: #fff;">
            &larr; Volver
        </a>
    </div>

    <div class="card">
        <form action="{{ route('incidencias.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <!-- Título -->
                <div style="grid-column: span 2;">
                    <label for="titulo" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Título de la Incidencia</label>
                    <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" 
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;" 
                        placeholder="Ej: La impresora no enciende" required>
                    @error('titulo') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo de Recurso -->
                <div>
                    <label for="tipo_recurso" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Tipo de Recurso</label>
                    <select id="tipo_recurso" style="width: 100%; padding: 0.75rem; background: rgba(30, 41, 59, 1); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                        <option value="">Selecciona un tipo (opcional)</option>
                        @foreach($tiposRecurso as $tipo)
                            <option value="{{ $tipo->id }}" {{ $selectedTipoId == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Recurso -->
                <div>
                    <label for="recurso_id" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Recurso Asociado</label>
                    <select name="recurso_id" id="recurso_id" 
                        style="width: 100%; padding: 0.75rem; background: rgba(30, 41, 59, 1); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                        <option value="">Selecciona primero un tipo</option>
                    </select>
                    @error('recurso_id') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Fecha -->
                <div>
                    <label for="fecha" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Fecha</label>
                    <input type="date" name="fecha" id="fecha" value="{{ old('fecha', date('Y-m-d')) }}" 
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;" required>
                    @error('fecha') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Prioridad -->
                <div>
                    <label for="prioridad" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Prioridad</label>
                    <select name="prioridad" id="prioridad" 
                        style="width: 100%; padding: 0.75rem; background: rgba(30, 41, 59, 1); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;" required>
                        <option value="baja" {{ old('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                        <option value="media" {{ old('prioridad', 'media') == 'media' ? 'selected' : '' }}>Media</option>
                        <option value="alta" {{ old('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                    </select>
                </div>

                <!-- Descripción -->
                <div style="grid-column: span 2;">
                    <label for="descripcion" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Descripción Detallada</label>
                    <textarea name="descripcion" id="descripcion" rows="4" 
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;" 
                        placeholder="Explica qué sucede con el mayor detalle posible..." required>{{ old('descripcion') }}</textarea>
                    @error('descripcion') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                    Enviar Incidencia
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tiposRecurso = @json($tiposRecurso);
        const tipoSelect = document.getElementById('tipo_recurso');
        const recursoSelect = document.getElementById('recurso_id');
        const selectedRecursoId = "{{ old('recurso_id', $selectedRecursoId ?? '') }}";

        function updateRecursos() {
            const tipoId = tipoSelect.value;
            recursoSelect.innerHTML = '<option value="">Selecciona un recurso (opcional)</option>';
            
            if (tipoId) {
                const tipo = tiposRecurso.find(t => t.id == tipoId);
                if (tipo && tipo.recursos) {
                    tipo.recursos.forEach(recurso => {
                        const option = document.createElement('option');
                        option.value = recurso.id;
                        option.textContent = `${recurso.nombre} (${recurso.ubicacion})`;
                        if (recurso.id == selectedRecursoId) {
                            option.selected = true;
                        }
                        recursoSelect.appendChild(option);
                    });
                }
            }
        }

        tipoSelect.addEventListener('change', updateRecursos);
        updateRecursos();
    });
</script>
@endpush
