<?php

namespace App\Services;

use DateTimeInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class BackupManagerService
{
    public function listBackups(): Collection
    {
        File::ensureDirectoryExists($this->backupDirectory());

        return collect(File::files($this->backupDirectory()))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'zip')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values()
            ->map(fn ($file) => $this->describeBackup($file->getRealPath()));
    }

    public function createBackup(?array $actor = null): array
    {
        $this->ensureZipAvailable();
        File::ensureDirectoryExists($this->backupDirectory());

        $tables = $this->fetchTables();
        $publicFiles = $this->publicFiles();
        $manifest = [
            'generated_at' => now()->toIso8601String(),
            'app_name' => config('app.name', 'Khai Tri Edu'),
            'environment' => config('app.env'),
            'database' => $this->databaseName(),
            'driver' => $this->driver(),
            'tables_count' => count($tables),
            'public_files_count' => count($publicFiles),
            'generated_by' => $actor,
        ];

        $fileName = 'khai-tri-backup-' . now()->format('Ymd-His') . '.zip';
        $zipPath = $this->backupDirectory() . DIRECTORY_SEPARATOR . $fileName;
        $databaseDump = $this->buildDatabaseDump($tables);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Không thể tạo file backup.');
        }

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $zip->addFromString('database.sql', $databaseDump);

        $publicRoot = $this->publicStoragePath();
        foreach ($publicFiles as $filePath) {
            $relativePath = ltrim(Str::replaceFirst($publicRoot, '', $filePath), DIRECTORY_SEPARATOR);
            $zip->addFile($filePath, 'public/' . str_replace('\\', '/', $relativePath));
        }

        $zip->close();

        return $this->describeBackup($zipPath);
    }

    public function restoreFromUpload(UploadedFile $backupFile): array
    {
        $this->ensureZipAvailable();

        $tempDirectory = $this->temporaryDirectory();
        File::ensureDirectoryExists($tempDirectory);

        $temporaryPath = $tempDirectory . DIRECTORY_SEPARATOR . 'uploaded-' . now()->format('Ymd-His') . '-' . Str::random(8) . '.zip';
        File::copy($backupFile->getRealPath(), $temporaryPath);

        try {
            return $this->restoreFromZip($temporaryPath);
        } finally {
            File::delete($temporaryPath);
        }
    }

    public function deleteBackup(string $fileName): void
    {
        $path = $this->resolveBackupPath($fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Không tìm thấy file backup cần xóa.');
        }

        File::delete($path);
    }

    public function downloadPath(string $fileName): string
    {
        $path = $this->resolveBackupPath($fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Không tìm thấy file backup cần tải xuống.');
        }

        return $path;
    }

    private function restoreFromZip(string $zipPath): array
    {
        $extractPath = $this->temporaryDirectory() . DIRECTORY_SEPARATOR . 'restore-' . Str::uuid();
        File::ensureDirectoryExists($extractPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Không thể mở file backup để khôi phục.');
        }

        if ($zip->locateName('database.sql') === false || $zip->locateName('manifest.json') === false) {
            $zip->close();
            File::deleteDirectory($extractPath);
            throw new RuntimeException('File backup không hợp lệ hoặc thiếu dữ liệu cần thiết.');
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $databaseDumpPath = $extractPath . DIRECTORY_SEPARATOR . 'database.sql';
        $manifestPath = $extractPath . DIRECTORY_SEPARATOR . 'manifest.json';
        $manifest = json_decode((string) File::get($manifestPath), true);
        $sql = (string) File::get($databaseDumpPath);

        if (! is_array($manifest) || trim($sql) === '') {
            File::deleteDirectory($extractPath);
            throw new RuntimeException('Backup không đọc được hoặc bị hỏng.');
        }

        DB::connection()->getPdo();

        try {
            $this->disableForeignKeys();
            foreach ($this->fetchTables() as $table) {
                DB::statement('DROP TABLE IF EXISTS ' . $this->quoteIdentifier($table));
            }
            DB::unprepared($sql);
            $this->enableForeignKeys();
        } catch (\Throwable $exception) {
            try {
                $this->enableForeignKeys();
            } catch (\Throwable $innerException) {
            }

            File::deleteDirectory($extractPath);
            throw new RuntimeException('Khôi phục cơ sở dữ liệu thất bại: ' . $exception->getMessage(), 0, $exception);
        }

        $publicPath = $this->publicStoragePath();
        File::ensureDirectoryExists($publicPath);
        File::cleanDirectory($publicPath);

        $restoredPublicPath = $extractPath . DIRECTORY_SEPARATOR . 'public';
        if (File::isDirectory($restoredPublicPath)) {
            File::copyDirectory($restoredPublicPath, $publicPath);
        }

        File::deleteDirectory($extractPath);

        return [
            'manifest' => $manifest,
            'restored_at' => now(),
            'database' => $manifest['database'] ?? $this->databaseName(),
            'tables_count' => (int) ($manifest['tables_count'] ?? 0),
            'public_files_count' => (int) ($manifest['public_files_count'] ?? 0),
        ];
    }

    private function buildDatabaseDump(array $tables): string
    {
        $dump = $this->driver() === 'sqlite'
            ? [
                '-- Khai Tri Edu backup',
                '-- Generated at: ' . now()->toDateTimeString(),
                'PRAGMA foreign_keys=OFF;',
                'BEGIN TRANSACTION;',
                '',
            ]
            : [
                '-- Khai Tri Edu backup',
                '-- Generated at: ' . now()->toDateTimeString(),
                'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
                'SET time_zone = "+00:00";',
                'SET FOREIGN_KEY_CHECKS=0;',
                '',
            ];

        foreach ($tables as $table) {
            $createTableSql = $this->fetchCreateTableSql($table);

            if (! $createTableSql) {
                continue;
            }

            $dump[] = '-- --------------------------------------------------------';
            $dump[] = '-- Table structure for ' . $this->quoteIdentifier($table);
            $dump[] = 'DROP TABLE IF EXISTS ' . $this->quoteIdentifier($table) . ';';
            $dump[] = rtrim($createTableSql, ';') . ';';
            $dump[] = '';

            $rows = collect(DB::table($table)->get())->map(fn ($row) => (array) $row);
            if ($rows->isEmpty()) {
                continue;
            }

            $columns = array_keys($rows->first());
            $columnSql = collect($columns)
                ->map(fn ($column) => $this->quoteIdentifier($column))
                ->implode(', ');

            foreach ($rows->chunk(200) as $chunk) {
                $valueSql = $chunk->map(function (array $row) use ($columns) {
                    $values = collect($columns)
                        ->map(fn ($column) => $this->quoteValue($row[$column] ?? null))
                        ->implode(', ');

                    return '(' . $values . ')';
                })->implode(",\n");

                $dump[] = 'INSERT INTO ' . $this->quoteIdentifier($table) . ' (' . $columnSql . ') VALUES';
                $dump[] = $valueSql . ';';
                $dump[] = '';
            }
        }

        if ($this->driver() === 'sqlite') {
            $dump[] = 'COMMIT;';
            $dump[] = 'PRAGMA foreign_keys=ON;';
        } else {
            $dump[] = 'SET FOREIGN_KEY_CHECKS=1;';
        }

        return implode("\n", $dump);
    }

    private function fetchTables(): array
    {
        if ($this->driver() === 'sqlite') {
            return collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"))
                ->map(fn ($row) => data_get((array) $row, 'name'))
                ->filter()
                ->values()
                ->all();
        }

        $rows = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');

        return collect($rows)
            ->map(fn ($row) => array_values((array) $row)[0] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    private function fetchCreateTableSql(string $table): ?string
    {
        if ($this->driver() === 'sqlite') {
            $row = DB::selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?', ['table', $table]);

            return $row?->sql ?: null;
        }

        $escapedTable = str_replace('`', '``', $table);
        $createTableRow = (array) (DB::select('SHOW CREATE TABLE `' . $escapedTable . '`')[0] ?? []);

        return array_values($createTableRow)[1] ?? null;
    }

    private function disableForeignKeys(): void
    {
        if ($this->driver() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    private function enableForeignKeys(): void
    {
        if ($this->driver() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function quoteIdentifier(string $identifier): string
    {
        if ($this->driver() === 'sqlite') {
            return '"' . str_replace('"', '""', $identifier) . '"';
        }

        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function publicFiles(): array
    {
        $publicPath = $this->publicStoragePath();

        if (! File::isDirectory($publicPath)) {
            return [];
        }

        return collect(File::allFiles($publicPath))
            ->map(fn ($file) => $file->getRealPath())
            ->filter()
            ->values()
            ->all();
    }

    private function describeBackup(string $path): array
    {
        $manifest = $this->readManifest($path);
        $createdAt = $manifest['generated_at'] ?? date(DATE_ATOM, filemtime($path));

        return [
            'name' => basename($path),
            'path' => $path,
            'size' => filesize($path) ?: 0,
            'size_label' => $this->formatBytes(filesize($path) ?: 0),
            'created_at' => $createdAt,
            'manifest' => $manifest,
        ];
    }

    private function readManifest(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $manifestContent = $zip->getFromName('manifest.json');
        $zip->close();

        if (! is_string($manifestContent) || trim($manifestContent) === '') {
            return [];
        }

        $manifest = json_decode($manifestContent, true);

        return is_array($manifest) ? $manifest : [];
    }

    private function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if ($value instanceof DateTimeInterface) {
            return DB::getPdo()->quote($value->format('Y-m-d H:i:s'));
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return DB::getPdo()->quote((string) $value);
    }

    private function ensureZipAvailable(): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Máy chủ chưa bật extension ZipArchive nên chưa thể sao lưu hoặc khôi phục.');
        }
    }

    private function resolveBackupPath(string $fileName): string
    {
        return $this->backupDirectory() . DIRECTORY_SEPARATOR . basename($fileName);
    }

    private function backupDirectory(): string
    {
        return storage_path('app/backups');
    }

    private function temporaryDirectory(): string
    {
        return storage_path('app/backup-temp');
    }

    private function publicStoragePath(): string
    {
        return storage_path('app/public');
    }

    private function databaseName(): ?string
    {
        return DB::connection()->getDatabaseName();
    }

    private function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 2) . ' ' . $units[$power];
    }
}
