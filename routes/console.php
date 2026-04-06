<?php

use App\Services\PortalNotificationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
