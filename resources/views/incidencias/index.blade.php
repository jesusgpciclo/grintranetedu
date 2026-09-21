@extends('layouts.app')

@section('title', 'Gestión de Incidencias')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestión de Incidencias</h1>
            <div style="color: var(--text-muted);">
                Reporte y seguimiento de problemas técnicos o incidencias.
            </div>
        </div>
        <div>
            <a href="{{ route('incidencias.create') }}" class="btn btn-primary">
                + Reportar Incidencia
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <form action="{{ route('incidencias.index') }}" method="GET" style="display: flex; gap: 0.5rem; width: 100%; max-width: 400px;">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por título, descripción, usuario..." style="flex-grow: 1; padding: 0.5rem; border-radius: 0.25rem; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: #fff;">
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Buscar</button>
            @if($search)
                <a href="{{ route('incidencias.index') }}" class="btn" style="background: rgba(255,255,255,0.1); color: #fff; padding: 0.5rem 1rem;">Limpiar</a>
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
                        <th>{!! $sortFn('titulo', 'Título') !!}</th>
                        <th>{!! $sortFn('fecha', 'Fecha') !!}</th>
                        <th>{!! $sortFn('prioridad', 'Prioridad') !!}</th>
                        <th>{!! $sortFn('estado', 'Estado') !!}</th>
                        <th>Recurso</th>
                        <th>Reportado por</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidencias as $incidencia)
                        <tr>
                            <td style="font-weight: 600; color: #fff;">{{ $incidencia->titulo }}</td>
                            <td>{{ \Carbon\Carbon::parse($incidencia->fecha)->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $prioColors = [
                                        'baja' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'color' => 'var(--success)'],
                                        'media' => ['bg' => 'rgba(56, 189, 248, 0.1)', 'color' => 'var(--primary)'],
                                        'alta' => ['bg' => 'rgba(239, 68, 68, 0.1)', 'color' => '#ef4444'],
                                    ];
                                    $prioStyle = $prioColors[$incidencia->prioridad] ?? $prioColors['media'];
                                @endphp
                                <span class="badge" style="background: {{ $prioStyle['bg'] }}; color: {{ $prioStyle['color'] }};">
                                    {{ ucfirst($incidencia->prioridad) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'abierta' => ['bg' => 'rgba(239, 68, 68, 0.1)', 'color' => '#ef4444'],
                                        'en curso' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b'],
                                        'resuelta' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'color' => 'var(--success)'],
                                        'cerrada' => ['bg' => 'rgba(255, 255, 255, 0.05)', 'color' => '#fff'],
                                    ];
                                    $statusStyle = $statusColors[$incidencia->estado] ?? $statusColors['abierta'];
                                @endphp
                                <span class="badge" style="background: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['color'] }};">
                                    {{ ucfirst($incidencia->estado) }}
                                </span>
                            </td>
                            <td>
                                @if($incidencia->recurso)
                                    <span style="font-size: 0.85rem;">{{ $incidencia->recurso->nombre }}</span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">N/A</span>
                                @endif
                            </td>
                            <td style="font-size: 0.85rem;">{{ $incidencia->user->name }}</td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ route('incidencias.edit', $incidencia) }}" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; background: rgba(56, 189, 248, 0.1); color: var(--primary);">
                                        Editar
                                    </a>
                                    <form action="{{ route('incidencias.destroy', $incidencia) }}" method="POST" onsubmit="return confirm('¿Estás seguro?')">
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
                            <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                No hay incidencias registradas aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem;">
            {{ $incidencias->links() }}
        </div>
    </div>
@endsection
