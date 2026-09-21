<div class="glass-panel rounded-2xl p-6 overflow-x-auto">
    <div class="font-mono text-sm text-sky-400 leading-relaxed whitespace-pre" style="font-family: 'JetBrains Mono', monospace;">
@php
    function renderTree($items, $prefix = '') {
        $output = '';
        $count = count($items);
        
        foreach ($items as $index => $item) {
            $isLastItem = ($index === $count - 1);
            $connector = $isLastItem ? '└── ' : '├── ';
            
            // Si es una Categoría (Carpeta)
            if ($item instanceof \App\Models\Categoria) {
                $url = route('categorias.show', $item);
                $output .= $prefix . $connector . '<a href="' . $url . '" class="text-white font-bold hover:text-sky-400 transition-colors duration-200 inline-flex items-center gap-1.5 group cursor-pointer"><svg class="w-4 h-4 text-amber-400 group-hover:text-sky-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>' . $item->nombre . '</a>' . "\n";
                
                $newPrefix = $prefix . ($isLastItem ? '    ' : '│   ');
                
                // Combinar hijos y documentos para procesarlos juntos
                $children = $item->childrenRecursive ?? collect();
                $docs = $item->documentos ?? collect();
                $merged = $children->concat($docs);
                
                if ($merged->count() > 0) {
                    $output .= renderTree($merged, $newPrefix);
                }
            } 
            // Si es un Documento (Archivo)
            else {
                $url = route('documentos.show', $item);
                $output .= $prefix . $connector . '<a href="' . $url . '" class="text-slate-400 hover:text-sky-400 transition-colors duration-200 inline-flex items-center gap-1.5 group cursor-pointer"><svg class="w-4 h-4 text-slate-500 group-hover:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>' . $item->titulo . '</a>' . "\n";
            }
        }
        return $output;
    }
@endphp
{!! renderTree($tree) !!}
    </div>
</div>
