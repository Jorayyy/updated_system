<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupController extends Controller
{
    protected $backupPath = 'backups';

    /**
     * Display a listing of backups
     */
    public function index()
    {
        $backups = collect(Storage::disk('local')->files($this->backupPath))
            ->map(function ($file) {
                return [
                    'filename' => basename($file),
                    'path' => $file,
                    'size' => Storage::disk('local')->size($file),
                    'size_formatted' => $this->formatBytes(Storage::disk('local')->size($file)),
                    'last_modified' => Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file)),
                ];
            })
            ->sortByDesc('last_modified')
            ->values();

        return view('backups.index', compact('backups'));
    }

    /**
     * Create a new backup
     */
    public function create()
    {
        try {
            // Ensure backup directory exists
            if (!Storage::disk('local')->exists($this->backupPath)) {
                Storage::disk('local')->makeDirectory($this->backupPath);
            }

            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $path = storage_path('app/' . $this->backupPath . '/' . $filename);

            // Get database credentials
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');

            // Build mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($path)
            );

            // Execute backup
            $result = null;
            $output = null;
            exec($command . ' 2>&1', $output, $result);

            if ($result !== 0 || !file_exists($path)) {
                // If mysqldump fails, create a PHP-based backup
                $this->createPhpBackup($path);
            }

            return back()->with('success', "Backup created successfully: {$filename}");
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file
     */
    public function download($filename)
    {
        $path = $this->backupPath . '/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        return Storage::disk('local')->download($path);
    }

    /**
     * Delete a backup file
     */
    public function destroy($filename)
    {
        $path = $this->backupPath . '/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        Storage::disk('local')->delete($path);

        return back()->with('success', 'Backup deleted successfully.');
    }

    /**
     * Create a PHP-based backup (fallback if mysqldump is not available)
     */
    protected function createPhpBackup($path)
    {
        $tables = DB::select('SHOW TABLES');
        $database = config('database.connections.mysql.database');
        $tableKey = 'Tables_in_' . $database;

        $sql = "-- MEBS Hiyas Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: {$database}\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            // Get create table statement
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

            // Get table data
            $rows = DB::table($tableName)->get();
            
            if ($rows->count() > 0) {
                $columns = array_keys((array)$rows->first());
                $columnNames = '`' . implode('`, `', $columns) . '`';
                
                foreach ($rows->chunk(100) as $chunk) {
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowValues = array_map(function ($value) {
                            if (is_null($value)) return 'NULL';
                            return "'" . addslashes($value) . "'";
                        }, (array)$row);
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    $sql .= "INSERT INTO `{$tableName}` ({$columnNames}) VALUES\n" . implode(",\n", $values) . ";\n\n";
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($path, $sql);
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
