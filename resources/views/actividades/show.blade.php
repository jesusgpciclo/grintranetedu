@extends('layouts.app')
@section('title', $actividad->titulo)
@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $actividad->titulo }}</h1>
        <a href="{{ route('actividades.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">← Volver</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card">
        <div class="grid-4">
            <div><span class="stat-label">Módulo</span><div style="font-weight: 600;">{{ $actividad->modulo->nombre ?? '—' }}</div></div>
            <div><span class="stat-label">Tipo</span><div><span class="badge badge-info">{{ ucfirst($actividad->tipo ?? 'tarea') }}</span></div></div>
            <div><span class="stat-label">Entrega</span><div style="font-weight: 600;">{{ $actividad->fecha_entrega ? $actividad->fecha_entrega->format('d/m/Y') : '—' }}</div></div>
            <div><span class="stat-label">Peso</span><div><span class="badge badge-warning">{{ $actividad->peso }}%</span></div></div>
        </div>
        @if($actividad->descripcion)<p style="color: var(--text-muted); margin-top: 1rem;">{{ $actividad->descripcion }}</p>@endif
    </div>

    {{-- Criterios de Evaluación vinculados --}}
    <div class="card">
        <h2 style="font-size: 1.15rem; color: #fff; margin-bottom: 1rem;">Criterios de Evaluación vinculados</h2>
        @if($actividad->criteriosEvaluacion->count())
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                @foreach($actividad->criteriosEvaluacion as $ce)
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.75rem 1rem;">
                        <span class="badge badge-info" style="font-size: 0.7rem;">{{ $ce->resultadoAprendizaje->codigo ?? '' }}</span>
                        <span class="badge badge-role" style="font-size: 0.75rem;">{{ $ce->codigo }}</span>
                        <span style="color: var(--text-muted); font-size: 0.85rem; margin-left: 0.25rem;">{{ Str::limit($ce->descripcion, 50) }}</span>
                        <span class="badge badge-warning" style="font-size: 0.65rem; margin-left: 0.25rem;">{{ $ce->peso }}%</span>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color: var(--text-muted);">No hay criterios de evaluación asignados.</p>
        @endif
    </div>

    {{-- Rúbrica --}}
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 style="font-size: 1.15rem; color: #fff;">Rúbrica</h2>
            @if(!$actividad->rubrica)
                <a href="{{ route('rubricas.create', $actividad) }}" class="btn btn-sm btn-primary">+ Crear Rúbrica</a>
            @endif
        </div>
        @if($actividad->rubrica)
            <p style="color: var(--text-muted); margin-bottom: 1rem;">{{ $actividad->rubrica->nombre }}</p>
            <div class="table-container">
                <table>
                    <thead><tr><th>Criterio</th><th>Peso</th><th>Niveles</th></tr></thead>
                    <tbody>
                        @foreach($actividad->rubrica->criterios as $cr)
                            <tr>
                                <td style="font-weight: 600;">{{ $cr->nombre }}</td>
                                <td><span class="badge badge-warning">{{ $cr->peso }}%</span></td>
                                <td>
                                    @if($cr->niveles)
                                        @foreach($cr->niveles as $nivel)
                                            <span class="badge badge-role" style="margin: 0.1rem;">{{ $nivel['nombre'] ?? '' }} ({{ $nivel['puntos'] ?? 0 }}pts)</span>
                                        @endforeach
                                    @else — @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <form action="{{ route('rubricas.destroy', $actividad->rubrica) }}" method="POST" style="margin-top: 0.75rem;" onsubmit="return confirm('¿Eliminar rúbrica?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Eliminar Rúbrica</button></form>
        @else
            <p style="color: var(--text-muted);">No hay rúbrica asociada a esta actividad.</p>
        @endif
    </div>
@endsection
