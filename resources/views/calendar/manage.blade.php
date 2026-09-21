@extends('layouts.app')

@section('title', 'Gestionar Calendarios')

@section('content')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 class="page-title">Gestionar Calendarios</h1>
        <div style="color:var(--text-muted);">Crea y administra calendarios escolares</div>
    </div>
    <div style="display:flex; gap:0.75rem;">
        <a href="{{ route('calendar.index') }}" class="btn" style="background:rgba(255,255,255,0.05); color:#fff;">
            &#x2190; Ver Calendario
        </a>
        <button onclick="openImportIcsModal()" class="btn" style="background:rgba(56,189,248,0.15); color:var(--primary); border:1px solid rgba(56,189,248,0.3);">
            &#x1F4E5; Importar .ics
        </button>
        <button onclick="document.getElementById('createModal').style.display='flex'" class="btn btn-primary">
            + Nuevo Calendario
        </button>
    </div>
</div>

@if(session('success'))
    <div style="background:rgba(34,197,94,0.1); color:var(--success); border:1px solid rgba(34,197,94,0.2); padding:1rem; border-radius:0.5rem; margin-bottom:1.5rem;">
        &#x2713; {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

{{-- Base calendar highlight --}}
@if($baseCalendar)
<div class="card" style="border:1px solid rgba(56,189,248,0.3); background:rgba(56,189,248,0.04); margin-bottom:1.5rem;">
    <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
        <span style="width:16px; height:16px; border-radius:50%; background:{{ $baseCalendar->color }}; flex-shrink:0;"></span>
        <div style="flex:1;">
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                <span style="font-size:1rem; font-weight:700; color:#fff;">{{ $baseCalendar->name }}</span>
                <span class="badge badge-role">Calendario Base</span>
            </div>
            @if($baseCalendar->description)
                <div style="color:var(--text-muted); font-size:0.85rem;">{{ $baseCalendar->description }}</div>
            @endif
            <div style="color:var(--text-muted); font-size:0.8rem; margin-top:0.25rem;">
                {{ $baseCalendar->events_count }} eventos &bull; Creado {{ $baseCalendar->created_at->locale('es')->diffForHumans() }}
                @if($baseCalendar->start_date && $baseCalendar->end_date)
                    &bull; {{ $baseCalendar->start_date->locale('es')->isoFormat('D MMM YYYY') }} - {{ $baseCalendar->end_date->locale('es')->isoFormat('D MMM YYYY') }}
                @endif
            </div>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <a href="{{ route('calendar.index', ['calendar_id' => $baseCalendar->id]) }}" class="btn"
                style="background:rgba(56,189,248,0.1); color:var(--primary); font-size:0.85rem; padding:0.4rem 0.8rem;">Ver</a>
            <button onclick="openEditModal({{ json_encode(['id'=>$baseCalendar->id,'name'=>$baseCalendar->name,'description'=>$baseCalendar->description,'color'=>$baseCalendar->color,'start_date'=>$baseCalendar->start_date,'end_date'=>$baseCalendar->end_date,'school_year_id'=>$baseCalendar->school_year_id]) }})"
                class="btn" style="background:rgba(255,255,255,0.05); color:#fff; font-size:0.85rem; padding:0.4rem 0.8rem;">Editar</button>
        </div>
    </div>
</div>
@endif

