@extends('layouts.app')
@section('title', 'Boletín de Calificaciones')
@section('content')
    <div class="page-header"><h1 class="page-title">Boletín de Calificaciones</h1></div>
    <div class="card" style="padding: 1rem;">
        <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0; min-width: 300px;">
                <label>Módulo</label>
                <select name="modulo_id" onchange="this.form.submit()"><option value="">Seleccionar módulo...</option>@foreach($modulos as $m)<option value="{{ $m->id }}" {{ request('modulo_id') == $m->id ? 'selected' : '' }}>{{ $m->nombre }} {{ $m->group ? '(' . $m->group->name . ')' : '' }}</option>@endforeach</select>
            </div>
        </form>
    </div>

    @if($moduloSeleccionado && !empty($calificaciones))
        <div class="card">
            <h2 style="font-size: 1.15rem; color: #fff; margin-bottom: 0.5rem;">{{ $moduloSeleccionado->nombre }}</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">
                Cálculo automático: Actividades → CE (peso %) → RA (peso %) → Nota Final
            </p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            @if(isset($calificaciones[0]['desglose']))
                                @foreach($calificaciones[0]['desglose'] as $d)
                                    <th style="text-align: center;">{{ $d['ra']->codigo }}<br><span style="font-weight: 400; font-size: 0.7rem;">{{ $d['ra']->peso }}%</span></th>
                                @endforeach
                            @endif
                            <th style="text-align: center;">Nota Final</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($calificaciones as $cal)
                            <tr>
                                <td style="font-weight: 600;">{{ $cal['student']->last_name }}, {{ $cal['student']->name }}</td>
                                @foreach($cal['desglose'] as $d)
                                    <td style="text-align: center;">
                                        <span style="font-weight: 600; color: {{ $d['nota'] >= 5 ? 'var(--success)' : ($d['nota'] > 0 ? 'var(--danger)' : 'var(--text-muted)') }};">
                                            {{ $d['nota'] > 0 ? $d['nota'] : '—' }}
                                        </span>
                                    </td>
                                @endforeach
                                <td style="text-align: center;">
                                    <span style="font-size: 1.5rem; font-weight: 700; color: {{ $cal['nota_final'] >= 5 ? 'var(--success)' : 'var(--danger)' }};">{{ $cal['nota_final'] }}</span>
                                </td>
                                <td>
                                    @if($cal['nota_final'] >= 5)<span class="badge badge-success">Aprobado</span>
                                    @else<span class="badge badge-danger">Suspenso</span>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($moduloSeleccionado)
        <div class="card"><p style="color: var(--text-muted); text-align: center; padding: 2rem;">No hay datos para calcular calificaciones. Configura los RA, CE y actividades del módulo.</p></div>
    @endif
@endsection
