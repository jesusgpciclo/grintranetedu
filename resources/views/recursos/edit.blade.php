@extends('layouts.app')

@section('title', 'Editar Recurso')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Editar Recurso: {{ $recurso->nombre }}</h1>
            <div style="color: var(--text-muted);">
                Actualiza la información del dispositivo.
            </div>
        </div>
        <a href="{{ route('recursos.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: #fff;">
            &larr; Volver
        </a>
    </div>

    <div class="card">
        <form action="{{ route('recursos.update', $recurso) }}" method="POST">
            @csrf
            @method('PUT')
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <!-- Nombre -->
                <div>
                    <label for="nombre" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Nombre del Recurso</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $recurso->nombre) }}" 
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;" 
                        required>
                    @error('nombre') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Tipo -->
                <div>
                    <label for="tipo_id" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Tipo de Recurso</label>
                    <select name="tipo_id" id="tipo_id" 
                        style="width: 100%; padding: 0.75rem; background: rgba(30, 41, 59, 1); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;" required>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->id }}" {{ old('tipo_id', $recurso->tipo_id) == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Marca -->
                <div>
                    <label for="marca" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Marca</label>
                    <input type="text" name="marca" id="marca" value="{{ old('marca', $recurso->marca) }}" 
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                </div>

                <!-- Modelo -->
                <div>
                    <label for="modelo" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Modelo</label>
                    <input type="text" name="modelo" id="modelo" value="{{ old('modelo', $recurso->modelo) }}" 
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                </div>

                <!-- Número de Serie -->
                <div>
                    <label for="numero_serie" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Número de Serie</label>
                    <input type="text" name="numero_serie" id="numero_serie" value="{{ old('numero_serie', $recurso->numero_serie) }}" 
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                    @error('numero_serie') <span style="color: #ef4444; font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="estado" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Estado</label>
                    <select name="estado" id="estado" 
                        style="width: 100%; padding: 0.75rem; background: rgba(30, 41, 59, 1); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;" required>
                        <option value="disponible" {{ old('estado', $recurso->estado) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="en reparación" {{ old('estado', $recurso->estado) == 'en reparación' ? 'selected' : '' }}>En reparación</option>
                        <option value="dado de baja" {{ old('estado', $recurso->estado) == 'dado de baja' ? 'selected' : '' }}>Dado de baja</option>
                    </select>
                </div>

                <!-- Ubicación -->
                <div>
                    <label for="ubicacion" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Ubicación</label>
                    <input type="text" name="ubicacion" id="ubicacion" value="{{ old('ubicacion', $recurso->ubicacion) }}" 
                        style="width: 100%; padding: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                </div>

                <!-- Responsable -->
                <div>
                    <label for="responsable_id" style="display: block; margin-bottom: 0.5rem; color: #fff; font-size: 0.9rem;">Responsable</label>
                    <select name="responsable_id" id="responsable_id" 
                        style="width: 100%; padding: 0.75rem; background: rgba(30, 41, 59, 1); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; color: #fff;">
                        <option value="">Sin responsable asignado</option>
                        @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" {{ old('responsable_id', $recurso->responsable_id) == $usuario->id ? 'selected' : '' }}>{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                    Actualizar Recurso
                </button>
            </div>
        </form>
    </div>
@endsection
