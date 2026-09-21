@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Usuarios</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('users.import') }}" class="btn"
                style="background: rgba(56, 189, 248, 0.1); color: var(--primary); border: 1px solid rgba(56, 189, 248, 0.2);">Importación
                Masiva</a>
            <a href="{{ route('users.create') }}" class="btn btn-primary">Nuevo Usuario</a>
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
        <form action="{{ route('users.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <!-- Preserve sort params -->
            @if(request('sort_by')) <input type="hidden" name="sort_by" value="{{ request('sort_by') }}"> @endif
            @if(request('sort_order')) <input type="hidden" name="sort_order" value="{{ request('sort_order') }}"> @endif

            <div style="flex: 2; min-width: 200px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, email o departamento..."
                    style="width: 100%;">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <select name="group_id" style="width: 100%;" onchange="this.form.submit()">
                    <option value="">Cualquier Grupo</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->course }} - {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <select name="role" style="width: 100%;" onchange="this.form.submit()">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filtrar</button>
            @if(request('search') || request('role') || request('group_id'))
                <a href="{{ route('users.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Limpiar</a>
            @endif
        </form>
    </div>

    <div class="card table-container">
        <table class="smart-table" data-column-filters="false">
            <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th><x-sort-header column="name" label="Nombre" route="users.index" /></th>
                    <th><x-sort-header column="last_name" label="Apellidos" route="users.index" /></th>
                    <th><x-sort-header column="departamento" label="Departamento" route="users.index" /></th>
                    <th>Grupo</th>
                    <th><x-sort-header column="email" label="Email" route="users.index" /></th>
                    <th>Roles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                            @else
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.8rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </td>
                        <td style="font-weight: 500;">{{ $user->name }}</td>
                        <td>{{ $user->last_name }}</td>
                        <td>
                            @if($user->departamento)
                                <span class="badge" style="background: rgba(56, 189, 248, 0.15); color: var(--primary); border: 1px solid var(--primary-border);">
                                    {{ $user->departamento }}
                                </span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td style="color: var(--primary); font-weight: 500;">
                            @if($user->groupRel)
                                {{ $user->groupRel->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td style="color: var(--text-muted);">{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge badge-role">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('users.edit', $user) }}" class="btn"
                                    style="background: rgba(255, 255, 255, 0.1); padding: 0.4rem 0.8rem; font-size: 0.8rem;">Editar</a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST"
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
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
@endsection