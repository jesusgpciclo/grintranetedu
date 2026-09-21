@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8 flex items-center space-x-4">
            <a href="{{ route('etiquetas.index') }}" class="text-indigo-600 hover:bg-indigo-50 p-2 rounded-full transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Etiqueta: #{{ $etiqueta->nombre }}</h1>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-8 border border-gray-100 dark:border-gray-700">
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Descripción</h2>
            <p class="text-lg text-gray-600 dark:text-gray-300">{{ $etiqueta->descripcion ?: 'Esta etiqueta no tiene una descripción definida.' }}</p>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Documentos vinculados ({{ $etiqueta->documentos->count() }})</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($etiqueta->documentos as $documento)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-transparent hover:border-indigo-400 transition">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $documento->titulo }}</h3>
                    <p class="text-gray-500 text-sm mb-4 line-clamp-2">{{ $documento->descripcion }}</p>
                    <a href="{{ route('documentos.show', $documento) }}" class="text-indigo-600 font-semibold hover:underline">Ver detalle →</a>
                </div>
            @empty
                <div class="col-span-full py-12 text-center bg-gray-50 dark:bg-gray-700/30 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600">
                    <p class="text-gray-500 dark:text-gray-400">No hay documentos vinculados a esta etiqueta actualmente.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
