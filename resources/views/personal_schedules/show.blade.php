@extends('layouts.app')

@php
    $isOwner = ($personal_schedule->user_id === Auth::id());
    $ownerName = $personal_schedule->user ? ($personal_schedule->user->name . ' ' . $personal_schedule->user->last_name) : 'Personal';
@endphp

@section('title', 'Horario de ' . $ownerName . ' - ' . ($personal_schedule->schoolYear ? $personal_schedule->schoolYear->name : 'General'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $isOwner ? 'Mi Horario' : 'Horario de ' . $ownerName }}</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">
                @if(!$isOwner)
                    Docente: <strong style="color: var(--text-heading);">{{ $ownerName }} ({{ $personal_schedule->user?->email }})</strong> &bull;
                @endif
                Curso Escolar: <strong>{{ $personal_schedule->schoolYear ? $personal_schedule->schoolYear->name : 'General' }}</strong> &bull; 
                Plantilla: <strong>{{ $template->name }}</strong>
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('personal-schedules.print', $personal_schedule) }}" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.2);">
                🖨️ Exportar PDF
            </a>
            <a href="{{ route('personal-schedules.export', [$personal_schedule, 'csv']) }}" class="btn btn-secondary btn-sm">
                ⬇ Exportar CSV
            </a>
            <a href="{{ route('personal-schedules.export', [$personal_schedule, 'json']) }}" class="btn btn-secondary btn-sm">
                ⬇ Exportar JSON
            </a>
            <button onclick="document.getElementById('modal-import-horario').classList.remove('hidden')" class="btn btn-secondary btn-sm">
                📥 Importar Horario
            </button>
            <a href="{{ (!empty($canManage) && !$isOwner) ? route('teacher-schedules.index', ['school_year_id' => $personal_schedule->school_year_id]) : route('personal-schedules.index') }}" class="btn btn-secondary">
                Volver
            </a>
            <a href="{{ route('personal-schedules.edit', $personal_schedule) }}" class="btn btn-primary">
                ✏️ Editar Horario
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @php
        $dayNames = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        $days = [];
        foreach (($template->active_days ?? [1, 2, 3, 4, 5]) as $dayId) {
            $days[$dayId] = $dayNames[$dayId] ?? $dayId;
        }

        $selections = $personal_schedule->selections->groupBy(function($item) {
            return $item->time_slot_id . '-' . $item->day;
        })->map->first();
    @endphp

    <div class="card" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 850px;">
            <thead>
                <tr>
                    <th style="padding: 1.25rem; width: 180px;">Tramo Horario</th>
                    @foreach($days as $dayId => $dayName)
                        @php
                            $isToday = (strtolower(trim($dayName)) === strtolower(trim($todaySpanish)));
                        @endphp
                        <th style="padding: 1.25rem; text-align: center; {{ $isToday ? 'background: var(--primary-light) !important; color: var(--primary) !important;' : '' }}">
                            {{ $dayName }}
                            @if($isToday)
                                <span style="display: block; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">(Hoy)</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($slots as $slot)
                    <tr>
                        <td style="padding: 1rem 1.25rem; vertical-align: middle;">
                            <div style="font-weight: 700; color: var(--text-heading);">{{ $slot->name }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                            </div>
                        </td>
                        @foreach($days as $dayId => $dayName)
                            @php
                                $key = $slot->id . '-' . $dayName;
                                $sel = $selections[$key] ?? null;
                                $isToday = (strtolower(trim($dayName)) === strtolower(trim($todaySpanish)));
                                
                                $type = $sel ? ($sel->type ?: ($sel->guardia_id ? 'guardia' : 'texto')) : null;
                            @endphp
                            <td style="padding: 0.75rem; text-align: center; vertical-align: middle; {{ $isToday ? 'background: var(--primary-light);' : '' }}">
                                @if($sel)
                                    @if($type === 'clase')
                                        <div style="padding: 0.6rem; background: var(--primary-light); border: 1px solid var(--primary-border); border-radius: 0.75rem;">
                                            <div style="font-weight: 800; font-size: 0.9rem; color: var(--primary);">
                                                📚 {{ $sel->subject ?: 'Clase' }}
                                            </div>
                                            @if($sel->group)
                                                <div style="margin-top: 0.3rem;">
                                                    <span class="badge badge-primary">
                                                        {{ $sel->group->course }} {{ $sel->group->name }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($type === 'guardia')
                                        <div style="padding: 0.6rem; background: var(--warning-light); border: 1px solid var(--warning-border); border-radius: 0.75rem;">
                                            <div style="font-weight: 800; font-size: 0.875rem; color: var(--warning);">
                                                🛡️ {{ $sel->guardia ? $sel->guardia->name : 'Guardia' }}
                                            </div>
                                        </div>
                                    @else
                                        <div style="padding: 0.6rem; background: var(--bg-hover); border: 1px solid var(--border); border-radius: 0.75rem;">
                                            <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-heading);">
                                                📝 {{ $sel->value }}
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted); opacity: 0.4;">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Import Modal -->
    <div id="modal-import-horario" class="hidden" style="position: fixed; inset: 0; background: rgba(0,0,0,0.65); backdrop-filter: blur(4px); z-index: 99; display: flex; align-items: center; justify-content: center; padding: 1rem;">
        <div class="card" style="width: 100%; max-width: 500px; margin: 0; box-shadow: var(--shadow-lg); padding: 1.5rem; background: var(--bg-card); border-radius: 0.75rem; border: 1px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--text-heading);">📥 Importar Horario</h3>
                <button onclick="document.getElementById('modal-import-horario').classList.add('hidden')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.2rem;">✕</button>
            </div>

            <form action="{{ route('personal-schedules.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="school_year_id" value="{{ $personal_schedule->school_year_id }}">
                <input type="hidden" name="schedule_template_id" value="{{ $personal_schedule->schedule_template_id }}">
                @if(!$isOwner)
                    <input type="hidden" name="user_id" value="{{ $personal_schedule->user_id }}">
                @endif

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="import_file" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-heading);">Archivo de Horario (CSV o JSON)</label>
                    <input type="file" name="file" id="import_file" accept=".csv,.json,.txt" required class="form-control" style="width: 100%;">
                </div>

                <div style="margin-bottom: 1.5rem; font-size: 0.8rem; color: var(--text-muted); background: var(--bg-hover); padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border);">
                    💡 Descargar plantillas de ejemplo:
                    <a href="{{ route('personal-schedules.template', 'csv') }}" style="font-weight: 700; color: var(--primary);">CSV</a> |
                    <a href="{{ route('personal-schedules.template', 'json') }}" style="font-weight: 700; color: var(--primary);">JSON</a>
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('modal-import-horario').classList.add('hidden')" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Comenzar Importación</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .hidden { display: none !important; }
    </style>
@endsection