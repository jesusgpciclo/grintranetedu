@extends('layouts.app')

@section('title', 'Editar Ausencia')

@section('content')
<div class="editar-ausencia-container max-w-4xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('ausencias.index', ['date' => $ausencia->fecha->format('Y-m-d')]) }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-white bg-slate-800/60 hover:bg-slate-800 px-3.5 py-2 rounded-xl border border-slate-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            <span>Volver a Ausencias</span>
        </a>
    </div>

    <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        
        <div class="flex items-center gap-3.5 mb-6 pb-5 border-b border-slate-800">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Modificar Ausencia</h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">Docente: {{ $ausencia->user->name ?? '' }} • {{ $ausencia->fecha->format('d/m/Y') }}</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-2xl text-xs space-y-1">
                <div class="font-bold">Por favor corrige los siguientes errores:</div>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ausencias.update', $ausencia) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fecha" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                        📅 Fecha de la Ausencia <span class="text-rose-400">*</span>
                    </label>
                    <input type="date" name="fecha" id="fecha" value="{{ old('fecha', $ausencia->fecha->format('Y-m-d')) }}" required
                           class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm font-medium focus:ring-2 focus:ring-sky-500 outline-none">
                </div>

                <div>
                    <label for="time_slot_id" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                        ⏰ Tramo Horario <span class="text-rose-400">*</span>
                    </label>
                    <select name="time_slot_id" id="time_slot_id" required
                            class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        @foreach($tramos as $tramo)
                            <option value="{{ $tramo->id }}" {{ old('time_slot_id', $ausencia->time_slot_id) == $tramo->id ? 'selected' : '' }}>
                                {{ $tramo->name }} ({{ \Carbon\Carbon::parse($tramo->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($tramo->end_time)->format('H:i') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="group_id" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                        👥 Grupo / Clase
                    </label>
                    <select name="group_id" id="group_id" class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">(Ninguno / Guardia)</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}" {{ old('group_id', $ausencia->group_id) == $grupo->id ? 'selected' : '' }}>
                                {{ $grupo->course }} - {{ $grupo->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="zona_id" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                        📍 Aula / Zona
                    </label>
                    <select name="zona_id" id="zona_id" class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                        <option value="">(Ninguna / Guardia)</option>
                        @foreach($zonas as $zona)
                            <option value="{{ $zona->id }}" {{ old('zona_id', $ausencia->zona_id) == $zona->id ? 'selected' : '' }}>
                                {{ $zona->nombre }}
                                @if($zona->identificacion) [{{ $zona->identificacion }}] @endif
                                @if($zona->ubicacion) ({{ $zona->ubicacion }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="tarea" class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                        📝 Asignación Pedagógica de Tareas <span class="text-rose-400">*</span>
                    </label>
                    <textarea name="tarea" id="tarea" rows="3" required
                              class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-4 text-sm leading-relaxed focus:ring-2 focus:ring-sky-500 outline-none">{{ old('tarea', $ausencia->tarea) }}</textarea>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                        <label for="enlace_tarea" class="form-label" style="margin-bottom: 0; font-size: 0.75rem;">
                            🔗 Enlace a Material Online (Google Classroom / Moodle / Drive)
                        </label>
                        <button type="button" onclick="openDocModal()" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.65rem; gap: 0.35rem;">
                            📂 Adjuntar Documento del Gestor
                        </button>
                    </div>
                    <input type="url" name="enlace_tarea" id="enlace_tarea" value="{{ old('enlace_tarea', $ausencia->enlace_tarea) }}"
                           placeholder="https://..."
                           class="w-full bg-slate-950 border border-slate-700 text-white rounded-2xl p-3.5 text-sm focus:ring-2 focus:ring-sky-500 outline-none">
                </div>
            </div>

            @if($isDirectiva)
                <div class="p-4 bg-slate-950/60 rounded-2xl border border-slate-800 flex items-center gap-3">
                    <input type="checkbox" name="justificada" id="justificada" value="1" {{ old('justificada', $ausencia->justificada) ? 'checked' : '' }}
                           class="w-5 h-5 text-emerald-500 bg-slate-900 border-slate-700 rounded focus:ring-emerald-500">
                    <label for="justificada" class="text-xs font-bold uppercase text-slate-300 cursor-pointer">
                        ✓ Documento justificativo entregado y validado
                    </label>
                </div>
            @endif

            <div class="pt-4 border-t border-slate-800 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white font-black text-sm rounded-2xl shadow-xl shadow-sky-500/25 transition active:scale-95">
                    Actualizar Ausencia
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Modal Gestor Documental -->
<div id="docModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 1.25rem; width: 100%; max-width: 580px; box-shadow: var(--shadow-lg); overflow: hidden; display: flex; flex-direction: column; max-height: 85vh;">
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
    function openDocModal() {
        document.getElementById('docModal').style.display = 'flex';
        document.getElementById('docSearchInput').value = '';
        searchDocs('');
    }

    function closeDocModal() {
        document.getElementById('docModal').style.display = 'none';
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

                    item.querySelector('button').onclick = () => {
                        document.getElementById('enlace_tarea').value = doc.url;
                        const tareaInput = document.getElementById('tarea');
                        const note = `\n📎 Documento: ${doc.titulo}`;
                        if (!tareaInput.value.includes(note)) {
                            tareaInput.value = (tareaInput.value.trim() ? tareaInput.value.trim() + note : note.trim());
                        }
                        closeDocModal();
                    };
                    container.appendChild(item);
                });
            })
            .catch(err => {
                container.innerHTML = '<div style="text-align: center; color: var(--danger); padding: 1rem; font-size: 0.85rem;">Error al cargar documentos.</div>';
            });
    }

    document.getElementById('docModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDocModal();
        }
    });
</script>
@endsection
