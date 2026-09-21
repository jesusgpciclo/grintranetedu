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
</style>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Carpetas</h1>
            <p class="text-slate-400 mt-1">Organiza y gestiona las carpetas para los documentos institucionales.</p>
        </div>
        <div class="flex items-center gap-4">
            <!-- Selector de Vista -->
            <div class="flex bg-slate-800/50 p-1 rounded-xl border border-slate-700">
                <button type="button" id="btn-view-list" 
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request('view') !== 'tree' ? 'bg-sky-500 text-white shadow-lg' : 'text-slate-400 hover:text-white' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Lista
                    </div>
                </button>
                <button type="button" id="btn-view-tree" 
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request('view') === 'tree' ? 'bg-sky-500 text-white shadow-lg' : 'text-slate-400 hover:text-white' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Jerarquía
                    </div>
                </button>
            </div>
            
            @hasanyrole('admin|directiva')
            <a href="{{ route('categorias.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition duration-300 shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Carpeta
            </a>
            @endhasanyrole
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Filtros Avanzados -->
    <div class="glass-panel rounded-2xl p-4 mb-8">
        <form action="{{ route('categorias.index') }}" method="GET" id="filter-form" class="flex flex-wrap items-center gap-3">
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
                       placeholder="Buscar carpeta..." 
                       class="filter-input w-full pl-10 rounded-xl text-sm py-2.5">
            </div>




            <!-- Limpiar -->
            <a href="{{ route('categorias.index') }}" id="btn-reset-filters" 
               class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-red-500 hover:text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-xl transition-all border border-red-500/20 {{ request()->filled('buscar') ? '' : 'hidden' }}" 
               title="Limpiar todos los filtros">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Restablecer filtros
            </a>
        </form>
    </div>

    <div id="table-container">
        @if(request('view') === 'tree')
            @include('categorias._tree')
        @else
            @include('categorias._table')
        @endif
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

        // Toggle View Logic
        const btnList = document.getElementById('btn-view-list');
        const btnTree = document.getElementById('btn-view-tree');
        let currentView = new URLSearchParams(window.location.search).get('view') || 'list';

        btnList.addEventListener('click', () => switchView('list'));
        btnTree.addEventListener('click', () => switchView('tree'));

        function switchView(view) {
            currentView = view;
            // Update buttons
            if (view === 'tree') {
                btnTree.classList.add('bg-sky-500', 'text-white', 'shadow-lg');
                btnTree.classList.remove('text-slate-400', 'hover:text-white');
                btnList.classList.remove('bg-sky-500', 'text-white', 'shadow-lg');
                btnList.classList.add('text-slate-400', 'hover:text-white');
            } else {
                btnList.classList.add('bg-sky-500', 'text-white', 'shadow-lg');
                btnList.classList.remove('text-slate-400', 'hover:text-white');
                btnTree.classList.remove('bg-sky-500', 'text-white', 'shadow-lg');
                btnTree.classList.add('text-slate-400', 'hover:text-white');
            }
            refreshResults();
        }

        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-content').forEach(el => el.classList.remove('show'));
            document.querySelectorAll('.arrow-icon').forEach(el => el.style.transform = 'rotate(0deg)');
        });

        document.querySelectorAll('.dropdown-content').forEach(el => {
            el.addEventListener('click', e => e.stopPropagation());
        });

        // AJAX Logic
        async function refreshResults() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            if (currentView === 'tree') {
                params.set('view', 'tree');
            } else {
                params.delete('view');
            }
            
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

            const hasFilters = params.get('buscar');
            
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
