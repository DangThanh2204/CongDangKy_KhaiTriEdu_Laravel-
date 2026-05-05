<?php

namespace App\Services;

use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\PortalAlertNotification;

class PortalNotificationService
{
    public function notifyEnrollmentReceived(CourseEnrollment $enrollment, bool $walletPaid = false): void
    {
        $enrollment->loadMissing(['user', 'courseClass.course.category']);

        $user = $enrollment->user;
        $course = $enrollment->courseClass?->course;
        $class = $enrollment->courseClass;

        if (! $user || ! $course || ! $class) {
            return;
        }

        $lines = [
            'Khóa học: ' . $course->title,
            'Đợt học: ' . $class->name,
            'Hình thức học: ' . $course->delivery_mode_label,
            optional($class->start_date)->format('d/m/Y')
                ? 'Ngày khai giảng dự kiến: ' . optional($class->start_date)->format('d/m/Y')
                : null,
            $walletPaid ? 'Hệ thống đã ghi nhận thanh toán từ ví của bạn.' : null,
            'Trạng thái hiện tại: Chờ duyệt.',
        ];

        $this->dispatch($user, [
            'category' => 'enrollment',
            'subject' => 'Khai Trí Edu đã tiếp nhận đăng ký khóa học',
            'title' => 'Đăng ký đã được tiếp nhận',
            'message' => 'Yêu cầu đăng ký khóa học "' . $course->title . '" của bạn đã được ghi nhận và đang chờ xét duyệt.',
            'lines' => array_values(array_filter($lines)),
            'action_text' => 'Mở bảng điều khiển',
            'action_url' => route('student.dashboard'),
            'icon' => 'fas fa-hourglass-half',
            'variant' => 'warning',
            'entity_type' => 'course_enrollment',
            'entity_id' => $enrollment->id,
        ]);
    }

    public function notifyEnrollmentApproved(CourseEnrollment $enrollment, bool $walletPaid = false): void
    {
        $enrollment->loadMissing(['user', 'courseClass.course']);

        $user = $enrollment->user;
        $course = $enrollment->courseClass?->course;
        $class = $enrollment->courseClass;

        if (! $user || ! $course || ! $class) {
            return;
        }

        $canLearnNow = $course->isOnline();

        $lines = [
            'Khóa học: ' . $course->title,
            'Đợt học: ' . $class->name,
            optional($class->start_date)->format('d/m/Y')
                ? 'Ngày khai giảng: ' . optional($class->start_date)->format('d/m/Y')
                : null,
            $walletPaid ? 'Thanh toán từ ví của bạn đã được xác nhận thành công.' : null,
            $canLearnNow ? 'Bạn có thể vào học ngay trên hệ thống.' : 'Hệ thống đã duyệt hồ sơ và giữ chỗ chính thức cho bạn.',
        ];

        $this->dispatch($user, [
            'category' => 'enrollment',
            'subject' => 'Đăng ký khóa học đã được duyệt',
            'title' => 'Đăng ký đã được duyệt',
            'message' => 'Bạn đã được duyệt vào khóa học "' . $course->title . '".',
            'lines' => array_values(array_filter($lines)),
            'action_text' => $canLearnNow ? 'Vào học ngay' : 'Xem khóa học',
            'action_url' => $canLearnNow ? route('courses.learn', $course) : route('courses.show', $course),
            'icon' => 'fas fa-circle-check',
            'variant' => 'success',
            'entity_type' => 'course_enrollment',
            'entity_id' => $enrollment->id,
        ]);
    }

    public function notifyEnrollmentRejected(CourseEnrollment $enrollment, ?string $reason = null): void
    {
        $enrollment->loadMissing(['user', 'courseClass.course']);

        $user = $enrollment->user;
        $course = $enrollment->courseClass?->course;
        $class = $enrollment->courseClass;

        if (! $user || ! $course || ! $class) {
            return;
        }

        $lines = [
            'Khóa học: ' . $course->title,
            'Đợt học: ' . $class->name,
            filled($reason) ? 'Lý do: ' . $reason : 'Bạn có thể xem lại hồ sơ và đăng ký lại ở đợt học khác.',
        ];

        $this->dispatch($user, [
            'category' => 'enrollment',
            'subject' => 'Đăng ký khóa học chưa được duyệt',
            'title' => 'Đăng ký bị từ chối',
            'message' => 'Yêu cầu đăng ký khóa học "' . $course->title . '" chưa được phê duyệt.',
            'lines' => array_values(array_filter($lines)),
            'action_text' => 'Xem khóa học',
            'action_url' => route('courses.show', $course),
            'icon' => 'fas fa-circle-xmark',
            'variant' => 'danger',
            'entity_type' => 'course_enrollment',
            'entity_id' => $enrollment->id,
        ]);
    }

