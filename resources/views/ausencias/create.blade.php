@extends('layouts.app')

@section('title', 'Notificar Nueva Ausencia')

@section('content')
<style>
    .ausencia-form-container {
        max-width: 1000px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .slot-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.85rem 0.5rem;
        border-radius: 0.875rem;
        border: 1px solid var(--border);
        background: var(--bg-input);
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        user-select: none;
    }

    .slot-pill:hover {
        border-color: var(--primary-border);
        background: var(--bg-hover);
    }

    .slot-pill.selected {
        border-color: var(--primary);
        background: var(--primary-light);
        box-shadow: 0 0 0 1px var(--primary);
    }

    .slot-pill.selected .slot-name {
        color: var(--primary);
        font-weight: 800;
    }

    .slot-detail-card {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        transition: all 0.2s ease;
        animation: fadeIn 0.25s ease-out;
    }

    .slot-detail-card.is-guardia {
        border-color: rgba(168, 85, 247, 0.35);
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.05), var(--bg-surface));
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Modal Gestor Documental */
    .doc-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .doc-modal-content {
        background: var(--bg-surface);
        border: 1px solid var(--border);
        border-radius: 1.25rem;
        width: 100%;
        max-width: 580px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 85vh;
    }
</style>

<div class="ausencia-form-container">
    
    <!-- Top Bar -->
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <a href="{{ route('ausencias.index', ['date' => $selectedDate]) }}" class="btn btn-secondary btn-sm">
            &larr; Volver al parte de ausencias
        </a>
    </div>

    <!-- Main Card Form -->
    <div class="card" style="padding: 1.75rem 2rem; margin-bottom: 0;">
        
        <!-- Header -->
        <div style="display: flex; align-items: center; gap: 1rem; padding-bottom: 1.25rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, var(--primary), #4f46e5); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; box-shadow: 0 4px 12px var(--primary-light);">
                📋
            </div>
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--text-heading);">Notificar Ausencia</h1>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0.15rem 0 0 0;">
                    Selecciona los tramos horarios y detalla las actividades o tareas para el profesorado de guardia.
                </p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ausencias.store') }}" method="POST" id="notificarAusenciaForm" style="display: flex; flex-direction: column; gap: 1.5rem;">
            @csrf

            <!-- Teacher selector (if Admin/Directiva) -->
            @if($isDirectiva && $docentes->isNotEmpty())
                <div style="padding: 1rem 1.25rem; background: rgba(168, 85, 247, 0.08); border: 1px solid rgba(168, 85, 247, 0.25); border-radius: 0.875rem;">
                    <label for="user_id" class="form-label" style="color: #c084fc; font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">
                        👤 Registrar en nombre de otro docente (Opcional - Directiva):
                    </label>
                    <select name="user_id" id="user_id" class="form-control" style="background: var(--bg-surface);">
                        <option value="{{ Auth::id() }}">Yo mismo ({{ Auth::user()->name }})</option>
                        @foreach($docentes as $docente)
                            @if($docente->id !== Auth::id())
                                <option value="{{ $docente->id }}" {{ old('user_id') == $docente->id ? 'selected' : '' }}>
                                    {{ $docente->name }} {{ $docente->last_name ?? '' }} ({{ $docente->email }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Fecha de la ausencia -->
            <div class="form-group" style="margin-bottom: 0;">
                <label for="fecha" class="form-label" style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">
                    📅 Fecha de la Ausencia <span style="color: var(--danger);">*</span>
                </label>
                <input type="date" name="fecha" id="fecha" value="{{ old('fecha', $selectedDate) }}" required class="form-control" style="max-width: 320px; font-weight: 600; cursor: pointer;">
            </div>

            <!-- Selector de Tramos Horarios Afectados -->
            <div>
                <label class="form-label" style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem; display: block;">
                    ⏰ Tramos Horarios Afectados <span style="color: var(--danger);">* (Pulsa para seleccionar o deseleccionar)</span>
                </label>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.65rem;">
                    @foreach($tramos as $tramo)
                        @php
                            $isPreSelected = (old('time_slot_ids') && in_array($tramo->id, old('time_slot_ids'))) || ($selectedTimeSlot == $tramo->id);
                        @endphp
                        <label class="slot-pill {{ $isPreSelected ? 'selected' : '' }}" id="pill-slot-{{ $tramo->id }}">
                            <input type="checkbox" name="time_slot_ids[]" value="{{ $tramo->id }}" 
                                   class="sr-only" {{ $isPreSelected ? 'checked' : '' }}
                                   onchange="toggleSlotCard({{ $tramo->id }}, this.checked)">
                            <span class="slot-name" style="font-size: 0.85rem; font-weight: 700; color: var(--text-heading);">
                                {{ $tramo->name }}
                            </span>
                            <span style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.15rem;">
                                {{ \Carbon\Carbon::parse($tramo->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($tramo->end_time)->format('H:i') }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Dynamic Slot Details Container -->
            <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                    Detalle de Tareas y Aulas por Tramo Horario
                </div>

                <div id="slotsDetailContainer" style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($tramos as $tramo)
                        @php
                            $isPreSelected = (old('time_slot_ids') && in_array($tramo->id, old('time_slot_ids'))) || ($selectedTimeSlot == $tramo->id);
                        @endphp
                        <div class="slot-detail-card" id="card-slot-{{ $tramo->id }}" style="{{ $isPreSelected ? '' : 'display: none;' }}">
                            
                            <!-- Card Header -->
                            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.6rem;">
                                    <span style="font-size: 1rem; font-weight: 800; color: var(--text-heading);">
                                        {{ $tramo->name }}
                                    </span>
                                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); background: var(--bg-hover); padding: 0.15rem 0.5rem; border-radius: 0.35rem;">
                                        {{ \Carbon\Carbon::parse($tramo->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($tramo->end_time)->format('H:i') }}
                                    </span>
                                </div>

                                <!-- Checkbox Es Guardia -->
                                <label style="display: inline-flex; align-items: center; gap: 0.4rem; cursor: pointer; user-select: none; font-size: 0.8rem; font-weight: 700; color: #a855f7; background: rgba(168, 85, 247, 0.1); padding: 0.3rem 0.65rem; border-radius: 0.5rem; border: 1px solid rgba(168, 85, 247, 0.25);">
                                    <input type="checkbox" name="slots[{{ $tramo->id }}][es_guardia]" value="1" 
                                           id="guardia_toggle_{{ $tramo->id }}"
                                           onchange="toggleSlotGuardia({{ $tramo->id }}, this.checked)">
                                    <span>🛡️ Es mi hora de Guardia</span>
                                </label>
                            </div>

                            <!-- Grupo y Aula (Fila 1) -->
                            <div id="group_zone_row_{{ $tramo->id }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                                <div>
                                    <label class="form-label" style="font-size: 0.75rem;">
                                        👥 Grupo / Clase <span style="color: var(--danger);">*</span>
                                    </label>
                                    <select name="slots[{{ $tramo->id }}][group_id]" id="group_select_{{ $tramo->id }}" class="form-control">
                                        <option value="">Selecciona un grupo</option>
                                        @foreach($grupos as $grupo)
                                            <option value="{{ $grupo->id }}">{{ $grupo->course }} - {{ $grupo->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label" style="font-size: 0.75rem;">
                                        📍 Aula / Zona <span style="color: var(--danger);">*</span>
                                    </label>
                                    <select name="slots[{{ $tramo->id }}][zona_id]" id="zona_select_{{ $tramo->id }}" class="form-control">
                                        <option value="">Selecciona un aula / espacio</option>
                                        @foreach($zonas as $zona)
                                            <option value="{{ $zona->id }}">
                                                {{ $zona->nombre }}
                                                @if($zona->identificacion) [{{ $zona->identificacion }}] @endif
                                                @if($zona->ubicacion) ({{ $zona->ubicacion }}) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div id="guardia_notice_{{ $tramo->id }}" style="display: none; padding: 0.65rem 0.85rem; border-radius: 0.5rem; background: rgba(168, 85, 247, 0.08); border: 1px solid rgba(168, 85, 247, 0.2); font-size: 0.8rem; color: #c084fc;">
                                🛡️ Tramo marcado como turno de guardia. No requiere selección de grupo ni aula.
                            </div>

                            <!-- Tareas Pedagógicas -->
                            <div>
                                <label class="form-label" style="font-size: 0.75rem;">
                                    📝 Tareas Pedagógicas para el Alumnado / Profesor de Guardia <span style="color: var(--danger);">*</span>
                                </label>
                                <textarea name="slots[{{ $tramo->id }}][tarea]" id="tarea_input_{{ $tramo->id }}" rows="2" class="form-control"
                                          placeholder="Detalla las instrucciones, páginas del libro o actividades a realizar durante esta hora..."></textarea>
                            </div>

                            <!-- Enlace Online y Botón Gestor Documental -->
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <label class="form-label" style="margin-bottom: 0; font-size: 0.75rem;">
                                        🔗 Enlace a Material Online / Documento
                                    </label>
                                    <button type="button" onclick="openDocModal({{ $tramo->id }})" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.65rem; gap: 0.35rem;">
                                        📂 Adjuntar Documento del Gestor
                                    </button>
                                </div>
                                <input type="url" name="slots[{{ $tramo->id }}][enlace_tarea]" id="enlace_input_{{ $tramo->id }}" class="form-control"
                                       placeholder="https://classroom.google.com/... o selecciona un documento de arriba">
                            </div>

                        </div>
                    @endforeach
                </div>

                <div id="noSlotSelectedMsg" style="{{ $selectedTimeSlot || old('time_slot_ids') ? 'display: none;' : '' }} padding: 2rem; border: 1px dashed var(--border); border-radius: 0.875rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
                    Selecciona al menos un tramo horario arriba para configurar los grupos y las tareas pedagógicas.
                </div>
            </div>

            <!-- Submit Button -->
            <div style="display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 0.95rem; font-weight: 800; box-shadow: 0 4px 16px var(--primary-light);">
                    ✓ REGISTRAR Y PUBLICAR AUSENCIA(S)
                </button>
            </div>

        </form>
    </div>

</div>

<!-- Modal Gestor Documental -->
<div id="docModal" class="doc-modal-backdrop" style="display: none;">
    <div class="doc-modal-content">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.25rem;">📂</span>
                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: var(--text-heading);">Gestor Documental</h3>
            </div>
            <button type="button" onclick="closeDocModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">
                &times;
            </button>
        </div>

        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);">
            <input type="text" id="docSearchInput" placeholder="Buscar documento por título o descripción..." 
                   class="form-control" oninput="searchDocs(this.value)">
        </div>

        <div id="docListContainer" style="padding: 1rem 1.5rem; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; min-height: 200px; max-height: 360px;">
            <!-- Rendered dynamically -->
        </div>

        <div style="padding: 0.75rem 1.5rem; background: var(--bg-hover); border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeDocModal()" class="btn btn-secondary btn-sm">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
    let activeSlotIdForDoc = null;

    function toggleSlotCard(slotId, isChecked) {
        const pill = document.getElementById('pill-slot-' + slotId);
        const card = document.getElementById('card-slot-' + slotId);
        
        if (isChecked) {
            pill.classList.add('selected');
            card.style.display = 'flex';
        } else {
            pill.classList.remove('selected');
            card.style.display = 'none';
        }

        // Check if any slot is selected
        const anySelected = document.querySelectorAll('.slot-pill input[type="checkbox"]:checked').length > 0;
        document.getElementById('noSlotSelectedMsg').style.display = anySelected ? 'none' : 'block';
    }

    function toggleSlotGuardia(slotId, isGuardia) {
        const card = document.getElementById('card-slot-' + slotId);
        const groupZoneRow = document.getElementById('group_zone_row_' + slotId);
        const notice = document.getElementById('guardia_notice_' + slotId);
        const tareaInput = document.getElementById('tarea_input_' + slotId);

        if (isGuardia) {
            card.classList.add('is-guardia');
            groupZoneRow.style.display = 'none';
            notice.style.display = 'block';
            if (!tareaInput.value.trim()) {
                tareaInput.value = 'Ausencia durante turno de guardia asignado.';
            }
        } else {
            card.classList.remove('is-guardia');
            groupZoneRow.style.display = 'grid';
            notice.style.display = 'none';
            if (tareaInput.value === 'Ausencia durante turno de guardia asignado.') {
                tareaInput.value = '';
            }
        }
    }

    function openDocModal(slotId) {
        activeSlotIdForDoc = slotId;
        document.getElementById('docModal').style.display = 'flex';
        document.getElementById('docSearchInput').value = '';
        searchDocs('');
    }

    function closeDocModal() {
        document.getElementById('docModal').style.display = 'none';
        activeSlotIdForDoc = null;
    }

    function searchDocs(query) {
        const container = document.getElementById('docListContainer');
        container.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1.5rem; font-size: 0.85rem;">Buscando documentos...</div>';

        fetch('/api/documentos-list?search=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 1.5rem; font-size: 0.85rem;">No se encontraron documentos en el Gestor Documental.</div>';
                    return;
                }

                container.innerHTML = '';
                data.forEach(doc => {
                    const item = document.createElement('div');
                    item.style = 'display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 0.75rem; background: var(--bg-card);';
                    item.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 0.65rem; overflow: hidden;">
                            <span style="font-size: 1.15rem;">📄</span>
                            <div style="overflow: hidden;">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-heading); text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                    ${doc.titulo}
                                </div>
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                    ${doc.url}
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.6rem; shrink-0: 0;">
                            Adjuntar
                        </button>
                    `;

                    item.querySelector('button').onclick = () => attachDocToSlot(doc);
                    container.appendChild(item);
                });
            })
            .catch(err => {
                container.innerHTML = '<div style="text-align: center; color: var(--danger); padding: 1rem; font-size: 0.85rem;">Error al cargar documentos.</div>';
            });
    }

    function attachDocToSlot(doc) {
        if (activeSlotIdForDoc) {
            const enlaceInput = document.getElementById('enlace_input_' + activeSlotIdForDoc);
            const tareaInput = document.getElementById('tarea_input_' + activeSlotIdForDoc);

            if (enlaceInput) {
                enlaceInput.value = doc.url;
            }

            if (tareaInput) {
                const note = `\n📎 Documento: ${doc.titulo}`;
                if (!tareaInput.value.includes(note)) {
                    tareaInput.value = (tareaInput.value.trim() ? tareaInput.value.trim() + note : note.trim());
                }
            }
        }
        closeDocModal();
    }

    // Dismiss modal on backdrop click
    document.getElementById('docModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDocModal();
        }
    });
</script>
@endsection