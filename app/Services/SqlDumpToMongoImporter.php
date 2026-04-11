<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use App\Models\ClassChangeLog;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseCertificate;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseMaterial;
use App\Models\CourseMaterialProgress;
use App\Models\CourseMaterialQuizAttempt;
use App\Models\CourseModule;
use App\Models\CourseReview;
use App\Models\CourseReviewReply;
use App\Models\CourseVideo;
use App\Models\DiscountCode;
use App\Models\Payment;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Setting;
use App\Models\SystemLog;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SplFileObject;

class SqlDumpToMongoImporter
{
    /**
     * @return array{imported: array<string,int>, skipped: array<string,int>, unknown_tables: array<string,int>, statements:int}
     */
    public function import(string $path, bool $dryRun = false): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("SQL dump not found: {$path}");
        }

        $imported = [];
        $skipped = [];
        $unknownTables = [];
        $statements = 0;
        $modelMap = $this->modelMap();
        $statementBuffer = '';

        $file = new SplFileObject($path, 'r');

        while (! $file->eof()) {
            $line = $file->fgets();

            if (! is_string($line)) {
                continue;
            }

            $trimmed = ltrim($line);

            if ($statementBuffer === '' && ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*'))) {
                continue;
            }

            $statementBuffer .= $line;

            if (! str_contains($line, ';')) {
                continue;
            }

            $statement = trim($statementBuffer);
            $statementBuffer = '';

            if (! preg_match('/^INSERT INTO\s+`([^`]+)`\s+\((.+?)\)\s+VALUES\s*(.+);$/is', $statement, $matches)) {
                continue;
            }

            $table = $matches[1];
            $columns = $this->parseColumns($matches[2]);
            $rows = $this->parseRows($matches[3]);
            $statements++;

            if (! isset($modelMap[$table])) {
                $unknownTables[$table] = ($unknownTables[$table] ?? 0) + count($rows);
                continue;
            }

            foreach ($rows as $rowValues) {
                $attributes = $this->combineRow($columns, $rowValues);

                if ($attributes === null) {
                    $skipped[$table] = ($skipped[$table] ?? 0) + 1;
                    continue;
                }

                if ($dryRun) {
                    $imported[$table] = ($imported[$table] ?? 0) + 1;
                    continue;
                }

                $modelClass = $modelMap[$table];
                $model = new $modelClass();
                $model->forceFill($attributes);
                $model->saveQuietly();

                $imported[$table] = ($imported[$table] ?? 0) + 1;
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'unknown_tables' => $unknownTables,
            'statements' => $statements,
        ];
    }

    public function flushCollections(): void
    {
        $connection = DB::connection('mongodb');

        foreach (array_keys($this->modelMap()) as $table) {
            $connection->getCollection($table)->deleteMany([]);
        }

        $connection->getCollection('counters')->deleteMany([]);
    }

    /**
     * @return array<string, class-string>
     */
    private function modelMap(): array
    {
        return [
            'assistant_conversations' => AssistantConversation::class,
            'assistant_messages' => AssistantMessage::class,
            'class_change_logs' => ClassChangeLog::class,
            'class_schedules' => ClassSchedule::class,
            'classes' => CourseClass::class,
            'course_categories' => CourseCategory::class,
            'course_certificates' => CourseCertificate::class,
            'course_enrollments' => CourseEnrollment::class,
            'course_material_progress' => CourseMaterialProgress::class,
            'course_material_quiz_attempts' => CourseMaterialQuizAttempt::class,
            'course_materials' => CourseMaterial::class,
            'course_modules' => CourseModule::class,
            'course_review_replies' => CourseReviewReply::class,
            'course_reviews' => CourseReview::class,
            'course_videos' => CourseVideo::class,
            'courses' => Course::class,
            'discount_codes' => DiscountCode::class,
            'notifications' => AppNotification::class,
            'payments' => Payment::class,
            'post_categories' => PostCategory::class,
            'posts' => Post::class,
            'quiz_answers' => QuizAnswer::class,
            'quiz_attempts' => QuizAttempt::class,
            'quiz_questions' => QuizQuestion::class,
            'quizzes' => Quiz::class,
            'settings' => Setting::class,
            'system_logs' => SystemLog::class,
            'users' => User::class,
            'wallet_transactions' => WalletTransaction::class,
            'wallets' => Wallet::class,
        ];
    }

    /**
     * @return list<string>
     */
    private function parseColumns(string $columnList): array
    {
        return array_map(
            static fn (string $column): string => trim($column, " \t\n\r\0\x0B`"),
            explode(',', $columnList),
        );
    }

    /**
     * @return list<list<string>>
     */
    private function parseRows(string $valuesBlock): array
    {
        $rows = [];
        $buffer = '';
        $depth = 0;
        $inString = false;
        $escaped = false;
        $length = strlen($valuesBlock);

        for ($index = 0; $index < $length; $index++) {
            $character = $valuesBlock[$index];

            if ($escaped) {
                $buffer .= $character;
                $escaped = false;
                continue;
            }

            if ($character === '\\') {
                $buffer .= $character;
                $escaped = true;
                continue;
            }

            if ($character === "'") {
                $inString = ! $inString;
                $buffer .= $character;
                continue;
            }

            if (! $inString && $character === '(') {
                if ($depth > 0) {
                    $buffer .= $character;
                }

                $depth++;
                continue;
            }

            if (! $inString && $character === ')') {
                $depth--;

                if ($depth === 0) {
                    $rows[] = $this->splitTupleValues($buffer);
                    $buffer = '';
                    continue;
                }
            }

            if ($depth > 0) {
                $buffer .= $character;
            }
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function splitTupleValues(string $tuple): array
    {
        $values = [];
        $buffer = '';
        $inString = false;
        $escaped = false;
        $length = strlen($tuple);

        for ($index = 0; $index < $length; $index++) {
            $character = $tuple[$index];

            if ($escaped) {
                $buffer .= $character;
                $escaped = false;
                continue;
            }

            if ($character === '\\') {
                $buffer .= $character;
                $escaped = true;
                continue;
            }

            if ($character === "'") {
                $inString = ! $inString;
                $buffer .= $character;
                continue;
            }

            if (! $inString && $character === ',') {
                $values[] = trim($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $character;
        }

        if ($buffer !== '') {
            $values[] = trim($buffer);
        }

        return $values;
    }

    /**
     * @param list<string> $columns
     * @param list<string> $rowValues
     * @return array<string,mixed>|null
     */
    private function combineRow(array $columns, array $rowValues): ?array
    {
        if (count($columns) !== count($rowValues)) {
            return null;
        }

        $attributes = [];

        foreach ($columns as $index => $column) {
            $attributes[$column] = $this->decodeValue($rowValues[$index]);
        }

        return $attributes;
    }

    private function decodeValue(string $value): mixed
    {
        $upperValue = strtoupper($value);

        if ($upperValue === 'NULL') {
            return null;
        }

        if (preg_match('/^-?\d+$/', $value)) {
            return (int) $value;
        }

        if (preg_match('/^-?\d+\.\d+$/', $value)) {
            return (float) $value;
        }

        if (! str_starts_with($value, "'") || ! str_ends_with($value, "'")) {
            return $value;
        }

        $decoded = substr($value, 1, -1);
        $decoded = str_replace(["\\'", "''"], ["'", "'"], $decoded);
        $decoded = stripcslashes($decoded);

        if ($decoded === '0000-00-00 00:00:00' || $decoded === '0000-00-00') {
            return null;
        }

        if (($decoded[0] ?? null) === '{' || ($decoded[0] ?? null) === '[') {
            $json = json_decode($decoded, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return $decoded;
    }
}