    public function notifyWaitlistJoined(CourseEnrollment $enrollment): void
    {
        $enrollment->loadMissing(['user', 'courseClass.course']);

        $user = $enrollment->user;
        $course = $enrollment->courseClass?->course;
        $class = $enrollment->courseClass;

        if (! $user || ! $course || ! $class) {
            return;
        }

        $position = $enrollment->waitlist_position;

        $this->dispatch($user, [
            'category' => 'waitlist',
            'subject' => 'Bạn đã được đưa vào hàng chờ',
            'title' => 'Đã vào hàng chờ',
            'message' => 'Đợt học "' . $class->name . '" hiện đã đủ chỗ. Hệ thống đã đưa bạn vào hàng chờ' . ($position ? ' ở vị trí #' . $position : '') . '.',
            'lines' => [
                'Khóa học: ' . $course->title,
                'Khi có chỗ trống, hệ thống sẽ giữ chỗ cho bạn trong 24 giờ và gửi thông báo ngay.',
            ],
            'action_text' => 'Xem khóa học',
            'action_url' => route('courses.show', $course),
            'icon' => 'fas fa-list-ol',
            'variant' => 'dark',
            'entity_type' => 'course_enrollment',
            'entity_id' => $enrollment->id,
        ]);
    }

    public function notifySeatHoldGranted(CourseEnrollment $enrollment): void
    {
        $enrollment->loadMissing(['user', 'courseClass.course']);

        $user = $enrollment->user;
        $course = $enrollment->courseClass?->course;
        $class = $enrollment->courseClass;

        if (! $user || ! $course || ! $class || ! $enrollment->seat_hold_expires_at) {
            return;
        }

        $this->dispatch($user, [
            'category' => 'seat_hold',
            'subject' => 'Bạn đang được giữ chỗ 24 giờ',
            'title' => 'Đã có chỗ trống cho bạn',
            'message' => 'Hệ thống vừa giữ chỗ cho bạn ở đợt học "' . $class->name . '" đến ' . $enrollment->seat_hold_expires_at->format('d/m/Y H:i') . '.',
            'lines' => [
                'Khóa học: ' . $course->title,
                'Vui lòng xác nhận đăng ký trước khi thời gian giữ chỗ kết thúc để không mất lượt.',
            ],
            'action_text' => 'Xác nhận giữ chỗ',
            'action_url' => route('courses.show', $course),
            'icon' => 'fas fa-stopwatch',
            'variant' => 'primary',
            'entity_type' => 'course_enrollment',
            'entity_id' => $enrollment->id,
        ]);
    }

    public function notifyClassStartingSoon(CourseEnrollment $enrollment): bool
    {
        $enrollment->loadMissing(['user', 'courseClass.course']);

        $user = $enrollment->user;
        $course = $enrollment->courseClass?->course;
        $class = $enrollment->courseClass;

        if (! $user || ! $course || ! $class || ! $class->start_date) {
            return false;
        }

        $reminderKey = 'class-starting-soon:' . $enrollment->id . ':' . $class->start_date->format('Y-m-d');

        return $this->dispatch($user, [
            'category' => 'class_start',
            'subject' => 'Lớp học của bạn sắp khai giảng',
            'title' => 'Lớp sắp khai giảng',
            'message' => 'Đợt học "' . $class->name . '" của khóa "' . $course->title . '" sẽ bắt đầu vào ' . $class->start_date->format('d/m/Y') . '.',
            'lines' => [
                'Hãy kiểm tra lại lịch học, giảng viên và các yêu cầu chuẩn bị trước buổi học.',
            ],
            'action_text' => 'Xem chi tiết lớp',
            'action_url' => route('courses.show', $course),
            'icon' => 'fas fa-calendar-day',
            'variant' => 'info',
            'entity_type' => 'course_class',
            'entity_id' => $class->id,
            'reminder_key' => $reminderKey,
        ], reminderKey: $reminderKey);
    }

    public function notifySeatHoldExpiringSoon(CourseEnrollment $enrollment): bool
    {
        $enrollment->loadMissing(['user', 'courseClass.course']);

        $user = $enrollment->user;
        $course = $enrollment->courseClass?->course;
        $class = $enrollment->courseClass;

        if (! $user || ! $course || ! $class || ! $enrollment->seat_hold_expires_at) {
            return false;
        }

        $reminderKey = 'seat-hold-expiring:' . $enrollment->id . ':' . $enrollment->seat_hold_expires_at->format('YmdHi');

        return $this->dispatch($user, [
            'category' => 'seat_hold',
            'subject' => 'Giữ chỗ của bạn sắp hết hạn',
            'title' => 'Giữ chỗ sắp hết hạn',
            'message' => 'Chỗ giữ tạm cho đợt học "' . $class->name . '" sẽ hết hạn lúc ' . $enrollment->seat_hold_expires_at->format('d/m/Y H:i') . '.',
            'lines' => [
                'Nếu bạn vẫn muốn học, hãy quay lại xác nhận đăng ký trước thời điểm này.',
            ],
            'action_text' => 'Xác nhận ngay',
            'action_url' => route('courses.show', $course),
            'icon' => 'fas fa-hourglass-end',
            'variant' => 'warning',
            'entity_type' => 'course_enrollment',
            'entity_id' => $enrollment->id,
            'reminder_key' => $reminderKey,
        ], reminderKey: $reminderKey);
    }

