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

    .filter-input {
        @apply bg-slate-800/50 border-slate-700 text-white placeholder-slate-400 focus:ring-2 focus:ring-sky-400/50 focus:border-sky-400 transition-all duration-300;
    }
</style>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Recursos</h1>
            <p class="text-slate-400 mt-1">Organiza y gestiona los tipos de categorías para los documentos.</p>
        </div>
        <a href="{{ route('tipo-recursos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl transition duration-300 shadow-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Recurso
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Filtros Avanzados -->
    <div class="glass-panel rounded-2xl p-4 mb-8">
        <form action="{{ route('tipo-recursos.index') }}" method="GET" id="filter-form" class="flex flex-wrap items-center gap-3">
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
                       placeholder="Buscar tipo de recurso..." 
                       class="filter-input w-full pl-10 rounded-xl text-sm py-2.5">
            </div>

            <!-- Limpiar -->
            <a href="{{ route('tipo-recursos.index') }}" id="btn-reset-filters" 
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
        @include('tipo_recursos._table')
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filter-form');
        const buscarInput = document.getElementById('buscar');
        const tableContainer = document.getElementById('table-container');

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

            const hasFilters = params.get('buscar') && params.get('buscar').length > 0;
            
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
