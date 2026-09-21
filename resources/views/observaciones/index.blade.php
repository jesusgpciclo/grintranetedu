@extends('layouts.app')
@section('title', 'Observaciones de Alumnos')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Observaciones de Alumnos</h1>
        <a href="{{ route('observaciones.create') }}" class="btn btn-primary">+ Nueva Observación</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- Barra de búsqueda y filtros --}}
    <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form action="{{ route('observaciones.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
            
            <div style="flex: 2; min-width: 200px;">
                <label>Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por descripción..." style="width: 100%;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label>Tipo</label>
                <select name="tipo" onchange="this.form.submit()" style="width: 100%;">
                    <option value="">Todos</option>
                    @foreach(['positiva','negativa','informativa','seguimiento'] as $t)
                        <option value="{{ $t }}" {{ request('tipo') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if(request()->hasAny(['search', 'tipo']))
                <a href="{{ route('observaciones.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="card table-container">
        <table>
            <thead>
                <tr>
                    <th><x-sort-header column="fecha" label="Fecha" route="observaciones.index" /></th>
                    <th>Alumno</th>
                    <th><x-sort-header column="tipo" label="Tipo" route="observaciones.index" /></th>
                    <th>Descripción</th>
                    <th>Profesor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($observaciones as $obs)
                    <tr>
                        <td style="font-weight: 500; color: var(--primary);">{{ $obs->fecha->format('d/m/Y') }}</td>
                        <td style="font-weight: 600;">{{ $obs->alumno->last_name ?? '' }}, {{ $obs->alumno->name ?? '' }}</td>
                        <td>
                            @if($obs->tipo == 'positiva')<span class="badge badge-success">Positiva</span>
                            @elseif($obs->tipo == 'negativa')<span class="badge badge-danger">Negativa</span>
                            @elseif($obs->tipo == 'seguimiento')<span class="badge badge-warning">Seguimiento</span>
                            @else<span class="badge badge-info">Informativa</span>@endif
                        </td>
                        <td style="max-width: 300px;">{{ Str::limit($obs->descripcion, 80) }}</td>
                        <td>{{ $obs->profesor->name ?? '' }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <form action="{{ route('observaciones.destroy', $obs) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">×</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay observaciones.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $observaciones->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
