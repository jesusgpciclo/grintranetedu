@extends('layouts.app')
@section('title', 'Gestión de Zonas')
@section('content')
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title">Gestión de Zonas</h1>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <!-- Exportar -->
            <div style="position: relative; display: inline-block;">
                <button onclick="document.getElementById('export-menu').classList.toggle('hidden')" class="btn" style="background: rgba(255,255,255,0.1); color: #fff;">Exportar ⬇</button>
                <div id="export-menu" class="hidden" style="position: absolute; right: 0; background: #1f2937; min-width: 120px; box-shadow: 0 8px 16px rgba(0,0,0,0.5); z-index: 10; border-radius: 8px; overflow: hidden; margin-top: 5px; border: 1px solid #374151;">
                    <a href="{{ route('aulas.export', 'csv') }}" style="color: white; padding: 10px 16px; text-decoration: none; display: block; border-bottom: 1px solid #374151; font-size: 0.9rem;">Formato CSV</a>
                    <a href="{{ route('aulas.export', 'json') }}" style="color: white; padding: 10px 16px; text-decoration: none; display: block; border-bottom: 1px solid #374151; font-size: 0.9rem;">Formato JSON</a>
                    <a href="{{ route('aulas.export', 'yaml') }}" style="color: white; padding: 10px 16px; text-decoration: none; display: block; font-size: 0.9rem;">Formato YAML</a>
                </div>
            </div>

            <!-- Importar -->
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                <form action="{{ route('aulas.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 0.5rem; align-items: center; margin: 0; background: rgba(0,0,0,0.2); padding: 0.25rem 0.5rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                    @csrf
                    <input type="file" name="file" accept=".csv,.json,.yaml,.yml" required style="color: #ccc; font-size: 0.8rem; width: 190px;" title="Selecciona un archivo CSV, JSON o YAML">
                    <button type="submit" class="btn" style="background: rgba(34, 197, 94, 0.2); color: #4ade80; padding: 0.4rem 0.8rem; font-size: 0.9rem;">Importar</button>
                </form>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-align: center;">
                    Plantillas de ejemplo: 
                    <a href="{{ route('aulas.template', 'csv') }}" style="color: #38bdf8; text-decoration: none;">CSV</a> | 
                    <a href="{{ route('aulas.template', 'json') }}" style="color: #38bdf8; text-decoration: none;">JSON</a> | 
                    <a href="{{ route('aulas.template', 'yaml') }}" style="color: #38bdf8; text-decoration: none;">YAML</a>
                </div>
            </div>

            <a href="{{ route('aulas.create') }}" class="btn btn-primary">+ Nueva Zona/Aula</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Barra de búsqueda --}}
    <div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form action="{{ route('aulas.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif
            <div style="flex: 2; min-width: 200px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, identificación, tipo o ubicación..." style="width: 100%;">
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if(request()->hasAny(['search']))
                <a href="{{ route('aulas.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="card table-container">
        <table class="smart-table">
            <thead>
                <tr>
                    <th><x-sort-header column="tipo" label="Tipo" route="aulas.index" /></th>
                    <th><x-sort-header column="identificacion" label="Identificación" route="aulas.index" /></th>
                    <th><x-sort-header column="nombre" label="Nombre" route="aulas.index" /></th>
                    <th><x-sort-header column="capacidad" label="Capacidad" route="aulas.index" /></th>
                    <th>Equipamiento</th>
                    <th><x-sort-header column="ubicacion" label="Ubicación" route="aulas.index" /></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aulas as $aula)
                    <tr>
                        <td>
                            @if(($aula->tipo ?? 'aula') === 'aula')
                                <span class="badge badge-info">Aula</span>
                            @else
                                <span class="badge" style="background: rgba(255,255,255,0.08); color: #fff;">Zona</span>
                            @endif
                        </td>
                        <td style="font-weight: 500;">{{ $aula->identificacion ?? '—' }}</td>
                        <td style="font-weight: 600;">{{ $aula->nombre }}</td>
                        <td>
                            @if(($aula->tipo ?? 'aula') === 'aula')
                                @if($aula->capacidad)
                                    <span class="badge badge-role">{{ $aula->capacidad }} plazas</span>
                                @else
                                    <span style="color: var(--text-muted); font-style: italic;">Sin capacidad</span>
                                @endif
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($aula->equipamiento)
                                @foreach($aula->equipamiento as $equip)
                                    <span class="badge badge-info" style="margin: 0.1rem;">{{ $equip }}</span>
                                @endforeach
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Sin equipamiento</span>
                            @endif
                        </td>
                        <td>{{ $aula->ubicacion ?? '—' }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('aulas.edit', $aula) }}" class="btn btn-sm" style="background: rgba(255,255,255,0.1);">Editar</a>
                                <form action="{{ route('aulas.destroy', $aula) }}" method="POST" onsubmit="return confirm('¿Eliminar esta zona/aula?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay zonas ni aulas creadas aún.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 1rem;">
            {{ $aulas->appends(request()->query())->links() }}
        </div>
    </div>

    <script>
        window.addEventListener('click', function(e) {
            if (!e.target.innerText || !e.target.innerText.includes('Exportar')) {
                const menu = document.getElementById('export-menu');
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
            }
        });
    </script>
    <style>
        .hidden { display: none !important; }
        #export-menu a:hover { background: #374151; }
    </style>
@endsection
