<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{


    /**
     * Listado de backups existentes.
     */
    public function index()
    {
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $files = glob($backupDir . '/*.sql');
        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => round(filesize($file) / 1024, 2),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        // Ordenar por fecha descendente
        usort($backups, fn($a, $b) => strcmp($b['date'], $a['date']));

        return view('backups.index', compact('backups'));
    }

    /**
     * Crear un nuevo backup de la base de datos.
     */
    public function store()
    {
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        // Leer la conexión ACTIVA del .env, no hardcodear 'mysql'
        $connection = config('database.default');
        $db = config("database.connections.{$connection}");

        // Si la conexión es SQLite, hacer una copia directa del archivo
        if ($connection === 'sqlite') {
            $sqlitePath = $db['database'];
            if (file_exists($sqlitePath)) {
                copy($sqlitePath, $filepath);
                return redirect()->route('backups.index')->with('success', "Copia de seguridad SQLite creada: {$filename}");
            }
            return redirect()->route('backups.index')->with('error', 'No se encontró el archivo de base de datos SQLite.');
        }

        // Para MySQL / MariaDB
        $passwordArg = empty($db['password']) ? '' : '--password=' . escapeshellarg($db['password']);
        
        // Buscar ruta de mysqldump en XAMPP / Laragon (Windows)
        $mysqldump = 'mysqldump';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Primero comprobar ruta directa de XAMPP
            $xamppPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
            if (file_exists($xamppPath)) {
                $mysqldump = $xamppPath;
            } else {
                // Buscar en Laragon como alternativa
                $possiblePaths = [
                    'C:\\laragon\\bin\\mysql',
                    'C:\\laragon\\bin\\mariadb'
                ];
                
                foreach ($possiblePaths as $basePath) {
                    if (is_dir($basePath)) {
                        $dirs = array_filter(glob($basePath . '\\*'), 'is_dir');
                        if (!empty($dirs)) {
                            rsort($dirs); 
                            foreach ($dirs as $mysqlDir) {
                                $exePath = $mysqlDir . '\\bin\\mysqldump.exe';
                                if (file_exists($exePath)) {
                                    $mysqldump = $exePath;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Comando limpio para Windows — usa los valores reales del .env
        $command = "\"$mysqldump\" --user={$db['username']} {$passwordArg} --host={$db['host']} --port={$db['port']} {$db['database']} --result-file=\"$filepath\" 2>&1";

        $result = null;
        $output = [];
        exec($command, $output, $result);

        if ($result === 0 && file_exists($filepath) && filesize($filepath) > 0) {
            return redirect()->route('backups.index')->with('success', "Copia de seguridad creada: {$filename}");
        }

        if (file_exists($filepath)) {
            @unlink($filepath);
        }

        $errorMsg = empty($output) ? 'No hay salida de error.' : implode(' ', $output);
        $fullDebug = "CMD: $command | RESULT: $result | OUTPUT: $errorMsg";
        return redirect()->route('backups.index')->with('error', 'Error al crear backup. Depuración: ' . $fullDebug);
    }

    /**
     * Descargar un backup.
     */
    public function download($filename)
    {
        $cleanFilename = basename($filename);
        if (!str_ends_with(strtolower($cleanFilename), '.sql') && !str_ends_with(strtolower($cleanFilename), '.sqlite')) {
            return redirect()->route('backups.index')->with('error', 'Nombre o formato de archivo no permitido.');
        }

        $backupDir = realpath(storage_path('app/backups'));
        $filepath = realpath($backupDir . '/' . $cleanFilename);

        if (!$backupDir || !$filepath || !str_starts_with($filepath, $backupDir) || !file_exists($filepath)) {
            return redirect()->route('backups.index')->with('error', 'Archivo no encontrado o acceso no permitido.');
        }

        return response()->download($filepath, $cleanFilename, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $cleanFilename . '"',
        ]);
    }

    /**
     * Eliminar un backup.
     */
    public function destroy($filename)
    {
        $cleanFilename = basename($filename);
        if (!str_ends_with(strtolower($cleanFilename), '.sql') && !str_ends_with(strtolower($cleanFilename), '.sqlite')) {
            return redirect()->route('backups.index')->with('error', 'Nombre o formato de archivo no permitido.');
        }

        $backupDir = realpath(storage_path('app/backups'));
        $filepath = realpath($backupDir . '/' . $cleanFilename);

        if (!$backupDir || !$filepath || !str_starts_with($filepath, $backupDir) || !file_exists($filepath)) {
            return redirect()->route('backups.index')->with('error', 'No se encuentra el archivo a eliminar.');
        }

        if (@unlink($filepath)) {
            return redirect()->route('backups.index')->with('success', 'Backup eliminado correctamente.');
        }

        return redirect()->route('backups.index')->with('error', 'No se pudo eliminar el archivo físico. Permisos denegados.');
    }
}
