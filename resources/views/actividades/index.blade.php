@extends('layouts.app')
@section('title', 'Actividades')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Actividades</h1>
        <a href="{{ route('actividades.create') }}" class="btn btn-primary">+ Nueva Actividad</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- Barra de búsqueda y filtros --}}
    <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form action="{{ route('actividades.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
            
            <div style="flex: 2; min-width: 200px;">
                <label>Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título o descripción..." style="width: 100%;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label>Módulo</label>
                <select name="modulo_id" onchange="this.form.submit()" style="width: 100%;">
                    <option value="">Todos</option>
                    @foreach($modulos as $m)
                        <option value="{{ $m->id }}" {{ request('modulo_id') == $m->id ? 'selected' : '' }}>{{ $m->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if(request()->hasAny(['search', 'modulo_id']))
                <a href="{{ route('actividades.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="card table-container">
        <table>
            <thead>
                <tr>
                    <th><x-sort-header column="titulo" label="Título" route="actividades.index" /></th>
                    <th>Módulo</th>
                    <th>Criterios</th>
                    <th><x-sort-header column="tipo" label="Tipo" route="actividades.index" /></th>
                    <th><x-sort-header column="fecha_entrega" label="Entrega" route="actividades.index" /></th>
                    <th><x-sort-header column="peso" label="Peso" route="actividades.index" /></th>
                    <th>Notas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($actividades as $act)
                    <tr>
                        <td style="font-weight: 600;">{{ $act->titulo }}</td>
                        <td>{{ $act->modulo->nombre ?? '—' }}</td>
                        <td>
                            @if($act->criteriosEvaluacion->count())
                                @foreach($act->criteriosEvaluacion as $ce)
                                    <span class="badge badge-role" style="font-size: 0.7rem; margin: 0.1rem;">{{ $ce->codigo }}</span>
                                @endforeach
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td><span class="badge badge-info">{{ ucfirst($act->tipo ?? 'tarea') }}</span></td>
                        <td>{{ $act->fecha_entrega ? $act->fecha_entrega->format('d/m/Y') : '—' }}</td>
                        <td><span class="badge badge-warning">{{ $act->peso }}%</span></td>
                        <td><span class="badge badge-role">{{ $act->notas_count }}</span></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('actividades.show', $act) }}" class="btn btn-sm" style="background: rgba(56,189,248,0.1); color: var(--primary);">Ver</a>
                                <a href="{{ route('actividades.edit', $act) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.1);">Editar</a>
                                <form action="{{ route('actividades.destroy', $act) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">×</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay actividades.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $actividades->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
