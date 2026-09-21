@extends('layouts.app')

@section('content')
<style>
    /* Estilos base consistentes */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #475569; }

    .glass-panel {
        background: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
    }

    .dropdown-content {
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 0.5rem;
        width: 18rem;
        z-index: 1000;
        display: none;
        background: #1e293b;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5);
    }
    .dropdown-content.show { display: block; }

    .filter-input {
        @apply bg-slate-800/50 border-slate-700 text-white placeholder-slate-400 focus:ring-2 focus:ring-sky-400/50 focus:border-sky-400 transition-all duration-300;
    }

    .custom-checkbox {
        appearance: none; width: 18px; height: 18px;
        border: 2px solid #4b5563; border-radius: 4px;
        background: transparent; cursor: pointer;
        position: relative; transition: all 0.2s;
    }
    .custom-checkbox:checked { background: #38bdf8; border-color: #38bdf8; }
    .custom-checkbox:checked::after {
        content: ""; position: absolute; left: 5px; top: 2px;
        width: 5px; height: 10px; border: solid white;
        border-width: 0 2px 2px 0; transform: rotate(45deg);
    }
    .dropdown-scroll-container {
        max-height: 200px;
        overflow-y: auto;
        padding: 0.5rem;
    }
</style>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Gestor Documental</h1>
        <div class="flex space-x-4">
            @hasanyrole('admin|directiva')

                <a href="{{ route('categorias.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-300 shadow-sm border border-gray-200">
                    Carpetas
                </a>
                <a href="{{ route('etiquetas.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-300 shadow-sm border border-gray-200">
                    Etiquetas
                </a>
                <a href="{{ route('documentos-institucionales.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 shadow-md">
                    + Nuevo Documento
                </a>
            @endhasanyrole
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Filtros Minimalistas -->
    <div class="glass-panel rounded-2xl p-4 mb-8">
        <form action="{{ route('documentos-institucionales.index') }}" method="GET" id="filter-form" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="sort" id="sort" value="{{ request('sort', 'created_at') }}">
            <input type="hidden" name="direction" id="direction" value="{{ request('direction', 'desc') }}">
            <!-- Búsqueda -->
            <div class="relative flex-1 min-w-[280px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                       placeholder="Buscar documentos..." 
                       class="filter-input w-full pl-10 rounded-xl text-sm py-2">
            </div>

            <!-- Categorías Custom Dropdown -->
            <div class="relative dropdown-container">
                <button type="button" class="dropdown-trigger filter-input flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm min-w-[140px] text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="pointer-events-none">Carpetas</span>
                    @if(request()->filled('categorias'))
                        <span class="ml-auto bg-blue-500 text-white text-[10px] px-1.5 py-0.5 rounded-full pointer-events-none">{{ count(request('categorias')) }}</span>
                    @endif
                    <svg class="w-3 h-3 ml-1 text-gray-400 transition-transform pointer-events-none arrow-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="dropdown-content shadow-xl">
                    <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                        <input type="text" class="dropdown-search-input w-full px-3 py-1.5 text-xs rounded-lg border-none bg-gray-50 dark:bg-gray-700 focus:ring-1 focus:ring-blue-500" placeholder="Buscar carpeta...">
                    </div>
                    <div class="dropdown-scroll-container">
                        <div class="grid grid-cols-1 gap-1">
                            @foreach($todasCarpetas as $categoria)
                                <label class="flex items-center gap-3 p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg cursor-pointer transition-all group/item">
                                    <input type="checkbox" name="categorias[]" value="{{ $categoria->id }}" 
                                           class="custom-checkbox"
                                           {{ is_array(request('categorias')) && in_array($categoria->id, request('categorias')) ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-200">
                                        {{ $categoria->nombre }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Etiquetas Custom Dropdown -->
            <div class="relative dropdown-container">
                <button type="button" class="dropdown-trigger filter-input flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm min-w-[140px] text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span class="pointer-events-none">Etiquetas</span>
                    @if(request()->filled('etiquetas'))
                        <span class="ml-auto bg-blue-500 text-white text-[10px] px-1.5 py-0.5 rounded-full pointer-events-none">{{ count(request('etiquetas')) }}</span>
                    @endif
                    <svg class="w-3 h-3 ml-1 text-gray-400 transition-transform pointer-events-none arrow-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="dropdown-content shadow-xl">
                    <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                        <input type="text" class="dropdown-search-input w-full px-3 py-1.5 text-xs rounded-lg border-none bg-gray-50 dark:bg-gray-700 focus:ring-1 focus:ring-blue-500" placeholder="Buscar etiqueta...">
                    </div>
                    <div class="dropdown-scroll-container">
                        <div class="grid grid-cols-1 gap-1">
                            @foreach($todasEtiquetas as $etiqueta)
                                <label class="flex items-center gap-3 p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg cursor-pointer transition-all group/item">
                                    <input type="checkbox" name="etiquetas[]" value="{{ $etiqueta->id }}" 
                                           class="custom-checkbox"
                                           {{ is_array(request('etiquetas')) && in_array($etiqueta->id, request('etiquetas')) ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-200">
                                        #{{ $etiqueta->nombre }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>


            <!-- Acciones Rápidas -->
            <div class="flex items-center gap-2 ml-auto">
                <a href="{{ route('documentos-institucionales.index') }}" id="btn-reset-filters" 
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-500 hover:text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-xl transition-all border border-red-500/20 {{ request()->anyFilled(['buscar', 'categorias', 'etiquetas']) ? '' : 'hidden' }}" 
                   title="Limpiar todos los filtros">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Restablecer filtros
                </a>
            </div>
        </form>
    </div>

    <div id="table-container">
        <div class="bg-[#1e293b] shadow-xl rounded-xl overflow-hidden border border-slate-700">
            <table class="min-w-full divide-y divide-slate-700">
                <thead class="bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-sky-400 transition-colors group" onclick="updateSort('titulo')">
                            <div class="flex items-center gap-1.5">
                                Título
                                <span class="flex flex-col">
                                    @if(request('sort') == 'titulo')
                                        <svg class="w-3 h-3 {{ request('direction') == 'asc' ? 'rotate-180' : '' }} text-sky-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    @else
                                        <svg class="w-3 h-3 text-slate-600 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Carpetas</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Etiquetas</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-sky-400 transition-colors group" onclick="updateSort('created_at')">
                            <div class="flex items-center gap-1.5">
                                Fecha
                                <span class="flex flex-col">
                                    @if(request('sort') == 'created_at')
                                        <svg class="w-3 h-3 {{ request('direction') == 'asc' ? 'rotate-180' : '' }} text-sky-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    @else
                                        <svg class="w-3 h-3 text-slate-600 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                    @endif
                                </span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($documentos as $documento)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-white">{{ $documento->titulo }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-normal break-words max-w-xs">{{ $documento->descripcion }}</div>
                                <div class="mt-1">
                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-[10px] font-bold rounded uppercase">
                                        {{ $documento->tipo_archivo }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($documento->categoria)
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded border bg-blue-500/10 text-white border-blue-500/20">
                                            {{ $documento->categoria->nombre }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($documento->etiquetas as $etiqueta)
                                        <span class="px-2 py-0.5 bg-blue-500/10 text-white text-[10px] font-bold rounded border border-blue-500/20">
                                            #{{ $etiqueta->nombre }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $documento->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end items-center space-x-3">
                                    <a href="{{ $documento->url }}" target="_blank" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 flex items-center" title="Ver enlace">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('documentos-institucionales.show', $documento) }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 flex items-center" title="Detalles">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                         </svg>
                                     </a>
                                     @hasanyrole('admin|directiva')
                                         <a href="{{ route('documentos-institucionales.edit', $documento) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 flex items-center" title="Editar">
                                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                             </svg>
                                         </a>
                                         <form action="{{ route('documentos-institucionales.destroy', $documento) }}" method="POST" class="m-0 flex items-center" onsubmit="return confirm('¿Borrar documento?')">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 flex items-center" title="Eliminar">
                                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                 </svg>
                                             </button>
                                         </form>
                                     @endhasanyrole
                                 </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No se encontraron documentos que coincidan con los filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-8 pagination-container">
            {{ $documentos->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filter-form');
        const buscarInput = document.getElementById('buscar');
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        const tableContainer = document.getElementById('table-container');
        const sortInputs = form.querySelectorAll('select');
        let timeout = null;

        // Función para actualizar resultados vía AJAX (mantiene dropdowns abiertos)
        async function refreshResults() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            
            try {
                const response = await fetch(`${window.location.pathname}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Actualizar solo el contenedor de la tabla y paginación
                const newTable = doc.getElementById('table-container');
                if (newTable) {
                    tableContainer.innerHTML = newTable.innerHTML;
                }
                
                // Actualizar la URL sin recargar
                window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);
                
                // Actualizar contadores y visibilidad del botón de reset
                updateBadges(params);
                updateResetButton(params);
            } catch (error) {
                console.error('Error actualizando resultados:', error);
            }
        }

        function updateResetButton(params) {
            const btnReset = document.getElementById('btn-reset-filters');
            if (!btnReset) return;

            const hasFilters = params.get('buscar') || 
                              params.getAll('categorias[]').length > 0 || 
                              params.getAll('etiquetas[]').length > 0;
            
            if (hasFilters) {
                btnReset.classList.remove('hidden');
            } else {
                btnReset.classList.add('hidden');
            }
        }

        function updateBadges(params) {
            const catCount = params.getAll('categorias[]').length;
            const tagCount = params.getAll('etiquetas[]').length;
            
            updateBadge('categorias', catCount);
            updateBadge('etiquetas', tagCount);
        }

        function updateBadge(type, count) {
            const trigger = document.querySelector(`.dropdown-container:has(input[name="${type}[]"]) .dropdown-trigger`);
            let badge = trigger.querySelector('.badge-count');
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge-count ml-auto bg-blue-500 text-white text-[10px] px-1.5 py-0.5 rounded-full pointer-events-none';
                    trigger.insertBefore(badge, trigger.querySelector('.arrow-icon'));
                }
                badge.textContent = count;
            } else if (badge) {
                badge.remove();
            }
        }

        window.updateSort = function(column) {
            const sortInput = document.getElementById('sort');
            const dirInput = document.getElementById('direction');
            
            if (sortInput.value === column) {
                dirInput.value = dirInput.value === 'asc' ? 'desc' : 'asc';
            } else {
                sortInput.value = column;
                dirInput.value = 'asc';
            }
            refreshResults();
        };

        // Toggle Dropdowns
        document.querySelectorAll('.dropdown-trigger').forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const container = this.closest('.dropdown-container');
                const content = container.querySelector('.dropdown-content');
                const arrow = this.querySelector('.arrow-icon');
                
                document.querySelectorAll('.dropdown-content').forEach(d => {
                    if (d !== content) d.classList.remove('show');
                });
                document.querySelectorAll('.arrow-icon').forEach(a => {
                    if (a !== arrow) a.classList.remove('rotate-180');
                });

                content.classList.toggle('show');
                arrow.classList.toggle('rotate-180');
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-container')) {
                document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
                document.querySelectorAll('.arrow-icon').forEach(a => a.classList.remove('rotate-180'));
            }
        });

        buscarInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(refreshResults, 500);
        });

        sortInputs.forEach(select => {
            select.addEventListener('change', refreshResults);
        });

        // Filtro interno en los dropdowns
        document.querySelectorAll('.dropdown-search-input').forEach(input => {
            input.addEventListener('click', e => e.stopPropagation()); // Evitar cerrar al hacer clic en el buscador
            input.addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                const container = this.closest('.dropdown-content').querySelector('.dropdown-scroll-container');
                container.querySelectorAll('label').forEach(label => {
                    const text = label.textContent.toLowerCase();
                    label.style.display = text.includes(filter) ? 'flex' : 'none';
                });
            });
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', refreshResults);
        });
    });
</script>
@endsection

