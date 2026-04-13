<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ApplicationStatusController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $enrollments = CourseEnrollment::query()
            ->with([
                'courseClass.course.category',
                'courseClass.instructor',
                'discountCode',
                'certificate',
            ])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->get();

        $paymentsByClass = Payment::query()
            ->with(['discountCode', 'courseClass.course.category'])
            ->where('user_id', $user->id)
            ->get()
            ->groupBy(fn (Payment $payment) => (string) $payment->class_id)
            ->map(fn (Collection $group) => $this->sortPaymentsForHistory($group));

        $applications = $enrollments->map(function (CourseEnrollment $enrollment) use ($paymentsByClass) {
            $class = $enrollment->courseClass;
            $course = $enrollment->course ?: $class?->course;
            $paymentHistory = $paymentsByClass->get((string) $enrollment->class_id, collect())->values();
            $payment = $this->sortPaymentsForApplication($paymentHistory)->first();
            $submittedState = $this->buildSubmittedState($enrollment);
            $paymentState = $this->buildPaymentState($enrollment, $payment);
            $approvalState = $this->buildApprovalState($enrollment);
            $assignmentState = $this->buildAssignmentState($enrollment);
            $overallStatus = $this->buildOverallStatus($enrollment, $paymentState, $approvalState);
            $blockchainTimeline = $this->buildBlockchainTimeline($enrollment, $payment);

            return [
                'enrollment' => $enrollment,
                'course' => $course,
                'class' => $class,
                'payment' => $payment,
                'payments' => $paymentHistory,
                'submitted_state' => $submittedState,
                'payment_state' => $paymentState,
                'approval_state' => $approvalState,
                'assignment_state' => $assignmentState,
                'steps' => [
                    $submittedState,
                    $paymentState,
                    $approvalState,
                    $assignmentState,
                ],
                'overall_status_label' => $overallStatus['label'],
                'overall_status_variant' => $overallStatus['variant'],
                'payment_method_label' => $this->resolvePaymentMethodLabel($payment, $enrollment),
                'primary_action' => $this->buildPrimaryAction($enrollment),
                'needs_attention' => ! $paymentState['completed'] || ! $approvalState['completed'],
                'blockchain_timeline' => $blockchainTimeline,
                'blockchain_summary' => $this->summarizeBlockchainTimeline($blockchainTimeline),
            ];
        });

        $summary = [
            'submitted' => $applications->count(),
            'approved' => $applications->filter(fn (array $application) => $application['approval_state']['completed'])->count(),
            'paid' => $applications->filter(fn (array $application) => $application['payment_state']['completed'])->count(),
            'assigned' => $applications->filter(fn (array $application) => $application['assignment_state']['completed'])->count(),
        ];

        return view('student.application-status', compact('applications', 'summary', 'user'));
    }

    private function buildSubmittedState(CourseEnrollment $enrollment): array
    {
        $submittedAt = optional($enrollment->created_at)->format('d/m/Y H:i');

        return [
            'title' => 'Nộp hồ sơ',
            'label' => 'Đã nộp hồ sơ',
            'description' => $submittedAt
                ? 'Hệ thống đã ghi nhận hồ sơ của bạn lúc ' . $submittedAt . '.'
                : 'Hệ thống đã ghi nhận hồ sơ của bạn.',
            'variant' => 'success',
            'icon' => 'fas fa-file-circle-check',
            'completed' => true,
        ];
    }

    private function buildPaymentState(CourseEnrollment $enrollment, ?Payment $payment): array
    {
        $payableAmount = (float) ($enrollment->final_price ?? $enrollment->base_price ?? 0);

        if ($payableAmount <= 0) {
            return [
                'title' => 'Thanh toán',
                'label' => 'Không cần thanh toán',
                'description' => (float) ($enrollment->discount_amount ?? 0) > 0
                    ? 'Hồ sơ này đã được miễn phí nhờ ưu đãi hoặc mã giảm giá.'
                    : 'Khóa học này hiện không yêu cầu thanh toán thêm.',
                'variant' => 'success',
                'icon' => 'fas fa-wallet',
                'completed' => true,
            ];
        }

        if ($payment && $payment->isCompleted()) {
            $paidAt = optional($payment->paid_at)->format('d/m/Y H:i');
            $method = $this->resolvePaymentMethodLabel($payment, $enrollment);
            $amount = number_format((float) $payment->amount, 0, ',', '.') . 'đ';

            return [
                'title' => 'Thanh toán',
                'label' => 'Đã thanh toán',
                'description' => trim('Đã thanh toán ' . $amount . ' bằng ' . $method . ($paidAt ? ' lúc ' . $paidAt : '') . '.'),
                'variant' => 'success',
                'icon' => 'fas fa-circle-check',
                'completed' => true,
            ];
        }

        if ($payment && $payment->isPending()) {
            return [
                'title' => 'Thanh toán',
                'label' => 'Đang chờ thanh toán',
                'description' => 'Hệ thống đã tạo yêu cầu thanh toán nhưng chưa ghi nhận hoàn tất.',
                'variant' => 'warning',
                'icon' => 'fas fa-hourglass-half',
                'completed' => false,
            ];
        }

        if ($payment && $payment->isFailed()) {
            return [
                'title' => 'Thanh toán',
                'label' => 'Thanh toán thất bại',
                'description' => $payment->notes ?: 'Hệ thống đã ghi nhận giao dịch chưa thành công. Bạn có thể thử lại.',
                'variant' => 'danger',
                'icon' => 'fas fa-circle-xmark',
                'completed' => false,
            ];
        }

        if ($enrollment->hasActiveSeatHold()) {
            $deadline = optional($enrollment->seat_hold_expires_at)->format('d/m/Y H:i');

            return [
                'title' => 'Thanh toán',
                'label' => 'Cần xác nhận giữ chỗ',
                'description' => $deadline
                    ? 'Bạn đang được giữ chỗ đến ' . $deadline . '. Khi xác nhận giữ chỗ, hệ thống sẽ hoàn tất thanh toán từ ví.'
                    : 'Bạn đang được giữ chỗ và cần hoàn tất bước xác nhận.',
                'variant' => 'primary',
                'icon' => 'fas fa-stopwatch',
                'completed' => false,
            ];
        }

        if ($enrollment->isWaitlisted()) {
            return [
                'title' => 'Thanh toán',
                'label' => 'Chờ tới lượt thanh toán',
                'description' => 'Hồ sơ đang ở hàng chờ. Hệ thống sẽ yêu cầu thanh toán khi bạn được nhả chỗ.',
                'variant' => 'dark',
                'icon' => 'fas fa-clock-rotate-left',
                'completed' => false,
            ];
        }

        return [
            'title' => 'Thanh toán',
            'label' => 'Chưa ghi nhận thanh toán',
            'description' => 'Hệ thống chưa ghi nhận giao dịch thanh toán cho hồ sơ này.',
            'variant' => 'warning',
            'icon' => 'fas fa-wallet',
            'completed' => false,
        ];
    }

    private function buildApprovalState(CourseEnrollment $enrollment): array
    {
        if ($enrollment->isCompleted() || $enrollment->isApproved()) {
            return [
                'title' => 'Duyệt hồ sơ',
                'label' => 'Đã duyệt',
                'description' => 'Hồ sơ của bạn đã được xác nhận và đủ điều kiện tham gia lớp học.',
                'variant' => 'success',
                'icon' => 'fas fa-user-check',
                'completed' => true,
            ];
        }

        if ($enrollment->isRejected()) {
            return [
                'title' => 'Duyệt hồ sơ',
                'label' => 'Bị từ chối',
                'description' => $enrollment->notes ?: 'Hồ sơ của bạn hiện chưa được duyệt. Bạn có thể xem ghi chú và đăng ký lại.',
                'variant' => 'danger',
                'icon' => 'fas fa-user-xmark',
                'completed' => false,
            ];
        }

        if ($enrollment->isCancelled()) {
            return [
                'title' => 'Duyệt hồ sơ',
                'label' => 'Đã hủy',
                'description' => 'Bạn hoặc hệ thống đã hủy hồ sơ này trước khi hoàn tất đăng ký.',
                'variant' => 'secondary',
                'icon' => 'fas fa-ban',
                'completed' => false,
            ];
        }

        if ($enrollment->hasActiveSeatHold()) {
            return [
                'title' => 'Duyệt hồ sơ',
                'label' => 'Đang giữ chỗ 24h',
                'description' => 'Hệ thống đang ưu tiên chỗ cho bạn. Hãy hoàn tất bước tiếp theo trước khi hết hạn.',
                'variant' => 'primary',
                'icon' => 'fas fa-hourglass-start',
                'completed' => false,
            ];
        }

        if ($enrollment->isWaitlisted()) {
            $position = $enrollment->waitlist_position;
            $positionText = $position ? ' Vị trí hiện tại của bạn là #' . $position . '.' : '';

            return [
                'title' => 'Duyệt hồ sơ',
                'label' => 'Trong hàng chờ',
                'description' => 'Lớp hiện đã đầy và hồ sơ của bạn đang ở danh sách chờ.' . $positionText,
                'variant' => 'dark',
                'icon' => 'fas fa-list-ol',
                'completed' => false,
            ];
        }

        return [
            'title' => 'Duyệt hồ sơ',
            'label' => 'Chờ duyệt',
            'description' => 'Hồ sơ đã gửi thành công và đang chờ trung tâm xác nhận.',
            'variant' => 'warning',
            'icon' => 'fas fa-user-clock',
            'completed' => false,
        ];
    }

    private function buildAssignmentState(CourseEnrollment $enrollment): array
    {
        $class = $enrollment->courseClass;

        if (! $class) {
            return [
                'title' => 'Xếp lớp',
                'label' => 'Chưa xếp lớp',
                'description' => 'Hệ thống chưa gán đợt học hoặc lớp học cụ thể cho hồ sơ này.',
                'variant' => 'secondary',
                'icon' => 'fas fa-users-viewfinder',
                'completed' => false,
            ];
        }

        $parts = [$class->name];

        if ($class->start_date) {
            $parts[] = 'Khai giảng: ' . $class->start_date->format('d/m/Y');
        }

        if ($class->schedule_text) {
            $parts[] = $class->schedule_text;
        }

        return [
            'title' => 'Xếp lớp',
            'label' => 'Đã ghi nhận đợt học',
            'description' => implode(' | ', $parts),
            'variant' => 'info',
            'icon' => 'fas fa-school',
            'completed' => true,
        ];
    }

    private function buildPrimaryAction(CourseEnrollment $enrollment): array
    {
        $course = $enrollment->course ?: $enrollment->courseClass?->course;

        if (! $course) {
            return [
                'label' => 'Xem lịch khai giảng',
                'url' => route('courses.intakes'),
                'class' => 'btn-outline-primary',
            ];
        }

        if ($enrollment->isApproved() || $enrollment->isCompleted()) {
            return [
                'label' => $enrollment->isCompleted() ? 'Xem lại khóa học' : 'Vào học ngay',
                'url' => route('courses.learn', $course),
                'class' => 'btn-success',
            ];
        }

        if ($enrollment->hasActiveSeatHold()) {
            return [
                'label' => 'Xác nhận giữ chỗ',
                'url' => route('courses.show', $course),
                'class' => 'btn-primary',
            ];
        }

        return [
            'label' => 'Xem chi tiết khóa học',
            'url' => route('courses.show', $course),
            'class' => 'btn-outline-primary',
        ];
    }

    private function buildOverallStatus(CourseEnrollment $enrollment, array $paymentState, array $approvalState): array
    {
        if ($enrollment->isCompleted()) {
            return ['label' => 'Hoàn thành khóa học', 'variant' => 'success'];
        }

        if ($enrollment->isApproved()) {
            return ['label' => 'Đã duyệt', 'variant' => 'success'];
        }

        if ($enrollment->isRejected()) {
            return ['label' => 'Bị từ chối', 'variant' => 'danger'];
        }

        if ($enrollment->isCancelled()) {
            return ['label' => 'Đã hủy', 'variant' => 'secondary'];
        }

        if ($enrollment->hasActiveSeatHold()) {
            return ['label' => 'Đang giữ chỗ 24h', 'variant' => 'primary'];
        }

        if ($enrollment->isWaitlisted()) {
            return ['label' => 'Trong hàng chờ', 'variant' => 'dark'];
        }

        if ($paymentState['completed'] && ! $approvalState['completed']) {
            return ['label' => 'Đã thanh toán, chờ duyệt', 'variant' => 'info'];
        }

        return ['label' => 'Đang xử lý hồ sơ', 'variant' => 'warning'];
    }

    private function buildBlockchainTimeline(CourseEnrollment $enrollment, ?Payment $payment): Collection
    {
        $timeline = collect();

        foreach ((array) data_get($enrollment->blockchain_meta, 'milestones', []) as $key => $milestone) {
            $entry = $this->normalizeBlockchainEntry((string) $key, (array) $milestone, 'hồ sơ', $enrollment, $payment);

            if ($entry) {
                $timeline->push($entry);
            }
        }

        foreach ((array) data_get($payment?->metadata, 'application_blockchain.milestones', []) as $key => $milestone) {
            $entry = $this->normalizeBlockchainEntry((string) $key, (array) $milestone, 'thanh toán', $enrollment, $payment);

            if ($entry) {
                $timeline->push($entry);
            }
        }

        $certificate = $enrollment->certificate;
        $certificateAudit = is_array($certificate?->meta) ? data_get($certificate->meta, 'blockchain_audit') : null;

        if (is_array($certificateAudit) && ! $timeline->contains(fn (array $item) => in_array($item['action'], ['application.certificate_issued', 'certificate.issued'], true))) {
            $timeline->push([
                'key' => 'certificate_' . ($certificate->id ?? 'proof'),
                'action' => 'certificate.issued',
                'title' => 'Cấp chứng chỉ',
                'description' => 'Hệ thống đã cấp chứng chỉ hoàn thành và tạo proof xác thực công khai.',
                'icon' => 'fas fa-certificate',
                'variant' => (bool) data_get($certificateAudit, 'success', false) ? 'success' : 'warning',
                'status_label' => (bool) data_get($certificateAudit, 'success', false) ? 'Đã neo lên FireFly' : 'Chờ đồng bộ',
                'success' => (bool) data_get($certificateAudit, 'success', false),
                'message' => data_get($certificateAudit, 'message') ?: 'Hệ thống đang đồng bộ proof chứng chỉ lên blockchain consortium.',
                'message_id' => data_get($certificateAudit, 'message_id') ?? data_get($certificateAudit, 'data.header.id') ?? data_get($certificateAudit, 'data.id'),
                'tx_id' => data_get($certificateAudit, 'tx_id') ?? data_get($certificateAudit, 'data.tx.id') ?? data_get($certificateAudit, 'data.tx') ?? data_get($certificateAudit, 'data.blockchain.transactionHash'),
                'state' => data_get($certificateAudit, 'state') ?? data_get($certificateAudit, 'data.state') ?? data_get($certificateAudit, 'status'),
                'occurred_at_label' => optional($certificate->issued_at ?? $certificate->created_at)->format('d/m/Y H:i'),
                'sort_time' => optional($certificate->issued_at ?? $certificate->created_at)->timestamp ?: 0,
                'sort_weight' => 90,
                'source_label' => 'chứng chỉ',
            ]);
        }

        return $timeline
            ->sortBy([
                ['sort_time', 'asc'],
                ['sort_weight', 'asc'],
            ])
            ->values();
    }

    private function normalizeBlockchainEntry(
        string $key,
        array $milestone,
        string $sourceLabel,
        CourseEnrollment $enrollment,
        ?Payment $payment
    ): ?array {
        $action = (string) ($milestone['action'] ?? '');

        if ($action === '') {
            return null;
        }

        $presentation = $this->milestonePresentation($action);
        $occurredAt = $this->resolveMilestoneTimestamp($action, $milestone, $enrollment, $payment);
        $success = (bool) data_get($milestone, 'success', false);

        return [
            'key' => $key,
            'action' => $action,
            'title' => $presentation['title'],
            'description' => $this->buildBlockchainDescription($action, $milestone, $enrollment, $payment),
            'icon' => $presentation['icon'],
            'variant' => $success ? 'success' : 'warning',
            'status_label' => $success ? 'Đã neo lên FireFly' : 'Chờ đồng bộ',
            'success' => $success,
            'message' => data_get($milestone, 'message') ?: ($success ? 'Bản ghi đã đạt đủ proof blockchain.' : 'Hệ thống sẽ tự đồng bộ lại khi FireFly sẵn sàng.'),
            'message_id' => data_get($milestone, 'message_id'),
            'tx_id' => data_get($milestone, 'tx_id'),
            'state' => data_get($milestone, 'state'),
            'occurred_at_label' => $occurredAt?->format('d/m/Y H:i'),
            'sort_time' => $occurredAt?->timestamp ?: 0,
            'sort_weight' => $presentation['weight'],
            'source_label' => $sourceLabel,
        ];
    }

    private function summarizeBlockchainTimeline(Collection $timeline): array
    {
        return [
            'total' => $timeline->count(),
            'anchored' => $timeline->where('success', true)->count(),
            'pending' => $timeline->where('success', false)->count(),
        ];
    }

    private function milestonePresentation(string $action): array
    {
        return [
            'application.created' => ['title' => 'Tạo hồ sơ', 'icon' => 'fas fa-file-circle-plus', 'weight' => 10],
            'application.waitlist_joined' => ['title' => 'Vào hàng chờ', 'icon' => 'fas fa-list-ol', 'weight' => 20],
            'application.seat_hold_granted' => ['title' => 'Được giữ chỗ 24h', 'icon' => 'fas fa-hourglass-start', 'weight' => 30],
            'application.seat_hold_confirmed' => ['title' => 'Xác nhận giữ chỗ', 'icon' => 'fas fa-circle-check', 'weight' => 40],
            'application.payment_recorded' => ['title' => 'Ghi nhận thanh toán', 'icon' => 'fas fa-wallet', 'weight' => 50],
            'application.approved' => ['title' => 'Duyệt hồ sơ', 'icon' => 'fas fa-user-check', 'weight' => 60],
            'application.rejected' => ['title' => 'Từ chối hồ sơ', 'icon' => 'fas fa-user-xmark', 'weight' => 61],
            'application.cancelled' => ['title' => 'Hủy hồ sơ', 'icon' => 'fas fa-ban', 'weight' => 62],
            'application.class_assigned' => ['title' => 'Xếp lớp', 'icon' => 'fas fa-school', 'weight' => 70],
            'application.class_changed' => ['title' => 'Đổi lớp', 'icon' => 'fas fa-right-left', 'weight' => 75],
            'application.seat_hold_expired' => ['title' => 'Hết hạn giữ chỗ', 'icon' => 'fas fa-clock', 'weight' => 80],
            'application.certificate_issued' => ['title' => 'Cấp chứng chỉ', 'icon' => 'fas fa-certificate', 'weight' => 90],
            'certificate.issued' => ['title' => 'Cấp chứng chỉ', 'icon' => 'fas fa-certificate', 'weight' => 90],
        ][$action] ?? ['title' => $action, 'icon' => 'fas fa-link', 'weight' => 999];
    }

    private function buildBlockchainDescription(
        string $action,
        array $milestone,
        CourseEnrollment $enrollment,
        ?Payment $payment
    ): string {
        return match ($action) {
            'application.created' => 'Hệ thống đã tạo hồ sơ đăng ký và ghi nhận mốc khởi tạo.',
            'application.waitlist_joined' => 'Hồ sơ được đưa vào danh sách chờ vì lớp đã kín chỗ.',
            'application.seat_hold_granted' => $this->buildSeatHoldGrantedDescription($milestone, $enrollment),
            'application.seat_hold_confirmed' => 'Học viên đã xác nhận giữ chỗ và hệ thống khóa suất học thành công.',
            'application.payment_recorded' => $this->buildPaymentRecordedDescription($milestone, $payment),
            'application.approved' => 'Trung tâm đã duyệt hồ sơ đăng ký.',
            'application.rejected' => 'Trung tâm đã từ chối hồ sơ và lưu lý do xử lý.',
            'application.cancelled' => 'Hồ sơ đã được hủy và trả lại trạng thái suất học tương ứng.',
            'application.class_assigned' => $this->buildClassAssignedDescription($milestone, $enrollment),
            'application.class_changed' => $this->buildClassChangedDescription($milestone),
            'application.seat_hold_expired' => 'Giữ chỗ đã hết hạn trước khi học viên hoàn tất bước xác nhận.',
            'application.certificate_issued', 'certificate.issued' => 'Hệ thống đã cấp chứng chỉ hoàn thành và tạo dữ liệu xác thực công khai.',
            default => data_get($milestone, 'message') ?: 'Hệ thống đã ghi nhận thêm một mốc nghiệp vụ trên blockchain.',
        };
    }

    private function buildSeatHoldGrantedDescription(array $milestone, CourseEnrollment $enrollment): string
    {
        $deadline = $this->parseTimestamp(
            data_get($milestone, 'payload.enrollment.seat_hold_expires_at')
            ?: $enrollment->seat_hold_expires_at
        );

        return $deadline
            ? 'Hệ thống đã nhả chỗ từ hàng chờ và giữ chỗ cho bạn đến ' . $deadline->format('d/m/Y H:i') . '.'
            : 'Hệ thống đã nhả chỗ từ hàng chờ và kích hoạt giữ chỗ 24 giờ cho bạn.';
    }

    private function buildPaymentRecordedDescription(array $milestone, ?Payment $payment): string
    {
        $amount = (float) (data_get($milestone, 'payload.payment.amount') ?? $payment?->amount ?? 0);
        $method = data_get($milestone, 'payload.payment.method') ?? $payment?->method;
        $amountLabel = $amount > 0 ? number_format($amount, 0, ',', '.') . 'đ' : 'số tiền không xác định';
        $methodLabel = $method ? $this->resolvePaymentMethodLabelFromValue((string) $method) : 'phương thức chưa xác định';

        return 'Hệ thống đã ghi nhận thanh toán ' . $amountLabel . ' bằng ' . $methodLabel . '.';
    }

    private function buildClassAssignedDescription(array $milestone, CourseEnrollment $enrollment): string
    {
        $className = data_get($milestone, 'payload.assigned_class.name') ?? $enrollment->courseClass?->name;

        return $className
            ? 'Hồ sơ đã được gắn vào lớp/đợt học ' . $className . '.'
            : 'Hồ sơ đã được trung tâm gắn vào lớp học phù hợp.';
    }

    private function buildClassChangedDescription(array $milestone): string
    {
        $fromClass = data_get($milestone, 'payload.from_class.name');
        $toClass = data_get($milestone, 'payload.to_class.name');

        if ($fromClass && $toClass) {
            return 'Hồ sơ được chuyển từ lớp ' . $fromClass . ' sang ' . $toClass . '.';
        }

        if ($toClass) {
            return 'Hồ sơ được cập nhật sang lớp ' . $toClass . '.';
        }

        return 'Trung tâm đã thay đổi thông tin xếp lớp của hồ sơ.';
    }

    private function resolveMilestoneTimestamp(
        string $action,
        array $milestone,
        CourseEnrollment $enrollment,
        ?Payment $payment
    ): ?Carbon {
        $candidate = match ($action) {
            'application.created' => $enrollment->created_at,
            'application.waitlist_joined' => data_get($milestone, 'payload.enrollment.waitlist_joined_at') ?: $enrollment->waitlist_joined_at,
            'application.seat_hold_granted' => data_get($milestone, 'payload.enrollment.waitlist_promoted_at') ?: $enrollment->waitlist_promoted_at,
            'application.seat_hold_confirmed' => data_get($milestone, 'payload.payment.paid_at') ?: data_get($milestone, 'payload.enrollment.enrolled_at') ?: $payment?->paid_at,
            'application.payment_recorded' => data_get($milestone, 'payload.payment.paid_at') ?: $payment?->paid_at ?: $payment?->created_at,
            'application.approved' => data_get($milestone, 'payload.enrollment.approved_at') ?: $enrollment->approved_at,
            'application.rejected' => data_get($milestone, 'payload.enrollment.rejected_at') ?: $enrollment->rejected_at,
            'application.cancelled' => data_get($milestone, 'payload.enrollment.cancelled_at') ?: $enrollment->cancelled_at,
            'application.class_assigned', 'application.class_changed' => data_get($milestone, 'last_attempt_at'),
            'application.seat_hold_expired' => data_get($milestone, 'payload.enrollment.cancelled_at') ?: data_get($milestone, 'last_attempt_at'),
            'application.certificate_issued', 'certificate.issued' => data_get($milestone, 'payload.issued_at') ?: $enrollment->certificate?->issued_at ?: $enrollment->certificate?->created_at,
            default => data_get($milestone, 'last_attempt_at'),
        };

        return $this->parseTimestamp($candidate);
    }

    private function parseTimestamp(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private function sortPaymentsForApplication(Collection $payments): Collection
    {
        return $payments
            ->sort(function (Payment $left, Payment $right): int {
                $compare = $this->statusPriority($left->status, ['completed']) <=> $this->statusPriority($right->status, ['completed']);

                if ($compare !== 0) {
                    return $compare;
                }

                $compare = $this->nullableDateComparison($right->paid_at, $left->paid_at);

                if ($compare !== 0) {
                    return $compare;
                }

                $compare = $this->nullableDateComparison($right->created_at, $left->created_at);

                if ($compare !== 0) {
                    return $compare;
                }

                return ((int) $right->id) <=> ((int) $left->id);
            })
            ->values();
    }

    private function sortPaymentsForHistory(Collection $payments): Collection
    {
        return $payments
            ->sort(function (Payment $left, Payment $right): int {
                $compare = $this->nullableDateComparison(
                    $this->paymentMoment($right),
                    $this->paymentMoment($left)
                );

                if ($compare !== 0) {
                    return $compare;
                }

                $compare = $this->nullableDateComparison($right->created_at, $left->created_at);

                if ($compare !== 0) {
                    return $compare;
                }

                return ((int) $right->id) <=> ((int) $left->id);
            })
            ->values();
    }

    private function paymentMoment(Payment $payment)
    {
        return $payment->paid_at ?: $payment->created_at;
    }

    private function nullableDateComparison($left, $right): int
    {
        $leftValue = $left?->format('Y-m-d H:i:s.u');
        $rightValue = $right?->format('Y-m-d H:i:s.u');

        return ($leftValue ?? '') <=> ($rightValue ?? '');
    }

    private function statusPriority(?string $status, array $priorityOrder): int
    {
        $index = array_search($status, $priorityOrder, true);

        return $index === false ? count($priorityOrder) : $index;
    }

    private function resolvePaymentMethodLabel(?Payment $payment, CourseEnrollment $enrollment): string
    {
        if ($payment) {
            return $this->resolvePaymentMethodLabelFromValue((string) $payment->method);
        }

        if ((float) ($enrollment->final_price ?? 0) <= 0) {
            return 'Miễn phí / ưu đãi';
        }

        if ($enrollment->hasActiveSeatHold()) {
            return 'Ví nội bộ khi xác nhận giữ chỗ';
        }

        if ($enrollment->isWaitlisted()) {
            return 'Chờ tới lượt thanh toán';
        }

        return 'Chưa ghi nhận';
    }

    private function resolvePaymentMethodLabelFromValue(string $method): string
    {
        return [
            'wallet' => 'Ví nội bộ',
            'promotion' => 'Khuyến mãi / miễn phí',
            'vnpay' => 'VNPay',
            'bank_transfer' => 'Chuyển khoản',
            'cash' => 'Tiền mặt',
            'counter' => 'Tại quầy',
        ][$method] ?? ucfirst(str_replace('_', ' ', $method));
    }
}
