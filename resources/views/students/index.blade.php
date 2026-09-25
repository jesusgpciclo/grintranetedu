@extends('layouts.app')

@section('title', 'Gestión de Alumnos')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestión de Alumnos</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">Administra los alumnos del centro educativo</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            
            <!-- Filtros -->
            <form method="GET" action="{{ route('students.index') }}" style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-card); padding: 0.35rem 0.65rem; border-radius: 0.625rem; border: 1px solid var(--border); flex-wrap: wrap;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar alumno u observaciones..." class="form-control" style="width: 210px; margin: 0; padding: 0.3rem 0.6rem; font-size: 0.85rem;">
                <select name="group_id" class="form-control" style="width: auto; margin: 0; padding: 0.3rem 0.6rem; font-size: 0.85rem;" onchange="this.form.submit()">
                    <option value="">Todos los Grupos</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->course }} {{ $group->name }}
                        </option>
                    @endforeach
                </select>
                <select name="per_page" class="form-control" style="width: auto; margin: 0; padding: 0.3rem 0.6rem; font-size: 0.85rem;" onchange="this.form.submit()">
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
                <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.35rem 0.65rem;">🔍</button>
                @if(request('search') || request('group_id'))
                    <a href="{{ route('students.index') }}" class="btn btn-secondary btn-sm" style="padding: 0.35rem 0.65rem;" title="Limpiar filtro">✕</a>
                @endif
            </form>

            <!-- Exportar -->
            <div style="position: relative; display: inline-block;">
                <button onclick="document.getElementById('export-menu').classList.toggle('hidden')" class="btn btn-secondary">Exportar ⬇</button>
                <div id="export-menu" class="hidden" style="position: absolute; right: 0; background: var(--bg-card-solid); min-width: 140px; box-shadow: var(--shadow-lg); z-index: 20; border-radius: 0.625rem; overflow: hidden; margin-top: 5px; border: 1px solid var(--border);">
                    <a href="{{ route('students.export', 'csv') }}" style="color: var(--text-color); padding: 10px 16px; text-decoration: none; display: block; border-bottom: 1px solid var(--border); font-size: 0.875rem;">Formato CSV</a>
                    <a href="{{ route('students.export', 'json') }}" style="color: var(--text-color); padding: 10px 16px; text-decoration: none; display: block; border-bottom: 1px solid var(--border); font-size: 0.875rem;">Formato JSON</a>
                    <a href="{{ route('students.export', 'yaml') }}" style="color: var(--text-color); padding: 10px 16px; text-decoration: none; display: block; font-size: 0.875rem;">Formato YAML</a>
                </div>
            </div>

            <!-- Importar -->
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                <form action="{{ route('students.mass-import') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 0.5rem; align-items: center; margin: 0; background: var(--bg-card); padding: 0.35rem 0.65rem; border-radius: 0.625rem; border: 1px solid var(--border);">
                    @csrf
                    <input type="file" name="file" accept=".csv,.json,.yaml,.yml" required style="color: var(--text-muted); font-size: 0.8rem; width: 190px;" title="Selecciona un archivo CSV, JSON o YAML">
                    <button type="submit" class="btn btn-success btn-sm">Importar</button>
                </form>
                <div style="font-size: 0.75rem; color: var(--text-muted); text-align: center;">
                    Plantillas: 
                    <a href="{{ route('students.template', 'seneca') }}" title="Formato oficial Séneca (Alumno/a, Unidad)">Séneca</a> | 
                    <a href="{{ route('students.template', 'csv') }}">CSV</a> | 
                    <a href="{{ route('students.template', 'json') }}">JSON</a> | 
                    <a href="{{ route('students.template', 'yaml') }}">YAML</a>
                </div>
            </div>

            <a href="{{ route('students.create') }}" class="btn btn-primary">Nuevo Alumno</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Bulk Actions Bar -->
    <div id="bulk-actions-bar" style="display: none; background: var(--bg-card); padding: 1rem; border-radius: 0.75rem; margin-bottom: 1rem; align-items: center; justify-content: space-between; border: 1px solid var(--border);">
        <div>
            <span id="selected-count" style="font-weight: bold; margin-right: 0.5rem; color: var(--primary);">0</span> alumnos seleccionados
        </div>
        <div style="display: flex; gap: 1rem;">
            <!-- Form to change group -->
            <form id="bulk-change-form" action="{{ route('students.bulk-change-group') }}" method="POST" style="margin: 0; display: flex; gap: 0.5rem; align-items: center;">
                @csrf
                <div id="hidden-inputs-change"></div>
                <select name="new_group_id" class="form-control" style="width: auto; padding: 0.35rem 0.6rem; font-size: 0.85rem;" required>
                    <option value="">-- Mover a Grupo --</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->course }} {{ $group->name }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="submitBulkForm('bulk-change-form', 'hidden-inputs-change')" class="btn btn-primary btn-sm">Mover</button>
            </form>
            
            <!-- Form to delete -->
            <form id="bulk-delete-form" action="{{ route('students.bulk-delete') }}" method="POST" style="margin: 0;">
                @csrf
                <div id="hidden-inputs-delete"></div>
                <button type="button" onclick="if(confirm('¿Seguro que deseas eliminar los alumnos seleccionados?')) submitBulkForm('bulk-delete-form', 'hidden-inputs-delete')" class="btn btn-danger btn-sm">Eliminar Selección</button>
            </form>
        </div>
    </div>

    <div class="card table-container" style="padding: 0; overflow: hidden;">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="select-all" onclick="toggleAll(this)">
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => (request('sort') === 'name' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" style="text-decoration: none; color: inherit;">
                            Nombre @if(request('sort') === 'name') {!! request('direction') === 'asc' ? '&#9650;' : '&#9660;' !!} @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'last_name', 'direction' => (request('sort') === 'last_name' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" style="text-decoration: none; color: inherit;">
                            Apellidos @if(request('sort') === 'last_name') {!! request('direction') === 'asc' ? '&#9650;' : '&#9660;' !!} @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'email', 'direction' => (request('sort') === 'email' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" style="text-decoration: none; color: inherit;">
                            Email @if(request('sort') === 'email') {!! request('direction') === 'asc' ? '&#9650;' : '&#9660;' !!} @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'group', 'direction' => (request('sort') === 'group' && request('direction') === 'asc') ? 'desc' : 'asc']) }}" style="text-decoration: none; color: inherit;">
                            Grupo @if(request('sort') === 'group') {!! request('direction') === 'asc' ? '&#9650;' : '&#9660;' !!} @endif
                        </a>
                    </th>
                    <th>Observaciones</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>
                            <input type="checkbox" class="student-checkbox" value="{{ $student->id }}" onchange="updateBulkBar()">
                        </td>
                        <td style="font-weight: 600;">{{ $student->name }}</td>
                        <td>{{ $student->last_name }}</td>
                        <td style="color: var(--text-muted);">{{ $student->email }}</td>
                        <td>
                            @if($student->groupRel)
                                <span class="badge badge-primary">{{ $student->groupRel->course }} {{ $student->groupRel->name }}</span>
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Sin asignar</span>
                            @endif
                        </td>
                        <td style="max-width: 220px;">
                            @if($student->observaciones)
                                <span style="font-size: 0.825rem; color: var(--text-secondary);" title="{{ $student->observaciones }}">
                                    {{ Str::limit($student->observaciones, 45) }}
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="{{ route('students.edit', $student) }}" class="btn btn-secondary btn-sm">Editar</a>
                                <form action="{{ route('students.destroy', $student) }}" method="POST" style="margin: 0;" onsubmit="return confirm('¿Estás seguro de querer eliminar este alumno?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 2.5rem; text-align: center; color: var(--text-muted);">
                            No hay alumnos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($perPage !== 'all' && $students->hasPages())
        <div style="margin-top: 1rem;">
            {{ $students->links() }}
        </div>
    @endif

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('student-checkbox');
            for(var i=0, n=checkboxes.length; i<n; i++) {
                checkboxes[i].checked = source.checked;
            }
            updateBulkBar();
        }

        function updateBulkBar() {
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
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
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
            const container = document.getElementById(containerId);
            container.innerHTML = ''; // clear previous inputs

            checkboxes.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });

            document.getElementById(formId).submit();
        }
    </script>
@endsection