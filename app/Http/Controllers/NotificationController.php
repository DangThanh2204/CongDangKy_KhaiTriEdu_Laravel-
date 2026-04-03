<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = collect();

        if ($user && $this->supportsNotifications()) {
            $notifications = $user->notifications()->latest()->paginate(20);
        }

        return view('notifications.index', compact('notifications'));
    }

    public function visit(Request $request, DatabaseNotification $notification): RedirectResponse
    {
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
            $request->user()->unreadNotifications->markAsRead();
        }

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }

    protected function supportsNotifications(): bool
    {
        try {
            return Schema::hasTable('notifications');
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }
}