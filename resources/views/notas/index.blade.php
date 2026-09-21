@extends('layouts.app')
@section('title', 'Gestión de Notas')
@section('content')
    <div class="page-header"><h1 class="page-title">Gestión de Notas</h1></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card" style="padding: 1rem;">
        <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0; min-width: 300px;">
                <label>Seleccionar Módulo</label>
                <select name="modulo_id" onchange="this.form.submit()"><option value="">Seleccionar módulo...</option>@foreach($modulos as $m)<option value="{{ $m->id }}" {{ request('modulo_id') == $m->id ? 'selected' : '' }}>{{ $m->nombre }} {{ $m->group ? '(' . $m->group->name . ')' : '' }}</option>@endforeach</select>
            </div>
        </form>
    </div>

    @if($moduloSeleccionado && $students->isNotEmpty() && $actividades->isNotEmpty())
        <div class="card">
            <h2 style="font-size: 1.15rem; color: #fff; margin-bottom: 1rem;">{{ $moduloSeleccionado->nombre }} — {{ $moduloSeleccionado->group->name ?? '' }}</h2>
            <form method="POST" action="{{ route('notas.guardar') }}">
                @csrf
                <div class="table-container" style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th style="position: sticky; left: 0; background: rgba(15,23,42,0.95); z-index: 1;">Alumno</th>
                                @foreach($actividades as $act)
                                    <th style="text-align: center; min-width: 120px;">{{ Str::limit($act->titulo, 15) }}<br><span style="font-weight: 400; font-size: 0.7rem;">{{ $act->peso }}%</span></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $sIdx => $student)
                                <tr>
                                    <td style="font-weight: 600; position: sticky; left: 0; background: rgba(15,23,42,0.95); z-index: 1;">{{ $student->last_name }}, {{ $student->name }}</td>
                                    @foreach($actividades as $aIdx => $act)
                                        @php $nota = $notasMap[$student->id][$act->id] ?? null; $inputIdx = $sIdx * $actividades->count() + $aIdx; @endphp
                                        <td style="text-align: center;">
                                            <input type="hidden" name="notas[{{ $inputIdx }}][student_id]" value="{{ $student->id }}">
                                            <input type="hidden" name="notas[{{ $inputIdx }}][actividad_id]" value="{{ $act->id }}">
                                            <input type="number" name="notas[{{ $inputIdx }}][valor]" value="{{ $nota->valor ?? '' }}" min="0" max="10" step="0.01" placeholder="—" style="width: 70px; text-align: center; padding: 0.4rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.375rem; color: #fff; font-size: 0.9rem;">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 1rem;"><button type="submit" class="btn btn-primary">Guardar Notas</button></div>
            </form>
        </div>
    @elseif($moduloSeleccionado)
        <div class="card"><p style="color: var(--text-muted); text-align: center; padding: 2rem;">{{ $students->isEmpty() ? 'No hay alumnos en el grupo de este módulo.' : 'No hay actividades evaluables en este módulo.' }}</p></div>
    @endif
@endsection
