<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\CsvExportService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $reference = $request->get('reference');
        $method = $request->get('method');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $payments = $this->filteredQuery($request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Payment::count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
        ];

        return view('admin.payments.index', compact('payments', 'search', 'status', 'reference', 'method', 'stats', 'fromDate', 'toDate'));
    }

    public function export(Request $request, CsvExportService $csvExportService)
    {
        $payments = $this->filteredQuery($request)
            ->latest()
            ->get();

        return $csvExportService->download(
            'payments-' . now()->format('Y-m-d-His') . '.csv',
            ['ID', 'Hoc vien', 'Email', 'Khoa hoc', 'Lop', 'So tien', 'Phuong thuc', 'Trang thai', 'Ma giao dich', 'Ngay tao'],
            $payments->map(function (Payment $payment) {
                return [
                    $payment->id,
                    $payment->user->fullname ?? $payment->user->username ?? '',
                    $payment->user->email ?? '',
                    $payment->course->title ?? '',
                    $payment->courseClass->name ?? '',
                    $payment->amount,
                    $payment->method,
                    $payment->status,
                    $payment->reference,
                    optional($payment->paid_at ?? $payment->created_at)->format('d/m/Y H:i'),
                ];
            })
        );
    }

    protected function filteredQuery(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $reference = $request->get('reference');
        $method = $request->get('method');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = Payment::with(['user', 'courseClass.course']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($method) {
            $query->where('method', $method);
        }

        if ($reference) {
            $query->where('reference', 'like', "%{$reference}%");
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_name')) {
            $className = $request->get('class_name');
            $query->whereHas('courseClass', function ($q) use ($className) {
                $q->where('name', 'like', "%{$className}%");
            });
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query;
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'courseClass.course']);
        return view('admin.payments.show', compact('payment'));
    }

    public function confirm(Payment $payment)
    {
        if (!$payment->isPending()) {
            return back()->with('error', 'Thanh toán đã được xử lý.');
        }

        $payment->markCompleted();

        \App\Services\SystemLogService::record('transaction', 'payment_confirmed', ['payment_id' => $payment->id, 'amount' => $payment->amount, 'reference' => $payment->reference]);

        try {
            $enrollment = \App\Models\CourseEnrollment::where('user_id', $payment->user_id)
                ->where('class_id', $payment->class_id)
                ->where('status', 'pending')
                ->first();

            if ($enrollment) {
                $enrollment->approve();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return back()->with('success', 'Đã xác nhận thanh toán thành công.');
    }

    public function fail(Request $request, Payment $payment)
    {
        if (!$payment->isPending()) {
            return back()->with('error', 'Thanh toán đã được xử lý.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $payment->markFailed($request->reason);

        \App\Services\SystemLogService::record('transaction', 'payment_failed', ['payment_id' => $payment->id, 'reason' => $request->reason, 'reference' => $payment->reference]);

        return back()->with('success', 'Đã đánh dấu thanh toán là thất bại.');
    }
}