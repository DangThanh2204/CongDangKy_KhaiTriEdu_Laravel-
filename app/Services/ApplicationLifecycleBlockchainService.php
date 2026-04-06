<?php

namespace App\Services;

use App\Models\CourseCertificate;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class ApplicationLifecycleBlockchainService
{
    protected ?bool $enrollmentMetaSupported = null;

    public function __construct(protected BlockchainAuditService $blockchainAudit)
    {
    }

    public function applicationCreated(CourseEnrollment $enrollment, array $context = [], array $extra = []): array
    {
        return $this->recordEnrollmentMilestone(
            $enrollment,
            'application_created',
            'application.created',
            $extra,
            $context
        );
    }

    public function waitlistJoined(CourseEnrollment $enrollment, array $context = [], array $extra = []): array
    {
        $key = 'waitlist_joined_' . optional($enrollment->waitlist_joined_at)->format('YmdHis');

        return $this->recordEnrollmentMilestone(
            $enrollment,
            $key,
            'application.waitlist_joined',
            $extra,
            $context
        );
    }

    public function seatHoldGranted(CourseEnrollment $enrollment, array $context = [], array $extra = []): array
    {
        $key = 'seat_hold_granted_' . optional($enrollment->waitlist_promoted_at)->format('YmdHis');

        return $this->recordEnrollmentMilestone(
            $enrollment,
            $key,
            'application.seat_hold_granted',
            $extra,
            $context
        );
    }

    public function seatHoldConfirmed(CourseEnrollment $enrollment, array $context = [], array $extra = []): array
    {
        return $this->recordEnrollmentMilestone(
            $enrollment,
            'seat_hold_confirmed',
            'application.seat_hold_confirmed',
            $extra,
            $context
        );
    }

    public function seatHoldExpired(CourseEnrollment $enrollment, array $context = [], array $extra = []): array
    {
        $key = 'seat_hold_expired_' . optional($enrollment->cancelled_at)->format('YmdHis');

        return $this->recordEnrollmentMilestone(
            $enrollment,
            $key,
            'application.seat_hold_expired',
            $extra,
            $context
        );
    }

    public function approved(CourseEnrollment $enrollment, array $context = [], array $extra = []): array
    {
        $key = 'approved_' . optional($enrollment->approved_at)->format('YmdHis');

        return $this->recordEnrollmentMilestone(
            $enrollment,
            $key,
            'application.approved',
            $extra,
            $context
        );
    }

    public function rejected(CourseEnrollment $enrollment, array $context = [], array $extra = []): array
    {
        $key = 'rejected_' . optional($enrollment->rejected_at)->format('YmdHis');

        return $this->recordEnrollmentMilestone(
            $enrollment,
            $key,
            'application.rejected',
            $extra,
            $context
        );
    }

    public function cancelled(CourseEnrollment $enrollment, array $context = [], array $extra = []): array
    {
        $key = 'cancelled_' . optional($enrollment->cancelled_at)->format('YmdHis');

        return $this->recordEnrollmentMilestone(
            $enrollment,
            $key,
            'application.cancelled',
            $extra,
            $context
        );
    }

    public function classAssigned(CourseEnrollment $enrollment, ?CourseClass $class = null, array $context = [], array $extra = []): array
    {
        $class ??= $enrollment->courseClass;
        $suffix = $class?->id ? (string) $class->id : 'none';

        return $this->recordEnrollmentMilestone(
            $enrollment,
            'class_assigned_' . $suffix,
            'application.class_assigned',
            array_merge([
                'assignment_type' => $extra['assignment_type'] ?? 'initial',
                'assigned_class' => $class ? $this->classSnapshot($class) : null,
            ], $extra),
            $context
        );
    }

    public function classChanged(CourseEnrollment $enrollment, ?CourseClass $fromClass, ?CourseClass $toClass, array $context = [], array $extra = []): array
    {
        $timestamp = now()->format('YmdHis');

        return $this->recordEnrollmentMilestone(
            $enrollment,
            'class_changed_' . $timestamp,
            'application.class_changed',
            array_merge([
                'from_class' => $fromClass ? $this->classSnapshot($fromClass) : null,
                'to_class' => $toClass ? $this->classSnapshot($toClass) : null,
            ], $extra),
            $context,
            true
        );
    }

    public function paymentRecorded(Payment $payment, ?CourseEnrollment $enrollment = null, array $context = [], array $extra = []): array
    {
        return $this->recordPaymentMilestone(
            $payment,
            'payment_recorded_' . $payment->id,
            'application.payment_recorded',
            array_merge($extra, [
                'enrollment' => $enrollment ? $this->enrollmentSnapshot($enrollment) : null,
            ]),
            $context
        );
    }

    public function certificateIssued(CourseCertificate $certificate, array $context = [], array $extra = []): array
    {
        $payload = [
            'certificate_no' => $certificate->certificate_no,
            'issued_at' => optional($certificate->issued_at)->toIso8601String(),
            'course_id' => $certificate->course_id,
            'user_id' => $certificate->user_id,
        ];

        return $this->blockchainAudit->record(
            'application.certificate_issued',
            array_merge($payload, $extra),
            array_merge([
                'reference' => $certificate->certificate_no,
                'user_id' => $certificate->user_id,
                'username' => $certificate->user?->username,
                'role' => $certificate->user?->role,
            ], array_filter($context, fn ($value) => $value !== null && $value !== ''))
        );
    }

    public function syncPendingEnrollmentMilestones(int $limit = 50): array
    {
        if (! $this->supportsEnrollmentMeta()) {
            return ['synced' => 0, 'failed' => 0];
        }

        $enrollments = CourseEnrollment::query()
            ->with(['user:id,fullname,username,email,role', 'courseClass.course:id,title,learning_type,delivery_mode'])
            ->whereNotNull('blockchain_meta')
            ->latest('updated_at')
            ->take(max($limit * 3, $limit))
            ->get();

        $synced = 0;
        $failed = 0;

        foreach ($enrollments as $enrollment) {
            $milestones = data_get($enrollment->blockchain_meta, 'milestones', []);
            if (! is_array($milestones) || $milestones === []) {
                continue;
            }

            foreach ($milestones as $key => $milestone) {
                if ($synced + $failed >= $limit) {
                    break 2;
                }

                if ((bool) ($milestone['success'] ?? false)) {
                    continue;
                }

                $result = $this->replayEnrollmentMilestone($enrollment, (string) $key, $milestone);
                if ($result['success']) {
                    $synced++;
                } else {
                    $failed++;
                }
            }
        }

        return ['synced' => $synced, 'failed' => $failed];
    }

    public function syncPendingPaymentMilestones(int $limit = 50): array
    {
        $payments = Payment::query()
            ->with(['user:id,fullname,username,email,role', 'courseClass.course:id,title,learning_type,delivery_mode'])
            ->whereNotNull('metadata')
            ->latest('updated_at')
            ->take(max($limit * 3, $limit))
            ->get();

        $synced = 0;
        $failed = 0;

        foreach ($payments as $payment) {
            $milestones = data_get($payment->metadata, 'application_blockchain.milestones', []);
            if (! is_array($milestones) || $milestones === []) {
                continue;
            }

            foreach ($milestones as $key => $milestone) {
                if ($synced + $failed >= $limit) {
                    break 2;
                }

                if ((bool) ($milestone['success'] ?? false)) {
                    continue;
                }

                $result = $this->replayPaymentMilestone($payment, (string) $key, $milestone);
                if ($result['success']) {
                    $synced++;
                } else {
                    $failed++;
                }
            }
        }

        return ['synced' => $synced, 'failed' => $failed];
    }

    protected function recordEnrollmentMilestone(
        CourseEnrollment $enrollment,
        string $key,
        string $action,
        array $payload = [],
        array $context = [],
        bool $force = false
    ): array {
        $enrollment->loadMissing([
            'user:id,fullname,username,email,role',
            'courseClass:id,course_id,name,start_date,end_date,status',
            'courseClass.course:id,title,learning_type,delivery_mode',
        ]);

        $meta = $this->supportsEnrollmentMeta() ? ($enrollment->blockchain_meta ?? []) : [];
        $existing = data_get($meta, 'milestones.' . $key, []);

        if (! $force && (bool) data_get($existing, 'success', false)) {
            return $existing;
        }

        $normalizedPayload = array_merge([
            'milestone' => $key,
            'enrollment' => $this->enrollmentSnapshot($enrollment),
        ], $payload);
        $normalizedContext = $this->normalizeEnrollmentContext($enrollment, $context, $key);
        $audit = $this->blockchainAudit->record($action, $normalizedPayload, $normalizedContext);
        $entry = $this->buildStoredEntry($action, $normalizedPayload, $normalizedContext, $audit, $existing);

        if ($this->supportsEnrollmentMeta()) {
            Arr::set($meta, 'milestones.' . $key, $entry);
            $enrollment->forceFill(['blockchain_meta' => $meta])->save();
        }

        return $entry;
    }

    protected function recordPaymentMilestone(
        Payment $payment,
        string $key,
        string $action,
        array $payload = [],
        array $context = [],
        bool $force = false
    ): array {
        $payment->loadMissing([
            'user:id,fullname,username,email,role',
            'courseClass:id,course_id,name,start_date,end_date,status',
            'courseClass.course:id,title,learning_type,delivery_mode',
        ]);

        $meta = $payment->metadata ?? [];
        $existing = data_get($meta, 'application_blockchain.milestones.' . $key, []);

        if (! $force && (bool) data_get($existing, 'success', false)) {
            return $existing;
        }

        $normalizedPayload = array_merge([
            'milestone' => $key,
            'payment' => $this->paymentSnapshot($payment),
        ], $payload);
        $normalizedContext = $this->normalizePaymentContext($payment, $context, $key);
        $audit = $this->blockchainAudit->record($action, $normalizedPayload, $normalizedContext);
        $entry = $this->buildStoredEntry($action, $normalizedPayload, $normalizedContext, $audit, $existing);

        Arr::set($meta, 'application_blockchain.milestones.' . $key, $entry);
        $payment->metadata = $meta;
        $payment->save();

        return $entry;
    }

    protected function replayEnrollmentMilestone(CourseEnrollment $enrollment, string $key, array $milestone): array
    {
        $payload = (array) ($milestone['payload'] ?? []);
        $context = (array) ($milestone['context'] ?? []);
        $action = (string) ($milestone['action'] ?? 'application.lifecycle');
        $audit = $this->blockchainAudit->record($action, $payload, $context);

        $meta = $enrollment->blockchain_meta ?? [];
        Arr::set($meta, 'milestones.' . $key, $this->buildStoredEntry($action, $payload, $context, $audit, $milestone));
        $enrollment->forceFill(['blockchain_meta' => $meta])->save();

        return ['success' => (bool) data_get($audit, 'success', false)];
    }

    protected function replayPaymentMilestone(Payment $payment, string $key, array $milestone): array
    {
        $payload = (array) ($milestone['payload'] ?? []);
        $context = (array) ($milestone['context'] ?? []);
        $action = (string) ($milestone['action'] ?? 'application.lifecycle');
        $audit = $this->blockchainAudit->record($action, $payload, $context);

        $meta = $payment->metadata ?? [];
        Arr::set($meta, 'application_blockchain.milestones.' . $key, $this->buildStoredEntry($action, $payload, $context, $audit, $milestone));
        $payment->metadata = $meta;
        $payment->save();

        return ['success' => (bool) data_get($audit, 'success', false)];
    }

    protected function buildStoredEntry(string $action, array $payload, array $context, array $audit, array $existing = []): array
    {
        return [
            'action' => $action,
            'payload' => $payload,
            'context' => $context,
            'success' => (bool) data_get($audit, 'success', false),
            'message' => data_get($audit, 'message'),
            'message_id' => data_get($audit, 'message_id')
                ?? data_get($audit, 'data.header.id')
                ?? data_get($audit, 'data.id'),
            'tx_id' => data_get($audit, 'tx_id')
                ?? data_get($audit, 'data.tx.id')
                ?? data_get($audit, 'data.tx')
                ?? data_get($audit, 'data.blockchain.transactionHash'),
            'state' => data_get($audit, 'state')
                ?? data_get($audit, 'data.state')
                ?? data_get($audit, 'status'),
            'result' => $audit,
            'attempts' => (int) data_get($existing, 'attempts', 0) + 1,
            'last_attempt_at' => now()->toDateTimeString(),
        ];
    }

    protected function normalizeEnrollmentContext(CourseEnrollment $enrollment, array $context, string $key): array
    {
        return array_filter([
            'reference' => $context['reference'] ?? ('ENROLLMENT-' . $enrollment->id . '-' . strtoupper($key)),
            'user_id' => $context['user_id'] ?? $enrollment->user_id,
            'username' => $context['username'] ?? $enrollment->user?->username,
            'role' => $context['role'] ?? $enrollment->user?->role,
            'ip' => $context['ip'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    protected function normalizePaymentContext(Payment $payment, array $context, string $key): array
    {
        return array_filter([
            'reference' => $context['reference'] ?? ($payment->reference ?: ('PAYMENT-' . $payment->id . '-' . strtoupper($key))),
            'user_id' => $context['user_id'] ?? $payment->user_id,
            'username' => $context['username'] ?? $payment->user?->username,
            'role' => $context['role'] ?? $payment->user?->role,
            'ip' => $context['ip'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    protected function enrollmentSnapshot(CourseEnrollment $enrollment): array
    {
        return [
            'enrollment_id' => $enrollment->id,
            'status' => $enrollment->status,
            'notes' => $enrollment->notes,
            'enrolled_at' => optional($enrollment->enrolled_at)->toIso8601String(),
            'approved_at' => optional($enrollment->approved_at)->toIso8601String(),
            'rejected_at' => optional($enrollment->rejected_at)->toIso8601String(),
            'cancelled_at' => optional($enrollment->cancelled_at)->toIso8601String(),
            'completed_at' => optional($enrollment->completed_at)->toIso8601String(),
            'waitlist_joined_at' => optional($enrollment->waitlist_joined_at)->toIso8601String(),
            'waitlist_promoted_at' => optional($enrollment->waitlist_promoted_at)->toIso8601String(),
            'seat_hold_expires_at' => optional($enrollment->seat_hold_expires_at)->toIso8601String(),
            'base_price' => (float) ($enrollment->base_price ?? 0),
            'discount_amount' => (float) ($enrollment->discount_amount ?? 0),
            'final_price' => (float) ($enrollment->final_price ?? 0),
            'student' => [
                'id' => $enrollment->user_id,
                'name' => $enrollment->user?->fullname ?: $enrollment->user?->username,
                'email' => $enrollment->user?->email,
            ],
            'course' => [
                'id' => $enrollment->courseClass?->course?->id,
                'title' => $enrollment->courseClass?->course?->title,
                'learning_type' => $enrollment->courseClass?->course?->learning_type,
                'delivery_mode' => $enrollment->courseClass?->course?->delivery_mode,
            ],
            'class' => $enrollment->courseClass ? $this->classSnapshot($enrollment->courseClass) : null,
        ];
    }

    protected function paymentSnapshot(Payment $payment): array
    {
        return [
            'payment_id' => $payment->id,
            'reference' => $payment->reference,
            'method' => $payment->method,
            'status' => $payment->status,
            'amount' => (float) ($payment->amount ?? 0),
            'base_amount' => (float) ($payment->base_amount ?? 0),
            'discount_amount' => (float) ($payment->discount_amount ?? 0),
            'paid_at' => optional($payment->paid_at)->toIso8601String(),
            'notes' => $payment->notes,
            'student' => [
                'id' => $payment->user_id,
                'name' => $payment->user?->fullname ?: $payment->user?->username,
                'email' => $payment->user?->email,
            ],
            'course' => [
                'id' => $payment->courseClass?->course?->id,
                'title' => $payment->courseClass?->course?->title,
                'learning_type' => $payment->courseClass?->course?->learning_type,
                'delivery_mode' => $payment->courseClass?->course?->delivery_mode,
            ],
            'class' => $payment->courseClass ? $this->classSnapshot($payment->courseClass) : null,
        ];
    }

    protected function classSnapshot(CourseClass $class): array
    {
        return [
            'id' => $class->id,
            'name' => $class->name,
            'status' => $class->status,
            'start_date' => optional($class->start_date)->toDateString(),
            'end_date' => optional($class->end_date)->toDateString(),
        ];
    }

    protected function supportsEnrollmentMeta(): bool
    {
        if ($this->enrollmentMetaSupported !== null) {
            return $this->enrollmentMetaSupported;
        }

        return $this->enrollmentMetaSupported = Schema::hasColumn('course_enrollments', 'blockchain_meta');
    }
}
