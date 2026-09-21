@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $documento->titulo }}</h1>
                <div class="flex flex-wrap gap-1">
                    @if($documento->categoria)
                        @php $categoria = $documento->categoria; @endphp
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded border {{ 
                            ($categoria->tipoRecurso->nombre ?? '') === 'departamento' ? 'bg-purple-50 text-purple-600 border-purple-100' : 
                            (($categoria->tipoRecurso->nombre ?? '') === 'curso' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-orange-50 text-orange-600 border-orange-100') 
                        }}">
                            {{ $categoria->nombre }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex space-x-4">
                @hasanyrole('admin|directiva')
                <a href="{{ route('documentos-institucionales.edit', $documento) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 shadow-md">
                    Editar
                </a>
                @endhasanyrole
                <a href="{{ route('documentos-institucionales.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition flex items-center">
                    &larr; Volver
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8 border border-gray-100 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Descripción</h2>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                        {{ $documento->descripcion ?: 'Sin descripción disponible.' }}
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8 border border-gray-100 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Recurso</h2>
                    <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-blue-600 rounded-lg text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-blue-900 dark:text-blue-300 uppercase tracking-wider">{{ $documento->tipo_archivo }}</p>
                                <p class="text-xs text-blue-600 dark:text-blue-400 truncate max-w-xs">{{ $documento->url }}</p>
                            </div>
                        </div>
                        <a href="{{ $documento->url }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                            Abrir enlace
                        </a>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8 border border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Detalles</h2>
                    <div class="space-y-4">
                        <div>
                            <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Categorías</span>
                            <div class="flex flex-wrap gap-1">
                                @if($documento->categoria)
                                    @php $categoria = $documento->categoria; @endphp
                                    <a href="{{ route('categorias.show', $categoria) }}" class="px-2 py-0.5 text-[10px] font-bold rounded border transition {{ 
                                        ($categoria->tipoRecurso->nombre ?? '') === 'departamento' ? 'bg-purple-50 text-purple-600 border-purple-100 hover:bg-purple-100' : 
                                        (($categoria->tipoRecurso->nombre ?? '') === 'curso' ? 'bg-green-50 text-green-600 border-green-100 hover:bg-green-100' : 'bg-orange-50 text-orange-600 border-orange-100 hover:bg-orange-100') 
                                    }}">
                                        {{ $categoria->nombre }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-500 italic">Sin categorías.</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Etiquetas</span>
                            <div class="flex flex-wrap gap-1">
                                @forelse($documento->etiquetas as $etiqueta)
                                    <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-bold rounded border border-blue-100 dark:border-blue-800">
                                        #{{ $etiqueta->nombre }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500 italic">Sin etiquetas.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

