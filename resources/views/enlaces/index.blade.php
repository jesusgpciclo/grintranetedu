@extends('layouts.app')

@section('title', 'Enlaces de interés')

@section('module_title')
Enlaces de <span class="text-blue-500">interés</span>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Barra de Búsqueda Rápida -->
        <div class="bg-[var(--bg-card)] p-3.5 sm:p-4 rounded-2xl border border-[var(--border)] shadow-sm mb-6">
            <div class="relative">
                <input type="text" id="filter-links-input" placeholder="Buscar por título o descripción..."
                    onkeyup="filterLinksTable()"
                    class="w-full bg-[var(--bg-input)] border border-[var(--border)] rounded-xl py-2.5 pl-10 pr-4 text-sm text-[var(--text-color)] placeholder:text-[var(--text-muted)] focus:ring-2 focus:ring-[var(--primary)] focus:border-[var(--primary)] transition-all">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- Tabla Simplificada de Enlaces (Desktop & Tablet) -->
        <div class="hidden md:block bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="links-table">
                    <thead>
                        <tr class="border-b border-[var(--border)] bg-[var(--bg-surface)] text-[var(--text-muted)] text-xs uppercase tracking-wider font-extrabold">
                            <th class="py-3.5 px-4 w-16 text-center">Icono</th>
                            <th class="py-3.5 px-6 w-2/5">Título</th>
                            <th class="py-3.5 px-6">Descripción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)] text-sm">
                        @foreach($links as $link)
                        <tr class="link-item hover:bg-[var(--bg-hover)] transition-colors"
                            data-search="{{ strtolower($link['title'] . ' ' . $link['description']) }}">
                            <td class="py-4 px-4 text-center text-2xl font-mono">
                                <span>{{ $link['icon'] }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <a href="{{ $link['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="font-extrabold text-[var(--text-heading)] hover:text-[var(--primary)] text-base tracking-tight inline-flex items-center gap-1.5 group transition-colors"
                                    title="Abrir {{ $link['title'] }} en una ventana nueva">
                                    <span class="group-hover:underline">{{ $link['title'] }}</span>
                                    <svg class="w-4 h-4 text-[var(--text-muted)] group-hover:text-[var(--primary)] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </td>
                            <td class="py-4 px-6 text-[var(--text-muted)] text-sm leading-relaxed">
                                {{ $link['description'] }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lista Simplificada en Tarjetas (Mobile View < 768px) -->
        <div class="md:hidden space-y-3 mb-6" id="links-mobile-list">
            @foreach($links as $link)
            <div class="link-item bg-[var(--bg-card)] p-4 rounded-2xl border border-[var(--border)] shadow-xs transition-all flex items-start gap-3.5"
                data-search="{{ strtolower($link['title'] . ' ' . $link['description']) }}">
                <span class="text-2xl shrink-0 mt-0.5">{{ $link['icon'] }}</span>
                <div class="flex-1 min-w-0">
                    <h2 class="text-sm font-extrabold leading-tight">
                        <a href="{{ $link['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-[var(--text-heading)] hover:text-[var(--primary)] transition-colors inline-flex items-center gap-1 group"
                            title="Abrir {{ $link['title'] }} en una ventana nueva">
                            <span class="group-hover:underline">{{ $link['title'] }}</span>
                            <svg class="w-3.5 h-3.5 text-[var(--text-muted)] group-hover:text-[var(--primary)] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </h2>
                    <p class="text-xs text-[var(--text-muted)] mt-1.5 leading-relaxed">
                        {{ $link['description'] }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Estado vacío si el buscador no encuentra resultados -->
        <div id="no-links-found" class="hidden text-center py-12 bg-[var(--bg-card)] rounded-2xl border border-[var(--border)] p-6">
            <div class="text-4xl mb-3">🔍</div>
            <h3 class="text-base font-bold text-[var(--text-heading)] mb-1">No se han encontrado enlaces</h3>
            <p class="text-xs text-[var(--text-muted)]">Prueba con otro término de búsqueda.</p>
        </div>
    </div>
</div>

<script>
    function filterLinksTable() {
        const query = document.getElementById('filter-links-input').value.toLowerCase().trim();
        const items = document.querySelectorAll('.link-item');
        let visibleCount = 0;

        items.forEach(item => {
            const searchData = item.getAttribute('data-search') || '';
            if (!query || searchData.includes(query)) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const noFound = document.getElementById('no-links-found');
        if (noFound) {
            if (visibleCount === 0) {
                noFound.classList.remove('hidden');
            } else {
                noFound.classList.add('hidden');
            }
        }
    }
</script>
@endsection
