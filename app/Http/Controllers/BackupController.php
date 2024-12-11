<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Tasks\Backup\BackupJobFactory;

class BackupController extends Controller
{
    public function createBackup()
    {
        try {
            // Run the backup command
            Artisan::call('backup:run');
            return response()->json(['message' => 'Backup created successfully!'], 200);
        } catch (\Exception $e) {
            // Log and return the error
            \Log::error('Backup error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create backup.'], 500);
        }
    }

    public function listBackups()
{
    $files = collect(Storage::disk('backups')->files())
        ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'sql')
        ->values()
        ->all();

    return response()->json($files);
}

public function restoreBackup(Request $request)
{
    $request->validate(['filename' => 'required|string']);
    $filename = $request->input('filename');
    $backupPath = storage_path("app/backups/{$filename}");

    if (!file_exists($backupPath)) {
        return response()->json(['error' => 'Backup file not found.'], 404);
    }

    if (pathinfo($backupPath, PATHINFO_EXTENSION) !== 'sql') {
        return response()->json(['error' => 'Invalid backup file format.'], 400);
    }

    try {
        $dbHost = config('database.connections.mysql.host');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $command = "mysql -h {$dbHost} -u {$dbUser} --password='{$dbPass}' {$dbName} < {$backupPath}";
        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            throw new Exception('Restore process failed.');
        }

        Log::info("Backup restored successfully: {$filename} by user ID: " . auth()->id());

        return response()->json(['message' => 'Backup restored successfully.']);
    } catch (Exception $e) {
        Log::error("Backup restore failed: {$filename}. Error: " . $e->getMessage());
        return response()->json(['error' => 'Restore failed.'], 500);
    }
}

}

