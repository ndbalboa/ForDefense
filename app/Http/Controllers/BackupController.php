<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class BackupController extends Controller
{
    public function createBackup()
    {
        try {
            $backupFileName = 'backup_' . now()->format('Y_m_d_H_i_s') . '.zip';
            $backupFilePath = storage_path('app/backups/' . $backupFileName);
            
            $zip = new ZipArchive();

            if ($zip->open($backupFilePath, ZipArchive::CREATE) !== TRUE) {
                return response()->json(['message' => 'Failed to create ZIP file.'], 500);
            }

            \Artisan::call('backup:run');
            
            $zip->addFile(base_path('.env'), '.env');
            
            $files = Storage::allFiles('public');
            foreach ($files as $file) {
                $zip->addFile(storage_path('app/' . $file), 'public/' . $file); 
            }

            $publicStoragePath = public_path('storage');
            if (is_dir($publicStoragePath)) {
                $this->addFolderToZip($zip, $publicStoragePath, 'storage');
            }

            $zip->close();

            return response()->json([
                'message' => 'Backup created successfully.',
                'backup_file' => $backupFileName,
                'backup_path' => storage_path('app/backups/' . $backupFileName)
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Backup failed: ' . $e->getMessage()], 500);
        }
    }

    private function addFolderToZip($zip, $folderPath, $zipFolderName)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folderPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folderPath) + 1);
                $zip->addFile($filePath, $zipFolderName . '/' . $relativePath);
            }
        }
    }

    public function restoreBackup(Request $request)
    {
        $backupFile = $request->file('backup_file');
    
        if (!$backupFile) {
            return response()->json(['message' => 'No backup file selected.'], 400);
        }
    
        try {
            $zip = new ZipArchive();
            $backupPath = $backupFile->getRealPath();
    
            if ($zip->open($backupPath) !== true) {
                return response()->json(['message' => 'Failed to open backup file.'], 500);
            }
    
            // Define the extraction path
            $extractPath = storage_path('app/backups/extracted');
    
            // Ensure the directory exists
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0777, true);
            }
    
            // Extract files selectively or entirely
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
    
                // Skip unnecessary files or large files if not needed
                if ($this->shouldExtractFile($fileName)) {
                    $zip->extractTo($extractPath, $fileName);
                }
            }
    
            $zip->close();
    
            return response()->json([
                'message' => 'Backup restored successfully.',
                'extracted_path' => $extractPath,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Restore failed: ' . $e->getMessage()], 500);
        }
    }
    
    private function shouldExtractFile(string $fileName): bool
    {
        // Example: Skip logs or temporary files
        $excludedExtensions = ['.log', '.tmp'];
        foreach ($excludedExtensions as $ext) {
            if (str_ends_with($fileName, $ext)) {
                return false;
            }
        }
        return true;
    }
    
    public function listBackups()
    {
        $backupFolder = storage_path('app/backups');
        
        $backups = [];

        if (is_dir($backupFolder)) {
            $backups = array_diff(scandir($backupFolder), ['.', '..']); 
        }

        return response()->json(['backups' => $backups]);
    }

    public function deleteBackup($filename)
    {
        $backupPath = storage_path('app/backups/' . $filename);

        if (!file_exists($backupPath)) {
            return response()->json(['message' => 'Backup file not found.'], 404);
        }

        try {
            unlink($backupPath); 
            return response()->json(['message' => 'Backup file deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete backup file: ' . $e->getMessage()], 500);
        }
    }

    public function downloadBackup($backupFileName)
    {
        $backupPath = storage_path('app/backups/' . $backupFileName);
    
        if (!file_exists($backupPath)) {
            return response()->json(['message' => 'Backup file not found.'], 404);
        }
    
        $headers = [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . basename($backupPath) . '"',
        ];
    
        return response()->download($backupPath, $backupFileName, $headers);
    }
}
