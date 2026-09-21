<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir - Cursos Escolares</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: #777;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; font-size: 14px; cursor: pointer;">Imprimir documento</button>
        <button onclick="window.close()" style="padding: 8px 16px; font-size: 14px; margin-left: 10px; cursor: pointer;">Cerrar</button>
    </div>

    <h1>Listado de Cursos Escolares</h1>
    <p style="text-align: center; font-size: 12px; color: #666;">Generado el {{ date('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre del Curso</th>
                <th>Estado</th>
                <th>Fecha de Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $sy)
                <tr>
                    <td>{{ $sy->id }}</td>
                    <td><strong>{{ $sy->name }}</strong></td>
                    <td>
                        @if($sy->is_active)
                            <span class="status-active">Activo</span>
                        @else
                            <span class="status-inactive">Inactivo</span>
                        @endif
                    </td>
                    <td>{{ $sy->created_at ? $sy->created_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        // Auto-open print dialog on load
        window.addEventListener('DOMContentLoaded', () => {
            window.print();
        });
    </script>
</body>
</html>
