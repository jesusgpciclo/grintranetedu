@extends('layouts.app')
@section('title', 'Nueva Actualización')
@section('content')
    <div class="page-header"><h1 class="page-title">Registrar Actualización</h1></div>
    <div class="card" style="max-width: 700px;">
        <form method="POST" action="{{ route('actualizaciones.store') }}">@csrf
            <div class="grid-2">
                <div class="form-group"><label>Versión *</label><input type="text" name="version" value="{{ old('version') }}" required placeholder="Ej: 2.1.0"></div>
                <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required></div>
            </div>
            <div class="form-group"><label>Título *</label><input type="text" name="titulo" value="{{ old('titulo') }}" required placeholder="Ej: Nuevo módulo de notas"></div>
            <div class="form-group"><label>Descripción *</label><textarea name="descripcion" rows="5" required placeholder="Describe los cambios realizados...">{{ old('descripcion') }}</textarea></div>
            @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Publicar</button>
                <a href="{{ route('actualizaciones.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
