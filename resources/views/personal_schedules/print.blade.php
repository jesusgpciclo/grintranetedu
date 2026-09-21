<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Horario - {{ $personal_schedule->schoolYear ? $personal_schedule->schoolYear->name : 'Personal' }}</title>
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
                padding: 0 !important;
            }
            tr {
                page-break-inside: avoid;
            }
            @page {
                size: landscape;
                margin: 1cm;
            }
        }
    </style>
</head>
<body class="p-8 bg-slate-50 min-h-screen">
    <!-- Toolbar for manual triggers -->
    <div class="no-print max-w-7xl mx-auto mb-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <div class="text-sm text-slate-500 font-medium">
            Vista previa de impresión. Para guardar como PDF, selecciona la opción "Guardar como PDF" en el destino de impresión.
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md transition-all flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2-2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir / Guardar como PDF
            </button>
            <button onclick="window.close()" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition-all text-sm">
                Cerrar
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-100 print:shadow-none print:border-none print:p-0">
        <!-- Header -->
        <div class="flex justify-between items-center border-b-2 border-slate-100 pb-6 mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Horario del Docente</h1>
                <p class="text-sm font-semibold text-slate-500 mt-1">
                    Docente: <span class="text-slate-700 font-bold">{{ $personal_schedule->user ? ($personal_schedule->user->name . ' ' . $personal_schedule->user->last_name) : Auth::user()->name }}</span> &bull; 
                    Curso Escolar: <span class="text-slate-700 font-bold">{{ $personal_schedule->schoolYear ? $personal_schedule->schoolYear->name : 'General' }}</span>
                </p>
            </div>
            <div class="text-right text-sm text-slate-500">
                <div><strong>Plantilla:</strong> {{ $template->name }}</div>
                <div><strong>Fecha de reporte:</strong> {{ now()->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl print:border-slate-300">
            <table class="min-w-full divide-y divide-slate-200 border-collapse table-fixed">
                <thead class="bg-slate-50 print:bg-slate-100">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-b border-slate-200 w-[140px]">
                            Tramo Horario
                        </th>
                        @foreach($days as $dayId => $dayName)
                            <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-b border-slate-200 last:border-r-0">
                                {{ $dayName }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @foreach($slots as $slot)
                        <tr class="print:break-inside-avoid">
                            <td class="px-4 py-3 border-r border-slate-200 bg-slate-50/50 print:bg-slate-50/20 font-medium">
                                <div class="font-bold text-slate-800 text-sm">{{ $slot->name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                </div>
                            </td>
                            @foreach($days as $dayId => $dayName)
                                @php
                                    $key = $slot->id . '-' . $dayName;
                                    $sel = $selections[$key] ?? null;
                                    $type = $sel ? ($sel->type ?: ($sel->guardia_id ? 'guardia' : 'texto')) : null;
                                @endphp
                                <td class="px-3 py-2 text-center border-r border-slate-200 last:border-r-0 align-middle h-[80px]">
                                    @if($sel)
                                        @if($type === 'clase')
                                            <div class="p-2 bg-blue-50 border border-blue-200 rounded-lg text-left h-full flex flex-col justify-center print:bg-white print:border-slate-300 print:text-black">
                                                <div class="font-bold text-xs text-blue-700 print:text-black">
                                                    📚 {{ $sel->subject ?: 'Clase' }}
                                                </div>
                                                @if($sel->group)
                                                    <div class="mt-1 text-[10px] inline-block font-semibold bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded print:border print:border-slate-400 print:text-black print:bg-white w-max">
                                                        {{ $sel->group->course }} {{ $sel->group->name }}
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($type === 'guardia')
                                            <div class="p-2 bg-amber-50 border border-amber-200 rounded-lg text-left h-full flex flex-col justify-center print:bg-white print:border-slate-300 print:text-black">
                                                <div class="font-bold text-xs text-amber-700 print:text-black">
                                                    🛡️ {{ $sel->guardia ? $sel->guardia->name : 'Guardia' }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg text-left h-full flex flex-col justify-center print:bg-white print:border-slate-300 print:text-black">
                                                <div class="font-semibold text-xs text-slate-700 print:text-black">
                                                    📝 {{ $sel->value }}
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-300 print:text-slate-400 font-light">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Print trigger -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
