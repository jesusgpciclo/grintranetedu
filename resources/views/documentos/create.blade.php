@extends('layouts.app')
@section('title', 'Nuevo Documento')
@section('content')
    <div class="page-header"><h1 class="page-title">Nuevo Documento</h1></div>
    <div class="card" style="max-width: 700px;">
        <form method="POST" action="{{ route('documentos.store') }}" enctype="multipart/form-data">@csrf
            <div class="form-group"><label>Título *</label><input type="text" name="titulo" value="{{ old('titulo') }}" required></div>
            <div class="grid-2">
                <div class="form-group"><label>Categoría *</label><select name="categoria" required><option value="normativa">Normativa</option><option value="programacion">Programación</option><option value="acta">Acta</option><option value="plantilla">Plantilla</option><option value="otro">Otro</option></select></div>
                <div class="form-group"><label>Departamento</label><input type="text" name="departamento" value="{{ old('departamento') }}" placeholder="Ej: Informática"></div>
            </div>
            <div class="form-group"><label>Curso</label><input type="text" name="curso" value="{{ old('curso') }}" placeholder="Ej: 2025/2026"></div>
            <div class="form-group"><label>Enlace externo (URL)</label><input type="url" name="url" value="{{ old('url') }}" placeholder="https://drive.google.com/..."></div>
            <div class="form-group"><label>O subir archivo</label><input type="file" name="archivo" style="color: var(--text-muted);"></div>
            <div class="form-group"><label>Descripción</label><textarea name="descripcion" rows="3">{{ old('descripcion') }}</textarea></div>
            @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('documentos.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
