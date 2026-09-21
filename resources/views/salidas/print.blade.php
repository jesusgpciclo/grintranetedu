<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Historial de Salidas - {{ now()->format('d/m/Y') }}</title>
    <!-- Outfit Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: white;
            color: #1e293b;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="p-8">
    <!-- Header -->
    <div class="flex justify-between items-center border-b-2 border-slate-200 pb-6 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800">Historial de Salidas</h1>
            <p class="text-sm font-semibold text-slate-500 mt-1">Control de pases al pasillo - GrIntranet</p>
        </div>
        <div class="text-right text-sm text-slate-500">
            <div><strong>Fecha de reporte:</strong> {{ now()->format('d/m/Y H:i') }}</div>
            <div><strong>Total registros:</strong> {{ $passes->count() }}</div>
        </div>
    </div>

    <!-- Toolbar for manual triggers -->
    <div class="no-print mb-6 flex justify-end gap-3">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md transition-all flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2-2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimir / Guardar como PDF
        </button>
        <button onclick="window.close()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition-all text-sm">
            Cerrar
        </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y-2 divide-slate-200 border border-slate-200 rounded-lg overflow-hidden">
            <thead class="bg-slate-50">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">Fecha</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">Alumno</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">Clase</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">Motivo</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">Duración</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">Profesor</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                @forelse($passes as $pass)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-700">
                            <strong>{{ $pass->date->format('d/m/Y') }}</strong>
                            <div class="text-[10px] text-slate-500">{{ $pass->start_time->format('H:i') }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-slate-800">
                            {{ $pass->student?->name }} {{ $pass->student?->last_name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-600">
                            {{ $pass->student?->groupRel?->course ?? '' }} {{ $pass->student?->groupRel?->name ?? '' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700">
                            {{ $pass->reason }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-600">
                            @if($pass->end_time)
                                {{ (int) $pass->start_time->diffInMinutes($pass->end_time) }} min
                            @else
                                <span class="text-emerald-600 font-bold">Activo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500 italic">
                            {{ $pass->teacher?->name ?? 'N/A' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500 text-sm">
                            No se han encontrado registros en el historial.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Print trigger -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
