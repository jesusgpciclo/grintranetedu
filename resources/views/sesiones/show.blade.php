@extends('layouts.app')
@section('title', 'Sesión ' . $sesion->fecha->format('d/m/Y'))
@section('content')
    <div class="page-header">
        <h1 class="page-title">Sesión del {{ $sesion->fecha->format('d/m/Y') }}</h1>
        <a href="{{ route('sesiones.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">← Volver</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    {{-- Info de la sesión --}}
    <div class="card">
        <div class="grid-3">
            <div><span class="stat-label">Módulo</span><div style="font-weight: 600;">{{ $sesion->modulo->nombre ?? '—' }}</div></div>
            <div><span class="stat-label">Grupo</span><div style="font-weight: 600;">{{ $sesion->group->name ?? '—' }}</div></div>
            <div><span class="stat-label">Profesor</span><div style="font-weight: 600;">{{ $sesion->profesor->name ?? '' }} {{ $sesion->profesor->last_name ?? '' }}</div></div>
        </div>
        @if($sesion->contenidos)<div style="margin-top: 1rem;"><span class="stat-label">Contenidos</span><p style="color: var(--text-muted);">{{ $sesion->contenidos }}</p></div>@endif
        @if($sesion->actividades_realizadas)<div style="margin-top: 0.75rem;"><span class="stat-label">Actividades</span><p style="color: var(--text-muted);">{{ $sesion->actividades_realizadas }}</p></div>@endif
        @if($sesion->tareas_mandadas)<div style="margin-top: 0.75rem;"><span class="stat-label">Tareas mandadas</span><p style="color: var(--text-muted);">{{ $sesion->tareas_mandadas }}</p></div>@endif
        @if($sesion->observaciones)<div style="margin-top: 0.75rem;"><span class="stat-label">Observaciones</span><p style="color: var(--text-muted);">{{ $sesion->observaciones }}</p></div>@endif
    </div>

    {{-- Lista de asistencia --}}
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
            <h2 style="font-size: 1.25rem; color: #fff; margin: 0;">Pasar Lista</h2>
            
            @if(!$students->isEmpty())
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                    <span style="font-size: 0.85rem; color: var(--text-muted); margin-right: 0.25rem;">Acciones rápidas:</span>
                    <button type="button" onclick="setAllAttendance('presente')" class="btn" style="background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); padding: 0.35rem 0.75rem; font-size: 0.85rem;">
                        ✅ Todos Presente
                    </button>
                    <button type="button" onclick="setAllAttendance('falta')" class="btn" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 0.35rem 0.75rem; font-size: 0.85rem;">
                        ❌ Todos Falta
                    </button>
                </div>
            @endif
        </div>

        @if($students->isEmpty())
            <div class="alert alert-info">No hay alumnos en este grupo. Asigna alumnos al grupo para poder pasar lista.</div>
        @else
            {{-- Panel de resumen en vivo --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem; background: rgba(255,255,255,0.02); padding: 0.85rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Total Alumnos</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #fff;" id="count-total">{{ $students->count() }}</div>
                </div>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #4ade80; font-weight: 600;">Presentes</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #4ade80;" id="count-presentes">0</div>
                </div>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #f87171; font-weight: 600;">Faltas</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #f87171;" id="count-faltas">0</div>
                </div>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: #fbbf24; font-weight: 600;">Retrasos</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #fbbf24;" id="count-retrasos">0</div>
                </div>
            </div>

            <form method="POST" action="{{ route('sesiones.asistencia', $sesion) }}">
                @csrf
                <div class="table-container">
                    <table>
                        <thead><tr><th>Alumno</th><th>Estado</th><th>Observación</th></tr></thead>
                        <tbody>
                            @foreach($students as $student)
                                @php $asist = $asistenciaMap[$student->id] ?? null; @endphp
                                <tr>
                                    <td style="font-weight: 600;">
                                        {{ $student->last_name }}, {{ $student->name }}
                                        <input type="hidden" name="asistencias[{{ $loop->index }}][user_id]" value="{{ $student->id }}">
                                    </td>
                                    <td>
                                        <select name="asistencias[{{ $loop->index }}][estado]" class="attendance-select" onchange="updateAttendanceSummary()" style="padding: 0.4rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.375rem; color: #fff;">
                                            <option value="presente" {{ ($asist->estado ?? 'presente') == 'presente' ? 'selected' : '' }}>Presente</option>
                                            <option value="falta" {{ ($asist->estado ?? '') == 'falta' ? 'selected' : '' }}>Falta</option>
                                            <option value="falta_justificada" {{ ($asist->estado ?? '') == 'falta_justificada' ? 'selected' : '' }}>Justificada</option>
                                            <option value="retraso" {{ ($asist->estado ?? '') == 'retraso' ? 'selected' : '' }}>Retraso</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="asistencias[{{ $loop->index }}][observacion]" value="{{ $asist->observacion ?? '' }}" placeholder="Observación..." style="width: 100%; padding: 0.4rem; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 0.375rem; color: #fff; font-size: 0.85rem;">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 1.25rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-weight: 600;">💾 Guardar Asistencia</button>
                </div>
            </form>
        @endif
    </div>

    <script>
        function updateAttendanceSummary() {
            const selects = document.querySelectorAll('.attendance-select');
            let presentes = 0, faltas = 0, retrasos = 0;

            selects.forEach(select => {
                if (select.value === 'presente') presentes++;
                else if (select.value === 'falta' || select.value === 'falta_justificada') faltas++;
                else if (select.value === 'retraso') retrasos++;
            });

            document.getElementById('count-presentes').textContent = presentes;
            document.getElementById('count-faltas').textContent = faltas;
            document.getElementById('count-retrasos').textContent = retrasos;
        }

        function setAllAttendance(status) {
            const selects = document.querySelectorAll('.attendance-select');
            selects.forEach(select => select.value = status);
            updateAttendanceSummary();
        }

        document.addEventListener('DOMContentLoaded', updateAttendanceSummary);
    </script>
@endsection
