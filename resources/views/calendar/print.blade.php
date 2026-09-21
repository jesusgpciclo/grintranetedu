<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Escolar Oficial - {{ $calendar ? $calendar->name : 'Centro Educativo' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #fff;
            color: #0f172a;
            margin: 0;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #0284c7;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #0f172a;
        }
        .header p {
            margin: 0.3rem 0 0 0;
            color: #64748b;
            font-size: 0.9rem;
        }
        .btn-print {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .stats-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 150px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }
        .stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #0284c7;
        }
        .stat-label {
            font-size: 0.78rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 0.65rem 0.85rem;
            text-align: left;
            font-size: 0.875rem;
        }
        th {
            background: #f1f5f9;
            font-weight: 700;
            color: #334155;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .footer {
            margin-top: 2.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #64748b;
        }
        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>📅 {{ $calendar ? $calendar->name : 'Calendario Escolar Oficial' }}</h1>
            <p>Documento Oficial del Centro &bull; Generado el {{ date('d/m/Y H:i') }}</p>
        </div>
        <button onclick="window.print()" class="btn-print">🖨️ Imprimir / Guardar en PDF</button>
    </div>

    @if(isset($stats) && $calendar)
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-value" style="color: #16a34a;">{{ $stats['teaching_days'] }}</div>
            <div class="stat-label">Días Lectivos Netos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #dc2626;">{{ $stats['holidays'] }}</div>
            <div class="stat-label">Festivos / No Lectivos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #2563eb;">{{ $stats['evaluations'] }}</div>
            <div class="stat-label">Evaluaciones</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #0891b2;">{{ $stats['excursions'] }}</div>
            <div class="stat-label">Salidas / Excursiones</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #d97706;">{{ $stats['meetings'] }}</div>
            <div class="stat-label">Reuniones y Claustros</div>
        </div>
    </div>
    @endif

    @if($events->isEmpty())
        <p style="color: #64748b; text-align: center; padding: 2rem;">No hay eventos registrados en este calendario.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 110px;">Fecha Inicio</th>
                    <th style="width: 110px;">Fecha Fin</th>
                    <th>Evento / Actividad</th>
                    <th style="width: 140px;">Categoría</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr>
                        <td style="font-weight: 600;">{{ $event->start_date ? $event->start_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $event->end_date ? $event->end_date->format('d/m/Y') : '-' }}</td>
                        <td style="font-weight: 600; color: #0f172a;">{{ $event->title }}</td>
                        <td>
                            <span class="badge" style="background: {{ $event->type_color }}22; color: {{ $event->type_color }}; border: 1px solid {{ $event->type_color }}55;">
                                {{ $event->type_label }}
                            </span>
                        </td>
                        <td style="color: #475569;">{{ $event->description ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <div>Intranet Educativa &bull; Calendario Escolar</div>
        <div>Página 1 de 1</div>
    </div>
</body>
</html>
