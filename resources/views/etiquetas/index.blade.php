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

    .dropdown-scroll-container {
        max-height: 160px;
        overflow-y: auto;
        padding: 0.5rem;
    }

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
</style>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Etiquetas</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Administra las etiquetas para organizar tus documentos.</p>
        </div>
        <a href="{{ route('etiquetas.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition duration-300 shadow-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Etiqueta
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Filtros -->
    <div class="glass-panel rounded-2xl p-4 mb-8">
        <form action="{{ route('etiquetas.index') }}" method="GET" id="filter-form" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="sort" id="sort-field" value="{{ request('sort', 'created_at') }}">
            <input type="hidden" name="direction" id="sort-direction" value="{{ request('direction', 'desc') }}">
            <!-- Búsqueda -->
            <div class="relative flex-1 min-w-[280px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" 
                       placeholder="Buscar etiqueta (ej: #urgente)..." 
                       class="filter-input w-full pl-10 rounded-xl text-sm py-2.5">
            </div>

            <!-- Etiquetas Dropdown -->
            <div class="relative dropdown-container">
                <button type="button" class="dropdown-trigger filter-input flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm min-w-[160px] text-gray-300 hover:bg-slate-700 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>Seleccionar</span>
                    @if(request()->filled('tags'))
                        <span class="ml-auto bg-sky-500 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ count(request('tags')) }}</span>
                    @endif
                    <svg class="w-3 h-3 ml-1 text-gray-400 transition-transform arrow-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="dropdown-content shadow-xl">
                    <div class="p-2 border-b border-slate-700">
                        <input type="text" class="dropdown-search-input w-full px-3 py-1.5 text-xs rounded-lg border-none bg-slate-800 text-white focus:ring-1 focus:ring-sky-500" placeholder="Filtrar etiquetas...">
                    </div>
                    <div class="dropdown-scroll-container">
                        <div class="grid grid-cols-1 gap-1">
                            @foreach($todasEtiquetasParaFiltro as $tag)
                                <label class="flex items-center gap-3 p-2 hover:bg-sky-900/30 rounded-lg cursor-pointer transition-all">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" 
                                           class="custom-checkbox"
                                           {{ is_array(request('tags')) && in_array($tag->id, request('tags')) ? 'checked' : '' }}>
                                    <span class="text-xs font-medium text-gray-200">#{{ $tag->nombre }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>


            <!-- Limpiar -->
            <a href="{{ route('etiquetas.index') }}" id="btn-reset-filters" 
               class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-red-500 hover:text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-xl transition-all border border-red-500/20 {{ request()->anyFilled(['buscar', 'tags']) ? '' : 'hidden' }}" 
               title="Limpiar todos los filtros">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Restablecer filtros
            </a>
        </form>
    </div>

    <div id="table-container">
        @include('etiquetas._table')
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filter-form');
        const buscarInput = document.getElementById('buscar');
        const tableContainer = document.getElementById('table-container');
        const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        const sortSelects = form.querySelectorAll('select');

        // Toggle Dropdowns
        dropdownTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const content = this.nextElementSibling;
                const arrow = this.querySelector('.arrow-icon');
                
                document.querySelectorAll('.dropdown-content').forEach(el => {
                    if (el !== content) el.classList.remove('show');
                });
                document.querySelectorAll('.arrow-icon').forEach(el => {
                    if (el !== arrow) el.style.transform = 'rotate(0deg)';
                });

                content.classList.toggle('show');
                if (arrow) arrow.style.transform = content.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
            });
        });

        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-content').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('.arrow-icon').forEach(el => el.style.transform = 'rotate(0deg)');
        });

        document.querySelectorAll('.dropdown-content').forEach(el => {
            el.addEventListener('click', e => e.stopPropagation());
        });

        // Filtro interno en el dropdown
        document.querySelectorAll('.dropdown-search-input').forEach(input => {
            input.addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                const container = this.closest('.dropdown-content').querySelector('.dropdown-scroll-container');
                container.querySelectorAll('label').forEach(label => {
                    const text = label.textContent.toLowerCase();
                    label.style.display = text.includes(filter) ? 'flex' : 'none';
                });
            });
        });

        // AJAX Logic
        async function refreshResults() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            
            try {
                const response = await fetch(`${window.location.pathname}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await response.text();
                tableContainer.innerHTML = html;
                window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);
                updateResetButton(params);
                initPagination();
            } catch (error) {
                console.error('Error al filtrar:', error);
            }
        }

        function updateResetButton(params) {
            const btnReset = document.getElementById('btn-reset-filters');
            if (!btnReset) return;

            const hasFilters = params.get('buscar') || 
                              params.getAll('tags[]').length > 0;
            
            if (hasFilters) {
                btnReset.classList.remove('hidden');
            } else {
                btnReset.classList.add('hidden');
            }
        }

        let timeout;
        buscarInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(refreshResults, 500);
        });

        checkboxes.forEach(cb => cb.addEventListener('change', refreshResults));

        // Sorting Logic
        function initSorting() {
            document.querySelectorAll('.sortable-header').forEach(header => {
                header.addEventListener('click', function() {
                    const field = this.dataset.sort;
                    const currentField = document.getElementById('sort-field').value;
                    const currentDir = document.getElementById('sort-direction').value;
                    
                    let newDir = 'desc';
                    if (field === currentField) {
                        newDir = currentDir === 'desc' ? 'asc' : 'desc';
                    }
                    
                    document.getElementById('sort-field').value = field;
                    document.getElementById('sort-direction').value = newDir;
                    refreshResults();
                });
            });
        }

        function initPagination() {
            initSorting(); // Re-init sorting after table reload
            document.querySelectorAll('.ajax-pagination a').forEach(link => {
                link.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const html = await response.text();
                    tableContainer.innerHTML = html;
                    window.history.pushState({}, '', url);
                    initPagination();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        }

        initPagination();
    });
</script>
@endsection
