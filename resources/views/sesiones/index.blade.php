@extends('layouts.app')
@section('title', 'Sesiones de Clase')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Sesiones de Clase</h1>
        <a href="{{ route('sesiones.create') }}" class="btn btn-primary">+ Nueva Sesión</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- Barra de búsqueda y filtros --}}
    <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form action="{{ route('sesiones.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
            
            <div style="flex: 2; min-width: 200px;">
                <label>Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por contenidos..." style="width: 100%;">
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

            <div style="flex: 1; min-width: 150px;">
                <label>Grupo</label>
                <select name="group_id" onchange="this.form.submit()" style="width: 100%;">
                    <option value="">Todos</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ request('group_id') == $g->id ? 'selected' : '' }}>{{ $g->course }} - {{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if(request()->hasAny(['search', 'modulo_id', 'group_id']))
                <a href="{{ route('sesiones.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="card table-container">
        <table>
            <thead>
                <tr>
                    <th><x-sort-header column="fecha" label="Fecha" route="sesiones.index" /></th>
                    <th>Módulo</th>
                    <th>Grupo</th>
                    <th>Contenidos</th>
                    <th>Asistencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sesiones as $sesion)
                    <tr>
                        <td style="font-weight: 600; color: var(--primary);">{{ $sesion->fecha->format('d/m/Y') }}</td>
                        <td>{{ $sesion->modulo->nombre ?? '—' }}</td>
                        <td>{{ $sesion->group->name ?? '—' }}</td>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Str::limit($sesion->contenidos, 60) }}</td>
                        <td><span class="badge badge-role">{{ $sesion->asistencias_count }}</span></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('sesiones.show', $sesion) }}" class="btn btn-sm" style="background: rgba(56,189,248,0.1); color: var(--primary);">Ver</a>
                                <a href="{{ route('sesiones.edit', $sesion) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.1);">Editar</a>
                                <form action="{{ route('sesiones.destroy', $sesion) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">×</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay sesiones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $sesiones->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
