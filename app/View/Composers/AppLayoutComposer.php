<?php

namespace App\View\Composers;

use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppLayoutComposer
{
    public function compose(View $view): void
    {
        $pendingEnrollmentCount = 0;

        $user = Auth::user();

        if ($user && $user->isStudent()) {
            $pendingEnrollmentCount = CourseEnrollment::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();
        }

        $view->with('pendingEnrollmentCount', $pendingEnrollmentCount);
    }
}