    public function notifyTopupExpiringSoon(WalletTransaction $transaction): bool
    {
        $transaction->loadMissing('wallet.user');

        $user = $transaction->wallet?->user;

        if (! $user || ! $transaction->expires_at) {
            return false;
        }

        $reminderKey = 'wallet-topup-expiring:' . $transaction->id . ':' . $transaction->expires_at->format('YmdHi');

        return $this->dispatch($user, [
            'category' => 'wallet_topup',
            'subject' => 'Yêu cầu nạp tiền của bạn sắp hết hạn',
            'title' => 'Nạp tiền sắp hết hạn',
            'message' => 'Yêu cầu nạp tiền mã ' . $transaction->reference . ' sẽ hết hạn lúc ' . $transaction->expires_at->format('d/m/Y H:i') . '.',
            'lines' => [
                'Số tiền: ' . number_format((float) $transaction->amount, 0, ',', '.') . 'đ',
                'Vui lòng hoàn tất hoặc tạo lại yêu cầu mới trước khi quá hạn.',
            ],
            'action_text' => 'Mở ví của tôi',
            'action_url' => route('wallet.index'),
            'icon' => 'fas fa-wallet',
            'variant' => 'warning',
            'entity_type' => 'wallet_transaction',
            'entity_id' => $transaction->id,
            'reminder_key' => $reminderKey,
        ], reminderKey: $reminderKey);
    }

    public function dispatchReminderNotifications(): array
    {
        $upcomingClasses = 0;
        $seatHoldExpiring = 0;
        $topupExpiring = 0;

        CourseEnrollment::query()
            ->with(['user', 'courseClass.course'])
            ->approved()
            ->whereHas('courseClass', function ($query) {
                $query->whereNotNull('start_date')
                    ->whereDate('start_date', '>=', now()->toDateString())
                    ->whereDate('start_date', '<=', now()->addDay()->toDateString());
            })
            ->chunkById(100, function ($enrollments) use (&$upcomingClasses) {
                foreach ($enrollments as $enrollment) {
                    if ($this->notifyClassStartingSoon($enrollment)) {
                        $upcomingClasses++;
                    }
                }
            });

        CourseEnrollment::query()
            ->with(['user', 'courseClass.course'])
            ->holdingSeat()
            ->whereBetween('seat_hold_expires_at', [now(), now()->addHours(6)])
            ->chunkById(100, function ($enrollments) use (&$seatHoldExpiring) {
                foreach ($enrollments as $enrollment) {
                    if ($this->notifySeatHoldExpiringSoon($enrollment)) {
                        $seatHoldExpiring++;
                    }
                }
            });

        WalletTransaction::query()
            ->with('wallet.user')
            ->pendingDirectApproval()
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addHours(6)])
            ->chunkById(100, function ($transactions) use (&$topupExpiring) {
                foreach ($transactions as $transaction) {
                    if ($this->notifyTopupExpiringSoon($transaction)) {
                        $topupExpiring++;
                    }
                }
            });

        return [
            'upcoming_classes' => $upcomingClasses,
            'seat_hold_expiring' => $seatHoldExpiring,
            'topup_expiring' => $topupExpiring,
        ];
    }

    protected function dispatch(User $user, array $payload, bool $mail = true, ?string $reminderKey = null): bool
    {
        if ($reminderKey && $this->alreadySentReminder($user, $reminderKey)) {
            return false;
        }

        if ($this->supportsDatabaseNotifications()) {
            try {
                $databaseNotification = new PortalAlertNotification($payload, ['database']);

                if ($this->usesMongoNotificationStore()) {
                    $user->portalNotifications()->create([
                        'type' => $databaseNotification::class,
                        'data' => $databaseNotification->toArray($user),
                        'read_at' => null,
                    ]);
                } else {
                    $user->notify($databaseNotification);
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        if ($mail && filled($user->email)) {
            app()->terminating(function () use ($user, $payload) {
                try {
                    $user->notify(new PortalAlertNotification($payload, ['mail']));
                } catch (\Throwable $exception) {
                    report($exception);
                }
            });
        }

        return true;
    }

    protected function alreadySentReminder(User $user, string $reminderKey): bool
    {
        if (! $this->supportsDatabaseNotifications()) {
            return false;
        }

        try {
            $query = $this->usesMongoNotificationStore()
                ? $user->portalNotifications()
                : $user->notifications();

            return $query
                ->where('type', PortalAlertNotification::class)
                ->where('data->reminder_key', $reminderKey)
                ->exists();
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    protected function supportsDatabaseNotifications(): bool
    {
        return true;
    }

    protected function usesMongoNotificationStore(): bool
    {
        return config('database.default') === 'mongodb';
    }
}
