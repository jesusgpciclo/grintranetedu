@extends('layouts.app')
@section('title', 'Nueva Observación')
@section('content')
    <div class="page-header"><h1 class="page-title">Nueva Observación</h1></div>
    <div class="card" style="max-width: 700px;">
        <form method="POST" action="{{ route('observaciones.store') }}">@csrf
            <div class="grid-2">
                <div class="form-group"><label>Alumno *</label><select name="alumno_id" required><option value="">Seleccionar alumno...</option>@foreach($students as $s)<option value="{{ $s->id }}" {{ ($selectedStudentId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->last_name }}, {{ $s->name }}</option>@endforeach</select></div>
                <div class="form-group"><label>Tipo *</label><select name="tipo" required><option value="informativa">Informativa</option><option value="positiva">Positiva</option><option value="negativa">Negativa</option><option value="seguimiento">Seguimiento</option></select></div>
            </div>
            <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required></div>
            <div class="form-group"><label>Descripción *</label><textarea name="descripcion" rows="4" required placeholder="Describe la observación...">{{ old('descripcion') }}</textarea></div>
            @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Registrar</button>
                <a href="{{ route('observaciones.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
