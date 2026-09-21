<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa - {{ $template->name }}</title>
    <style>
        :root {
            --bg-color: #f8fafc;
            --text-color: #0f172a;
            --border-color: #e2e8f0;
            --primary-color: #3b82f6;
            --table-header-bg: #e2e8f0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            padding: 2rem;
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .header-section {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        h1 {
            margin: 0 0 0.5rem 0;
            color: var(--text-color);
        }

        .description {
            color: #64748b;
            font-size: 1.1rem;
            margin: 0;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .schedule-table th,
        .schedule-table td {
            border: 1px solid var(--border-color);
            padding: 12px;
            text-align: center;
        }

        .schedule-table th {
            background-color: var(--table-header-bg);
            font-weight: 600;
        }

        .time-col {
            width: 150px;
            background-color: var(--bg-color);
        }
        
        .time-col strong {
            display: block;
            font-size: 1.1em;
            color: var(--primary-color);
        }
        
        .time-col small {
            color: #64748b;
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-secondary {
            background-color: #64748b;
        }

        .btn:hover {
            opacity: 0.9;
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .schedule-table th,
            .schedule-table td {
                border: 1px solid #000;
            }
            .schedule-table th {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }
            .time-col {
                background-color: transparent;
            }
            .time-col strong {
                color: #000;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 20px; display: flex; gap: 10px; justify-content: flex-end; max-width: 1200px; margin: 0 auto 1rem auto;">
        <button class="btn" onclick="window.print()">🖨️ Imprimir</button>
        <button class="btn btn-secondary" onclick="window.close()">✕ Cerrar</button>
    </div>

    <div class="container">
        <div class="header-section">
            <h1>{{ $template->name }}</h1>
            @if($template->description)
                <p class="description">{{ $template->description }}</p>
            @endif
        </div>

        <table class="schedule-table">
            <thead>
                <tr>
                    <th class="time-col">Horario</th>
                    @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $index => $day)
                        @if(in_array($index + 1, $template->active_days ?? []))
                            <th>{{ $day }}</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($template->timeSlots as $slot)
                    <tr>
                        <td class="time-col">
                            <strong>
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                            </strong>
                            <small>{{ $slot->name }}</small>
                        </td>
                        @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $index => $day)
                            @if(in_array($index + 1, $template->active_days ?? []))
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>

</html>