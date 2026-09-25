@extends('layouts.app')
@section('title', 'Importar Alumnos')
@section('content')
    <div class="page-header">
        <h1 class="page-title">Importar Alumnos a {{ $group->course }} — {{ $group->name }}</h1>
        <a href="{{ route('groups.show', $group) }}" class="btn" style="background: rgba(255,255,255,0.1);">← Volver</a>
    </div>
    <div class="card" style="max-width: 700px;">
        <div class="alert alert-info">
            <strong>Formatos CSV soportados (Séneca):</strong><br>
            • <code style="font-size: 0.85rem;">Alumno/a,Unidad</code> (ej: "Algaba Marín, Francisco",1º GM SMR B)<br>
            • <code style="font-size: 0.85rem;">Apellidos;Nombre;DNI/NIE;Email</code><br>
            Se detecta automáticamente el delimitador (, o ;) y la cabecera.
        </div>
        <div style="margin-bottom: 1.5rem;">
            <a href="{{ route('students.csv-template') }}" class="btn btn-sm btn-success">Descargar plantilla CSV</a>
        </div>
        <form method="POST" action="{{ route('students.import.process', $group) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Archivo CSV *</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required style="color: var(--text-muted);">
            </div>
            @if($errors->any())<div class="alert alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <button type="submit" class="btn btn-primary">Importar Alumnos</button>
        </form>
    </div>
@endsection
