@extends('layouts.app')
@section('title', 'Nueva Sesión')
@section('content')
    <div class="page-header"><h1 class="page-title">Registrar Sesión</h1></div>
    <div class="card" style="max-width: 800px;">
        <form method="POST" action="{{ route('sesiones.store') }}">@csrf
            <div class="grid-2">
                <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required></div>
                <div class="form-group"><label>Módulo *</label><select name="modulo_id" required><option value="">Seleccionar...</option>@foreach($modulos as $m)<option value="{{ $m->id }}" {{ old('modulo_id') == $m->id ? 'selected' : '' }}>{{ $m->nombre }} {{ $m->group ? '(' . $m->group->name . ')' : '' }}</option>@endforeach</select></div>
            </div>
            <div class="form-group"><label>Grupo *</label><select name="group_id" required><option value="">Seleccionar...</option>@foreach($groups as $g)<option value="{{ $g->id }}" {{ old('group_id') == $g->id ? 'selected' : '' }}>{{ $g->course }} - {{ $g->name }}</option>@endforeach</select></div>
            <div class="form-group"><label>Contenidos impartidos</label><textarea name="contenidos" rows="3" placeholder="¿Qué contenidos se han dado en esta sesión?">{{ old('contenidos') }}</textarea></div>
            <div class="form-group"><label>Actividades realizadas</label><textarea name="actividades_realizadas" rows="3" placeholder="¿Qué actividades se han realizado?">{{ old('actividades_realizadas') }}</textarea></div>
            <div class="form-group"><label>Tareas mandadas</label><textarea name="tareas_mandadas" rows="2" placeholder="¿Qué tareas se han mandado para casa?">{{ old('tareas_mandadas') }}</textarea></div>
            <div class="form-group"><label>Observaciones</label><textarea name="observaciones" rows="2" placeholder="Observaciones generales de la sesión...">{{ old('observaciones') }}</textarea></div>
            @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary">Registrar Sesión</button>
                <a href="{{ route('sesiones.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
