@extends('layouts.app')
@section('title', 'Detalle Alumno')
@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $alumno->last_name }}, {{ $alumno->name }}</h1>
        <a href="{{ route('cuaderno.index') }}?modulo_id={{ $modulo->id ?? '' }}" class="btn" style="background: rgba(255,255,255,0.1);">← Volver</a>
    </div>
    @if($modulo)
    <div class="card"><h2 style="font-size: 1.15rem; color: var(--primary); margin-bottom: 0.5rem;">{{ $modulo->nombre }}</h2></div>

    {{-- Notas --}}
    <div class="card">
        <h3 style="font-size: 1rem; color: #fff; margin-bottom: 1rem;">Notas</h3>
        <div class="table-container">
            <table>
                <thead><tr><th>Actividad</th><th>Nota</th></tr></thead>
                <tbody>
                    @foreach($modulo->actividades as $act)
                        @php $nota = $act->notas->first(); @endphp
                        <tr><td>{{ $act->titulo }}</td><td>@if($nota)<span style="font-weight: 700; color: {{ $nota->valor >= 5 ? 'var(--success)' : 'var(--danger)' }};">{{ $nota->valor }}</span>@else — @endif</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Asistencia --}}
    <div class="card">
        <h3 style="font-size: 1rem; color: #fff; margin-bottom: 1rem;">Asistencia</h3>
        @if($asistencias->isEmpty())<p style="color: var(--text-muted);">Sin registros de asistencia.</p>
        @else
        <div class="table-container">
            <table>
                <thead><tr><th>Fecha</th><th>Estado</th><th>Observación</th></tr></thead>
                <tbody>
                    @foreach($asistencias as $a)
                        <tr>
                            <td>{{ $a->sesion->fecha->format('d/m/Y') }}</td>
                            <td>@if($a->estado == 'presente')<span class="badge badge-success">Presente</span>@elseif($a->estado == 'falta')<span class="badge badge-danger">Falta</span>@elseif($a->estado == 'falta_justificada')<span class="badge badge-info">Justificada</span>@else<span class="badge badge-warning">Retraso</span>@endif</td>
                            <td style="color: var(--text-muted);">{{ $a->observacion ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- Observaciones --}}
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1rem; color: #fff;">Observaciones</h3>
            <a href="{{ route('observaciones.create') }}?alumno_id={{ $alumno->id }}" class="btn btn-sm btn-primary">+ Nueva</a>
        </div>
        @forelse($observaciones as $obs)
            <div style="border-left: 3px solid {{ $obs->tipo == 'positiva' ? 'var(--success)' : ($obs->tipo == 'negativa' ? 'var(--danger)' : 'var(--primary)') }}; padding: 0.75rem 1rem; margin-bottom: 0.75rem; background: rgba(255,255,255,0.02); border-radius: 0 0.5rem 0.5rem 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                    <span class="badge {{ $obs->tipo == 'positiva' ? 'badge-success' : ($obs->tipo == 'negativa' ? 'badge-danger' : 'badge-info') }}">{{ ucfirst($obs->tipo) }}</span>
                    <span style="color: var(--text-muted); font-size: 0.8rem;">{{ $obs->fecha->format('d/m/Y') }} — {{ $obs->profesor->name ?? '' }}</span>
                </div>
                <p style="color: var(--text-muted); font-size: 0.9rem;">{{ $obs->descripcion }}</p>
            </div>
        @empty
            <p style="color: var(--text-muted);">Sin observaciones registradas.</p>
        @endforelse
    </div>
@endsection
