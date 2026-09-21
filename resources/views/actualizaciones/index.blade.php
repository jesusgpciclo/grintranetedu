@extends('layouts.app')
@section('title', 'Novedades')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Novedades y Actualizaciones</h1>
        @role('admin')<a href="{{ route('actualizaciones.create') }}" class="btn btn-primary">+ Nueva</a>@endrole
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @foreach($actualizaciones as $act)
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                <div>
                    <span class="badge badge-info" style="font-size: 0.9rem; margin-right: 0.5rem;">v{{ $act->version }}</span>
                    <span style="font-size: 1.15rem; font-weight: 700; color: #fff;">{{ $act->titulo }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">{{ $act->fecha->format('d/m/Y') }}</span>
                    @role('admin')
                    <form action="{{ route('actualizaciones.destroy', $act) }}" method="POST" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">×</button></form>
                    @endrole
                </div>
            </div>
            <p style="color: var(--text-muted); line-height: 1.6;">{{ $act->descripcion }}</p>
            @if($act->autor)<div style="margin-top: 0.75rem; font-size: 0.8rem; color: var(--text-muted);">Por: {{ $act->autor->name }}</div>@endif
        </div>
    @endforeach
    @if($actualizaciones->isEmpty())<div class="card"><p style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay novedades.</p></div>@endif
@endsection
