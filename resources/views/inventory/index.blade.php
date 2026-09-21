@extends('layouts.app')

@section('title', 'Inventario - Gestor de Usuarios')

@section('content')
<div class="page-header">
    <h1 class="page-title">Gestión de Inventario</h1>
    <p class="text-muted">Administra los materiales y equipos del centro.</p>
</div>

<!-- Barra de Filtros -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(30, 41, 59, 0.5);">
    <form action="{{ route('inventory.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.75rem; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; display: block;">Buscar:</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Descripción..." style="width: 100%; padding: 0.4rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.4rem; color: #fff; font-size: 0.85rem;">
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; display: block;">Tipo:</label>
            <select name="tipo" style="width: 100%; padding: 0.4rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.4rem; color: #fff; font-size: 0.85rem;">
                <option value="">Todos</option>
                @foreach($tipos as $t) <option value="{{ $t }}" {{ request('tipo') == $t ? 'selected' : '' }}>{{ $t }}</option> @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; display: block;">Estado:</label>
            <select name="estado" style="width: 100%; padding: 0.4rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.4rem; color: #fff; font-size: 0.85rem;">
                <option value="">Todos</option>
                @foreach($estados as $e) <option value="{{ $e }}" {{ request('estado') == $e ? 'selected' : '' }}>{{ $e }}</option> @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; display: block;">Edificio:</label>
            <select name="edificio" style="width: 100%; padding: 0.4rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.4rem; color: #fff; font-size: 0.85rem;">
                <option value="">Todos</option>
                @foreach($edificios as $ed) <option value="{{ $ed }}" {{ request('edificio') == $ed ? 'selected' : '' }}>{{ $ed }}</option> @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem; display: block;">Planta:</label>
            <select name="planta" style="width: 100%; padding: 0.4rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.4rem; color: #fff; font-size: 0.85rem;">
                <option value="">Todas</option>
                @foreach($plantas as $p) <option value="{{ $p }}" {{ request('planta') == $p ? 'selected' : '' }}>{{ $p }}</option> @endforeach
            </select>
        </div>

        <div style="display: flex; gap: 0.4rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Filtrar</button>
            <a href="{{ route('inventory.index') }}" class="btn" style="padding: 0.4rem 0.8rem; background: rgba(255,255,255,0.05); color: #fff; font-size: 0.85rem; text-decoration: none;">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;">Materiales Inventariados</h2>
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">
            + Nuevo Material
        </a>
    </div>

    @if(session('success'))
        <div class="alert" style="background: rgba(34, 197, 94, 0.1); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.2); margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-container">
        <table class="smart-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo / Subtipo</th>
                    <th>Descripción</th>
                    <th>Ubicación</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td style="font-size: 0.85rem;">{{ $item->id }}</td>
                    <td>
                        <div style="font-weight: 600; font-size: 0.95rem;">{{ $item->tipo }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $item->subtipo }}</div>
                    </td>
                    <td style="font-size: 0.9rem;">{{ Str::limit($item->descripcion, 40) }}</td>
                    <td style="font-size: 0.9rem;">
                        <div style="font-weight: 500;">{{ $item->localizacion }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $item->edificio }} - {{ $item->planta }}</div>
                    </td>
                    <td>
                        <span class="badge" style="background: {{ 
                            $item->estado == 'En uso' ? 'rgba(34, 197, 94, 0.1)' : 
                            ($item->estado == 'Pendiente de retirar por APAE' ? 'rgba(239, 68, 68, 0.1)' : 
                            ($item->estado == 'No disponible' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(255, 255, 255, 0.05)')) 
                        }}; color: {{ 
                            $item->estado == 'En uso' ? 'var(--success)' : 
                            ($item->estado == 'Pendiente de retirar por APAE' ? 'var(--danger)' : 
                            ($item->estado == 'No disponible' ? '#f59e0b' : '#fff')) 
                        }};">
                            {{ $item->estado }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <a href="{{ route('inventory.edit', $item->id) }}" style="color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.85rem;">Editar</a>
                            
                            <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este material?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: var(--danger); font-weight: 600; cursor: pointer; font-size: 0.85rem; padding: 0;">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        No hay materiales que coincidan con los filtros.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1rem;">
        {{ $items->appends(request()->query())->links() }}
    </div>
</div>
@endsection
