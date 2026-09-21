<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use Illuminate\Http\Request;

class AulaController extends Controller
{

    /**
     * Listado de todas las aulas.
     */
    public function index(Request $request)
    {
        $query = Aula::query();

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('identificacion', 'like', "%{$search}%")
                  ->orWhere('ubicacion', 'like', "%{$search}%")
                  ->orWhere('tipo', 'like', "%{$search}%");
            });
        }

        // Ordenación
        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->input('sort_by');
            if (in_array($sortBy, ['nombre', 'tipo', 'identificacion', 'capacidad', 'ubicacion', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('nombre', 'asc');
        }

        $aulas = $query->paginate($request->input('per_page', 25));
        return view('aulas.index', compact('aulas'));
    }

    /**
     * Formulario de creación de aula.
     */
    public function create()
    {
        return view('aulas.create');
    }

    /**
     * Almacenar nueva aula.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|in:aula,zona',
            'identificacion' => 'nullable|string|max:255',
            'capacidad' => 'nullable|integer|min:1|max:500',
            'equipamiento' => 'nullable|array',
            'equipamiento.*' => 'string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        if ($data['tipo'] === 'zona') {
            $data['capacidad'] = null;
        }

        Aula::create($data);

        return redirect()->route('aulas.index')->with('success', 'Zona/Aula creada correctamente.');
    }

    /**
     * Mostrar detalle de un aula.
     */
    public function show(Aula $aula)
    {
        return view('aulas.show', compact('aula'));
    }

    /**
     * Formulario de edición de aula.
     */
    public function edit(Aula $aula)
    {
        return view('aulas.edit', compact('aula'));
    }

    /**
     * Actualizar aula existente.
     */
    public function update(Request $request, Aula $aula)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|string|in:aula,zona',
            'identificacion' => 'nullable|string|max:255',
            'capacidad' => 'nullable|integer|min:1|max:500',
            'equipamiento' => 'nullable|array',
            'equipamiento.*' => 'string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $data = $request->all();
        if ($data['tipo'] === 'zona') {
            $data['capacidad'] = null;
        }
        if (!$request->has('equipamiento')) {
            $data['equipamiento'] = [];
        }
        $aula->update($data);

        return redirect()->route('aulas.index')->with('success', 'Zona/Aula actualizada correctamente.');
    }

    /**
     * Eliminar aula.
     */
    public function destroy(Aula $aula)
    {
        $aula->delete();
        return redirect()->route('aulas.index')->with('success', 'Aula eliminada correctamente.');
    }

    public function export($format)
    {
        $aulas = Aula::all();
        $data = $aulas->map(function ($aula) {
            return [
                'tipo' => $aula->tipo ?? 'aula',
                'identificacion' => $aula->identificacion,
                'nombre' => $aula->nombre,
                'capacidad' => $aula->capacidad,
                'ubicacion' => $aula->ubicacion,
                'equipamiento' => $aula->equipamiento ? implode(', ', $aula->equipamiento) : '',
                'descripcion' => $aula->descripcion,
            ];
        })->toArray();

        return $this->downloadFormattedData($data, 'zonas_aulas_' . date('Ymd_His'), $format);
    }

    public function template($format)
    {
        $data = [
            [
                'tipo' => 'aula',
                'identificacion' => 'A-101',
                'nombre' => 'Aula 101',
                'capacidad' => 30,
                'ubicacion' => 'Planta 1',
                'equipamiento' => 'Proyector, Pizarra digital',
                'descripcion' => 'Aula estándar de secundaria'
            ],
            [
                'tipo' => 'zona',
                'identificacion' => 'Z-BIB',
                'nombre' => 'Biblioteca',
                'capacidad' => '',
                'ubicacion' => 'Planta Baja',
                'equipamiento' => 'Aire acondicionado',
                'descripcion' => 'Zona de estudio común'
            ]
        ];

        return $this->downloadFormattedData($data, 'plantilla_zonas_aulas', $format);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $content = file_get_contents($file->getRealPath());

        $data = [];

        try {
            if ($extension === 'json') {
                $data = json_decode($content, true);
            } elseif ($extension === 'yaml' || $extension === 'yml') {
                $data = \Symfony\Component\Yaml\Yaml::parse($content);
            } elseif ($extension === 'csv') {
                $lines = array_map('str_getcsv', file($file->getRealPath()));
                if (count($lines) > 0) {
                    $headers = array_shift($lines);
                    // Remove potential BOM from first header
                    if (count($headers) > 0) {
                        $headers[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers[0]);
                    }
                    foreach ($lines as $line) {
                        if (count($headers) === count($line)) {
                            $data[] = array_combine($headers, $line);
                        }
                    }
                }
            } else {
                return back()->withErrors('Formato no soportado. Usa CSV, JSON o YAML.');
            }
        } catch (\Exception $e) {
            return back()->withErrors('Error al procesar el archivo: ' . $e->getMessage());
        }

        if (empty($data)) {
            return back()->withErrors('El archivo está vacío o no tiene el formato correcto.');
        }

        $imported = 0;
        foreach ($data as $row) {
            if (empty($row['nombre']) || empty($row['tipo'])) {
                continue;
            }

            $tipo = strtolower(trim($row['tipo']));
            if (!in_array($tipo, ['aula', 'zona'])) {
                $tipo = 'aula';
            }

            $capacidad = null;
            if ($tipo === 'aula' && isset($row['capacidad']) && $row['capacidad'] !== '') {
                $capacidad = (int)$row['capacidad'];
            }

            $equipamiento = [];
            if (!empty($row['equipamiento'])) {
                $equipamiento = array_map('trim', explode(',', $row['equipamiento']));
            }

            Aula::updateOrCreate(
                [
                    'nombre' => trim($row['nombre'])
                ],
                [
                    'tipo' => $tipo,
                    'identificacion' => !empty($row['identificacion']) ? trim($row['identificacion']) : null,
                    'capacidad' => $capacidad,
                    'ubicacion' => !empty($row['ubicacion']) ? trim($row['ubicacion']) : null,
                    'equipamiento' => $equipamiento,
                    'descripcion' => !empty($row['descripcion']) ? trim($row['descripcion']) : null,
                ]
            );
            $imported++;
        }

        return redirect()->route('aulas.index')->with('success', "Se han importado/actualizado $imported zonas/aulas correctamente.");
    }

    private function downloadFormattedData($data, $filenameBase, $format)
    {
        $filename = $filenameBase . '.' . $format;

        if ($format === 'json') {
            return response()->json($data)->withHeaders([
                'Content-Disposition' => 'attachment; filename=' . $filename,
            ]);
        } elseif ($format === 'yaml' || $format === 'yml') {
            $yaml = \Symfony\Component\Yaml\Yaml::dump($data);
            return response($yaml)->withHeaders([
                'Content-Type' => 'text/yaml',
                'Content-Disposition' => 'attachment; filename=' . $filename,
            ]);
        } elseif ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=' . $filename,
            ];
            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM for Excel
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                if (count($data) > 0) {
                    fputcsv($file, array_keys($data[0]));
                    foreach ($data as $row) {
                        fputcsv($file, $row);
                    }
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        abort(400, 'Invalid format');
    }
}
