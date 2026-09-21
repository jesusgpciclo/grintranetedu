@extends('layouts.app')

@section('title', 'Gestión de Zonas')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Zonas</h1>
        <a href="{{ route('zonas.create') }}" class="btn btn-primary">Nueva Zona</a>
    </div>

    @if(session('success'))
        <div class="alert"
            style="background: rgba(34, 197, 94, 0.1); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.2);">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search and Filter Bar -->
    <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form action="{{ route('zonas.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center;">
            <!-- Preserve sort params -->
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif

            <div style="flex: 2;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o planta..."
                    style="width: 100%;">
            </div>

            <button type="submit" class="btn btn-primary">Buscar</button>
            @if(request('search'))
                <a href="{{ route('zonas.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="card table-container">
        <table class="smart-table">
            <thead>
                <tr>
                    <th><x-sort-header column="nombre" label="Nombre" route="zonas.index" /></th>
                    <th><x-sort-header column="planta" label="Planta" route="zonas.index" /></th>
                    <th><x-sort-header column="created_at" label="Fecha Creación" route="zonas.index" /></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($zonas as $zona)
                    <tr>
                        <td style="font-weight: 500;">{{ $zona->nombre }}</td>
                        <td style="color: var(--text-muted);">{{ $zona->planta }}</td>
                        <td style="color: var(--text-muted);">{{ $zona->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('zonas.edit', $zona) }}" class="btn"
                                    style="background: rgba(255, 255, 255, 0.1); padding: 0.4rem 0.8rem; font-size: 0.8rem;">Editar</a>
                                <form action="{{ route('zonas.destroy', $zona) }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $zonas->appends(request()->query())->links() }}
        </div>
    </div>
@endsection