{{-- Derived calendars --}}
@php $derived = $calendars->where('is_base', false); @endphp
@if($derived->isNotEmpty())
<div class="card">
    <h2 style="font-size:1.1rem; font-weight:600; color:#fff; margin-bottom:1rem;">Calendarios Personalizados ({{ $derived->count() }})</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1rem;">
        @foreach($derived as $cal)
        <div style="background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.75rem; padding:1.25rem; border-left:4px solid {{ $cal->color }};">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.5rem; margin-bottom:0.75rem;">
                <div>
                    <div style="font-weight:600; color:#fff; font-size:0.95rem;">{{ $cal->name }}</div>
                    @if($cal->parent)
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                            Basado en: {{ $cal->parent->name }}
                        </div>
                    @endif
                </div>
                <span style="width:12px; height:12px; border-radius:50%; background:{{ $cal->color }}; flex-shrink:0; margin-top:3px;"></span>
            </div>
            @if($cal->description)
                <p style="color:var(--text-muted); font-size:0.82rem; margin:0 0 0.75rem;">{{ $cal->description }}</p>
            @endif
            <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:1rem;">
                {{ $cal->events_count }} eventos propios
                @if($cal->user) &bull; {{ $cal->user->name }} @endif
                @if($cal->start_date && $cal->end_date)
                    <br>{{ $cal->start_date->locale('es')->isoFormat('D MMM YYYY') }} - {{ $cal->end_date->locale('es')->isoFormat('D MMM YYYY') }}
                @endif
            </div>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('calendar.index', ['calendar_id' => $cal->id]) }}"
                    class="btn" style="background:rgba(56,189,248,0.1); color:var(--primary); font-size:0.8rem; padding:0.35rem 0.7rem; flex:1; justify-content:center;">
                    Ver
                </a>
                <button onclick="openEditModal({{ json_encode(['id'=>$cal->id,'name'=>$cal->name,'description'=>$cal->description,'color'=>$cal->color,'start_date'=>$cal->start_date,'end_date'=>$cal->end_date,'school_year_id'=>$cal->school_year_id]) }})"
                    class="btn" style="background:rgba(255,255,255,0.05); color:#fff; font-size:0.8rem; padding:0.35rem 0.7rem;">
                    Editar
                </button>
                <form method="POST" action="{{ route('calendars.destroy', $cal) }}" onsubmit="return confirm('Eliminar el calendario {{ addslashes($cal->name) }}? Se perderan sus eventos.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn" style="background:rgba(239,68,68,0.1); color:var(--danger); font-size:0.8rem; padding:0.35rem 0.7rem; border:1px solid rgba(239,68,68,0.2) !important;">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="card" style="text-align:center; padding:3rem;">
    <div style="font-size:3rem; margin-bottom:1rem;">&#x1F4C1;</div>
    <div style="color:var(--text-muted); margin-bottom:1.5rem;">No hay calendarios personalizados todavia.</div>
    <button onclick="document.getElementById('createModal').style.display='flex'" class="btn btn-primary">
        Crear primer calendario personalizado
    </button>
</div>
@endif

