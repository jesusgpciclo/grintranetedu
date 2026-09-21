@extends('layouts.app')

@section('title', 'Calendario Escolar')

@section('content')
<div class="page-header" style="margin-bottom: 1.5rem;">
    <div>
        <h1 class="page-title">&#x1F4C5; Calendario Escolar</h1>
        @if($view === 'weekly')
            <div style="color:var(--text-muted);">{{ $months[0]['name'] }}</div>
        @elseif($view === 'academic')
            <div style="color:var(--text-muted);">Año Académico</div>
        @endif
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

{{-- Control bar reorganizada estilo Google Calendar --}}
<div style="display:flex; flex-direction:column; gap:1rem; margin-bottom:1.5rem;">
    {{-- Fila 1: Barra de herramientas superior --}}
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; background:rgba(255,255,255,0.02); border:1px solid var(--border); padding:0.75rem 1.25rem; border-radius:0.75rem;">
        
        {{-- Izquierda: Controles de navegación de fecha estilo Google Calendar --}}
        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <a href="{{ route('calendar.index', ['view' => $view, 'date' => now()->format('Y-m-d'), 'type' => $filterType, 'calendar_id' => $activeCalendar?->id]) }}"
                class="btn" style="background:rgba(255,255,255,0.06); color:#fff; border:1px solid var(--border); border-radius:1.5rem; padding:0.45rem 1.1rem; font-weight:600; font-size:0.875rem;">Hoy</a>
            
            <div style="display:flex; gap:0.25rem;">
                <a href="{{ route('calendar.index', ['view' => $view, 'date' => $prevDate, 'type' => $filterType, 'calendar_id' => $activeCalendar?->id]) }}"
                    class="btn" style="background:rgba(255,255,255,0.06); color:#fff; border:1px solid var(--border); border-radius:50%; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; font-weight:700; text-decoration:none;" title="Anterior">&lt;</a>
                <a href="{{ route('calendar.index', ['view' => $view, 'date' => $nextDate, 'type' => $filterType, 'calendar_id' => $activeCalendar?->id]) }}"
                    class="btn" style="background:rgba(255,255,255,0.06); color:#fff; border:1px solid var(--border); border-radius:50%; width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; font-weight:700; text-decoration:none;" title="Siguiente">&gt;</a>
            </div>

            <h2 style="font-size:1.35rem; font-weight:700; color:#fff; margin:0 0 0 0.5rem; text-transform:capitalize; letter-spacing:-0.02em;">
                {{ $months[0]['name'] ?? '' }}
            </h2>
        </div>

        {{-- Derecha: Selector de Vista (Google Calendar style dropdown) + Acciones --}}
        <div style="display:flex; align-items:center; gap:0.6rem; flex-wrap:wrap;">
            
            {{-- Selector de Vista estilo Google Calendar (Día / Semana / Mes / Año) --}}
            <select id="calendarViewSelect" onchange="window.location.href=this.value" 
                style="background:var(--card-bg); border:1px solid var(--border); color:#fff; padding:0.5rem 1rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; cursor:pointer; font-family:var(--font-main);">
                <option value="{{ route('calendar.index', ['view' => 'daily', 'date' => $dateParam, 'type' => $filterType, 'calendar_id' => $activeCalendar?->id]) }}" {{ $view === 'daily' ? 'selected' : '' }}>Día</option>
                <option value="{{ route('calendar.index', ['view' => 'weekly', 'date' => $dateParam, 'type' => $filterType, 'calendar_id' => $activeCalendar?->id]) }}" {{ $view === 'weekly' ? 'selected' : '' }}>Semana</option>
                <option value="{{ route('calendar.index', ['view' => 'monthly', 'date' => $dateParam, 'type' => $filterType, 'calendar_id' => $activeCalendar?->id]) }}" {{ $view === 'monthly' ? 'selected' : '' }}>Mes</option>
                <option value="{{ route('calendar.index', ['view' => 'academic', 'date' => $dateParam, 'type' => $filterType, 'calendar_id' => $activeCalendar?->id]) }}" {{ $view === 'academic' ? 'selected' : '' }}>Año (Curso Escolar)</option>
            </select>

            {{-- Selector de Calendario y Ajustes --}}
            <form method="GET" action="{{ route('calendar.index') }}" id="calendarSelectorForm" style="display:flex; align-items:center; gap:0.4rem; margin:0;">
                <input type="hidden" name="date" value="{{ $dateParam }}">
                <input type="hidden" name="type" value="{{ $filterType }}">
                <input type="hidden" name="view" value="{{ $view }}">
                <select name="calendar_id" onchange="document.getElementById('calendarSelectorForm').submit()"
                    style="background:var(--card-bg); border:1px solid var(--border); color:#fff; padding:0.5rem 0.75rem; border-radius:0.5rem; font-size:0.875rem; cursor:pointer; font-family:var(--font-main);">
                    @if($calendars->isEmpty())
                        <option value="">Sin calendarios</option>
                    @else
                        @foreach($calendars as $cal)
                            <option value="{{ $cal->id }}" {{ $activeCalendar && $activeCalendar->id == $cal->id ? 'selected' : '' }}>
                                📂 {{ $cal->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @role('admin|directiva')
                <a href="{{ route('calendars.index') }}" class="btn" style="background:rgba(255,255,255,0.05); color:#fff; padding:0.5rem;" title="Gestionar Calendarios">
                    ⚙️
                </a>
                @endrole
            </form>

            <button type="button" onclick="toggleSummaryBar()" class="btn" id="toggleSummaryBtn" title="Ver Resumen Estadístico" style="background:rgba(255,255,255,0.05); color:var(--text-muted); border:1px solid var(--border); padding:0.5rem 0.75rem; font-size:1rem; cursor:pointer; border-radius:0.5rem;">📊</button>

            @role('admin|directiva|profesor')
            <button onclick="openImportIcsModal()" class="btn" style="background:rgba(56,189,248,0.15); color:var(--primary); border:1px solid rgba(56,189,248,0.3); padding:0.5rem 0.75rem; border-radius:0.5rem;" title="Importar calendario (.ics)">
                📥
            </button>
            @endrole

            {{-- Menú de Exportación y Suscripción --}}
            <div style="position:relative; display:inline-block;">
                <button type="button" onclick="toggleExportMenu(event)" class="btn" style="background:rgba(255,255,255,0.05); color:#fff; border:1px solid var(--border); padding:0.5rem 0.75rem; border-radius:0.5rem;" title="Exportar o Suscribirse">
                    📤 Exportar ▾
                </button>
                <div id="exportMenu" style="display:none; position:absolute; top:110%; right:0; background:var(--card-bg); border:1px solid var(--border); border-radius:0.5rem; box-shadow:0 10px 25px rgba(0,0,0,0.5); z-index:100; min-width:210px; padding:0.5rem 0;">
                    <a href="{{ route('calendar.export-ics', ['calendar_id' => $activeCalendar?->id]) }}" style="display:flex; align-items:center; gap:0.5rem; padding:0.6rem 1rem; color:#fff; text-decoration:none; font-size:0.85rem;">
                        📥 Descargar .ics
                    </a>
                    <a href="{{ route('calendar.export-csv', ['calendar_id' => $activeCalendar?->id]) }}" style="display:flex; align-items:center; gap:0.5rem; padding:0.6rem 1rem; color:#fff; text-decoration:none; font-size:0.85rem;">
                        📊 Exportar a CSV
                    </a>
                    <a href="{{ route('calendar.print', ['calendar_id' => $activeCalendar?->id]) }}" target="_blank" style="display:flex; align-items:center; gap:0.5rem; padding:0.6rem 1rem; color:#fff; text-decoration:none; font-size:0.85rem;">
                        🖨️ Vista Imprimible / PDF
                    </a>
                    @if($activeCalendar)
                    <div style="border-top:1px solid var(--border); margin:0.3rem 0;"></div>
                    <button type="button" onclick="openSubscribeModal('{{ $activeCalendar->feed_url }}')" style="width:100%; text-align:left; background:none; border:none; display:flex; align-items:center; gap:0.5rem; padding:0.6rem 1rem; color:var(--primary); font-size:0.85rem; cursor:pointer;">
                        🔗 Enlace de Suscripción iCal
                    </button>
                    @endif
                </div>
            </div>

            @role('admin|directiva|profesor')
            <button onclick="openAddModal()" class="btn btn-primary" style="padding:0.5rem 1rem;">+ Nuevo Evento</button>
            @endrole

            {{-- Dropdown de tipo de evento --}}
            <div style="min-width:170px; display:inline-block;">
                <select id="filterTypeSelect" onchange="window.location.href=this.value"
                    style="background:var(--card-bg); border:1px solid var(--border); color:#fff; padding:0.5rem 0.75rem; border-radius:0.5rem; font-size:0.875rem; cursor:pointer; width:100%; font-family:var(--font-main);">
                    <option value="{{ route('calendar.index', ['view' => $view, 'date' => $dateParam, 'type' => 'all', 'calendar_id' => $activeCalendar?->id]) }}" {{ $filterType === 'all' ? 'selected' : '' }}>
                        Todos los eventos
                    </option>
                    @foreach($eventTypes as $key => $label)
                        <option value="{{ route('calendar.index', ['view' => $view, 'date' => $dateParam, 'type' => $key, 'calendar_id' => $activeCalendar?->id]) }}" {{ $filterType === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>
</div>

{{-- Teaching Days & Event Summary Bar --}}
@if($activeCalendar && isset($stats))
<div id="summaryBar" style="display:none; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
    <div style="background:rgba(34,197,94,0.08); border:1px solid rgba(34,197,94,0.2); padding:0.85rem 1rem; border-radius:0.75rem; display:flex; align-items:center; gap:0.75rem;">
        <div style="font-size:1.5rem; background:rgba(34,197,94,0.15); padding:0.4rem 0.6rem; border-radius:0.5rem; color:#22c55e;">🟢</div>
        <div>
            <div style="font-size:1.25rem; font-weight:700; color:#fff;">{{ $stats['teaching_days'] }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted);">Días Lectivos Netos</div>
        </div>
    </div>
    <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); padding:0.85rem 1rem; border-radius:0.75rem; display:flex; align-items:center; gap:0.75rem;">
        <div style="font-size:1.5rem; background:rgba(239,68,68,0.15); padding:0.4rem 0.6rem; border-radius:0.5rem; color:#ef4444;">🔴</div>
        <div>
            <div style="font-size:1.25rem; font-weight:700; color:#fff;">{{ $stats['holidays'] }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted);">Festivos / No Lectivos</div>
        </div>
    </div>
    <div style="background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.2); padding:0.85rem 1rem; border-radius:0.75rem; display:flex; align-items:center; gap:0.75rem;">
        <div style="font-size:1.5rem; background:rgba(59,130,246,0.15); padding:0.4rem 0.6rem; border-radius:0.5rem; color:#3b82f6;">🔵</div>
        <div>
            <div style="font-size:1.25rem; font-weight:700; color:#fff;">{{ $stats['evaluations'] }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted);">Períodos de Evaluación</div>
        </div>
    </div>
    <div style="background:rgba(6,182,212,0.08); border:1px solid rgba(6,182,212,0.2); padding:0.85rem 1rem; border-radius:0.75rem; display:flex; align-items:center; gap:0.75rem;">
        <div style="font-size:1.5rem; background:rgba(6,182,212,0.15); padding:0.4rem 0.6rem; border-radius:0.5rem; color:#06b6d4;">🧳</div>
        <div>
            <div style="font-size:1.25rem; font-weight:700; color:#fff;">{{ $stats['excursions'] ?? 0 }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted);">Salidas / Excursiones</div>
        </div>
    </div>
    <div style="background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.2); padding:0.85rem 1rem; border-radius:0.75rem; display:flex; align-items:center; gap:0.75rem;">
        <div style="font-size:1.5rem; background:rgba(245,158,11,0.15); padding:0.4rem 0.6rem; border-radius:0.5rem; color:#f59e0b;">🟣</div>
        <div>
            <div style="font-size:1.25rem; font-weight:700; color:#fff;">{{ $stats['meetings'] }}</div>
            <div style="font-size:0.78rem; color:var(--text-muted);">Reuniones y Claustros</div>
        </div>
    </div>
</div>
@endif

{{-- Grid based on view --}}
<div class="{{ $view === 'academic' ? 'calendar-grid-academic' : '' }}" style="{{ $view === 'academic' ? 'display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1.5rem;' : '' }}">
@foreach($months as $monthData)
    @if(isset($monthData['is_weekly']) || isset($monthData['is_daily']))
        <div class="card" style="padding:1rem;">
            <div style="display:flex; flex-direction:column; gap:0.5rem;">
                @foreach($monthData['days'] as $day)
                    @php
                        $dayEventsJson = json_encode($day['events']->map(fn($e) => [
                            'id'             => $e->id,
                            'title'          => $e->title,
                            'type'           => $e->type,
                            'type_label'     => $e->type_label,
                            'start_date'     => $e->start_date->format('Y-m-d'),
                            'end_date'       => $e->end_date?->format('Y-m-d'),
                            'description'    => $e->description,
                            'attachment_url' => $e->attachment_url,
                            'color'          => $e->type_color,
                            'calendar_id'    => $e->calendar_id,
                        ])->values());
                        $bgColor = $day['is_today'] ? 'rgba(56,189,248,0.08)' : 'rgba(255,255,255,0.02)';
                        $border = $day['is_today'] ? 'rgba(56,189,248,0.3)' : 'var(--border)';
                    @endphp
                    <div style="display:flex; padding:1rem; background:{{ $bgColor }}; border:1px solid {{ $border }}; border-radius:0.5rem; cursor:pointer;" onclick="openDayModal('{{ $day['date'] }}', {{ $dayEventsJson }})">
                        <div style="width:140px; font-weight:600; text-transform:capitalize; color:{{ $day['is_today'] ? 'var(--primary)' : ($day['is_weekend'] ? 'var(--text-muted)' : '#fff') }}; flex-shrink:0;">
                            {{ $day['full_name'] }}
                        </div>
                        <div style="flex:1; display:flex; flex-wrap:wrap; gap:0.5rem; min-width:0; overflow:hidden;">
                            @foreach($day['events'] as $event)
                                <span class="badge" title="{{ $event->title }}{{ $event->description ? ' — '.$event->description : '' }}" style="background:{{ $event->type_color }}33; color:{{ $event->type_color }}; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:inline-block;">
                                    {{ $event->title }}
                                </span>
                            @endforeach
                            @if($day['events']->isEmpty())
                                <span style="color:var(--text-muted); font-size:0.85rem;">Sin eventos</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="card" style="padding:1rem; margin-bottom:{{ $view === 'academic' ? '0' : '1.5rem' }};">
            @if($view === 'academic')
                <h3 style="text-align:center; margin-bottom:1rem; color:var(--primary); font-weight:600; text-transform:capitalize;">{{ $monthData['name'] }}</h3>
            @endif
            <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px; margin-bottom:4px;">
                @foreach(['Lun','Mar','Mie','Jue','Vie','Sab','Dom'] as $dayName)
                    <div style="text-align:center; font-size:0.75rem; font-weight:600; color:var(--text-muted); padding:0.5rem 0; text-transform:uppercase; letter-spacing:0.05em;">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>
            <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:2px;">
                @foreach($monthData['days'] as $day)
                    @if($day === null)
                        <div style="min-height:{{ $view === 'academic' ? '40px' : '95px' }};"></div>
                    @else
                        @php
                            $dayEventsJson = json_encode($day['events']->map(fn($e) => [
                                'id'             => $e->id,
                                'title'          => $e->title,
                                'type'           => $e->type,
                                'type_label'     => $e->type_label,
                                'start_date'     => $e->start_date->format('Y-m-d'),
                                'end_date'       => $e->end_date?->format('Y-m-d'),
                                'description'    => $e->description,
                                'attachment_url' => $e->attachment_url,
                                'color'          => $e->type_color,
                                'calendar_id'    => $e->calendar_id,
                            ])->values());
                            $bgColor   = $day['is_today'] ? 'rgba(56,189,248,0.08)' : 'rgba(255,255,255,0.02)';
                            $border    = $day['is_today'] ? 'rgba(56,189,248,0.3)' : 'rgba(255,255,255,0.08)';
                            $dayColor  = $day['is_today'] ? 'var(--primary)' : ($day['is_weekend'] ? 'var(--text-muted)' : '#fff');
                            
                            // For academic view, if there's an event highlight the cell strongly
                            if ($view === 'academic' && $day['events']->isNotEmpty()) {
                                $mainEvent = $day['events']->first();
                                $bgColor = $mainEvent->type_color . '33';
                                $border = $mainEvent->type_color . '88';
                                $dayColor = '#fff';
                            }
                        @endphp
                        <div style="min-height:{{ $view === 'academic' ? '40px' : '95px' }}; min-width:0; overflow:hidden; box-sizing:border-box; background:{{ $bgColor }}; border:1px solid {{ $border }}; border-radius:0.5rem; padding:0.4rem; cursor:pointer; transition:background 0.15s; display:flex; flex-direction:column; align-items:stretch; justify-content:{{ $view === 'academic' ? 'center' : 'flex-start' }};"
                            onmouseover="this.style.filter='brightness(1.2)'"
                            onmouseout="this.style.filter='none'"
                            ondragover="handleDragOver(event)"
                            ondrop="handleDrop(event, '{{ $day['date'] }}')"
                            onclick="openDayModal('{{ $day['date'] }}', {{ $dayEventsJson }})">
                            <div style="font-size:0.85rem; text-align:center; font-weight:{{ $day['is_today'] || ($view === 'academic' && $day['events']->isNotEmpty()) ? '700' : '500' }}; color:{{ $dayColor }}; margin-bottom:{{ $view === 'academic' ? '0' : '0.25rem' }};">
                                {{ $day['day'] }}
                            </div>
                            @if($view !== 'academic')
                                @foreach($day['events']->take(3) as $event)
                                    <div draggable="true" ondragstart="handleDragStart(event, {{ $event->id }})"
                                        title="{{ $event->title }}{{ $event->description ? ' — '.$event->description : '' }}"
                                        style="font-size:0.72rem; background:{{ $event->type_color }}28; color:{{ $event->type_color }}; border-left:3px solid {{ $event->type_color }}; padding:3px 6px; border-radius:4px; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; width:100%; box-sizing:border-box; cursor:grab; display:block;">
                                        {{ $event->title }}
                                    </div>
                                @endforeach
                                @if($day['events']->count() > 3)
                                    <div style="font-size:0.65rem; color:var(--text-muted); padding:1px 4px; width:100%; text-align:center;">+{{ $day['events']->count() - 3 }} más</div>
                                @endif
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
@endforeach
</div>

{{-- Upcoming events --}}
@php $upcoming = $events->filter(fn($e) => $e->start_date->gte(now()->startOfDay()))->sortBy('start_date')->take(6); @endphp
@if($upcoming->isNotEmpty())
<div class="card">
    <h2 style="font-size:1.1rem; font-weight:600; color:#fff; margin-bottom:1rem;">Proximos eventos</h2>
    <div style="display:flex; flex-direction:column; gap:0.5rem;">
        @foreach($upcoming as $event)
        <div style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem; background:rgba(255,255,255,0.02); border-radius:0.5rem; border:1px solid var(--border); border-left:3px solid {{ $event->type_color }};">
            <div style="flex:1;">
                <div style="font-weight:600; color:#fff; font-size:0.9rem;">{{ $event->title }}</div>
                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:2px;">
                    {{ $event->start_date->locale('es')->isoFormat('D MMM YYYY') }}
                    @if($event->end_date && $event->end_date != $event->start_date)
                        &mdash; {{ $event->end_date->locale('es')->isoFormat('D MMM YYYY') }}
                    @endif
                </div>
            </div>
            <span class="badge" style="background:{{ $event->type_color }}22; color:{{ $event->type_color }}; font-size:0.7rem;">{{ $event->type_label }}</span>
            @role('admin|directiva')
            <button onclick="openEditModal({{ json_encode(['id'=>$event->id,'title'=>$event->title,'type'=>$event->type,'start_date'=>$event->start_date->format('Y-m-d'),'end_date'=>$event->end_date?->format('Y-m-d'),'description'=>$event->description,'color'=>$event->color,'calendar_id'=>$event->calendar_id]) }})"
                style="background:rgba(56,189,248,0.1); color:var(--primary); border:none; padding:0.3rem 0.6rem; border-radius:0.4rem; cursor:pointer; font-size:0.8rem;">
                Editar
            </button>
            @endrole
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- DAY MODAL --}}
<div id="dayModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#1e293b; border:1px solid var(--border); border-radius:1rem; padding:2rem; width:100%; max-width:500px; max-height:80vh; overflow-y:auto; position:relative;">
        <button onclick="closeDayModal()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer;">&#x2715;</button>
        <h3 id="dayModalTitle" style="font-size:1.1rem; font-weight:700; color:#fff; margin-bottom:1rem;"></h3>
        <div id="dayModalEvents"></div>
        @role('admin|directiva')
        <button onclick="openAddFromDay()" class="btn btn-primary" style="margin-top:1rem; width:100%;">+ Anadir evento este dia</button>
        @endrole
    </div>
</div>

{{-- ADD EVENT MODAL --}}
@role('admin|directiva')
<div id="addModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1001; align-items:center; justify-content:center;">
    <div style="background:#1e293b; border:1px solid var(--border); border-radius:1rem; padding:2rem; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; position:relative;">
        <button onclick="closeAddModal()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer;">&#x2715;</button>
        <h3 style="font-size:1.2rem; font-weight:700; color:#fff; margin-bottom:1.5rem;">Nuevo Evento</h3>
        <form method="POST" action="{{ route('calendar.events.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="active_date" value="{{ $dateParam }}">
            <div class="form-group">
                <label>Calendario</label>
                <select name="calendar_id" required style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; font-family:var(--font-main);">
                    @foreach($calendars as $cal)
                        <option value="{{ $cal->id }}" {{ $activeCalendar && $activeCalendar->id == $cal->id ? 'selected' : '' }}>
                            {{ $cal->name }}{{ $cal->is_base ? ' (Base)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Titulo *</label>
                <input type="text" name="title" id="addTitle" required placeholder="Nombre del evento"
                    style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff;">
            </div>
            <div class="form-group">
                <label>Tipo *</label>
                <select name="type" required style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; font-family:var(--font-main);">
                    @foreach($eventTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>Fecha inicio *</label>
                    <input type="date" name="start_date" id="addStartDate" required
                        style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; color-scheme:dark;">
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" name="end_date"
                        style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; color-scheme:dark;">
                </div>
            </div>
            <div class="form-group">
                <label>Descripcion</label>
                <textarea name="description" rows="3" placeholder="Descripcion opcional..."
                    style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; resize:vertical; font-family:var(--font-main);"></textarea>
            </div>
            <div class="form-group">
                <label>Archivo Adjunto (opcional)</label>
                <input type="file" name="attachment_file" class="form-control" style="width:100%;">
                <small style="color:var(--text-muted); font-size:0.75rem;">Formatos aceptados: PDF, imágenes, Word, etc. (Máx. 10MB)</small>
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:1rem;">
                <label style="margin:0;">Color (opcional)</label>
                <input type="color" name="color" value="#38bdf8" style="height:40px; width:60px; border:1px solid var(--border); border-radius:0.5rem; cursor:pointer;">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Crear Evento</button>
        </form>
    </div>
</div>

{{-- EDIT EVENT MODAL --}}
<div id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1001; align-items:center; justify-content:center;">
    <div style="background:#1e293b; border:1px solid var(--border); border-radius:1rem; padding:2rem; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; position:relative;">
        <button onclick="closeEditModal()" style="position:absolute; top:1rem; right:1rem; background:none; border:none; color:var(--text-muted); font-size:1.2rem; cursor:pointer;">&#x2715;</button>
        <h3 style="font-size:1.2rem; font-weight:700; color:#fff; margin-bottom:1.5rem;">Editar Evento</h3>
        <form method="POST" id="editForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_date" value="{{ $dateParam }}">
            <div class="form-group">
                <label>Titulo *</label>
                <input type="text" name="title" id="editTitle" required
                    style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff;">
            </div>
            <div class="form-group">
                <label>Tipo *</label>
                <select name="type" id="editType" required style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; font-family:var(--font-main);">
                    @foreach($eventTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label>Fecha inicio *</label>
                    <input type="date" name="start_date" id="editStartDate" required
                        style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; color-scheme:dark;">
                </div>
                <div class="form-group">
                    <label>Fecha fin</label>
                    <input type="date" name="end_date" id="editEndDate"
                        style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; color-scheme:dark;">
                </div>
            </div>
            <div class="form-group">
                <label>Descripcion</label>
                <textarea name="description" id="editDescription" rows="3"
                    style="width:100%; padding:0.75rem 1rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:0.5rem; color:#fff; resize:vertical; font-family:var(--font-main);"></textarea>
            </div>
            <div class="form-group">
                <label>Archivo Adjunto</label>
                <input type="file" name="attachment_file" class="form-control" style="width:100%;">
                <small style="color:var(--text-muted); font-size:0.75rem;">Subir archivo para reemplazar el existente (Máx. 10MB)</small>
            </div>
            <div class="form-group" style="display:flex; align-items:center; gap:1rem;">
                <label style="margin:0;">Color</label>
                <input type="color" name="color" id="editColor" value="#38bdf8" style="height:40px; width:60px; border:1px solid var(--border); border-radius:0.5rem; cursor:pointer;">
            </div>
            <div style="display:flex; gap:0.75rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Guardar Cambios</button>
                <button type="button" id="deleteEventBtn" class="btn" style="background:rgba(239,68,68,0.1); color:var(--danger); border:1px solid rgba(239,68,68,0.2) !important;">Eliminar</button>
            </div>
        </form>
        <form method="POST" id="deleteForm" style="display:none;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="active_date" value="{{ $dateParam }}">
        </form>
    </div>
</div>
@endrole

{{-- Import ICS Modal --}}
@role('admin|directiva|profesor')
<div id="importIcsModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:550px; position:relative; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h2 style="font-size:1.2rem; font-weight:700; color:#fff; margin:0;">&#x1F4E5; Importar Calendario (.ics)</h2>
            <button type="button" onclick="closeImportIcsModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('calendar.import-ics') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="active_calendar_id" value="{{ $activeCalendar?->id }}">

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
                        <input type="radio" name="target_option" value="active" checked onchange="toggleImportTargetOptions(this.value)">
                        Importar en el calendario activo ({{ $activeCalendar?->name ?? 'Calendario Activo' }})
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; color:#fff; font-size:0.875rem; cursor:pointer;">
                        <input type="radio" name="target_option" value="new" onchange="toggleImportTargetOptions(this.value)">
                        Crear un nuevo calendario con estos eventos
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; color:#fff; font-size:0.875rem; cursor:pointer;">
                        <input type="radio" name="target_option" value="existing" onchange="toggleImportTargetOptions(this.value)">
                        Seleccionar otro calendario existente
                    </label>
                </div>
            </div>

            <div id="newCalNameGroup" class="form-group" style="display:none; margin-bottom:1.25rem;">
                <label style="display:block; font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem;">Nombre del nuevo calendario</label>
                <input type="text" name="new_cal_name" placeholder="Ej: Calendario Escolar 2025/2026" class="form-control" style="width:100%;">
            </div>

            <div id="existingCalGroup" class="form-group" style="display:none; margin-bottom:1.25rem;">
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
@endrole

{{-- Modal de Suscripción iCal --}}
<div id="subscribeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:1000; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="width:100%; max-width:550px; position:relative;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h2 style="font-size:1.2rem; font-weight:700; color:#fff; margin:0;">🔗 Suscripción iCal en Tiempo Real</h2>
            <button type="button" onclick="closeSubscribeModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <p style="color:var(--text-muted); font-size:0.875rem; line-height:1.5; margin-bottom:1.25rem;">
            Copia esta URL y agrégala a tu aplicación de calendario (Google Calendar, Apple Calendar en iPhone/Mac, Outlook, etc.) para tener el calendario escolar sincronizado automáticamente en tu dispositivo.
        </p>

        <div class="form-group" style="margin-bottom:1.5rem;">
            <label style="display:block; font-size:0.875rem; color:var(--text-muted); margin-bottom:0.5rem; font-weight:600;">URL del Feed iCal:</label>
            <div style="display:flex; gap:0.5rem;">
                <input type="text" id="subscribeUrlInput" readonly class="form-control" style="width:100%; font-family:monospace; font-size:0.85rem; background:rgba(255,255,255,0.03);" onclick="this.select()">
                <button type="button" onclick="copySubscribeUrl()" class="btn btn-primary" style="white-space:nowrap;">📋 Copiar</button>
            </div>
            <small id="copySuccessMsg" style="color:var(--success); font-size:0.78rem; display:none; margin-top:0.35rem;">✓ ¡Enlace copiado al portapapeles!</small>
        </div>

        <div style="display:flex; justify-content:flex-end;">
            <button type="button" onclick="closeSubscribeModal()" class="btn" style="background:rgba(255,255,255,0.05); color:#fff;">Cerrar</button>
        </div>
    </div>
</div>

<script>
var activeDayDate = null;
var canManage = @role('admin|directiva') true @else false @endrole;

function toggleSummaryBar() {
    var bar = document.getElementById('summaryBar');
    var btn = document.getElementById('toggleSummaryBtn');
    if (bar) {
        var isHidden = (bar.style.display === 'none' || !bar.style.display);
        bar.style.display = isHidden ? 'grid' : 'none';
        if (btn) {
            btn.style.background = isHidden ? 'var(--primary)' : 'rgba(255,255,255,0.05)';
            btn.style.color = isHidden ? '#fff' : 'var(--text-muted)';
        }
    }
}

function toggleExportMenu(e) {
    if (e) e.stopPropagation();
    var menu = document.getElementById('exportMenu');
    if (menu) {
        menu.style.display = (menu.style.display === 'none' || !menu.style.display) ? 'block' : 'none';
    }
}
document.addEventListener('click', function(e) {
    var menu = document.getElementById('exportMenu');
    if (menu && !menu.contains(e.target)) {
        menu.style.display = 'none';
    }
});

function openSubscribeModal(url) {
    var modal = document.getElementById('subscribeModal');
    var input = document.getElementById('subscribeUrlInput');
    if (input) input.value = url;
    if (modal) modal.style.display = 'flex';
}
function closeSubscribeModal() {
    var modal = document.getElementById('subscribeModal');
    if (modal) modal.style.display = 'none';
}
function copySubscribeUrl() {
    var input = document.getElementById('subscribeUrlInput');
    if (input) {
        input.select();
        navigator.clipboard.writeText(input.value).then(function() {
            var msg = document.getElementById('copySuccessMsg');
            if (msg) {
                msg.style.display = 'block';
                setTimeout(function() { msg.style.display = 'none'; }, 3000);
            }
        });
    }
}

function openDayModal(date, events) {
    activeDayDate = date;
    var d = new Date(date + 'T00:00:00');
    var opts = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
    document.getElementById('dayModalTitle').textContent = d.toLocaleDateString('es-ES', opts);
    var container = document.getElementById('dayModalEvents');
    if (!events || events.length === 0) {
        container.innerHTML = '<div style="color:var(--text-muted); font-size:0.9rem; padding:1rem 0;">No hay eventos registrados para este día.</div>';
    } else {
        container.innerHTML = events.map(function(e) {
            var editBtn = canManage ? '<button onclick=\'openEditModal(' + JSON.stringify(e) + ')\' style="background:rgba(56,189,248,0.1); color:var(--primary); border:none; padding:0.25rem 0.5rem; border-radius:0.4rem; cursor:pointer; font-size:0.75rem; white-space:nowrap; flex-shrink:0;">Editar</button>' : '';
            var attachmentLink = e.attachment_url ? '<div style="margin-top:6px;"><a href="' + e.attachment_url + '" target="_blank" style="display:inline-flex; align-items:center; gap:0.25rem; font-size:0.78rem; color:var(--primary); text-decoration:none; background:rgba(56,189,248,0.1); padding:0.2rem 0.5rem; border-radius:0.3rem;">📎 Descargar adjunto</a></div>' : '';
            return '<div style="display:flex; align-items:flex-start; gap:0.75rem; padding:0.75rem; background:rgba(255,255,255,0.03); border-radius:0.5rem; border-left:3px solid ' + e.color + '; margin-bottom:0.5rem;">'
                + '<div style="flex:1;">'
                + '<div style="font-weight:600; color:#fff; font-size:0.9rem;">' + e.title + '</div>'
                + '<div style="font-size:0.78rem; color:' + e.color + '; margin-top:2px;">' + e.type_label + '</div>'
                + (e.description ? '<div style="font-size:0.8rem; color:var(--text-muted); margin-top:4px;">' + e.description + '</div>' : '')
                + attachmentLink
                + '</div>'
                + editBtn
                + '</div>';
        }).join('');
    }
    document.getElementById('dayModal').style.display = 'flex';
}

function closeDayModal() { document.getElementById('dayModal').style.display = 'none'; }

function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }

function openImportIcsModal() {
    var modal = document.getElementById('importIcsModal');
    if (modal) modal.style.display = 'flex';
}
function closeImportIcsModal() {
    var modal = document.getElementById('importIcsModal');
    if (modal) modal.style.display = 'none';
}
function toggleImportTargetOptions(val) {
    var newGroup = document.getElementById('newCalNameGroup');
    var existingGroup = document.getElementById('existingCalGroup');
    if (newGroup) newGroup.style.display = (val === 'new') ? 'block' : 'none';
    if (existingGroup) existingGroup.style.display = (val === 'existing') ? 'block' : 'none';
}

function openAddFromDay() {
    closeDayModal();
    if (activeDayDate && document.getElementById('addStartDate')) {
        document.getElementById('addStartDate').value = activeDayDate;
    }
    openAddModal();
}

function openEditModal(event) {
    closeDayModal();
    document.getElementById('editTitle').value = event.title;
    document.getElementById('editType').value = event.type;
    document.getElementById('editStartDate').value = event.start_date;
    document.getElementById('editEndDate').value = event.end_date || '';
    document.getElementById('editDescription').value = event.description || '';
    document.getElementById('editColor').value = event.color || '#38bdf8';
    document.getElementById('editForm').action = '/calendar/events/' + event.id;
    document.getElementById('deleteForm').action = '/calendar/events/' + event.id;
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }

var deleteBtn = document.getElementById('deleteEventBtn');
if (deleteBtn) {
    deleteBtn.addEventListener('click', function() {
        if (confirm('Eliminar este evento?')) {
            document.getElementById('deleteForm').submit();
        }
    });
}

var draggedEventId = null;

function handleDragStart(e, eventId) {
    if (!canManage) return;
    draggedEventId = eventId;
    if (e.dataTransfer) {
        e.dataTransfer.setData('text/plain', eventId);
        e.dataTransfer.effectAllowed = 'move';
    }
}

function handleDragOver(e) {
    if (!canManage) return;
    e.preventDefault();
    if (e.dataTransfer) {
        e.dataTransfer.dropEffect = 'move';
    }
}

function handleDrop(e, targetDate) {
    if (!canManage) return;
    e.preventDefault();
    if (!draggedEventId) return;

    fetch('/calendar/events/' + draggedEventId + '/move', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ new_date: targetDate })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            alert('No se pudo mover el evento.');
        }
    })
    .catch(function() {
        alert('Error al reprogramar la fecha del evento.');
    });
}

['dayModal','addModal','editModal','importIcsModal','subscribeModal'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
});
</script>
@endsection
