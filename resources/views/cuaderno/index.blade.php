@extends('layouts.app')
@section('title', 'Cuaderno de Clase')
@section('content')
    <div class="page-header"><h1 class="page-title">Cuaderno de Clase</h1></div>
    <div class="card" style="padding: 1rem;">
        <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0; min-width: 300px;">
                <label>Módulo</label>
                <select name="modulo_id" onchange="this.form.submit()"><option value="">Seleccionar módulo...</option>@foreach($modulos as $m)<option value="{{ $m->id }}" {{ request('modulo_id') == $m->id ? 'selected' : '' }}>{{ $m->nombre }} {{ $m->group ? '(' . $m->group->name . ')' : '' }}</option>@endforeach</select>
            </div>
        </form>
    </div>
    @if($moduloSeleccionado && $alumnos->isNotEmpty())
        <div class="card">
            <h2 style="font-size: 1.15rem; color: #fff; margin-bottom: 1rem;">{{ $moduloSeleccionado->nombre }} — {{ $moduloSeleccionado->group->name ?? '' }}</h2>
            <div class="table-container">
                <table>
                    <thead><tr><th>Alumno</th><th style="text-align: center;">Sesiones</th><th style="text-align: center;">Faltas</th><th style="text-align: center;">% Absentismo</th><th style="text-align: center;">Retrasos</th><th style="text-align: center;">Nota Media</th><th style="text-align: center;">Observaciones</th><th></th></tr></thead>
                    <tbody>
                        @foreach($alumnos as $alumno)
                            @php $r = $resumen[$alumno->id] ?? []; @endphp
                            <tr>
                                <td style="font-weight: 600;">
                                    {{ $alumno->last_name }}, {{ $alumno->name }}
                                </td>
                                <td style="text-align: center;">{{ $r['total_sesiones'] ?? 0 }}</td>
                                <td style="text-align: center;"><span class="badge {{ ($r['faltas'] ?? 0) > 0 ? 'badge-danger' : 'badge-success' }}">{{ $r['faltas'] ?? 0 }}</span></td>
                                <td style="text-align: center;">
                                    @if(!empty($r['alerta_absentismo']))
                                        <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4);" title="Riesgo de pérdida de evaluación continua por superar el 15% de faltas">
                                            ⚠️ {{ $r['porcentaje_faltas'] }}%
                                        </span>
                                    @else
                                        <span style="font-size: 0.85rem; color: var(--text-muted);">{{ $r['porcentaje_faltas'] ?? 0 }}%</span>
                                    @endif
                                </td>
                                <td style="text-align: center;"><span class="badge {{ ($r['retrasos'] ?? 0) > 0 ? 'badge-warning' : 'badge-success' }}">{{ $r['retrasos'] ?? 0 }}</span></td>
                                <td style="text-align: center;">
                                    @if(($r['nota_media'] ?? null) !== null)
                                        <span style="font-weight: 700; color: {{ $r['nota_media'] >= 5 ? 'var(--success)' : 'var(--danger)' }};">{{ $r['nota_media'] }}</span>
                                    @else — @endif
                                </td>
                                <td style="text-align: center;"><span class="badge badge-info">{{ $r['observaciones'] ?? 0 }}</span></td>
                                <td>
                                    <a href="{{ route('cuaderno.alumno', $alumno) }}?modulo_id={{ $moduloSeleccionado->id }}" class="btn btn-sm" style="background: rgba(56,189,248,0.1); color: var(--primary);">Detalle</a>
                                    <a href="{{ route('observaciones.create') }}?alumno_id={{ $alumno->id }}" class="btn btn-sm btn-warning" style="margin-left: 0.25rem;">+ Obs.</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($moduloSeleccionado)
        <div class="card"><p style="color: var(--text-muted); text-align: center; padding: 2rem;">No hay alumnos en este grupo.</p></div>
    @endif
@endsection
