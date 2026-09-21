@extends('layouts.app')

@section('title', 'Gestión de Recursos')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Inventario de Recursos</h1>
            <div style="color: var(--text-muted);">
                Gestión completa de dispositivos y equipamiento.
            </div>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('tipo-recursos.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: #fff;">
                Ver Tipos de Recurso
            </a>
            <a href="{{ route('recursos.create') }}" class="btn btn-primary">
                + Nuevo Recurso
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <form action="{{ route('recursos.index') }}" method="GET" style="display: flex; gap: 0.5rem; width: 100%; max-width: 400px;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por nombre, marca, modelo, ubicación..." style="flex-grow: 1; padding: 0.5rem; border-radius: 0.25rem; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: #fff;">
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Buscar</button>
            @if($search)
                <a href="{{ route('recursos.index') }}" class="btn" style="background: rgba(255,255,255,0.1); color: #fff; padding: 0.5rem 1rem;">Limpiar</a>
            @endif
        </form>
    </div>

    @php
        $sortFn = function($column, $label) use ($sort, $direction) {
            $newDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
            $icon = '';
            if ($sort === $column) {
                $icon = $direction === 'asc' ? ' &uarr;' : ' &darr;';
            }
            $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $newDirection]);
            return '<a href="'.$url.'" style="color: inherit; text-decoration: none;">'.$label.$icon.'</a>';
        };
    @endphp

    <div class="card">
        <div class="table-container">
            <table class="smart-table">
                <thead>
                    <tr>
                        <th>{!! $sortFn('nombre', 'Nombre') !!}</th>
                        <th>Tipo</th>
                        <th>Marca/Modelo</th>
                        <th>Nº Serie</th>
                        <th>{!! $sortFn('estado', 'Estado') !!}</th>
                        <th>{!! $sortFn('ubicacion', 'Ubicación') !!}</th>
                        <th>Responsable</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recursos as $recurso)
                        <tr>
                            <td style="font-weight: 600; color: #fff;">{{ $recurso->nombre }}</td>
                            <td><span class="badge" style="background: rgba(56, 189, 248, 0.1); color: var(--primary);">{{ $recurso->tipo->nombre }}</span></td>
                            <td style="font-size: 0.85rem;">
                                {{ $recurso->marca ?? '-' }} {{ $recurso->modelo ? '/ ' . $recurso->modelo : '' }}
                            </td>
                            <td style="font-family: monospace; font-size: 0.85rem;">{{ $recurso->numero_serie ?? '-' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'disponible' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'color' => 'var(--success)'],
                                        'en reparación' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b'],
                                        'dado de baja' => ['bg' => 'rgba(239, 68, 68, 0.1)', 'color' => '#ef4444'],
                                    ];
                                    $style = $statusColors[$recurso->estado] ?? $statusColors['disponible'];
                                @endphp
                                <span class="badge" style="background: {{ $style['bg'] }}; color: {{ $style['color'] }};">
                                    {{ ucfirst($recurso->estado) }}
                                </span>
                            </td>
                            <td style="font-size: 0.85rem;">{{ $recurso->ubicacion ?? '-' }}</td>
                            <td style="font-size: 0.85rem;">{{ $recurso->responsable->name ?? '-' }}</td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ route('incidencias.create', ['recurso_id' => $recurso->id]) }}" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                        Incidencia
                                    </a>
                                    <a href="{{ route('recursos.edit', $recurso) }}" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; background: rgba(56, 189, 248, 0.1); color: var(--primary);">
                                        Editar
                                    </a>
                                    <form action="{{ route('recursos.destroy', $recurso) }}" method="POST" onsubmit="return confirm('¿Estás seguro?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; cursor: pointer;">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                No hay recursos registrados aún. <a href="{{ route('recursos.create') }}" style="color: var(--primary);">Crea el primero aquí</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem;">
            {{ $recursos->links() }}
        </div>
    </div>
@endsection
