@extends('layouts.app')
@section('title', 'Crear Rúbrica')
@section('content')
    <div class="page-header"><h1 class="page-title">Crear Rúbrica para: {{ $actividad->titulo }}</h1></div>
    <div class="card" style="max-width: 800px;">
        <form method="POST" action="{{ route('rubricas.store', $actividad) }}" id="rubricaForm">@csrf
            <div class="form-group"><label>Nombre de la rúbrica *</label><input type="text" name="nombre" required placeholder="Ej: Rúbrica de evaluación del proyecto"></div>
            <div class="form-group"><label>Descripción</label><textarea name="descripcion" rows="2"></textarea></div>
            <h3 style="color: #fff; font-size: 1rem; margin: 1.5rem 0 1rem;">Criterios de la rúbrica</h3>
            <div id="criterios-container">
                <div class="criterio-block" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem;">
                    <div class="grid-2">
                        <div class="form-group"><label>Nombre *</label><input type="text" name="criterios[0][nombre]" required placeholder="Ej: Funcionalidad"></div>
                        <div class="form-group"><label>Peso (%) *</label><input type="number" name="criterios[0][peso]" required min="0" max="100" value="100"></div>
                    </div>
                    <div class="form-group"><label>Descripción</label><textarea name="criterios[0][descripcion]" rows="2" placeholder="Describe qué se evalúa..."></textarea></div>
                </div>
            </div>
            <button type="button" onclick="addCriterio()" class="btn btn-sm" style="background: rgba(56,189,248,0.1); color: var(--primary); margin-bottom: 1rem;">+ Añadir Criterio</button>
            @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Crear Rúbrica</button>
                <a href="{{ route('actividades.show', $actividad) }}" class="btn" style="background: rgba(255,255,255,0.1);">Cancelar</a>
            </div>
        </form>
    </div>
    <script>
        let criterioIndex = 1;
        function addCriterio() {
            const container = document.getElementById('criterios-container');
            const block = document.createElement('div');
            block.className = 'criterio-block';
            block.style.cssText = 'background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem;';
            block.innerHTML = `<div class="grid-2"><div class="form-group"><label>Nombre *</label><input type="text" name="criterios[${criterioIndex}][nombre]" required></div><div class="form-group"><label>Peso (%)</label><input type="number" name="criterios[${criterioIndex}][peso]" required min="0" max="100" value="0"></div></div><div class="form-group"><label>Descripción</label><textarea name="criterios[${criterioIndex}][descripcion]" rows="2"></textarea></div><button type="button" onclick="this.parentElement.remove()" class="btn btn-sm btn-danger">Eliminar criterio</button>`;
            container.appendChild(block);
            criterioIndex++;
        }
    </script>
@endsection
