@extends('layouts.app')
@section('title', 'Nuevo Módulo')
@section('content')
    <div class="page-header"><h1 class="page-title">Nuevo Módulo</h1></div>
    <div class="card" style="max-width: 700px;">
        <form method="POST" action="{{ route('modulos.store') }}">@csrf
            <div class="grid-2">
                <div class="form-group"><label>Código *</label><input type="text" name="codigo" value="{{ old('codigo') }}" required placeholder="Ej: 0484"></div>
                <div class="form-group"><label>Horas semanales</label><input type="number" name="horas_semanales" value="{{ old('horas_semanales', 0) }}" min="0" max="40"></div>
            </div>
            <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Bases de Datos"></div>
            <div class="grid-2">
                <div class="form-group"><label>Grupo</label><select name="group_id"><option value="">— Sin grupo —</option>@foreach($groups as $g)<option value="{{ $g->id }}" {{ old('group_id') == $g->id ? 'selected' : '' }}>{{ $g->course }} - {{ $g->name }}</option>@endforeach</select></div>
                <div class="form-group"><label>Profesores</label><select name="profesores_ids[]" multiple style="height: 120px;">@foreach($profesores as $p)<option value="{{ $p->id }}" {{ in_array($p->id, old('profesores_ids', [])) ? 'selected' : '' }}>{{ $p->name }} {{ $p->last_name }}</option>@endforeach</select><small>Ctrl+Click para seleccionar varios</small></div>
            </div>
            <div class="form-group"><label>Descripción</label><textarea name="descripcion" rows="3">{{ old('descripcion') }}</textarea></div>
            @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Crear Módulo</button>
                <a href="{{ route('modulos.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
