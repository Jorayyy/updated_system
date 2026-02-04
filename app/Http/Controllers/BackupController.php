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

            // Check database driver
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql') {
                $this->createMysqlBackup($path, $filename);
            } else {
                // For SQLite and other drivers, use PHP-based backup
                $this->createPhpBackup($path);
            }

            return back()->with('success', "Backup created successfully: {$filename}");
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Create MySQL backup using mysqldump
     */
    protected function createMysqlBackup($path, $filename)
    {
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
     * Restore database from backup file
     */
    public function restore($filename)
    {
        $path = $this->backupPath . '/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        try {
            $sql = Storage::disk('local')->get($path);
            $driver = DB::getDriverName();
            
            // Disable foreign key checks before restore
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } else {
                DB::statement('PRAGMA foreign_keys=OFF');
            }
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    DB::unprepared($statement);
                }
            }
            
            // Re-enable foreign key checks
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } else {
                DB::statement('PRAGMA foreign_keys=ON');
            }

            return back()->with('success', 'Database restored successfully from: ' . $filename);
        } catch (\Exception $e) {
            // Re-enable foreign key checks even on failure
            try {
                $driver = DB::getDriverName();
                if ($driver === 'mysql') {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } else {
                    DB::statement('PRAGMA foreign_keys=ON');
                }
            } catch (\Exception $ex) {}
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload and restore from an external backup file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:102400', // Max 100MB
        ]);

        $file = $request->file('backup_file');
        $extension = $file->getClientOriginalExtension();

        // Validate file extension
        if (!in_array($extension, ['sql'])) {
            return back()->with('error', 'Invalid file type. Only .sql files are allowed.');
        }

        try {
            // Store the uploaded file
            $filename = 'uploaded_' . date('Y-m-d_His') . '.sql';
            $file->storeAs($this->backupPath, $filename, 'local');

            // Get the file contents and restore
            $path = storage_path('app/' . $this->backupPath . '/' . $filename);
            $sql = file_get_contents($path);
            $driver = DB::getDriverName();

            // Disable foreign key checks before restore
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } else {
                DB::statement('PRAGMA foreign_keys=OFF');
            }

            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    DB::unprepared($statement);
                }
            }

            // Re-enable foreign key checks
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } else {
                DB::statement('PRAGMA foreign_keys=ON');
            }

            return back()->with('success', 'Database restored successfully from uploaded file. Backup saved as: ' . $filename);
        } catch (\Exception $e) {
            // Re-enable foreign key checks even on failure
            try {
                $driver = DB::getDriverName();
                if ($driver === 'mysql') {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                } else {
                    DB::statement('PRAGMA foreign_keys=ON');
                }
            } catch (\Exception $ex) {}
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Create a PHP-based backup (fallback if mysqldump is not available)
     */
    protected function createPhpBackup($path)
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite specific query
            $tables = DB::select('SELECT name FROM sqlite_master WHERE type = "table" AND name NOT LIKE "sqlite_%"');
            $tableKey = 'name';
        } else {
            // MySQL specific query
            $tables = DB::select('SHOW TABLES');
            $database = config('database.connections.mysql.database');
            $tableKey = 'Tables_in_' . $database;
        }

        $sql = "-- MEBS Hiyas Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database Driver: {$driver}\n\n";
        
        if ($driver === 'mysql') {
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        } else {
            $sql .= "PRAGMA foreign_keys=OFF;\n\n";
        }

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            if ($driver === 'mysql') {
                // Get create table statement for MySQL
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
            } else {
                // For SQLite, get schema from sqlite_master
                $createTable = DB::select("SELECT sql FROM sqlite_master WHERE name = ? AND type = 'table'", [$tableName]);
                if (!empty($createTable)) {
                    $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $sql .= $createTable[0]->sql . ";\n\n";
                }
            }

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

        if ($driver === 'mysql') {
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        } else {
            $sql .= "PRAGMA foreign_keys=ON;\n";
        }

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
