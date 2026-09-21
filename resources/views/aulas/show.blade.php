@extends('layouts.app')
@section('title', $aula->nombre)
@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ $aula->nombre }}</h1>
        <a href="{{ route('aulas.index') }}" class="btn" style="background: rgba(255,255,255,0.1);">← Volver</a>
    </div>
    <div class="card">
        <div class="grid-3" style="margin-bottom: 1.5rem;">
            <div>
                <span class="stat-label">Tipo</span>
                <div style="font-weight: 600; font-size: 1.2rem;">
                    @if(($aula->tipo ?? 'aula') === 'aula')
                        <span class="badge badge-info">Aula</span>
                    @else
                        <span class="badge" style="background: rgba(255,255,255,0.08); color: #fff;">Zona</span>
                    @endif
                </div>
            </div>
            <div>
                <span class="stat-label">Identificación</span>
                <div style="font-weight: 600; font-size: 1.2rem; color: #fff;">
                    {{ $aula->identificacion ?? '—' }}
                </div>
            </div>
            <div>
                <span class="stat-label">Ubicación</span>
                <div style="font-weight: 600; font-size: 1.2rem; color: #fff;">
                    {{ $aula->ubicacion ?? '—' }}
                </div>
            </div>
        </div>

        <div class="grid-2" style="border-top: 1px solid var(--border); padding-top: 1.5rem; margin-top: 1.5rem;">
            @if(($aula->tipo ?? 'aula') === 'aula')
                <div>
                    <span class="stat-label">Capacidad</span>
                    <div style="font-weight: 600; font-size: 1.5rem; color: var(--primary);">
                        {{ $aula->capacidad ?? 'No especificada' }} plazas
                    </div>
                </div>
            @endif
            <div>
                <span class="stat-label">Equipamiento</span>
                <div style="margin-top: 0.25rem;">
                    @if($aula->equipamiento)
                        @foreach($aula->equipamiento as $e)
                            <span class="badge badge-info" style="margin: 0.1rem;">{{ $e }}</span>
                        @endforeach
                    @else
                        <span style="color: var(--text-muted); font-style: italic;">Sin equipamiento</span>
                    @endif
                </div>
            </div>
        </div>

        @if($aula->descripcion)
            <div style="border-top: 1px solid var(--border); padding-top: 1.5rem; margin-top: 1.5rem;">
                <span class="stat-label">Descripción</span>
                <p style="color: var(--text-muted); margin-top: 0.5rem; line-height: 1.6;">{{ $aula->descripcion }}</p>
            </div>
        @endif
    </div>
@endsection
