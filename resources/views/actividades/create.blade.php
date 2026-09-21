@extends('layouts.app')
@section('title', 'Nueva Actividad')
@section('content')
    <div class="page-header"><h1 class="page-title">Nueva Actividad</h1></div>
    <div class="card" style="max-width: 750px;">
        <form method="POST" action="{{ route('actividades.store') }}">@csrf
            <div class="form-group"><label>Título *</label><input type="text" name="titulo" value="{{ old('titulo') }}" required></div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Módulo *</label>
                    <select name="modulo_id" id="modulo_select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($modulos as $m)
                            <option value="{{ $m->id }}" {{ old('modulo_id', request('modulo_id')) == $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Tipo</label><select name="tipo"><option value="tarea">Tarea</option><option value="examen">Examen</option><option value="proyecto">Proyecto</option><option value="practica">Práctica</option></select></div>
            </div>
            <div class="grid-2">
                <div class="form-group"><label>Fecha de entrega</label><input type="date" name="fecha_entrega" value="{{ old('fecha_entrega') }}"></div>
                <div class="form-group"><label>Peso (%) dentro del CE</label><input type="number" name="peso" value="{{ old('peso', 0) }}" min="0" max="100" step="0.01"></div>
            </div>

            {{-- Criterios de Evaluación (multi-select) --}}
            @if($criterios->isNotEmpty())
            <div class="form-group">
                <label>Criterios de Evaluación vinculados</label>
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.5rem; padding: 1rem; max-height: 250px; overflow-y: auto;">
                    @foreach($criterios as $ce)
                        <div class="form-check" style="padding: 0.4rem 0; border-bottom: 1px solid var(--border);">
                            <input type="checkbox" name="criterios_ids[]" value="{{ $ce->id }}" id="ce_{{ $ce->id }}"
                                {{ in_array($ce->id, old('criterios_ids', [])) ? 'checked' : '' }}>
                            <label for="ce_{{ $ce->id }}" style="color: #fff; font-size: 0.9rem; cursor: pointer;">
                                <span class="badge badge-info" style="font-size: 0.7rem;">{{ $ce->resultadoAprendizaje->codigo ?? '' }}</span>
                                <span class="badge badge-role" style="font-size: 0.7rem;">{{ $ce->codigo }}</span>
                                {{ Str::limit($ce->descripcion, 60) }}
                                <span class="badge badge-warning" style="font-size: 0.65rem;">{{ $ce->peso }}%</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <small style="color: var(--text-muted);">Selecciona los criterios que esta actividad evalúa. Se pueden marcar varios.</small>
            </div>
            @else
                @if(request('modulo_id'))
                    <div class="alert alert-info">Este módulo no tiene criterios de evaluación configurados. <a href="{{ route('modulos.show', request('modulo_id')) }}" style="color: var(--primary); text-decoration: underline;">Configúralos aquí</a>.</div>
                @else
                    <div class="alert alert-info">Selecciona un módulo y pulsa "Cargar CEs" para ver los criterios disponibles.</div>
                @endif
            @endif

            <div class="form-group"><label>Descripción</label><textarea name="descripcion" rows="3">{{ old('descripcion') }}</textarea></div>
            <div class="form-check"><input type="checkbox" name="es_evaluable" id="es_evaluable" checked><label for="es_evaluable" style="color: #fff;">Es evaluable</label></div>
            @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Crear Actividad</button>
                <a href="{{ route('actividades.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('modulo_select')?.addEventListener('change', function() {
            if (this.value) {
                window.location.href = '{{ route("actividades.create") }}?modulo_id=' + this.value;
            }
        });
    </script>
@endsection
