<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use MongoDB\BSON\Binary;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Database as MongoDatabase;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
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

        $db = $this->mongoDb();
        $collections = $this->fetchCollections($db);
        $publicFiles = $this->publicFiles();

        $manifest = [
            'generated_at'       => now()->toIso8601String(),
            'app_name'           => config('app.name', 'Khai Tri Edu'),
            'environment'        => config('app.env'),
            'database'           => $this->databaseName(),
            'driver'             => 'mongodb',
            'collections_count'  => count($collections),
            'public_files_count' => count($publicFiles),
            'generated_by'       => $actor,
        ];

        $fileName = 'khai-tri-backup-' . now()->format('Ymd-His') . '.zip';
        $zipPath  = $this->backupDirectory() . DIRECTORY_SEPARATOR . $fileName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Không thể tạo file backup.');
        }

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        foreach ($collections as $name) {
            $json = $this->dumpCollection($db, $name);
            $zip->addFromString('collections/' . $name . '.json', $json);
        }

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

        $hasManifest    = $zip->locateName('manifest.json') !== false;
        $hasCollections = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (str_starts_with($zip->getNameIndex($i), 'collections/')) {
                $hasCollections = true;
                break;
            }
        }

        if (! $hasManifest || ! $hasCollections) {
            $zip->close();
            File::deleteDirectory($extractPath);
            throw new RuntimeException('File backup không hợp lệ hoặc thiếu dữ liệu cần thiết.');
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $manifest = json_decode((string) File::get($extractPath . '/manifest.json'), true);
        if (! is_array($manifest)) {
            File::deleteDirectory($extractPath);
            throw new RuntimeException('Backup không đọc được hoặc bị hỏng.');
        }

        $collectionsPath = $extractPath . DIRECTORY_SEPARATOR . 'collections';
        $collectionFiles = File::isDirectory($collectionsPath) ? File::files($collectionsPath) : [];

        $db = $this->mongoDb();

        try {
            // Drop existing collections
            foreach ($this->fetchCollections($db) as $name) {
                $db->dropCollection($name);
            }

            // Restore each collection
            foreach ($collectionFiles as $file) {
                $name      = $file->getFilenameWithoutExtension();
                $documents = json_decode((string) File::get($file->getRealPath()), true);

                if (! empty($documents)) {
                    $docs = array_map(fn ($doc) => $this->arrayToBson($doc), $documents);
                    $db->selectCollection($name)->insertMany($docs);
                }
            }
        } catch (\Throwable $e) {
            File::deleteDirectory($extractPath);
            throw new RuntimeException('Khôi phục cơ sở dữ liệu thất bại: ' . $e->getMessage(), 0, $e);
        }

        // Restore public files
        $publicPath = $this->publicStoragePath();
        File::ensureDirectoryExists($publicPath);
        File::cleanDirectory($publicPath);

        $restoredPublicPath = $extractPath . DIRECTORY_SEPARATOR . 'public';
        if (File::isDirectory($restoredPublicPath)) {
            File::copyDirectory($restoredPublicPath, $publicPath);
        }

        File::deleteDirectory($extractPath);

        return [
            'manifest'           => $manifest,
            'restored_at'        => now(),
            'database'           => $manifest['database'] ?? $this->databaseName(),
            'collections_count'  => count($collectionFiles),
            'public_files_count' => (int) ($manifest['public_files_count'] ?? 0),
        ];
    }

    private function mongoDb(): MongoDatabase
    {
        return DB::connection('mongodb')->getMongoDB();
    }

    private function fetchCollections(MongoDatabase $db): array
    {
        $names = [];
        foreach ($db->listCollectionNames() as $name) {
            $names[] = $name;
        }
        sort($names);
        return $names;
    }

    private function dumpCollection(MongoDatabase $db, string $name): string
    {
        $documents = [];
        foreach ($db->selectCollection($name)->find() as $doc) {
            $documents[] = $this->bsonToArray($doc);
        }
        return json_encode($documents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    // Convert BSON types → plain PHP array (JSON-safe)
    private function bsonToArray(mixed $value): mixed
    {
        if ($value instanceof ObjectId) {
            return ['$oid' => (string) $value];
        }

        if ($value instanceof UTCDateTime) {
            return ['$date' => $value->toDateTime()->format('c')];
        }

        if ($value instanceof Decimal128) {
            return ['$numberDecimal' => (string) $value];
        }

        if ($value instanceof Binary) {
            return ['$binary' => base64_encode($value->getData()), '$type' => $value->getType()];
        }

        if ($value instanceof Regex) {
            return ['$regex' => $value->getPattern(), '$options' => $value->getFlags()];
        }

        if ($value instanceof BSONDocument || (is_object($value) && ! ($value instanceof \JsonSerializable))) {
            $result = [];
            foreach ((array) $value as $k => $v) {
                $result[$k] = $this->bsonToArray($v);
            }
            return $result;
        }

        if ($value instanceof BSONArray) {
            return array_values(array_map(fn ($v) => $this->bsonToArray($v), (array) $value));
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result[$k] = $this->bsonToArray($v);
            }
            return $result;
        }

        return $value;
    }

    // Convert plain PHP array → BSON types (for restore)
    private function arrayToBson(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (isset($value['$oid']) && count($value) === 1) {
            return new ObjectId($value['$oid']);
        }

        if (isset($value['$date']) && count($value) === 1) {
            return new UTCDateTime(new \DateTime($value['$date']));
        }

        if (isset($value['$numberDecimal']) && count($value) === 1) {
            return new Decimal128($value['$numberDecimal']);
        }

        if (isset($value['$binary'], $value['$type'])) {
            return new Binary(base64_decode($value['$binary']), (int) $value['$type']);
        }

        if (isset($value['$regex'])) {
            return new Regex($value['$regex'], $value['$options'] ?? '');
        }

        $result = [];
        foreach ($value as $k => $v) {
            $result[$k] = $this->arrayToBson($v);
        }
        return $result;
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
        $manifest  = $this->readManifest($path);
        $createdAt = $manifest['generated_at'] ?? date(DATE_ATOM, filemtime($path));

        return [
            'name'       => basename($path),
            'path'       => $path,
            'size'       => filesize($path) ?: 0,
            'size_label' => $this->formatBytes(filesize($path) ?: 0),
            'created_at' => $createdAt,
            'manifest'   => $manifest,
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

        $content = $zip->getFromName('manifest.json');
        $zip->close();

        if (! is_string($content) || trim($content) === '') {
            return [];
        }

        $manifest = json_decode($content, true);

        return is_array($manifest) ? $manifest : [];
    }

    private function databaseName(): string
    {
        return DB::connection('mongodb')->getDatabaseName();
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
