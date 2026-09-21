@extends('layouts.app')

@section('title', 'Editar Material - Inventario')

@section('content')
<div class="page-header">
    <h1 class="page-title">Editar Material</h1>
    <p class="text-muted">Actualiza los datos del elemento inventariado.</p>
</div>

<form action="{{ route('inventory.update', $inventory) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card" style="padding: 1.5rem;">
        <!-- Header Filters -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label>Tipo:</label>
                <select name="tipo" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                    <option value="Material inventariable" {{ $inventory->tipo == 'Material inventariable' ? 'selected' : '' }} style="background: #1e293b;">Material inventariable</option>
                    <option value="Mobiliario" {{ $inventory->tipo == 'Mobiliario' ? 'selected' : '' }} style="background: #1e293b;">Mobiliario</option>
                    <option value="Equipo Informático" {{ $inventory->tipo == 'Equipo Informático' ? 'selected' : '' }} style="background: #1e293b;">Equipo Informático</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label>Subtipo:</label>
                <select name="subtipo" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                    <option value="Cualquiera" {{ $inventory->subtipo == 'Cualquiera' ? 'selected' : '' }} style="background: #1e293b;">Cualquiera</option>
                    <option value="Router" {{ $inventory->subtipo == 'Router' ? 'selected' : '' }} style="background: #1e293b;">Router</option>
                    <option value="Silla" {{ $inventory->subtipo == 'Silla' ? 'selected' : '' }} style="background: #1e293b;">Silla</option>
                    <option value="Mesa" {{ $inventory->subtipo == 'Mesa' ? 'selected' : '' }} style="background: #1e293b;">Mesa</option>
                </select>
            </div>
        </div>

        <!-- Section: Datos del alta -->
        <div style="margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary); font-size: 1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 6px; height: 6px; background: var(--primary); border-radius: 2px;"></span>
                Datos del alta
            </h3>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label>Procedencia:</label>
                    <select name="procedencia" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                        <option value="Compras realizadas por el centro" {{ $inventory->procedencia == 'Compras realizadas por el centro' ? 'selected' : '' }} style="background: #1e293b;">Compras realizadas por el centro</option>
                        <option value="Dotación" {{ $inventory->procedencia == 'Dotación' ? 'selected' : '' }} style="background: #1e293b;">Dotación</option>
                        <option value="Donación" {{ $inventory->procedencia == 'Donación' ? 'selected' : '' }} style="background: #1e293b;">Donación</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label>Fecha de alta:</label>
                    <input type="date" name="fecha_alta" value="{{ $inventory->fecha_alta }}" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff; color-scheme: dark;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label>Precio (€):</label>
                    <input type="number" step="0.01" name="precio" value="{{ $inventory->precio }}" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                </div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label>Nº de registro general:</label>
                    <input type="text" name="num_registro_general" value="{{ $inventory->num_registro_general }}" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                </div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label>Nº de serie:</label>
                    <input type="text" name="num_serie" value="{{ $inventory->num_serie }}" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                </div>
            </div>
        </div>

        <!-- Section: Datos de su ubicación actual -->
        <div style="margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary); font-size: 1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 6px; height: 6px; background: var(--primary); border-radius: 2px;"></span>
                Datos de su ubicación actual
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.75rem;">
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label>Edificio:</label>
                    <select name="edificio" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                        <option value="Principal" {{ $inventory->edificio == 'Principal' ? 'selected' : '' }} style="background: #1e293b;">Principal</option>
                        <option value="Anexo" {{ $inventory->edificio == 'Anexo' ? 'selected' : '' }} style="background: #1e293b;">Anexo</option>
                        <option value="Talleres" {{ $inventory->edificio == 'Talleres' ? 'selected' : '' }} style="background: #1e293b;">Talleres</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label>Planta:</label>
                    <select name="planta" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                        <option value="Planta Baja" {{ $inventory->planta == 'Planta Baja' ? 'selected' : '' }} style="background: #1e293b;">Planta Baja</option>
                        <option value="Planta 1" {{ $inventory->planta == 'Planta 1' ? 'selected' : '' }} style="background: #1e293b;">Planta 1</option>
                        <option value="Planta 2" {{ $inventory->planta == 'Planta 2' ? 'selected' : '' }} style="background: #1e293b;">Planta 2</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label>Localización:</label>
                    <input type="text" name="localizacion" value="{{ $inventory->localizacion }}" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                </div>
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label>Dependencia / Departamento:</label>
                    <select name="dependencia_adscripcion" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                        <option value="INFORMÁTICA" {{ $inventory->dependencia_adscripcion == 'INFORMÁTICA' ? 'selected' : '' }} style="background: #1e293b;">INFORMÁTICA</option>
                        <option value="ADMINISTRATIVO" {{ $inventory->dependencia_adscripcion == 'ADMINISTRATIVO' ? 'selected' : '' }} style="background: #1e293b;">ADMINISTRATIVO</option>
                        <option value="ELECTRÓNICA" {{ $inventory->dependencia_adscripcion == 'ELECTRÓNICA' ? 'selected' : '' }} style="background: #1e293b;">ELECTRÓNICA</option>
                        <option value="BIBLIOTECA" {{ $inventory->dependencia_adscripcion == 'BIBLIOTECA' ? 'selected' : '' }} style="background: #1e293b;">BIBLIOTECA</option>
                        <option value="SECRETARÍA" {{ $inventory->dependencia_adscripcion == 'SECRETARÍA' ? 'selected' : '' }} style="background: #1e293b;">SECRETARÍA</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section: Estado / Retirada -->
        <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 2rem; padding: 1rem; background: rgba(255,255,255,0.02); border-radius: 0.75rem; border: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <label style="margin-bottom: 0; cursor: pointer;">
                    <input type="checkbox" name="solicitud_retirada" value="1" {{ $inventory->solicitud_retirada ? 'checked' : '' }} style="width: 16px; height: 16px; border-radius: 4px; accent-color: var(--primary);">
                    <span style="margin-left: 0.5rem; color: var(--text-color); font-weight: 500; font-size: 0.85rem;">Comunicar a Secretaría</span>
                </label>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem;">
                <label style="margin-bottom: 0;">Estado del material:</label>
                <select name="estado" style="padding: 0.4rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff;">
                    <option value="En uso" {{ $inventory->estado == 'En uso' ? 'selected' : '' }}>En uso</option>
                    <option value="No disponible" {{ $inventory->estado == 'No disponible' ? 'selected' : '' }}>No disponible</option>
                    <option value="Pendiente de retirar por APAE" {{ $inventory->estado == 'Pendiente de retirar por APAE' ? 'selected' : '' }}>Pendiente de retirar por APAE</option>
                    <option value="Cualquiera" {{ $inventory->estado == 'Cualquiera' ? 'selected' : '' }}>Cualquiera</option>
                </select>
            </div>
        </div>

        <!-- Section: Observaciones -->
        <div style="margin-bottom: 1.5rem;">
            <h3 style="color: var(--primary); font-size: 1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 6px; height: 6px; background: var(--primary); border-radius: 2px;"></span>
                Observaciones
            </h3>
            <textarea name="observaciones" rows="3" style="width: 100%; padding: 0.6rem; background: #1e293b; border: 1px solid var(--border); border-radius: 0.5rem; color: #fff; resize: none;">{{ $inventory->observaciones }}</textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('inventory.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: #fff;">Cancelar</a>
            <button type="submit" class="btn btn-primary">Actualizar Material</button>
        </div>
    </div>
</form>
@endsection
