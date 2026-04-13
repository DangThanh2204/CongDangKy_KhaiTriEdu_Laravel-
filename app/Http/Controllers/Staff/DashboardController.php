<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->startOfDay();
        $windowEnd = now()->addDays(30)->endOfDay();

        $activeClasses = CourseClass::query()
            ->with(['course.category', 'instructor', 'enrollments'])
            ->where('status', 'active')
            ->get();

        $upcomingClasses = $activeClasses
            ->filter(function (CourseClass $class) use ($today, $windowEnd): bool {
                return $class->start_date
                    && $class->start_date->greaterThanOrEqualTo($today)
                    && $class->start_date->lessThanOrEqualTo($windowEnd);
            })
            ->sortBy(fn (CourseClass $class) => optional($class->start_date)->timestamp ?? PHP_INT_MAX)
            ->take(6)
            ->values();

        $classHotspots = $activeClasses
            ->filter(fn (CourseClass $class) => $class->is_full || $class->waitlist_count > 0 || $class->held_seats_count > 0)
            ->sortByDesc(function (CourseClass $class): int {
                return ($class->is_full ? 1000 : 0)
                    + ($class->waitlist_count * 10)
                    + $class->held_seats_count;
            })
            ->take(6)
            ->values();

        $recentEnrollments = CourseEnrollment::query()
            ->with(['student', 'courseClass.course.category', 'discountCode'])
            ->latest('created_at')
            ->limit(6)
            ->get()
            ->map(function (CourseEnrollment $enrollment) {
                $class = $enrollment->courseClass;
                $course = $enrollment->course ?: $class?->course;

                if ($course) {
                    $enrollment->setRelation('course', $course);
                }

                return $enrollment;
            });

        $pendingPayments = Payment::query()
            ->with(['user', 'courseClass.course.category', 'discountCode'])
            ->where('status', 'pending')
            ->latest('created_at')
            ->limit(6)
            ->get();

        $stats = [
            'pending_enrollments' => CourseEnrollment::pending()->count(),
            'seat_holds' => CourseEnrollment::holdingSeat()->count(),
            'waitlisted' => CourseEnrollment::waitlisted()->count(),
            'pending_payments' => Payment::query()->where('status', 'pending')->count(),
            'new_enrollments_today' => CourseEnrollment::query()->where('created_at', '>=', $today)->count(),
            'payments_today' => Payment::query()->where('created_at', '>=', $today)->count(),
            'upcoming_classes' => $upcomingClasses->count(),
            'classes_requiring_attention' => $classHotspots->count(),
            'online_classes' => $activeClasses->filter(fn (CourseClass $class) => $class->isOnline())->count(),
            'offline_classes' => $activeClasses->filter(fn (CourseClass $class) => $class->isOffline())->count(),
            'pending_amount' => $pendingPayments->sum(fn (Payment $payment) => (float) $payment->amount),
        ];

        $staffChecklist = [
            [
                'title' => 'Rà soát hồ sơ mới',
                'copy' => 'Ưu tiên hồ sơ offline, hồ sơ có mã giảm giá và hồ sơ vừa tạo trong hôm nay.',
                'value' => $stats['pending_enrollments'],
                'tone' => 'blue',
            ],
            [
                'title' => 'Theo dõi thanh toán chờ xử lý',
                'copy' => 'Kiểm tra giao dịch treo, xác minh thủ công và phối hợp khi VNPay chưa callback đủ.',
                'value' => $stats['pending_payments'],
                'tone' => 'orange',
            ],
            [
                'title' => 'Canh hàng chờ / giữ chỗ',
                'copy' => 'Xử lý nhanh các lớp có giữ chỗ 24h hoặc học viên đang nằm trong waitlist.',
                'value' => $stats['seat_holds'] + $stats['waitlisted'],
                'tone' => 'teal',
            ],
        ];

        return view('staff.dashboard', compact(
            'user',
            'stats',
            'recentEnrollments',
            'pendingPayments',
            'upcomingClasses',
            'classHotspots',
            'staffChecklist'
        ));
    }
}
