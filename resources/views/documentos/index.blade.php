@extends('layouts.app')
@section('title', 'Gestor Documental')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Gestor Documental</h1>
        <a href="{{ route('documentos.create') }}" class="btn btn-primary">+ Nuevo Documento</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- Barra de búsqueda y filtros --}}
    <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form action="{{ route('documentos.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
            
            <div style="flex: 2; min-width: 200px;">
                <label>Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título o descripción..." style="width: 100%;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label>Categoría</label>
                <select name="categoria" onchange="this.form.submit()" style="width: 100%;">
                    <option value="">Todas</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c }}" {{ request('categoria') == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label>Departamento</label>
                <select name="departamento" onchange="this.form.submit()" style="width: 100%;">
                    <option value="">Todos</option>
                    @foreach($departamentos as $d)
                        <option value="{{ $d }}" {{ request('departamento') == $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if(request()->hasAny(['search', 'categoria', 'departamento']))
                <a href="{{ route('documentos.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="card table-container">
        <table>
            <thead>
                <tr>
                    <th><x-sort-header column="titulo" label="Título" route="documentos.index" /></th>
                    <th><x-sort-header column="categoria" label="Categoría" route="documentos.index" /></th>
                    <th><x-sort-header column="departamento" label="Departamento" route="documentos.index" /></th>
                    <th>Enlace</th>
                    <th>Autor</th>
                    <th><x-sort-header column="created_at" label="Fecha" route="documentos.index" /></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documentos as $doc)
                    <tr>
                        <td style="font-weight: 600;">{{ $doc->titulo }}</td>
                        <td><span class="badge badge-info">{{ ucfirst($doc->categoria) }}</span></td>
                        <td>{{ $doc->departamento ?? '—' }}</td>
                        <td>
                            @if($doc->url)<a href="{{ $doc->url }}" target="_blank" style="color: var(--primary);">🔗 Enlace</a>
                            @elseif($doc->archivo)<a href="{{ asset('storage/' . $doc->archivo) }}" target="_blank" style="color: var(--primary);">📎 Archivo</a>
                            @else — @endif
                        </td>
                        <td>{{ $doc->autor->name ?? '—' }}</td>
                        <td style="color: var(--text-muted);">{{ $doc->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('documentos.edit', $doc) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.1);">Editar</a>
                                <form action="{{ route('documentos.destroy', $doc) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">×</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay documentos.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $documentos->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
