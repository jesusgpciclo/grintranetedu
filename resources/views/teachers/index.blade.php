@extends('layouts.app')

@section('title', 'Gestión de Profesores')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestión de Profesores</h1>
            <p style="color: var(--text-muted);">Administra el claustro de profesores del centro educativo</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            
            <!-- Filtros -->
            <form method="GET" action="{{ route('teachers.index') }}" style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-hover); padding: 0.25rem 0.5rem; border-radius: 8px; border: 1px solid var(--border);">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, email, dpto..." class="form-control" style="width: 220px; margin: 0; padding: 0.35rem 0.65rem;">
                <select name="per_page" class="form-control" style="width: auto; margin: 0; padding: 0.35rem 0.65rem;" onchange="this.form.submit()">
                    <option value="15" {{ request('per_page') == '15' ? 'selected' : '' }}>15 / pág.</option>
                    <option value="30" {{ request('per_page', 30) == '30' ? 'selected' : '' }}>30 / pág.</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 / pág.</option>
                    <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 / pág.</option>
                    <option value="200" {{ request('per_page') == '200' ? 'selected' : '' }}>200 / pág.</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Todos</option>
                </select>
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                @endif
                <button type="submit" class="btn" style="background: var(--bg-hover); border: 1px solid var(--border); padding: 0.35rem 0.65rem; font-size: 0.85rem;">🔍</button>
                @if(request('search'))
                    <a href="{{ route('teachers.index') }}" class="btn" style="background: var(--bg-hover); border: 1px solid var(--border); padding: 0.35rem 0.65rem; font-size: 0.85rem;" title="Limpiar búsqueda">✕</a>
                @endif
            </form>

            <!-- Exportar -->
            <div style="position: relative; display: inline-block;">
                <button onclick="document.getElementById('export-menu').classList.toggle('hidden')" class="btn" style="background: var(--bg-hover); border: 1px solid var(--border); color: var(--text-heading);">Exportar ⬇</button>
                <div id="export-menu" class="hidden" style="position: absolute; right: 0; background: var(--bg-card-solid); min-width: 130px; box-shadow: var(--shadow-lg); z-index: 20; border-radius: 8px; overflow: hidden; margin-top: 5px; border: 1px solid var(--border);">
                    <a href="{{ route('teachers.export', 'csv') }}" style="color: var(--text-color); padding: 10px 16px; text-decoration: none; display: block; border-bottom: 1px solid var(--border); font-size: 0.875rem;">Formato CSV</a>
                    <a href="{{ route('teachers.export', 'json') }}" style="color: var(--text-color); padding: 10px 16px; text-decoration: none; display: block; border-bottom: 1px solid var(--border); font-size: 0.875rem;">Formato JSON</a>
                    <a href="{{ route('teachers.export', 'yaml') }}" style="color: var(--text-color); padding: 10px 16px; text-decoration: none; display: block; font-size: 0.875rem;">Formato YAML</a>
                </div>
            </div>

            <!-- Importar -->
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                <form action="{{ route('teachers.mass-import') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 0.5rem; align-items: center; margin: 0; background: var(--bg-hover); padding: 0.25rem 0.5rem; border-radius: 8px; border: 1px solid var(--border);">
                    @csrf
                    <input type="file" name="file" accept=".csv,.json,.yaml,.yml" required style="font-size: 0.8rem; width: 190px;" title="Selecciona un archivo CSV, JSON o YAML">
                    <button type="submit" class="btn" style="background: rgba(34, 197, 94, 0.15); color: #16a34a; border: 1px solid rgba(34, 197, 94, 0.3); padding: 0.4rem 0.8rem; font-size: 0.875rem; font-weight: 600;">Importar</button>
                </form>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-align: center;">
                    Plantillas de ejemplo: 
                    <a href="{{ route('teachers.template', 'csv') }}" style="color: var(--primary); text-decoration: none;">CSV</a> | 
                    <a href="{{ route('teachers.template', 'json') }}" style="color: var(--primary); text-decoration: none;">JSON</a> | 
                    <a href="{{ route('teachers.template', 'yaml') }}" style="color: var(--primary); text-decoration: none;">YAML</a>
                </div>
            </div>

            <a href="{{ route('teachers.create') }}" class="btn btn-primary">Nuevo Profesor</a>
        </div>
    </div>

    @if(!$roleExists)
        <div class="alert" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
            ⚠️ <strong>Atención:</strong> El rol <strong>"profesor"</strong> no existe en el sistema. Debes crearlo previamente en la sección de 
            <a href="{{ route('roles.index') }}" style="color: #38bdf8; text-decoration: underline;">Administración &gt; Roles</a>.
        </div>
    @endif

    @if(session('success'))
        <div class="alert" style="background: rgba(34, 197, 94, 0.1); color: var(--success); border: 1px solid rgba(34, 197, 94, 0.2); padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); padding: 1rem; margin-bottom: 1rem; border-radius: 8px;">
            {{ session('error') }}
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

    <!-- Bulk Actions Bar -->
    <div id="bulk-actions-bar" style="display: none; background: var(--bg-hover); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; align-items: center; justify-content: space-between; border: 1px solid var(--border);">
        <div>
            <span id="selected-count" style="font-weight: bold; margin-right: 0.5rem; color: var(--text-heading);">0</span> profesores seleccionados
        </div>
        <div style="display: flex; gap: 1rem;">
            <!-- Form to delete -->
            <form id="bulk-delete-form" action="{{ route('teachers.bulk-delete') }}" method="POST" style="margin: 0;">
                @csrf
                <div id="hidden-inputs-delete"></div>
                <button type="button" onclick="if(confirm('¿Seguro que deseas eliminar los profesores seleccionados?')) submitBulkForm('bulk-delete-form', 'hidden-inputs-delete')" class="btn" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 0.4rem 0.8rem;">Eliminar Selección</button>
            </form>
        </div>
    </div>

    <div class="card table-container" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-hover);">
                    <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); width: 40px;">
                        <input type="checkbox" id="select-all" onclick="toggleAll(this)">
                    </th>
                    <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border);">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => (request('sort') === 'name' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" style="color: var(--text-heading); text-decoration: none;">
                            Nombre @if(request('sort') === 'name') {!! request('direction') === 'asc' ? '&#9650;' : '&#9660;' !!} @endif
                        </a>
                    </th>
                    <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border);">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'last_name', 'direction' => (request('sort') === 'last_name' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" style="color: var(--text-heading); text-decoration: none;">
                            Apellidos @if(request('sort') === 'last_name') {!! request('direction') === 'asc' ? '&#9650;' : '&#9660;' !!} @endif
                        </a>
                    </th>
                    <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border);">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'departamento', 'direction' => (request('sort') === 'departamento' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" style="color: var(--text-heading); text-decoration: none;">
                            Departamento @if(request('sort') === 'departamento') {!! request('direction') === 'asc' ? '&#9650;' : '&#9660;' !!} @endif
                        </a>
                    </th>
                    <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border);">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'direction' => (request('sort') === 'email' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" style="color: var(--text-heading); text-decoration: none;">
                            Email @if(request('sort') === 'email') {!! request('direction') === 'asc' ? '&#9650;' : '&#9660;' !!} @endif
                        </a>
                    </th>
                    <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--border);">Observaciones</th>
                    <th style="padding: 1rem; text-align: right; border-bottom: 1px solid var(--border);">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $teacher)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 1rem;">
                            <input type="checkbox" class="teacher-checkbox" value="{{ $teacher->id }}" onchange="updateBulkBar()">
                        </td>
                        <td style="padding: 1rem; font-weight: 500; color: var(--text-heading);">{{ $teacher->name }}</td>
                        <td style="padding: 1rem; color: var(--text-color);">{{ $teacher->last_name }}</td>
                        <td style="padding: 1rem;">
                            @if($teacher->departamento)
                                <span class="badge" style="background: rgba(56, 189, 248, 0.15); color: var(--primary); border: 1px solid var(--primary-border);">
                                    {{ $teacher->departamento }}
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; color: var(--text-muted);">{{ $teacher->email }}</td>
                        <td style="padding: 1rem; max-width: 220px;">
                            @if($teacher->observaciones)
                                <span style="font-size: 0.825rem; color: var(--text-secondary);" title="{{ $teacher->observaciones }}">
                                    {{ Str::limit($teacher->observaciones, 45) }}
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="{{ route('teacher-schedules.index', ['search' => $teacher->email]) }}" class="btn" style="background: rgba(59, 130, 246, 0.15); color: #38bdf8; padding: 0.3rem 0.6rem; font-size: 0.85rem;" title="Gestionar Horario">📅 Horario</a>
                                <a href="{{ route('teachers.edit', $teacher) }}" class="btn" style="background: var(--bg-hover); border: 1px solid var(--border); padding: 0.3rem 0.6rem; font-size: 0.85rem; color: var(--text-heading);">Editar</a>
                                <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar a este profesor?');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 0.3rem 0.6rem; font-size: 0.85rem;">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                            No se encontraron profesores registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($perPage !== 'all' && $teachers->hasPages())
        <div style="margin-top: 1rem;">
            {{ $teachers->links() }}
        </div>
    @endif

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('teacher-checkbox');
            for(var i=0, n=checkboxes.length; i<n; i++) {
                checkboxes[i].checked = source.checked;
            }
            updateBulkBar();
        }

        function updateBulkBar() {
            const checkboxes = document.querySelectorAll('.teacher-checkbox:checked');
            const bulkBar = document.getElementById('bulk-actions-bar');
            const selectedCount = document.getElementById('selected-count');

            if (checkboxes.length > 0) {
                bulkBar.style.display = 'flex';
                selectedCount.innerText = checkboxes.length;
            } else {
                bulkBar.style.display = 'none';
            }
        }

        function submitBulkForm(formId, containerId) {
            const checkboxes = document.querySelectorAll('.teacher-checkbox:checked');
            const container = document.getElementById(containerId);
            container.innerHTML = ''; // clear previous inputs

            checkboxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'teacher_ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            document.getElementById(formId).submit();
        }
    </script>
@endsection
