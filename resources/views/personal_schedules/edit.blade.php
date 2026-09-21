@extends('layouts.app')

@php
    $isOwner = ($personal_schedule->user_id === Auth::id());
    $ownerName = $personal_schedule->user ? ($personal_schedule->user->name . ' ' . $personal_schedule->user->last_name) : 'Personal';
    $backUrl = route('personal-schedules.show', $personal_schedule);
@endphp

@section('title', 'Configurar Horario - ' . $ownerName)

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $isOwner ? 'Configurar Mi Horario' : 'Configurar Horario de ' . $ownerName }}</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">
                @if(!$isOwner)
                    Docente: <strong style="color: var(--text-heading);">{{ $ownerName }} ({{ $personal_schedule->user?->email }})</strong> &bull;
                @endif
                Curso: <strong>{{ $personal_schedule->schoolYear ? $personal_schedule->schoolYear->name : 'General' }}</strong> &bull; 
                Plantilla: <strong>{{ $template->name }}</strong>
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('personal-schedules.print', $personal_schedule) }}" target="_blank" class="btn btn-secondary btn-sm" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.2);">
                🖨️ PDF
            </a>
            <a href="{{ route('personal-schedules.export', [$personal_schedule, 'csv']) }}" class="btn btn-secondary btn-sm">
                ⬇ Exportar CSV
            </a>
            <a href="{{ $backUrl }}" class="btn btn-secondary">
                Volver
            </a>
            <button type="button" onclick="saveSchedule()" class="btn btn-primary">
                💾 Guardar Cambios
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="padding: 0; overflow-x: auto;">
        <form id="scheduleForm" action="{{ route('personal-schedules.update', $personal_schedule) }}" method="POST">
            @csrf
            @method('PUT')

            <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
                <thead>
                    <tr>
                        <th style="padding: 1.25rem; width: 180px;">Tramo Horario</th>
                        @foreach($days as $dayId => $dayName)
                            <th style="padding: 1.25rem; text-align: center;">{{ $dayName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($slots as $slot)
                        <tr>
                            <td style="padding: 1rem 1.25rem; vertical-align: top;">
                                <div style="font-weight: 700; color: var(--text-heading);">{{ $slot->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                </div>
                            </td>
                            @foreach($days as $dayId => $dayName)
                                @php
                                    $key = $slot->id . '-' . $dayName;
                                    $sel = $selections[$key] ?? null;
                                    
                                    // Determine type
                                    $currentType = 'none';
                                    if ($sel) {
                                        if ($sel->type) {
                                            $currentType = $sel->type;
                                        } elseif ($sel->guardia_id) {
                                            $currentType = 'guardia';
                                        } elseif ($sel->value) {
                                            $currentType = 'texto';
                                        }
                                    }
                                @endphp
                                <td style="padding: 0.6rem; vertical-align: top;">
                                    <div class="cell-box" id="box-{{ $slot->id }}-{{ $dayName }}"
                                         style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 0.75rem; padding: 0.6rem; transition: all 0.2s ease;">
                                        
                                        <!-- Type Selector -->
                                        <div style="margin-bottom: 0.5rem;">
                                            <select name="selections[{{ $slot->id }}][{{ $dayName }}][type]"
                                                    class="form-control type-select"
                                                    data-slot="{{ $slot->id }}"
                                                    data-day="{{ $dayName }}"
                                                    style="font-size: 0.8rem; padding: 0.35rem 0.5rem; font-weight: 700;"
                                                    onchange="handleTypeChange(this)">
                                                <option value="none" {{ $currentType === 'none' ? 'selected' : '' }}>-- Vacío --</option>
                                                <option value="clase" {{ $currentType === 'clase' ? 'selected' : '' }}>📚 Clase con grupo</option>
                                                <option value="guardia" {{ $currentType === 'guardia' ? 'selected' : '' }}>🛡️ Guardia</option>
                                                <option value="texto" {{ $currentType === 'texto' ? 'selected' : '' }}>📝 Texto libre</option>
                                            </select>
                                        </div>

                                        <!-- Panel: CLASE -->
                                        <div class="panel-clase {{ $currentType === 'clase' ? '' : 'hidden' }}"
                                             style="display: flex; flex-direction: column; gap: 0.4rem;">
                                            <input type="text"
                                                   name="selections[{{ $slot->id }}][{{ $dayName }}][subject]"
                                                   value="{{ $sel ? $sel->subject : '' }}"
                                                   placeholder="Asignatura (ej: Mates)"
                                                   style="font-size: 0.8rem; padding: 0.35rem 0.5rem;"
                                                   class="form-control">
                                            
                                            <select name="selections[{{ $slot->id }}][{{ $dayName }}][group_id]"
                                                    style="font-size: 0.8rem; padding: 0.35rem 0.5rem;"
                                                    class="form-control">
                                                <option value="">-- Grupo --</option>
                                                @foreach($groups as $grp)
                                                    <option value="{{ $grp->id }}" {{ ($sel && $sel->group_id == $grp->id) ? 'selected' : '' }}>
                                                        {{ $grp->course }} {{ $grp->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Panel: GUARDIA (sin opciones adicionales) -->
                                        <div class="panel-guardia {{ $currentType === 'guardia' ? '' : 'hidden' }}">
                                            <!-- Vacío: no se muestra nada más cuando se elige Guardia -->
                                        </div>

                                        <!-- Panel: TEXTO -->
                                        <div class="panel-texto {{ $currentType === 'texto' ? '' : 'hidden' }}">
                                            <input type="text"
                                                   name="selections[{{ $slot->id }}][{{ $dayName }}][text_value]"
                                                   value="{{ $sel ? $sel->value : '' }}"
                                                   placeholder="ej: Tutoría, Reunión"
                                                   style="font-size: 0.8rem; padding: 0.35rem 0.5rem;"
                                                   class="form-control">
                                        </div>

                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    </div>

    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem;">
        <a href="{{ route('personal-schedules.index') }}" class="btn btn-secondary">Cancelar</a>
        <button type="button" onclick="saveSchedule()" class="btn btn-primary" style="padding: 0.85rem 2rem;">
            💾 Guardar Mi Horario
        </button>
    </div>

    <script>
        function handleTypeChange(selectEl) {
            const container = selectEl.closest('.cell-box');
            const val = selectEl.value;

            const panelClase = container.querySelector('.panel-clase');
            const panelGuardia = container.querySelector('.panel-guardia');
            const panelTexto = container.querySelector('.panel-texto');

            if (panelClase) panelClase.classList.add('hidden');
            if (panelGuardia) panelGuardia.classList.add('hidden');
            if (panelTexto) panelTexto.classList.add('hidden');

            if (val === 'clase' && panelClase) panelClase.classList.remove('hidden');
            if (val === 'guardia' && panelGuardia) panelGuardia.classList.remove('hidden');
            if (val === 'texto' && panelTexto) panelTexto.classList.remove('hidden');

            // Highlight container depending on type
            if (val === 'clase') {
                container.style.borderColor = 'var(--primary)';
                container.style.background = 'var(--primary-light)';
            } else if (val === 'guardia') {
                container.style.borderColor = 'var(--warning)';
                container.style.background = 'var(--warning-light)';
            } else if (val === 'texto') {
                container.style.borderColor = 'var(--border)';
                container.style.background = 'var(--bg-hover)';
            } else {
                container.style.borderColor = 'var(--border)';
                container.style.background = 'var(--bg-surface)';
            }
        }

        // Initialize background colors on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.type-select').forEach(select => {
                handleTypeChange(select);
            });
        });

        function saveSchedule() {
            const form = document.getElementById('scheduleForm');
            const formData = new FormData(form);

            const btn = event.target;
            const originalText = btn.innerText;
            btn.innerText = 'Guardando...';
            btn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "{{ $backUrl }}";
                } else {
                    alert('Error: ' + (data.message || 'No se pudo guardar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ocurrió un error al guardar.');
            })
            .finally(() => {
                btn.innerText = originalText;
                btn.disabled = false;
            });
        }
    </script>
    <style>
        .hidden { display: none !important; }
    </style>
@endsection