@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6 flex items-center space-x-2">
            <a href="{{ route('documentos-institucionales.index') }}" class="text-blue-600 hover:underline">← Volver</a>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Nuevo Documento</h1>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 border border-gray-100 dark:border-gray-700">
            <form action="{{ route('documentos-institucionales.store') }}" method="POST">
                @csrf
                
                @if($errors->has('etiquetas') || $errors->has('categorias'))
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 text-red-700 dark:text-red-400 rounded-r-xl">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="font-bold text-sm">Atención</p>
                        </div>
                        <p class="text-xs mt-1">No puedes dejar el documento sin etiquetas o sin carpetas. Por favor, selecciona al menos una de cada.</p>
                    </div>
                @endif
                <div class="space-y-6">
                    <div>
                        <label for="titulo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Título del Documento</label>
                        <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}" required placeholder="Ej: Manual de Convivencia"
                            class="w-full rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition p-3">
                        @error('titulo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="url" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Dirección URL</label>
                        <input type="url" name="url" id="url" value="{{ old('url') }}" required placeholder="https://..."
                            class="w-full rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition p-3">
                        @error('url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="categoria_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Carpeta</label>
                        <select name="categoria_id" id="categoria_id" size="5"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition overflow-y-auto">
                            <option value="">Sin carpeta</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }} class="py-1">
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('categoria_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="etiquetas" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Etiquetas</label>
                        <select name="etiquetas[]" id="etiquetas" multiple size="5"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition overflow-y-auto">
                            @foreach($etiquetas as $etiqueta)
                                <option value="{{ $etiqueta->id }}" {{ collect(old('etiquetas'))->contains($etiqueta->id) ? 'selected' : '' }} class="py-1">
                                    #{{ $etiqueta->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('etiquetas') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="mt-2 text-xs text-gray-500 italic">Haz clic para seleccionar/deseleccionar. No necesitas Ctrl.</p>
                    </div>

                    <div>
                        <label for="tipo_archivo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tipo de archivo</label>
                        <select name="tipo_archivo" id="tipo_archivo" required
                            class="w-full rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition p-3">
                            <option value="Documento" {{ old('tipo_archivo') == 'Documento' ? 'selected' : '' }}>Documento</option>
                            <option value="Video" {{ old('tipo_archivo') == 'Video' ? 'selected' : '' }}>Video</option>
                            <option value="Presentación" {{ old('tipo_archivo') == 'Presentación' ? 'selected' : '' }}>Presentación</option>
                            <option value="Imagen" {{ old('tipo_archivo') == 'Imagen' ? 'selected' : '' }}>Imagen</option>
                            <option value="Otros" {{ old('tipo_archivo') == 'Otros' ? 'selected' : '' }}>Otros</option>
                        </select>
                    </div>

                    <div>
                        <label for="descripcion" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Descripción (Opcional)</label>
                        <textarea name="descripcion" id="descripcion" rows="3" placeholder="Explica brevemente de qué trata este documento..."
                            class="w-full rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition p-3">{{ old('descripcion') }}</textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl transition duration-300 shadow-lg transform hover:-translate-y-1">
                            Guardar Documento
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Permitir selección múltiple sin pulsar Ctrl
    const toggleSelect = (id) => {
        const el = document.getElementById(id);
        el.onmousedown = function(e) {
            e.preventDefault();
            var scroll = this.scrollTop;
            e.target.selected = !e.target.selected;
            setTimeout(() => this.scrollTop = scroll, 0);
            this.focus();
        };
    };

    toggleSelect('etiquetas');
</script>
@endsection

