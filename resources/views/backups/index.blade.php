@extends('layouts.app')
@section('title', 'Backups')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Copias de Seguridad</h1>
        <form method="POST" action="{{ route('backups.store') }}" style="display: inline;">@csrf<button type="submit" class="btn btn-primary">+ Crear Backup</button></form>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
    <div class="card table-container">
        <table>
            <thead><tr><th>Archivo</th><th>Tamaño</th><th>Fecha</th><th>Acciones</th></tr></thead>
            <tbody>
                @forelse($backups as $backup)
                    <tr>
                        <td style="font-weight: 600;">{{ $backup['name'] }}</td>
                        <td>{{ $backup['size'] }} KB</td>
                        <td>{{ $backup['date'] }}</td>
                        <td style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('backups.download', $backup['name']) }}" class="btn btn-sm btn-success">Descargar</a>
                            <form action="{{ route('backups.destroy', $backup['name']) }}" method="POST" onsubmit="return confirm('¿Eliminar backup?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Eliminar</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay backups. Crea el primero.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
