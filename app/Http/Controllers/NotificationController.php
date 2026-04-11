<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = collect();
        $unreadNotificationsCount = 0;

        if ($user && $this->supportsNotifications()) {
            if ($this->usesMongoNotificationStore()) {
                $notifications = $user->portalNotifications()->latest('created_at')->paginate(20);
                $unreadNotificationsCount = $user->unreadPortalNotifications()->count();
            } else {
                $notifications = $user->notifications()->latest()->paginate(20);
                $unreadNotificationsCount = $user->unreadNotifications()->count();
            }
        }

        return view('notifications.index', compact('notifications', 'unreadNotificationsCount'));
    }

    public function visit(Request $request, string $notification): RedirectResponse
    {
        $notification = $this->findNotification($request, $notification);

        abort_unless((int) $notification->notifiable_id === (int) $request->user()->id, 403);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $target = data_get($notification->data, 'action_url') ?: route('student.dashboard');

        return redirect()->to($target);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        if ($request->user() && $this->supportsNotifications()) {
            if ($this->usesMongoNotificationStore()) {
                $request->user()->unreadPortalNotifications()->get()->each->markAsRead();
            } else {
                $request->user()->unreadNotifications->markAsRead();
            }
        }

        return back()->with('success', 'Da danh dau tat ca thong bao la da doc.');
    }

    protected function supportsNotifications(): bool
    {
        return true;
    }

    protected function usesMongoNotificationStore(): bool
    {
        return config('database.default') === 'mongodb';
    }

    protected function findNotification(Request $request, string $notificationId)
    {
        if ($this->usesMongoNotificationStore()) {
            return AppNotification::query()
                ->where('id', (int) $notificationId)
                ->firstOrFail();
        }

        return $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();
    }
}
