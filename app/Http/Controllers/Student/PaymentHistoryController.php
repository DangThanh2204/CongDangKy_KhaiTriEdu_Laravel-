<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\CollectionPaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $payments = $this->sortPaymentsForHistory(
            Payment::query()
                ->with(['courseClass.course.category', 'discountCode'])
                ->where('user_id', $user->id)
                ->get()
        );

        $summary = [
            'total' => $payments->count(),
            'completed' => $payments->filter(fn (Payment $payment) => $payment->isCompleted())->count(),
            'pending' => $payments->filter(fn (Payment $payment) => $payment->isPending())->count(),
            'failed' => $payments->filter(fn (Payment $payment) => $payment->isFailed())->count(),
            'completed_amount' => $payments
                ->filter(fn (Payment $payment) => $payment->isCompleted())
                ->reduce(fn (float $carry, Payment $payment) => $carry + (float) $payment->amount, 0.0),
        ];

        $paginatedPayments = CollectionPaginator::paginate(
            $payments,
            10,
            max((int) $request->query('page', 1), 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'page',
            ],
        );

        return view('student.payments.index', [
            'payments' => $paginatedPayments,
            'summary' => $summary,
            'user' => $user,
        ]);
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
}
