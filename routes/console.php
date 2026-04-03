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

    $this->info('Đã gửi nhắc việc hệ thống.');
    $this->line('Lớp sắp khai giảng: ' . ($summary['upcoming_classes'] ?? 0));
    $this->line('Giữ chỗ sắp hết hạn: ' . ($summary['seat_hold_expiring'] ?? 0));
    $this->line('Nạp tiền sắp hết hạn: ' . ($summary['topup_expiring'] ?? 0));
})->purpose('Dispatch automated portal reminders');

Schedule::command('portal:dispatch-reminders')
    ->everyFifteenMinutes()
    ->withoutOverlapping();