<?php

namespace App\Services;

use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\Setting;

class EnrollmentQueueService
{
    public function __construct(
        protected PortalNotificationService $notificationService,
        protected ApplicationLifecycleBlockchainService $lifecycleBlockchain,
    ) {
    }

    public function syncClassQueue(CourseClass $class): void
    {
        $class->loadMissing('course');

        if (! $class->isOffline()) {
            return;
        }

        $this->expireSeatHolds($class);

        if (($class->max_students ?: 0) <= 0 || $class->status !== 'active') {
            return;
        }

        $freshClass = $class->fresh();

        while ($freshClass && ($freshClass->remaining_slots ?? 0) > 0) {
            $nextEnrollment = $this->nextWaitlistedEnrollment($freshClass);

            if (! $nextEnrollment) {
                break;
            }

            $this->offerSeatHold($nextEnrollment);
            $freshClass = $freshClass->fresh();
        }
    }

    public function joinWaitlist(int $userId, CourseClass $class, ?string $notes = null, array $context = [], array $extra = []): CourseEnrollment
    {
        $enrollment = CourseEnrollment::firstOrNew([
            'user_id' => $userId,
            'class_id' => $class->id,
        ]);

        $enrollment->forceFill([
            'status' => 'pending',
            'notes' => $notes,
            'enrolled_at' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'cancelled_at' => null,
            'completed_at' => null,
            'waitlist_joined_at' => $enrollment->waitlist_joined_at ?: now(),
            'waitlist_promoted_at' => null,
            'seat_hold_expires_at' => null,
        ])->save();

        $enrollment = $enrollment->fresh();
        $this->lifecycleBlockchain->applicationCreated($enrollment, $context, array_merge(['source' => $extra['source'] ?? 'waitlist'], $extra));
        $this->lifecycleBlockchain->classAssigned($enrollment, $enrollment->courseClass, $context, array_merge(['assignment_type' => 'waitlist_target'], $extra));
        $this->lifecycleBlockchain->waitlistJoined($enrollment, $context, $extra);

        return $enrollment;
    }

    public function currentWaitlistPosition(CourseEnrollment $enrollment): ?int
    {
        if (! $enrollment->isWaitlisted()) {
            return null;
        }

        $joinedAt = $enrollment->waitlist_joined_at ?: $enrollment->created_at;

        return CourseEnrollment::query()
            ->where('class_id', $enrollment->class_id)
            ->pending()
            ->waitlisted()
            ->where(function ($query) use ($joinedAt, $enrollment) {
                $query->where('waitlist_joined_at', '<', $joinedAt)
                    ->orWhere(function ($sameTimeQuery) use ($joinedAt, $enrollment) {
                        $sameTimeQuery->where('waitlist_joined_at', $joinedAt)
                            ->where('id', '<=', $enrollment->id);
                    });
            })
            ->count();
    }

    public function expireSeatHolds(?CourseClass $class = null): int
    {
        $query = CourseEnrollment::query()
            ->pending()
            ->whereNotNull('waitlist_promoted_at')
            ->whereNotNull('seat_hold_expires_at')
            ->where('seat_hold_expires_at', '<=', now());

        if ($class) {
            $query->where('class_id', $class->id);
        }

        $expiredEnrollments = $query->get();

        if ($expiredEnrollments->isEmpty()) {
            return 0;
        }

        $classIds = [];

        foreach ($expiredEnrollments as $enrollment) {
            $classIds[] = $enrollment->class_id;

            $enrollment->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'notes' => 'seat_hold_expired',
                'waitlist_promoted_at' => null,
                'seat_hold_expires_at' => null,
            ])->save();

            $this->lifecycleBlockchain->seatHoldExpired($enrollment->fresh(), [
                'role' => 'system',
                'username' => 'system',
            ], [
                'trigger' => 'queue_expiration',
            ]);
        }

        foreach (collect($classIds)->unique() as $classId) {
            $queueClass = CourseClass::find($classId);

            if ($queueClass) {
                $this->syncClassQueue($queueClass);
            }
        }

        return $expiredEnrollments->count();
    }

    public function offerSeatHold(CourseEnrollment $enrollment): CourseEnrollment
    {
        $enrollment->forceFill([
            'status' => 'pending',
            'waitlist_promoted_at' => now(),
            'seat_hold_expires_at' => now()->addHours($this->seatHoldHours()),
            'rejected_at' => null,
            'cancelled_at' => null,
        ])->save();

        $enrollment = $enrollment->fresh();
        $this->notificationService->notifySeatHoldGranted($enrollment);
        $this->lifecycleBlockchain->seatHoldGranted($enrollment, [
            'role' => 'system',
            'username' => 'system',
        ], [
            'trigger' => 'queue_sync',
            'seat_hold_hours' => $this->seatHoldHours(),
        ]);

        return $enrollment;
    }

    public function clearQueueState(CourseEnrollment $enrollment): void
    {
        $enrollment->forceFill([
            'waitlist_joined_at' => null,
            'waitlist_promoted_at' => null,
            'seat_hold_expires_at' => null,
        ])->save();
    }

    public function seatHoldHours(): int
    {
        return max(1, (int) Setting::get('offline_seat_hold_hours', '24'));
    }

    private function nextWaitlistedEnrollment(CourseClass $class): ?CourseEnrollment
    {
        return CourseEnrollment::query()
            ->where('class_id', $class->id)
            ->pending()
            ->waitlisted()
            ->orderBy('waitlist_joined_at')
            ->orderBy('id')
            ->first();
    }
}