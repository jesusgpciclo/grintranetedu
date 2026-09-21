@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Editar Recurso</h1>
            <a href="{{ route('tipo-recursos.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
                &larr; Volver
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
            <form action="{{ route('tipo-recursos.update', $tipo_recurso) }}" method="POST" class="p-8">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nombre del Tipo</label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $tipo_recurso->nombre) }}" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="Ej: Departamento" required>
                        @error('nombre')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-xl transition duration-300 shadow-lg transform hover:-translate-y-1">
                            Actualizar Recurso
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
