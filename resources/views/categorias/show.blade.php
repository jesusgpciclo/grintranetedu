@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $categoria->nombre }}</h1>
                <span class="px-3 py-1 text-sm font-semibold rounded-full {{ 
                    $categoria->tipo === 'departamento' ? 'bg-purple-100 text-purple-800' : 
                    ($categoria->tipo === 'curso' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800') 
                }}">
                    {{ ucfirst(str_replace('_', ' ', $categoria->tipo)) }}
                </span>
            </div>
            <a href="{{ route('categorias.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
                &larr; Volver
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 mb-8">
            <div class="p-8">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Documentos en esta categoría
                </h2>

                <div class="space-y-4">
                    @forelse($categoria->documentos as $documento)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-800 transition">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $documento->titulo }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $documento->tipo_archivo }}</p>
                                </div>
                            </div>
                            <a href="{{ route('documentos.show', $documento) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                Ver detalles &rarr;
                            </a>
                        </div>
                    @empty
                        <p class="text-center py-8 text-gray-500 dark:text-gray-400 italic">No hay documentos vinculados a esta categoría todavía.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
