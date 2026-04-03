<?php

namespace App\View\Composers;

use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AppLayoutComposer
{
    public function compose(View $view): void
    {
        $pendingEnrollmentCount = 0;
        $appNotifications = collect();
        $unreadNotificationsCount = 0;

        try {
            $user = Auth::user();

            if ($user && $user->isStudent()) {
                $pendingEnrollmentCount = CourseEnrollment::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->count();
            }

            if ($user && Schema::hasTable('notifications')) {
                $appNotifications = $user->notifications()
                    ->latest()
                    ->limit(6)
                    ->get();

                $unreadNotificationsCount = $user->unreadNotifications()->count();
            }
        } catch (\Throwable $exception) {
            report($exception);
            $pendingEnrollmentCount = 0;
            $appNotifications = collect();
            $unreadNotificationsCount = 0;
        }

        $view->with([
            'pendingEnrollmentCount' => $pendingEnrollmentCount,
            'appNotifications' => $appNotifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
        ]);
    }
}