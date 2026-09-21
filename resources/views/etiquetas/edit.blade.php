@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-xl mx-auto">
        <div class="mb-6 flex items-center space-x-2">
            <a href="{{ route('etiquetas.index') }}" class="text-indigo-600 hover:underline">← Volver</a>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Editar Etiqueta</h1>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 border border-gray-100 dark:border-gray-700">
            <form action="{{ route('etiquetas.update', $etiqueta) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nombre de la Etiqueta</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $etiqueta->nombre) }}" required
                            class="w-full rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition p-3">
                        @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="descripcion" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Descripción (Opcional)</label>
                        <textarea name="descripcion" id="descripcion" rows="3"
                            class="w-full rounded-xl border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition p-3">{{ old('descripcion', $etiqueta->descripcion) }}</textarea>
                        @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl transition duration-300 shadow-lg transform hover:-translate-y-1">
                            Actualizar Etiqueta
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
