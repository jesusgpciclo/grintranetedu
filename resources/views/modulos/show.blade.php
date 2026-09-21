@extends('layouts.app')
@section('title', $modulo->nombre . ' — Resultados de Aprendizaje')
@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $modulo->codigo }} — {{ $modulo->nombre }}</h1>
        <a href="{{ route('modulos.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">← Volver</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- Info del módulo --}}
    <div class="card">
        <div class="grid-3">
            <div><span class="stat-label">Grupo</span><div style="font-weight: 600;">{{ $modulo->group ? $modulo->group->course . ' - ' . $modulo->group->name : '—' }}</div></div>
            <div><span class="stat-label">Profesores</span><div style="font-weight: 600;">{{ $modulo->profesores->count() > 0 ? collect($modulo->profesores)->map(function($p) { return $p->name . ' ' . $p->last_name; })->join(', ') : '—' }}</div></div>
            <div><span class="stat-label">Horas/Semana</span><div style="font-weight: 600;">{{ $modulo->horas_semanales }}h</div></div>
        </div>
        @if($modulo->descripcion)<p style="color: var(--text-muted); margin-top: 1rem;">{{ $modulo->descripcion }}</p>@endif
    </div>

    {{-- Barra de peso total de RAs --}}
    @php $totalPesoRA = $modulo->resultadosAprendizaje->sum('peso'); @endphp
    <div class="card" style="padding: 1rem 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.9rem; color: var(--text-muted);">Peso total de RAs:</span>
            <span class="badge {{ $totalPesoRA == 100 ? 'badge-success' : 'badge-danger' }}" style="font-size: 1rem; padding: 0.4rem 1rem;">
                {{ $totalPesoRA }}%
            </span>
        </div>
        <div style="margin-top: 0.5rem; background: rgba(255,255,255,0.05); border-radius: 99px; height: 8px; overflow: hidden;">
            <div style="width: {{ min($totalPesoRA, 100) }}%; height: 100%; background: {{ $totalPesoRA == 100 ? 'var(--success)' : 'var(--danger)' }}; border-radius: 99px; transition: width 0.3s;"></div>
        </div>
    </div>

    {{-- Resultados de Aprendizaje (Drag & Drop) --}}
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; color: #fff;">Resultados de Aprendizaje</h2>
            @hasanyrole('admin|directiva')
            <span style="font-size: 0.8rem; color: var(--text-muted);">⇅ Arrastra para reordenar</span>
            @endhasanyrole
        </div>

        <div id="ra-sortable">
            @foreach($modulo->resultadosAprendizaje as $ra)
                <div class="ra-item" data-id="{{ $ra->id }}" style="background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1rem; cursor: grab; transition: transform 0.15s, box-shadow 0.15s;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span class="drag-handle" style="color: var(--text-muted); cursor: grab; font-size: 1.2rem; user-select: none;">⋮⋮</span>
                            <span class="badge badge-info" style="font-size: 0.85rem;">{{ $ra->codigo }}</span>
                            <span class="badge badge-warning" style="margin-left: 0.25rem;">{{ $ra->peso }}%</span>
                        </div>
                        @hasanyrole('admin|directiva')
                        <form action="{{ route('ra.destroy', $ra) }}" method="POST" onsubmit="return confirm('¿Eliminar este RA y sus CE?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">×</button></form>
                        @endhasanyrole
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">{{ $ra->descripcion }}</p>

                    {{-- Criterios de Evaluación (Drag & Drop anidado) --}}
                    @php $totalPesoCE = $ra->criteriosEvaluacion->sum('peso'); @endphp
                    @if($ra->criteriosEvaluacion->count())
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; margin-left: 1rem;">
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Criterios de Evaluación</span>
                            <span class="badge {{ $totalPesoCE == 100 ? 'badge-success' : 'badge-warning' }}" style="font-size: 0.7rem;">CE: {{ $totalPesoCE }}%</span>
                        </div>
                        <div class="ce-sortable" data-ra-id="{{ $ra->id }}" style="margin-left: 1rem; border-left: 2px solid var(--primary); padding-left: 1rem;">
                            @foreach($ra->criteriosEvaluacion as $ce)
                                <div class="ce-item" data-id="{{ $ce->id }}" style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--border); cursor: grab;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="drag-handle" style="color: var(--text-muted); cursor: grab; font-size: 1rem; user-select: none;">⋮⋮</span>
                                        <span class="badge badge-role">{{ $ce->codigo }}</span>
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">{{ Str::limit($ce->descripcion, 80) }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span class="badge badge-warning">{{ $ce->peso }}%</span>
                                        @hasanyrole('admin|directiva')
                                        <form action="{{ route('ce.destroy', $ce) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" style="padding: 0.2rem 0.4rem;">×</button></form>
                                        @endhasanyrole
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Formulario añadir CE --}}
                    @hasanyrole('admin|directiva')
                    <form method="POST" action="{{ route('ce.store', $ra) }}" style="display: flex; gap: 0.5rem; margin-top: 0.75rem; margin-left: 1rem; align-items: end;">
                        @csrf
                        <input type="text" name="codigo" placeholder="CE" required style="width: 80px; padding: 0.4rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.375rem; color: #fff; font-size: 0.85rem;">
                        <input type="text" name="descripcion" placeholder="Descripción del criterio..." required style="flex: 1; padding: 0.4rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.375rem; color: #fff; font-size: 0.85rem;">
                        <input type="number" name="peso" placeholder="%" value="0" min="0" max="100" style="width: 70px; padding: 0.4rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.375rem; color: #fff; font-size: 0.85rem;">
                        <button type="submit" class="btn btn-sm btn-primary">+ CE</button>
                    </form>
                    @endhasanyrole
                </div>
            @endforeach
        </div>

        {{-- Formulario añadir RA --}}
        @hasanyrole('admin|directiva')
        <div style="background: rgba(56,189,248,0.03); border: 2px dashed rgba(56,189,248,0.2); border-radius: 0.75rem; padding: 1.25rem; margin-top: 1rem;">
            <h3 style="font-size: 0.9rem; color: var(--primary); margin-bottom: 0.75rem;">Añadir Resultado de Aprendizaje</h3>
            <form method="POST" action="{{ route('ra.store', $modulo) }}">
                @csrf
                <div style="display: flex; gap: 0.5rem; align-items: end;">
                    <input type="text" name="codigo" placeholder="RA" required style="width: 80px; padding: 0.5rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.375rem; color: #fff;">
                    <input type="text" name="descripcion" placeholder="Descripción del resultado de aprendizaje..." required style="flex: 1; padding: 0.5rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.375rem; color: #fff;">
                    <input type="number" name="peso" placeholder="%" value="0" min="0" max="100" style="width: 70px; padding: 0.5rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.375rem; color: #fff;">
                    <button type="submit" class="btn btn-primary">+ RA</button>
                </div>
            </form>
        </div>
        @endhasanyrole
    </div>

    {{-- SortableJS CDN + Drag & Drop Script --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = '{{ csrf_token() }}';

            // RA Sortable
            const raContainer = document.getElementById('ra-sortable');
            if (raContainer) {
                new Sortable(raContainer, {
                    animation: 200,
                    handle: '.ra-item > div:first-child .drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: function() {
                        const items = raContainer.querySelectorAll('.ra-item');
                        const order = Array.from(items).map((el, index) => ({
                            id: parseInt(el.dataset.id),
                            position: index
                        }));
                        fetch('{{ route("ra.reorder") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ order: order })
                        });
                    }
                });
            }

            // CE Sortable (por cada RA)
            document.querySelectorAll('.ce-sortable').forEach(function(container) {
                new Sortable(container, {
                    animation: 200,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: function() {
                        const items = container.querySelectorAll('.ce-item');
                        const order = Array.from(items).map((el, index) => ({
                            id: parseInt(el.dataset.id),
                            position: index
                        }));
                        fetch('{{ route("ce.reorder") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ order: order })
                        });
                    }
                });
            });
        });
    </script>

    <style>
        .sortable-ghost {
            opacity: 0.3;
        }
        .sortable-chosen {
            box-shadow: 0 8px 25px rgba(56, 189, 248, 0.3);
            transform: scale(1.02);
            border-color: var(--primary) !important;
        }
        .sortable-drag {
            opacity: 0.9;
        }
        .ra-item:hover {
            border-color: rgba(56, 189, 248, 0.3) !important;
        }
        .ce-item:hover {
            background: rgba(255,255,255,0.02);
        }
        .drag-handle:hover {
            color: var(--primary) !important;
        }
    </style>
@endsection