{{-- CREATE MODAL --}}
<div id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#1e293b; border:1px solid var(--border); border-radius:1rem; padding:2rem; width:100%; max-width:480px; position:relative;">
        <button onclick="document.getElementById('createModal').style.display='none'" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer;">&#x2715;</button>
        <h3 style="font-size:1.2rem; font-weight:700; color:#fff; margin-bottom:1.5rem;">Nuevo Calendario</h3>
        <form method="POST" action="{{ route('calendars.store') }}">
            @csrf
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="name" required placeholder="Ej: Calendario Departamento Matematicas"
                    style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff;">
            </div>
            <div class="form-group">
                <label>Descripcion</label>
                <textarea name="description" rows="2" placeholder="Descripcion opcional..."
                    style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; resize:vertical; font-family:var(--font-main);"></textarea>
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:1rem;">
                <label style="margin:0;">Color</label>
                <input type="color" name="color" value="#38bdf8" style="height:40px; width:60px; border:1px solid var(--border); border-radius:0.5rem; cursor:pointer;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>Fecha inicio *</label>
                    <input type="date" name="start_date" required
                        style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; color-scheme:dark;">
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" name="end_date"
                        style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; color-scheme:dark;">
                </div>
            </div>
            <div class="form-group">
                <label>Curso Escolar *</label>
                <select name="school_year_id" required style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff;">
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}" {{ $sy->id == $activeSchoolYearId ? 'selected' : '' }}>{{ $sy->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($baseCalendar)
            <div style="background:rgba(56,189,248,0.05); border:1px solid rgba(56,189,248,0.2); border-radius:0.5rem; padding:0.75rem; margin-bottom:1rem; font-size:0.85rem; color:var(--text-muted);">
                Este calendario heredara todos los eventos del calendario base <strong style="color:#fff;">{{ $baseCalendar->name }}</strong>.
                Podras anadir eventos adicionales o sobreescribir fechas.
            </div>
            @endif
            <button type="submit" class="btn btn-primary" style="width:100%;">Crear Calendario</button>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#1e293b; border:1px solid var(--border); border-radius:1rem; padding:2rem; width:100%; max-width:480px; position:relative;">
        <button onclick="document.getElementById('editModal').style.display='none'" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer;">&#x2715;</button>
        <h3 style="font-size:1.2rem; font-weight:700; color:#fff; margin-bottom:1.5rem;">Editar Calendario</h3>
        <form method="POST" id="editCalForm">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="name" id="editCalName" required
                    style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff;">
            </div>
            <div class="form-group">
                <label>Descripcion</label>
                <textarea name="description" id="editCalDesc" rows="2"
                    style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; resize:vertical; font-family:var(--font-main);"></textarea>
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:1rem;">
                <label style="margin:0;">Color</label>
                <input type="color" name="color" id="editCalColor" value="#38bdf8" style="height:40px; width:60px; border:1px solid var(--border); border-radius:0.5rem; cursor:pointer;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>Fecha inicio *</label>
                    <input type="date" name="start_date" id="editCalStartDate" required
                        style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; color-scheme:dark;">
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" name="end_date" id="editCalEndDate"
                        style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; color-scheme:dark;">
                </div>
            </div>
            <div class="form-group">
                <label>Curso Escolar *</label>
                <select name="school_year_id" id="editCalSchoolYear" required style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff;">
                    @foreach($schoolYears as $sy)
                        <option value="{{ $sy->id }}">{{ $sy->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Guardar Cambios</button>
        </form>
    </div>
</div>

{{-- Import ICS Modal --}}
<div id="importIcsModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:550px; position:relative; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h2 style="font-size:1.2rem; font-weight:700; color:#fff; margin:0;">&#x1F4E5; Importar Calendario (.ics)</h2>
            <button type="button" onclick="closeImportIcsModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('calendar.import-ics') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group" style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem; font-weight:600;">1. Seleccionar archivo .ics o iCalendar</label>
                <input type="file" name="ics_file" accept=".ics,.ical,.txt" class="form-control" style="width:100%;">
                <small style="color:var(--text-muted); font-size:0.78rem; display:block; margin-top:0.35rem;">
                    Formatos compatibles: .ics, .ical (Exportados desde Séneca, Google Calendar, Outlook, etc.)
                </small>
            </div>

            <div class="form-group" style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem; font-weight:600;">O pegar contenido .ics directamente:</label>
                <textarea name="ics_text" rows="3" placeholder="BEGIN:VCALENDAR&#10;BEGIN:VEVENT..." style="width:100%; padding:0.5rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; font-family:monospace; font-size:0.8rem;"></textarea>
            </div>

            <div class="form-group" style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem; font-weight:600;">2. Destino de los eventos</label>
                <div style="display:flex; flex-direction:column; gap:0.5rem; background:rgba(255,255,255,0.02); padding:0.75rem; border-radius:0.5rem; border:1px solid var(--border);">
                    <label style="display:flex; align-items:center; gap:0.5rem; color:#fff; font-size:0.875rem; cursor:pointer;">
                        <input type="radio" name="target_option" value="new" checked onchange="toggleManageImportTargetOptions(this.value)">
                        Crear un nuevo calendario con estos eventos
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; color:#fff; font-size:0.875rem; cursor:pointer;">
                        <input type="radio" name="target_option" value="existing" onchange="toggleManageImportTargetOptions(this.value)">
                        Importar en un calendario existente
                    </label>
                </div>
            </div>

            <div id="manageNewCalNameGroup" class="form-group" style="margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Nombre del nuevo calendario</label>
                <input type="text" name="new_cal_name" placeholder="Ej: Calendario Escolar IES Los Alcores" class="form-control" style="width:100%;">
            </div>

            <div id="manageExistingCalGroup" class="form-group" style="display:none; margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Seleccionar calendario destino</label>
                <select name="calendar_id" class="form-control" style="width:100%;">
                    @foreach($calendars as $cal)
                        <option value="{{ $cal->id }}">{{ $cal->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom:1.5rem;">
                <label style="display:flex; align-items:center; gap:0.5rem; color:var(--text-muted); font-size:0.85rem; cursor:pointer;">
                    <input type="checkbox" name="clear_existing" value="1">
                    Vaciar eventos existentes en el calendario de destino antes de importar
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" onclick="closeImportIcsModal()" class="btn" style="background:rgba(255,255,255,0.05); color:#fff;">Cancelar</button>
                <button type="submit" class="btn btn-primary">&#x1F4E5; Importar Eventos</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(cal) {
    document.getElementById('editCalName').value = cal.name;
    document.getElementById('editCalDesc').value = cal.description || '';
    document.getElementById('editCalColor').value = cal.color || '#38bdf8';
    document.getElementById('editCalStartDate').value = cal.start_date ? cal.start_date.substring(0, 10) : '';
    document.getElementById('editCalEndDate').value = cal.end_date ? cal.end_date.substring(0, 10) : '';
    document.getElementById('editCalSchoolYear').value = cal.school_year_id || '';
    document.getElementById('editCalForm').action = '/calendars/' + cal.id;
    document.getElementById('editModal').style.display = 'flex';
}

function openImportIcsModal() {
    var modal = document.getElementById('importIcsModal');
    if (modal) modal.style.display = 'flex';
}
function closeImportIcsModal() {
    var modal = document.getElementById('importIcsModal');
    if (modal) modal.style.display = 'none';
}
function toggleManageImportTargetOptions(val) {
    var newGroup = document.getElementById('manageNewCalNameGroup');
    var existingGroup = document.getElementById('manageExistingCalGroup');
    if (newGroup) newGroup.style.display = (val === 'new') ? 'block' : 'none';
    if (existingGroup) existingGroup.style.display = (val === 'existing') ? 'block' : 'none';
}

['createModal','editModal','importIcsModal'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
});
</script>
@endsection
