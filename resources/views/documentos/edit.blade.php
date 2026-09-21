@extends('layouts.app')
@section('title', 'Editar Documento')
@section('content')
    <div class="page-header"><h1 class="page-title">Editar Documento</h1></div>
    <div class="card" style="max-width: 700px;">
        <form method="POST" action="{{ route('documentos.update', $documento) }}" enctype="multipart/form-data">@csrf @method('PUT')
            <div class="form-group"><label>Título *</label><input type="text" name="titulo" value="{{ old('titulo', $documento->titulo) }}" required></div>
            <div class="grid-2">
                <div class="form-group"><label>Categoría *</label><select name="categoria" required>@foreach(['normativa','programacion','acta','plantilla','otro'] as $c)<option value="{{ $c }}" {{ old('categoria', $documento->categoria) == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>@endforeach</select></div>
                <div class="form-group"><label>Departamento</label><input type="text" name="departamento" value="{{ old('departamento', $documento->departamento) }}"></div>
            </div>
            <div class="form-group"><label>Curso</label><input type="text" name="curso" value="{{ old('curso', $documento->curso) }}"></div>
            <div class="form-group"><label>URL</label><input type="url" name="url" value="{{ old('url', $documento->url) }}"></div>
            <div class="form-group"><label>Archivo</label><input type="file" name="archivo" style="color: var(--text-muted);">@if($documento->archivo)<p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Actual: {{ $documento->archivo }}</p>@endif</div>
            <div class="form-group"><label>Descripción</label><textarea name="descripcion" rows="3">{{ old('descripcion', $documento->descripcion) }}</textarea></div>
            @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('documentos.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
