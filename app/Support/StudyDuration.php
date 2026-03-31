<?php

namespace App\Support;

use App\Models\CourseMaterial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class StudyDuration
{
    public const DEFAULT_VIDEO_MINUTES = 15;
    public const DEFAULT_DOCUMENT_MINUTES = 10;
    public const DEFAULT_ASSIGNMENT_MINUTES = 15;
    public const DEFAULT_QUIZ_MINUTES = 5;
    public const DEFAULT_MEETING_MINUTES = 60;
    public const WORDS_PER_MINUTE = 180;
    public const PDF_PAGE_MINUTES = 3;

    public static function formatMinutes(?int $minutes): string
    {
        $minutes = (int) $minutes;

        if ($minutes <= 0) {
            return 'Chưa ước tính';
        }

        if ($minutes < 60) {
            return $minutes . ' phút';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . ' giờ';
        }

        return $hours . ' giờ ' . $remainingMinutes . ' phút';
    }

    public static function estimateForMaterialInput(
        string $type,
        ?UploadedFile $uploadedFile = null,
        array $metadata = [],
        ?string $content = null,
        ?int $manualMinutes = null
    ): array {
        $manualMinutes = $manualMinutes ? max(1, (int) $manualMinutes) : null;

        if ($manualMinutes) {
            return [
                'minutes' => $manualMinutes,
                'metadata' => array_merge($metadata, [
                    'duration_source' => 'manual',
                    'manual_duration_minutes' => $manualMinutes,
                ]),
            ];
        }

        return match ($type) {
            'video' => self::estimateVideo($metadata),
            'pdf' => self::estimateDocument($uploadedFile, $metadata, $content),
            'assignment' => self::estimateAssignment($metadata, $content),
            'quiz' => self::estimateQuiz($metadata),
            'meeting' => self::estimateMeeting($metadata),
            default => [
                'minutes' => self::DEFAULT_DOCUMENT_MINUTES,
                'metadata' => array_merge($metadata, ['duration_source' => 'default']),
            ],
        };
    }

    public static function estimateForPersistedMaterial(CourseMaterial $material): int
    {
        $storedMinutes = $material->getRawOriginal('estimated_duration_minutes');
        if (! is_null($storedMinutes)) {
            return (int) $storedMinutes;
        }

        $metadata = $material->metadata ?? [];

        if (($metadata['duration_source'] ?? null) === 'manual' && ! empty($metadata['manual_duration_minutes'])) {
            return max(1, (int) $metadata['manual_duration_minutes']);
        }

        return match ($material->type) {
            'video' => self::estimateVideoMinutesFromMetadata($metadata),
            'pdf' => self::estimateDocumentMinutesFromStoredFile($material->file_path, $metadata, $material->content),
            'assignment' => self::estimateAssignmentMinutes($metadata, $material->content),
            'quiz' => self::estimateQuizMinutes($metadata),
            'meeting' => self::estimateMeetingMinutes($metadata),
            default => self::DEFAULT_DOCUMENT_MINUTES,
        };
    }

    protected static function estimateVideo(array $metadata): array
    {
        $minutes = self::estimateVideoMinutesFromMetadata($metadata);

        return [
            'minutes' => $minutes,
            'metadata' => array_merge($metadata, [
                'duration_source' => $minutes === self::DEFAULT_VIDEO_MINUTES ? 'video_default' : 'video_metadata',
            ]),
        ];
    }

    protected static function estimateVideoMinutesFromMetadata(array $metadata): int
    {
        $minutes = (int) data_get($metadata, 'duration_minutes', 0);

        return $minutes > 0 ? $minutes : self::DEFAULT_VIDEO_MINUTES;
    }

    protected static function estimateDocument(?UploadedFile $uploadedFile, array $metadata, ?string $content = null): array
    {
        $metadata = array_merge($metadata, self::buildDocumentInsights($uploadedFile, $metadata));

        if (! empty($metadata['document_word_count'])) {
            return [
                'minutes' => self::minutesFromWordCount((int) $metadata['document_word_count']),
                'metadata' => array_merge($metadata, ['duration_source' => 'document_word_count']),
            ];
        }

        if (! empty($metadata['document_page_count'])) {
            return [
                'minutes' => self::minutesFromPageCount((int) $metadata['document_page_count']),
                'metadata' => array_merge($metadata, ['duration_source' => 'document_page_count']),
            ];
        }

        if (filled($content)) {
            $wordCount = self::countWords($content);
            if ($wordCount > 0) {
                return [
                    'minutes' => self::minutesFromWordCount($wordCount),
                    'metadata' => array_merge($metadata, [
                        'document_word_count' => $wordCount,
                        'duration_source' => 'document_content_words',
                    ]),
                ];
            }
        }

        if (! empty($metadata['document_size_kb'])) {
            return [
                'minutes' => self::minutesFromFileSize((int) $metadata['document_size_kb']),
                'metadata' => array_merge($metadata, ['duration_source' => 'document_file_size']),
            ];
        }

        return [
            'minutes' => self::DEFAULT_DOCUMENT_MINUTES,
            'metadata' => array_merge($metadata, ['duration_source' => 'document_default']),
        ];
    }

    protected static function estimateDocumentMinutesFromStoredFile(?string $filePath, array $metadata, ?string $content = null): int
    {
        $metadata = array_merge($metadata, self::buildDocumentInsightsFromStorage($filePath, $metadata));

        if (! empty($metadata['document_word_count'])) {
            return self::minutesFromWordCount((int) $metadata['document_word_count']);
        }

        if (! empty($metadata['document_page_count'])) {
            return self::minutesFromPageCount((int) $metadata['document_page_count']);
        }

        if (filled($content)) {
            $wordCount = self::countWords($content);
            if ($wordCount > 0) {
                return self::minutesFromWordCount($wordCount);
            }
        }

        if (! empty($metadata['document_size_kb'])) {
            return self::minutesFromFileSize((int) $metadata['document_size_kb']);
        }

        return self::DEFAULT_DOCUMENT_MINUTES;
    }

    protected static function estimateAssignment(array $metadata, ?string $content = null): array
    {
        $minutes = self::estimateAssignmentMinutes($metadata, $content);

        return [
            'minutes' => $minutes,
            'metadata' => array_merge($metadata, [
                'duration_source' => 'assignment_reading',
            ]),
        ];
    }

    protected static function estimateAssignmentMinutes(array $metadata, ?string $content = null): int
    {
        $text = trim((string) data_get($metadata, 'content', $content ?? ''));
        if ($text === '') {
            return self::DEFAULT_ASSIGNMENT_MINUTES;
        }

        return max(self::DEFAULT_ASSIGNMENT_MINUTES, self::minutesFromWordCount(self::countWords($text)));
    }

    protected static function estimateQuiz(array $metadata): array
    {
        $minutes = self::estimateQuizMinutes($metadata);

        return [
            'minutes' => $minutes,
            'metadata' => array_merge($metadata, [
                'duration_source' => 'quiz_question_count',
            ]),
        ];
    }

    protected static function estimateQuizMinutes(array $metadata): int
    {
        $questionCount = collect($metadata['questions'] ?? [])->filter(fn ($question) => filled($question['question'] ?? null))->count();

        if ($questionCount <= 0) {
            return self::DEFAULT_QUIZ_MINUTES;
        }

        return max(self::DEFAULT_QUIZ_MINUTES, $questionCount * 2);
    }

    protected static function estimateMeeting(array $metadata): array
    {
        $minutes = self::estimateMeetingMinutes($metadata);
        [$startsAt, $endsAt] = self::resolveMeetingWindow($metadata);

        return [
            'minutes' => $minutes,
            'metadata' => array_merge($metadata, [
                'duration_source' => ($startsAt && $endsAt && $endsAt->gt($startsAt))
                    ? 'meeting_schedule'
                    : 'meeting_default',
            ]),
        ];
    }

    protected static function estimateMeetingMinutes(array $metadata): int
    {
        [$startsAt, $endsAt] = self::resolveMeetingWindow($metadata);

        if ($startsAt && $endsAt && $endsAt->gt($startsAt)) {
            return max(15, (int) $startsAt->diffInMinutes($endsAt));
        }

        $minutes = (int) data_get($metadata, 'duration_minutes', 0);

        return $minutes > 0 ? $minutes : self::DEFAULT_MEETING_MINUTES;
    }

    protected static function minutesFromWordCount(int $wordCount): int
    {
        if ($wordCount <= 0) {
            return self::DEFAULT_DOCUMENT_MINUTES;
        }

        return max(5, (int) ceil($wordCount / self::WORDS_PER_MINUTE));
    }

    protected static function minutesFromPageCount(int $pageCount): int
    {
        if ($pageCount <= 0) {
            return self::DEFAULT_DOCUMENT_MINUTES;
        }

        return max(5, $pageCount * self::PDF_PAGE_MINUTES);
    }

    protected static function minutesFromFileSize(int $sizeKb): int
    {
        if ($sizeKb <= 0) {
            return self::DEFAULT_DOCUMENT_MINUTES;
        }

        return max(5, (int) ceil($sizeKb / 250) * 4);
    }

    protected static function buildDocumentInsights(?UploadedFile $uploadedFile, array $metadata = []): array
    {
        if (! $uploadedFile || ! $uploadedFile->isValid()) {
            return $metadata;
        }

        $path = $uploadedFile->getRealPath();
        $extension = strtolower((string) $uploadedFile->getClientOriginalExtension());
        $insights = [
            'document_extension' => $extension,
            'document_size_kb' => max(1, (int) ceil(($uploadedFile->getSize() ?: 0) / 1024)),
        ];

        if ($extension === 'docx') {
            $wordCount = self::extractDocxWordCount($path);
            if ($wordCount > 0) {
                $insights['document_word_count'] = $wordCount;
            }
        }

        if ($extension === 'pdf') {
            $pageCount = self::extractPdfPageCount($path);
            if ($pageCount > 0) {
                $insights['document_page_count'] = $pageCount;
            }
        }

        return array_merge($metadata, $insights);
    }

    protected static function buildDocumentInsightsFromStorage(?string $filePath, array $metadata = []): array
    {
        if (! $filePath || ! Storage::disk('public')->exists($filePath)) {
            return $metadata;
        }

        $absolutePath = Storage::disk('public')->path($filePath);
        $extension = strtolower((string) pathinfo($absolutePath, PATHINFO_EXTENSION));
        $insights = [
            'document_extension' => $metadata['document_extension'] ?? $extension,
            'document_size_kb' => $metadata['document_size_kb'] ?? max(1, (int) ceil((filesize($absolutePath) ?: 0) / 1024)),
        ];

        if ($extension === 'docx' && empty($metadata['document_word_count'])) {
            $wordCount = self::extractDocxWordCount($absolutePath);
            if ($wordCount > 0) {
                $insights['document_word_count'] = $wordCount;
            }
        }

        if ($extension === 'pdf' && empty($metadata['document_page_count'])) {
            $pageCount = self::extractPdfPageCount($absolutePath);
            if ($pageCount > 0) {
                $insights['document_page_count'] = $pageCount;
            }
        }

        return array_merge($metadata, $insights);
    }

    protected static function extractDocxWordCount(?string $path): int
    {
        if (! $path || ! class_exists(ZipArchive::class)) {
            return 0;
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return 0;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! $xml) {
            return 0;
        }

        return self::countWords(strip_tags($xml));
    }

    protected static function extractPdfPageCount(?string $path): int
    {
        if (! $path || ! is_file($path)) {
            return 0;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return 0;
        }

        preg_match_all('/\/Type\s*\/Page\b/', $contents, $matches);

        return count($matches[0]);
    }

    protected static function countWords(?string $text): int
    {
        if (! $text) {
            return 0;
        }

        preg_match_all('/[\p{L}\p{N}_]+/u', strip_tags($text), $matches);

        return count($matches[0]);
    }

    protected static function resolveMeetingWindow(array $metadata): array
    {
        return [
            self::parseDate(data_get($metadata, 'meeting_starts_at')),
            self::parseDate(data_get($metadata, 'meeting_ends_at')),
        ];
    }

    protected static function parseDate(mixed $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
