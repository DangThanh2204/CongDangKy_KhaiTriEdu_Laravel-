<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupManagerService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;

class AdminBackupController extends Controller
{
    public function index(BackupManagerService $backupManagerService)
    {
        $backups = $backupManagerService->listBackups();

        $stats = [
            'total_backups' => $backups->count(),
            'total_size' => $backups->sum('size'),
            'last_backup_at' => $backups->first()['created_at'] ?? null,
        ];

        return view('admin.backups.index', compact('backups', 'stats'));
    }

    public function store(Request $request, BackupManagerService $backupManagerService)
    {
        try {
            $backup = $backupManagerService->createBackup([
                'id' => $request->user()->id,
                'name' => $request->user()->fullname ?: $request->user()->username,
                'email' => $request->user()->email,
            ]);

            $this->recordSystemEvent($request, 'backup_created', [
                'file_name' => $backup['name'],
                'size' => $backup['size'],
                'database' => data_get($backup, 'manifest.database'),
                'tables_count' => data_get($backup, 'manifest.tables_count'),
                'public_files_count' => data_get($backup, 'manifest.public_files_count'),
            ], $backup['name']);

            return redirect()
                ->route('admin.backups.index')
                ->with('success', 'Đã tạo bản sao lưu dữ liệu thành công.');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Tạo bản sao lưu thất bại: ' . $exception->getMessage());
        }
    }

    public function download(string $backup, BackupManagerService $backupManagerService)
    {
        $path = $backupManagerService->downloadPath($backup);

        return response()->download($path, basename($path));
    }

    public function destroy(Request $request, string $backup, BackupManagerService $backupManagerService)
    {
        $fileName = basename($backup);

        try {
            $backupManagerService->deleteBackup($fileName);

            $this->recordSystemEvent($request, 'backup_deleted', [
                'file_name' => $fileName,
            ], $fileName);

            return redirect()
                ->route('admin.backups.index')
                ->with('success', 'Đã xóa bản sao lưu dữ liệu.');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Xóa bản sao lưu thất bại: ' . $exception->getMessage());
        }
    }

    public function restore(Request $request, BackupManagerService $backupManagerService)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:204800',
            'restore_confirmation' => 'required|in:KHOI PHUC',
        ], [
            'restore_confirmation.in' => 'Bạn cần nhập đúng cụm KHOI PHUC để xác nhận khôi phục dữ liệu.',
        ]);

        try {
            $result = $backupManagerService->restoreFromUpload($request->file('backup_file'));

            $this->recordSystemEvent($request, 'backup_restored', [
                'database' => $result['database'] ?? null,
                'tables_count' => $result['tables_count'] ?? 0,
                'public_files_count' => $result['public_files_count'] ?? 0,
                'backup_generated_at' => data_get($result, 'manifest.generated_at'),
            ], 'backup-restore');

            return redirect()
                ->route('admin.backups.index')
                ->with('success', 'Khôi phục dữ liệu thành công. Hệ thống đã nạp lại cơ sở dữ liệu và file tải lên từ bản sao lưu.');
        } catch (\Throwable $exception) {
            $this->recordSystemEvent($request, 'backup_restore_failed', [
                'message' => $exception->getMessage(),
            ], 'backup-restore');

            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Khôi phục dữ liệu thất bại: ' . $exception->getMessage());
        }
    }

    private function recordSystemEvent(Request $request, string $action, array $details = [], ?string $reference = null): void
    {
        try {
            SystemLogService::record('system', $action, $details, $reference, $request);
        } catch (\Throwable $exception) {
        }
    }
}