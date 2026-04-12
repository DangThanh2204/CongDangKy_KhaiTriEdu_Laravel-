<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredCourses = collect();
        $latestPosts = collect();

        try {
            $featuredCourses = Course::published()
                ->with(['category', 'modules'])
                ->orderByDesc('created_at')
                ->limit(4)
                ->get();

            $this->attachModulesCount($featuredCourses);
            $this->attachEnrollmentState($featuredCourses);
        } catch (\Throwable $exception) {
            report($exception);
            $featuredCourses = collect();
        }

        try {
            $latestPosts = Post::published()
                ->with(['author', 'category'])
                ->orderByDesc('created_at')
                ->limit(3)
                ->get();
        } catch (\Throwable $exception) {
            report($exception);
            $latestPosts = collect();
        }

        return view('home', [
            'featuredCourses' => $featuredCourses,
            'latestPosts' => $latestPosts,
        ]);
    }

    private function attachModulesCount(Collection $courses): void
    {
        $courses->each(function ($course) {
            $course->setAttribute('modules_count', $course->modules->count());
        });
    }

    private function attachEnrollmentState(Collection $courses): void
    {
        $user = Auth::user();

        $courses->each(function ($course) {
            $course->setAttribute('is_current_user_enrolled', false);
            $course->setAttribute('is_current_user_pending', false);
        });

        if (! $user || $courses->isEmpty()) {
            return;
        }

        $statusesByCourse = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->whereIn('course_id', $courses->pluck('id')->all())
            ->get(['course_id', 'status'])
            ->groupBy(fn ($enrollment) => (string) $enrollment->course_id);

        $courses->each(function ($course) use ($statusesByCourse) {
            $courseStatuses = $statusesByCourse->get((string) $course->id, collect());

            $course->setAttribute(
                'is_current_user_enrolled',
                $courseStatuses->contains(fn ($enrollment) => in_array($enrollment->status, ['approved', 'completed'], true))
            );

            $course->setAttribute(
                'is_current_user_pending',
                $courseStatuses->contains('status', 'pending')
            );
        });
    }
}
