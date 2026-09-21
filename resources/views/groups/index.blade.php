@extends('layouts.app')

@section('title', 'Gestión de Grupos - Gestor de Usuarios')

@section('content')
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 class="page-title">{{ auth()->user()->hasRole('admin') ? 'Cursos y Grupos' : 'Mis Grupos' }}</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                Curso escolar activo: <strong style="color: var(--primary);">{{ $activeSchoolYearName ?? 'Sin seleccionar' }}</strong>
            </p>
        </div>
        @role('admin')
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <!-- Exportar -->
            <div style="position: relative; display: inline-block;">
                <button onclick="document.getElementById('export-menu').classList.toggle('hidden')" class="btn" style="background: rgba(255,255,255,0.1); color: #fff;">Exportar ⬇</button>
                <div id="export-menu" class="hidden" style="position: absolute; right: 0; background: #1f2937; min-width: 120px; box-shadow: 0 8px 16px rgba(0,0,0,0.5); z-index: 10; border-radius: 8px; overflow: hidden; margin-top: 5px; border: 1px solid #374151;">
                    <a href="{{ route('groups.export', 'csv') }}" style="color: white; padding: 10px 16px; text-decoration: none; display: block; border-bottom: 1px solid #374151; font-size: 0.9rem;">Formato CSV</a>
                    <a href="{{ route('groups.export', 'json') }}" style="color: white; padding: 10px 16px; text-decoration: none; display: block; border-bottom: 1px solid #374151; font-size: 0.9rem;">Formato JSON</a>
                    <a href="{{ route('groups.export', 'yaml') }}" style="color: white; padding: 10px 16px; text-decoration: none; display: block; font-size: 0.9rem;">Formato YAML</a>
                </div>
            </div>

            <!-- Importar -->
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                <form action="{{ route('groups.import') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 0.5rem; align-items: center; margin: 0; background: rgba(0,0,0,0.2); padding: 0.25rem 0.5rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                    @csrf
                    <input type="file" name="file" accept=".csv,.json,.yaml,.yml" required style="color: #ccc; font-size: 0.8rem; width: 190px;" title="Selecciona un archivo CSV, JSON o YAML">
                    <button type="submit" class="btn" style="background: rgba(34, 197, 94, 0.2); color: #4ade80; padding: 0.4rem 0.8rem; font-size: 0.9rem;">Importar</button>
                </form>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-align: center;">
                    Plantillas de ejemplo: 
                    <a href="{{ route('groups.template', 'csv') }}" style="color: #38bdf8; text-decoration: none;">CSV</a> | 
                    <a href="{{ route('groups.template', 'json') }}" style="color: #38bdf8; text-decoration: none;">JSON</a> | 
                    <a href="{{ route('groups.template', 'yaml') }}" style="color: #38bdf8; text-decoration: none;">YAML</a>
                </div>
            </div>

            <a href="{{ route('groups.create') }}" class="btn btn-primary">Nuevo Grupo</a>
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
        @endrole
    </div>

    @if(session('success'))
        <div class="alert" style="background: rgba(34, 197, 94, 0.1); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.2); padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card table-container" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="padding: 1rem;">Curso Académico</th>
                    <th style="padding: 1rem;">Ciclo/Etapa</th>
                    <th style="padding: 1rem;">Grupo</th>
                    <th style="padding: 1rem;">Tutor</th>
                    <th style="padding: 1rem; text-align: center;">Alumnos</th>
                    <th style="padding: 1rem; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groups as $group)
                    <tr>
                        <td style="padding: 1rem;">
                            @if($group->schoolYear)
                                <span class="badge" style="background: rgba(56, 189, 248, 0.1); color: var(--primary); border: 1px solid rgba(56, 189, 248, 0.2);">
                                    {{ $group->schoolYear->name }}
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-style: italic; font-size: 0.8rem;">Sin asignar</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; font-weight: 500; color: var(--primary);">{{ $group->course }}</td>
                        <td style="padding: 1rem; font-weight: 600; color: #fff;">{{ $group->name }}</td>
                        <td style="padding: 1rem;">
                            @if($group->tutor)
                                <span style="color: #fff;">{{ $group->tutor->name }} {{ $group->tutor->last_name }}</span>
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Sin tutor</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <span class="badge badge-role">{{ $group->students_count }}</span>
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; flex-wrap: wrap;">
                                <a href="{{ route('groups.show', $group) }}" class="btn"
                                    style="background: rgba(56, 189, 248, 0.1); color: var(--primary); padding: 0.35rem 0.75rem; font-size: 0.8rem;">Ver Alumnos</a>
                                @role('admin')
                                <a href="{{ route('groups.edit', $group) }}" class="btn"
                                    style="background: rgba(255, 255, 255, 0.05); color: #fff; padding: 0.35rem 0.75rem; font-size: 0.8rem;">Editar</a>
                                <form action="{{ route('groups.destroy', $group) }}" method="POST"
                                    onsubmit="return confirm('¿Eliminar este grupo?')" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn"
                                        style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 0.35rem 0.75rem; font-size: 0.8rem;">Eliminar</button>
                                </form>
                                @endrole
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                            No hay grupos para el curso <strong>{{ $activeSchoolYearName ?? 'seleccionado' }}</strong>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection