@extends('layouts.app')

@section('title', 'Cursos Escolares')

@section('content')
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="page-title">Gestión de Cursos Escolares</h1>
        <a href="{{ route('school-years.create') }}" class="btn btn-primary">Nuevo Curso Escolar</a>
    </div>

    @if(session('success'))
        <div class="alert" style="background: rgba(34, 197, 94, 0.1); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.2);">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Toolbar for Filters, Search & Exports -->
    <div class="smart-table-toolbar" style="margin-bottom: 1.5rem;">
        <form action="{{ route('school-years.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 1rem; width: 100%; justify-content: space-between; align-items: center;">
            
            <div class="smart-table-search-group" style="display: flex; gap: 0.5rem; align-items: center; flex: 1; min-width: 250px;">
                <input type="text" name="search" class="smart-table-search-input" value="{{ $search }}" placeholder="Buscar curso (ej: 2026)...">
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <!-- Filter Status -->
                <select name="is_active" class="smart-table-select" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="1" {{ $isActiveFilter === '1' ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ $isActiveFilter === '0' ? 'selected' : '' }}>Inactivo</option>
                </select>

                <!-- Page Size Selector -->
                <select name="per_page" class="smart-table-select" onchange="this.form.submit()">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 por pág.</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 por pág.</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 por pág.</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 por pág.</option>
                </select>

                <button type="submit" class="btn btn-primary" style="padding: 0.45rem 1rem;">Filtrar</button>

                @if($search || $isActiveFilter !== null && $isActiveFilter !== '')
                    <a href="{{ route('school-years.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1); padding: 0.45rem 1rem;">Limpiar</a>
                @endif
            </div>

            <!-- Export Buttons -->
            <div class="smart-table-export-group" style="display: flex; gap: 0.5rem;">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-export-csv" style="padding: 0.45rem 1rem;">Exportar CSV</a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'json']) }}" class="btn btn-export-json" style="padding: 0.45rem 1rem;">Exportar JSON</a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'print']) }}" target="_blank" class="btn btn-export-pdf" style="padding: 0.45rem 1rem;">Imprimir</a>
            </div>

        </form>
    </div>

    <!-- Data Table -->
    <div class="card table-container" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="padding: 1rem;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => ($sort == 'name' && $direction == 'asc') ? 'desc' : 'asc']) }}" style="color: inherit; text-decoration: none;">
                            Nombre {!! $sort == 'name' ? ($direction == 'asc' ? '↑' : '↓') : '' !!}
                        </a>
                    </th>
                    <th style="padding: 1rem;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'is_active', 'direction' => ($sort == 'is_active' && $direction == 'asc') ? 'desc' : 'asc']) }}" style="color: inherit; text-decoration: none;">
                            Estado {!! $sort == 'is_active' ? ($direction == 'asc' ? '↑' : '↓') : '' !!}
                        </a>
                    </th>
                    <th style="padding: 1rem;">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => ($sort == 'created_at' && $direction == 'asc') ? 'desc' : 'asc']) }}" style="color: inherit; text-decoration: none;">
                            Fecha de Registro {!! $sort == 'created_at' ? ($direction == 'asc' ? '↑' : '↓') : '' !!}
                        </a>
                    </th>
                    <th style="padding: 1rem; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schoolYears as $sy)
                    <tr>
                        <td style="padding: 1rem; font-weight: 600; color: #fff;">{{ $sy->name }}</td>
                        <td style="padding: 1rem;">
                            @if($sy->is_active)
                                <span class="badge" style="background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3);">Activo</span>
                            @else
                                <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted); border: 1px solid rgba(255, 255, 255, 0.1);">Inactivo</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; color: var(--text-muted);">{{ $sy->created_at ? $sy->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; align-items: center;">
                                @if(!$sy->is_active)
                                    <form action="{{ route('school-years.activate', $sy) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn" style="background: rgba(56, 189, 248, 0.1); color: var(--primary); padding: 0.35rem 0.75rem; font-size: 0.8rem;">Activar</button>
                                    </form>
                                @endif
                                <a href="{{ route('school-years.edit', $sy) }}" class="btn" style="background: rgba(255, 255, 255, 0.05); color: #fff; padding: 0.35rem 0.75rem; font-size: 0.8rem;">Editar</a>
                                @if(!$sy->is_active)
                                    <form action="{{ route('school-years.destroy', $sy) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este curso escolar?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 0.35rem 0.75rem; font-size: 0.8rem;">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding: 2rem; text-align: center; color: var(--text-muted);">No se encontraron cursos escolares.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $schoolYears->appends(request()->query())->links() }}
    </div>
@endsection
