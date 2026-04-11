<?php

use Database\Seeders\RenderDemoSeeder;
use App\Services\SqlDumpToMongoImporter;
use Illuminate\Database\QueryException;
use App\Services\PortalNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:bootstrap-database', function () {
    if (config('database.default') === 'mongodb') {
        return $this->call('mongodb:bootstrap');
    }

    return $this->call('migrate', ['--force' => true]);
})->purpose('Bootstrap the configured application database');

Artisan::command('mongodb:bootstrap {--fresh} {--seed-demo}', function () {
    if (config('database.default') !== 'mongodb') {
        $this->warn('Skipping MongoDB bootstrap because DB_CONNECTION is not mongodb.');

        return 0;
    }

    $collections = [
        'assistant_conversations',
        'assistant_messages',
        'class_change_logs',
        'class_schedules',
        'course_categories',
        'course_certificates',
        'course_enrollments',
        'course_material_progress',
        'course_material_quiz_attempts',
        'course_materials',
        'course_modules',
        'course_review_replies',
        'course_reviews',
        'course_videos',
        'courses',
        'counters',
        'discount_codes',
        'notifications',
        'payments',
        'post_categories',
        'posts',
        'quiz_answers',
        'quiz_attempts',
        'quiz_questions',
        'quizzes',
        'settings',
        'system_logs',
        'users',
        'wallet_transactions',
        'wallets',
    ];

    if ($this->option('fresh')) {
        $connection = DB::connection('mongodb');

        foreach ($collections as $collection) {
            try {
                $connection->getCollection($collection)->deleteMany([]);
            } catch (QueryException $exception) {
                report($exception);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }

    if ($this->option('seed-demo')) {
        $this->call('db:seed', [
            '--class' => RenderDemoSeeder::class,
            '--force' => true,
        ]);
    }

    $this->info('MongoDB bootstrap completed.');

    return 0;
})->purpose('Prepare MongoDB collections and optionally seed demo data');

Artisan::command('mongodb:import-sql-dump {path?} {--fresh} {--dry-run}', function (SqlDumpToMongoImporter $importer) {
    $path = (string) ($this->argument('path') ?: base_path('khaitriedu.sql'));
    $dryRun = (bool) $this->option('dry-run');

    if (! $dryRun && config('database.default') !== 'mongodb') {
        $this->warn('Skipping SQL dump import because DB_CONNECTION is not mongodb.');

        return 0;
    }

    if (! is_file($path)) {
        $this->error('SQL dump not found: ' . $path);

        return 1;
    }

    if ($this->option('fresh') && ! $dryRun) {
        $importer->flushCollections();
    }

    $summary = $importer->import($path, $dryRun);

    $this->info(($dryRun ? 'Dry-run parsed' : 'Imported') . ' SQL dump: ' . $path);
    $this->line('INSERT statements: ' . ($summary['statements'] ?? 0));

    foreach (($summary['imported'] ?? []) as $table => $count) {
        $this->line(sprintf('  - %s: %d', $table, $count));
    }

    foreach (($summary['skipped'] ?? []) as $table => $count) {
        $this->warn(sprintf('Skipped rows in %s: %d', $table, $count));
    }

    foreach (($summary['unknown_tables'] ?? []) as $table => $count) {
        $this->warn(sprintf('Unknown table %s: %d row(s)', $table, $count));
    }

    return 0;
})->purpose('Import a MySQL SQL dump into MongoDB collections used by the app');

Artisan::command('portal:dispatch-reminders', function (PortalNotificationService $notificationService) {
    $summary = $notificationService->dispatchReminderNotifications();

    $this->info('?? g?i nh?c vi?c h? th?ng.');
    $this->line('L?p s?p khai gi?ng: ' . ($summary['upcoming_classes'] ?? 0));
    $this->line('Gi? ch? s?p h?t h?n: ' . ($summary['seat_hold_expiring'] ?? 0));
    $this->line('N?p ti?n s?p h?t h?n: ' . ($summary['topup_expiring'] ?? 0));
})->purpose('Dispatch automated portal reminders');

Schedule::command('portal:dispatch-reminders')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('blockchain:sync-pending --limit=20')
    ->everyThirtyMinutes()
    ->withoutOverlapping();
