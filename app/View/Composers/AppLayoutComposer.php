<?php

namespace App\View\Composers;

use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
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

            if ($user) {
                $payload = Cache::remember(
                    sprintf('layout.app.user.%s', $user->id),
                    now()->addSeconds(15),
                    function () use ($user) {
                        $pendingEnrollmentCount = 0;
                        $appNotifications = collect();
                        $unreadNotificationsCount = 0;

                        if ($user->isStudent()) {
                            $pendingEnrollmentCount = CourseEnrollment::query()
                                ->where('user_id', $user->id)
                                ->where('status', 'pending')
                                ->count();
                        }

                        if ($this->supportsDatabaseNotifications()) {
                            if ($this->usesMongoNotificationStore()) {
                                $appNotifications = $user->portalNotifications()
                                    ->latest('created_at')
                                    ->limit(6)
                                    ->get();

                                $unreadNotificationsCount = $user->unreadPortalNotifications()->count();
                            } else {
                                $appNotifications = $user->notifications()
                                    ->latest()
                                    ->limit(6)
                                    ->get();

                                $unreadNotificationsCount = $user->unreadNotifications()->count();
                            }
                        }

                        return [
                            'pendingEnrollmentCount' => $pendingEnrollmentCount,
                            'appNotifications' => $appNotifications,
                            'unreadNotificationsCount' => $unreadNotificationsCount,
                        ];
                    }
                );

                $pendingEnrollmentCount = $payload['pendingEnrollmentCount'];
                $appNotifications = $payload['appNotifications'];
                $unreadNotificationsCount = $payload['unreadNotificationsCount'];
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

    private function supportsDatabaseNotifications(): bool
    {
        return true;
    }

    private function usesMongoNotificationStore(): bool
    {
        return config('database.default') === 'mongodb';
    }
}
