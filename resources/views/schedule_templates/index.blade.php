@extends('layouts.app')

@section('title', 'Plantillas de Horario')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Plantillas de Horario</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('schedule-templates.create') }}" class="btn btn-primary">+ Crear Nueva Plantilla</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert"
            style="background: rgba(34, 197, 94, 0.1); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.2);">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search and Filter Bar -->
    <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form action="{{ route('schedule-templates.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center;">
            <!-- Preserve sort params -->
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif

            <div style="flex: 2;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar plantilla por nombre..."
                    style="width: 100%;" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Buscar</button>
            @if(request('search'))
                <a href="{{ route('schedule-templates.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="card table-container">
        <table class="smart-table">
            <thead>
                <tr>
                    <th><x-sort-header column="name" label="Nombre" route="schedule-templates.index" /></th>
                    <th>Días Activos</th>
                    <th><x-sort-header column="created_at" label="Fecha Creación" route="schedule-templates.index" /></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td>
                            <strong style="color: var(--primary);">{{ $template->name }}</strong>
                            @if($template->description)
                                <div style="font-size: 0.85em; color: var(--text-muted); margin-top: 4px;">{{ $template->description }}</div>
                            @endif
                        </td>
                        <td style="color: var(--text-color);">
                            @if($template->active_days)
                                @php
                                    $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                                    $active = array_map(function($dayIndex) use ($days) {
                                        return $days[$dayIndex - 1] ?? '';
                                    }, $template->active_days);
                                @endphp
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    @foreach($active as $dayName)
                                        <span class="badge badge-role" style="font-size: 0.75rem;">{{ $dayName }}</span>
                                    @endforeach
                                </div>
                            @else
                                <em style="color: var(--text-muted);">No definidos</em>
                            @endif
                        </td>
                        <td style="color: var(--text-muted);">{{ $template->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('schedule-templates.preview', $template->id) }}" class="btn"
                                    style="background: rgba(56, 189, 248, 0.1); color: var(--primary); padding: 0.4rem 0.8rem; font-size: 0.8rem;" target="_blank">Vista Previa</a>
                                <a href="{{ route('schedule-templates.edit', $template->id) }}" class="btn"
                                    style="background: rgba(255, 255, 255, 0.1); padding: 0.4rem 0.8rem; font-size: 0.8rem;">Editar</a>
                                <form action="{{ route('schedule-templates.copy', $template->id) }}" method="POST"
                                    style="display: inline-block;">
                                    @csrf
                                    <button type="submit" class="btn"
                                        style="background: rgba(255, 255, 255, 0.1); padding: 0.4rem 0.8rem; font-size: 0.8rem;">Copiar</button>
                                </form>
                                <form action="{{ route('schedule-templates.destroy', $template->id) }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">No se han encontrado plantillas de horario.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($templates->hasPages())
            <div style="margin-top: 1rem; padding: 1rem;">
                {{ $templates->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection