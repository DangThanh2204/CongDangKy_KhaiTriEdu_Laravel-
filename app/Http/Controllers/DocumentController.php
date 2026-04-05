<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function registrationForm(CourseEnrollment $enrollment)
    {
        abort_unless($this->canAccessEnrollment($enrollment), 403);

        $enrollment->loadMissing([
            'user',
            'courseClass.course.category',
            'discountCode',
        ]);

        $payment = $this->latestPaymentForEnrollment($enrollment);
        $course = $enrollment->courseClass?->course;
        $class = $enrollment->courseClass;
        $student = $enrollment->user;
        $fileBase = Str::slug($course?->title ?: 'phieu-dang-ky') ?: 'phieu-dang-ky';
        $fileName = 'phieu-dang-ky-' . $fileBase . '-' . $enrollment->id . '.pdf';

        return Pdf::loadView('documents.registration-form', compact('enrollment', 'payment', 'course', 'class', 'student'))
            ->setPaper('a4')
            ->download($fileName);
    }

    public function paymentReceipt(Payment $payment)
    {
        abort_unless($this->canAccessPayment($payment), 403);

        if (! $payment->isCompleted()) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('error', 'Biên nhận chỉ sẵn sàng sau khi giao dịch được hoàn tất.');
        }

        $payment->loadMissing([
            'user',
            'courseClass.course.category',
            'discountCode',
        ]);

        $enrollment = CourseEnrollment::query()
            ->with(['user', 'courseClass.course.category', 'discountCode'])
            ->where('user_id', $payment->user_id)
            ->where('class_id', $payment->class_id)
            ->latest('created_at')
            ->first();

        $course = $payment->courseClass?->course;
        $class = $payment->courseClass;
        $student = $payment->user;
        $safeReference = Str::slug($payment->reference ?: ('bien-nhan-' . $payment->id)) ?: ('bien-nhan-' . $payment->id);
        $fileName = $safeReference . '.pdf';

        return Pdf::loadView('documents.payment-receipt', compact('payment', 'enrollment', 'course', 'class', 'student'))
            ->setPaper('a4')
            ->download($fileName);
    }

    private function latestPaymentForEnrollment(CourseEnrollment $enrollment): ?Payment
    {
        return Payment::query()
            ->with(['user', 'courseClass.course.category', 'discountCode'])
            ->where('user_id', $enrollment->user_id)
            ->where('class_id', $enrollment->class_id)
            ->orderByRaw("CASE WHEN status = 'completed' THEN 0 ELSE 1 END")
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->first();
    }

    private function canAccessEnrollment(CourseEnrollment $enrollment): bool
    {
        $user = Auth::user();

        return $user !== null
            && ($user->id === $enrollment->user_id || $user->isAdmin());
    }

    private function canAccessPayment(Payment $payment): bool
    {
        $user = Auth::user();

        return $user !== null
            && ($user->id === $payment->user_id || $user->isAdmin());
    }
}