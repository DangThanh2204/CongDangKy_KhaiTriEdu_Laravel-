<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Payment;
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
            ->get()
            ->map(function (CourseEnrollment $enrollment) {
                $class = $enrollment->courseClass;
                $course = $enrollment->course ?: $class?->course;

                if ($course) {
                    $enrollment->setRelation('course', $course);
                }

                return $enrollment;
            });

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
