@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        /** @var \App\Models\User $user */
        $user = Auth::user();
    @endphp
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem; font-size: 0.95rem;">
                Bienvenido, <strong>{{ $user->name }}</strong>
            </p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
        {{-- 1. Acceso Directo: Gestor de Salidas (Primero) --}}
        @canany(['salidas.view', 'salidas.create'])
        <div class="card card-hover" style="border: 2px solid var(--warning-border, #f59e0b); background: var(--warning-light, rgba(245, 158, 11, 0.05));">
            <h3 style="color: #d97706; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                🚪 Gestor de Salidas</h3>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.35rem;">Pases de Salida</div>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">Gestiona los pases de salida del alumnado y consulta el estado en tiempo real.</p>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('salidas.index') }}" class="btn btn-primary btn-sm">Abrir Gestor &rarr;</a>
                <a href="{{ route('salidas.history') }}" class="btn btn-secondary btn-sm">Historial</a>
            </div>
        </div>
        @endcanany

        {{-- 2. Servicio de Guardias (Segundo) --}}
        @canany(['guardias.view', 'ausencias.view', 'ausencias.create'])
        <div class="card card-hover" style="border: 2px solid var(--primary-border); background: var(--primary-light);">
            <h3 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                🛡️ Servicio de Guardias</h3>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.35rem;">Parte de Guardia</div>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">Consulta ausencias en vivo, tareas pedagógicas y confirma guardias en 1 toque.</p>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="{{ route('guardias.parte') }}" class="btn btn-primary btn-sm">Ver Parte Hoy &rarr;</a>
                <a href="{{ route('ausencias.create') }}" class="btn btn-secondary btn-sm">+ Notificar Ausencia</a>
            </div>
        </div>
        @endcanany

        {{-- Personalización: Mi Horario --}}
        @can('schedules.view')
        <div class="card card-hover" style="border: 2px dashed var(--primary-border); background: var(--bg-card);">
            <h3 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                Personalización</h3>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text-heading); margin-bottom: 0.35rem;">Mi Horario</div>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">Configura tus guardias y actividades sobre una plantilla escolar.</p>
            <a href="{{ route('personal-schedules.index') }}" style="display: inline-flex; font-size: 0.875rem; color: var(--primary); font-weight: 700;">
                Ir a mi horario &rarr;
            </a>
        </div>
        @endcan

        {{-- Mi Perfil --}}
        <div class="card card-hover">
            <h3 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                Mi Perfil</h3>
            <div style="margin-top: 0.75rem; display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    @foreach($user->getRoleNames() as $role)
                        <span class="badge badge-role" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">{{ ucfirst($role) }}</span>
                    @endforeach
                </div>
                <a href="{{ route('profile.edit') }}" style="font-size: 0.875rem; font-weight: 600;">Editar Perfil &rarr;</a>
            </div>
        </div>

        {{-- Totales al final (para usuarios con rol/permiso) --}}
        @can('users.view')
        <div class="card card-hover">
            <h3 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                Total Usuarios</h3>
            <div style="font-size: 2.75rem; font-weight: 800; color: var(--primary); line-height: 1;">{{ $usersCount }}</div>
            <a href="{{ route('users.index') }}"
                style="display: inline-flex; align-items: center; gap: 0.35rem; margin-top: 1.25rem; font-size: 0.875rem; font-weight: 600;">
                Gestionar Usuarios &rarr;
            </a>
        </div>
        @endcan

        @can('roles.view')
        <div class="card card-hover">
            <h3 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                Total Roles</h3>
            <div style="font-size: 2.75rem; font-weight: 800; color: var(--success); line-height: 1;">{{ $rolesCount }}</div>
            <a href="{{ route('roles.index') }}"
                style="display: inline-flex; align-items: center; gap: 0.35rem; margin-top: 1.25rem; font-size: 0.875rem; font-weight: 600; color: var(--success);">
                Gestionar Roles &rarr;
            </a>
        </div>
        @endcan

        @can('groups.view')
        <div class="card card-hover">
            <h3 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                Total Grupos</h3>
            <div style="font-size: 2.75rem; font-weight: 800; color: var(--warning); line-height: 1;">{{ $groupsCount }}</div>
            <a href="{{ route('groups.index') }}"
                style="display: inline-flex; align-items: center; gap: 0.35rem; margin-top: 1.25rem; font-size: 0.875rem; font-weight: 600; color: var(--warning);">
                Gestionar Grupos &rarr;
            </a>
        </div>
        @endcan

        @canany(['zonas.view', 'zonas.manage', 'aulas.view', 'aulas.manage'])
        <div class="card card-hover">
            <h3 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                Total Zonas</h3>
            <div style="font-size: 2.75rem; font-weight: 800; color: #10b981; line-height: 1;">{{ $zonasCount }}</div>
            <a href="{{ route('zonas.index') }}"
                style="display: inline-flex; align-items: center; gap: 0.35rem; margin-top: 1.25rem; font-size: 0.875rem; font-weight: 600; color: #10b981;">
                Gestionar Zonas &rarr;
            </a>
        </div>
        @endcanany

        @role('profesor')
        <div class="card card-hover">
            <h3 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                Mis Alumnos Totales</h3>
            <div style="font-size: 2.75rem; font-weight: 800; color: var(--primary); line-height: 1;">{{ $myStudentsCount }}</div>
            <p style="color: var(--text-muted); font-size: 0.825rem; margin-top: 0.5rem;">Suma de alumnos en todos tus grupos tutorizados.</p>
        </div>

        <div class="card card-hover">
            <h3 style="color: var(--text-muted); margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                Grupos Tutorizados</h3>
            <div style="font-size: 2.75rem; font-weight: 800; color: var(--success); line-height: 1;">{{ $myGroups->count() }}</div>
            <a href="{{ route('groups.index') }}"
                style="display: inline-flex; align-items: center; gap: 0.35rem; margin-top: 1.25rem; font-size: 0.875rem; font-weight: 600; color: var(--success);">
                Ver Mis Grupos &rarr;
            </a>
        </div>
        @endrole
    </div>

    @if($user->hasRole('profesor') && $myGroups->isNotEmpty())
        <div class="card" style="margin-top: 2rem;">
            <h2 style="margin-bottom: 1.25rem; font-size: 1.25rem; color: var(--text-heading);">Resumen de Mis Grupos</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Grupo</th>
                            <th>Nº Alumnos</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myGroups as $group)
                            <tr>
                                <td style="color: var(--primary); font-weight: 600;">{{ $group->course }}</td>
                                <td style="font-weight: 700;">{{ $group->name }}</td>
                                <td><span class="badge badge-role">{{ $group->students_count }}</span></td>
                                <td>
                                    <a href="{{ route('groups.show', $group) }}" class="btn btn-secondary btn-sm">
                                        Ver Alumnos
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection