@extends('layouts.app')
@section('title', 'Módulos Formativos')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Módulos Formativos</h1>
        @hasanyrole('admin|directiva')
        <a href="{{ route('modulos.create') }}" class="btn btn-primary">+ Nuevo Módulo</a>
        @endhasanyrole
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- Barra de búsqueda --}}
    <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form action="{{ route('modulos.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
            <div style="flex: 2; min-width: 200px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por código o nombre..." style="width: 100%;">
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if(request()->hasAny(['search']))
                <a href="{{ route('modulos.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="card table-container">
        <table class="smart-table">
            <thead>
                <tr>
                    <th><x-sort-header column="codigo" label="Código" route="modulos.index" /></th>
                    <th><x-sort-header column="nombre" label="Nombre" route="modulos.index" /></th>
                    <th>Grupo</th>
                    <th>Profesores</th>
                    <th><x-sort-header column="horas_semanales" label="Horas/Sem" route="modulos.index" /></th>
                    <th>RAs</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($modulos as $modulo)
                    <tr>
                        <td><span class="badge badge-info">{{ $modulo->codigo }}</span></td>
                        <td style="font-weight: 600;">{{ $modulo->nombre }}</td>
                        <td>{{ $modulo->group ? $modulo->group->course . ' - ' . $modulo->group->name : '—' }}</td>
                        <td>{{ $modulo->profesores->count() > 0 ? collect($modulo->profesores)->map(function($p) { return $p->name . ' ' . $p->last_name; })->join(', ') : '—' }}</td>
                        <td>{{ $modulo->horas_semanales }}h</td>
                        <td><span class="badge badge-role">{{ $modulo->resultados_aprendizaje_count }}</span></td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('modulos.show', $modulo) }}" class="btn btn-sm" style="background: rgba(56,189,248,0.1); color: var(--primary);">RA</a>
                                @hasanyrole('admin|directiva')
                                <a href="{{ route('modulos.edit', $modulo) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.1);">Editar</a>
                                <form action="{{ route('modulos.destroy', $modulo) }}" method="POST" onsubmit="return confirm('¿Eliminar módulo?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Eliminar</button></form>
                                @endhasanyrole
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay módulos.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $modulos->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
