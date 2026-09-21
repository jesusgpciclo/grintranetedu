<div class="bg-[#1e293b] shadow-xl rounded-xl overflow-hidden border border-slate-700">
    <table class="min-w-full divide-y divide-slate-700">
        <thead class="bg-slate-800/50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-sky-400 transition-colors sortable-header group" data-sort="nombre">
                    <div class="flex items-center gap-1.5">
                        Nombre
                        <span class="flex flex-col">
                            @if(request('sort') == 'nombre')
                                <svg class="w-3 h-3 {{ request('direction') == 'asc' ? 'rotate-180' : '' }} text-sky-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @else
                                <svg class="w-3 h-3 text-slate-600 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            @endif
                        </span>
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Descripción</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider cursor-pointer hover:text-sky-400 transition-colors sortable-header group" data-sort="documentos_count">
                    <div class="flex items-center gap-1.5">
                        Documentos vinculados
                        <span class="flex flex-col">
                            @if(request('sort') == 'documentos_count')
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
            @forelse($etiquetas as $etiqueta)
                <tr class="hover:bg-slate-700/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 bg-indigo-50/10 text-white font-bold rounded-full border border-indigo-500/20">
                            #{{ $etiqueta->nombre }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-normal break-words max-w-xs">{{ $etiqueta->descripcion ?: 'Sin descripción' }}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $etiqueta->documentos_count }} documentos
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end items-center space-x-3">
                            <a href="{{ route('etiquetas.edit', $etiqueta) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 flex items-center" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('etiquetas.destroy', $etiqueta) }}" method="POST" class="m-0 flex items-center" onsubmit="return confirm('¿Estás seguro?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 flex items-center" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No hay etiquetas que coincidan con la búsqueda.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6 ajax-pagination">
    {{ $etiquetas->links() }}
</div>
