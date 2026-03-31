<?php

namespace App\View\Composers;

use App\Models\CourseEnrollment;
use App\Models\CourseReview;
use App\Models\Payment;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminLayoutComposer
{
    private const REVIEWS_SEEN_AT_SESSION_KEY = 'admin.notifications.reviews_seen_at';

    public function compose(View $view): void
    {
        $summary = [
            'pending_enrollment_count' => 0,
            'new_review_count' => 0,
            'pending_payment_count' => 0,
            'pending_wallet_topup_count' => 0,
            'payment_attention_count' => 0,
            'total_attention_count' => 0,
            'has_attention_items' => false,
        ];

        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            $view->with('adminAttentionSummary', $summary);

            return;
        }

        try {
            if (Schema::hasTable((new WalletTransaction())->getTable())) {
                WalletTransaction::expireOverdueDirectTopups();

                $summary['pending_wallet_topup_count'] = WalletTransaction::query()
                    ->pendingDirectApproval()
                    ->count();
            }

            if (Schema::hasTable((new CourseEnrollment())->getTable())) {
                $summary['pending_enrollment_count'] = CourseEnrollment::query()
                    ->where('status', 'pending')
                    ->count();
            }

            if (Schema::hasTable((new Payment())->getTable())) {
                $summary['pending_payment_count'] = Payment::query()
                    ->where('status', 'pending')
                    ->count();
            }

            if (! session()->has(self::REVIEWS_SEEN_AT_SESSION_KEY)) {
                session()->put(self::REVIEWS_SEEN_AT_SESSION_KEY, now()->toDateTimeString());
            }

            $reviewsSeenAt = session(self::REVIEWS_SEEN_AT_SESSION_KEY);

            if (Schema::hasTable((new CourseReview())->getTable())) {
                $summary['new_review_count'] = CourseReview::query()
                    ->when($reviewsSeenAt, fn ($query) => $query->where('created_at', '>', $reviewsSeenAt))
                    ->count();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $summary['payment_attention_count'] = $summary['pending_payment_count'] + $summary['pending_wallet_topup_count'];
        $summary['total_attention_count'] = $summary['pending_enrollment_count']
            + $summary['new_review_count']
            + $summary['pending_payment_count']
            + $summary['pending_wallet_topup_count'];
        $summary['has_attention_items'] = $summary['total_attention_count'] > 0;

        $view->with('adminAttentionSummary', $summary);
    }
}