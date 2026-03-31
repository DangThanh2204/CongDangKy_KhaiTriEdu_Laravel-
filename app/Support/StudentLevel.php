<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StudentLevel
{
    public const LEVELS = [
        [
            'key' => 'dong',
            'title' => 'Đồng',
            'badge_name' => 'Huy hiệu Đồng',
            'min_points' => 0,
            'icon' => 'fas fa-medal',
            'headline' => 'Bạn đang xây nền tảng học tập đầu tiên.',
            'accent' => '#b87333',
        ],
        [
            'key' => 'bac',
            'title' => 'Bạc',
            'badge_name' => 'Huy hiệu Bạc',
            'min_points' => 320,
            'icon' => 'fas fa-shield-alt',
            'headline' => 'Bạn đã học đều và bắt đầu tạo đà tăng trưởng.',
            'accent' => '#94a3b8',
        ],
        [
            'key' => 'vang',
            'title' => 'Vàng',
            'badge_name' => 'Huy hiệu Vàng',
            'min_points' => 820,
            'icon' => 'fas fa-trophy',
            'headline' => 'Bạn đang giữ phong độ học tập rất tốt.',
            'accent' => '#eab308',
        ],
        [
            'key' => 'kim_cuong',
            'title' => 'Kim cương',
            'badge_name' => 'Huy hiệu Kim cương',
            'min_points' => 1650,
            'icon' => 'fas fa-gem',
            'headline' => 'Bạn thuộc nhóm học viên nổi bật nhất hệ thống.',
            'accent' => '#38bdf8',
        ],
    ];

    public static function makeForUser(User $user): array
    {
        if (! $user->isStudent()) {
            return self::emptyProfile();
        }

        try {
            $enrollments = $user->relationLoaded('enrollments')
                ? $user->enrollments
                : $user->enrollments()->select(['id', 'user_id', 'status', 'completed_at'])->get();

            $progressRecords = $user->relationLoaded('materialProgress')
                ? $user->materialProgress
                : $user->materialProgress()->with('material:id,estimated_duration_minutes')->get();

            if ($progressRecords instanceof EloquentCollection) {
                $progressRecords->loadMissing('material:id,estimated_duration_minutes');
            }

            $certificates = $user->relationLoaded('certificates')
                ? $user->certificates
                : $user->certificates()->select(['id', 'user_id'])->get();

            $approvedEnrollments = $enrollments->filter(fn ($enrollment) => in_array($enrollment->status, ['approved', 'completed'], true));
            $completedEnrollments = $enrollments->filter(fn ($enrollment) => $enrollment->isCompleted());
            $completedMaterials = $progressRecords->filter(fn ($progress) => ! is_null($progress->completed_at));
            $passedQuizzes = $progressRecords->filter(fn ($progress) => ! is_null($progress->passed_at));

            $studyMinutes = (int) round($progressRecords->sum(function ($progress) {
                $estimatedMinutes = (int) ($progress->material?->estimated_duration_minutes ?? 0);

                if ($estimatedMinutes <= 0) {
                    return 0;
                }

                $progressPercent = ! is_null($progress->completed_at)
                    ? 100
                    : max(0, min(100, (int) ($progress->progress_percent ?? 0)));

                return $estimatedMinutes * ($progressPercent / 100);
            }));

            $activeStudyDays = $progressRecords
                ->map(function ($progress) {
                    $moment = $progress->last_viewed_at ?? $progress->completed_at ?? $progress->started_at;

                    return $moment?->toDateString();
                })
                ->filter()
                ->unique()
                ->count();

            $certificateCount = $certificates->count();

            $pointsBreakdown = [
                'study_minutes' => (int) floor($studyMinutes / 8),
                'completed_materials' => $completedMaterials->count() * 12,
                'passed_quizzes' => $passedQuizzes->count() * 18,
                'approved_courses' => $approvedEnrollments->count() * 30,
                'completed_courses' => $completedEnrollments->count() * 140,
                'certificates' => $certificateCount * 180,
                'active_days' => $activeStudyDays * 5,
            ];

            $points = array_sum($pointsBreakdown);
            $currentLevel = self::resolveLevel($points);
            $nextLevel = self::resolveNextLevel($points);

            if ($nextLevel) {
                $range = max(1, $nextLevel['min_points'] - $currentLevel['min_points']);
                $earnedSinceCurrentLevel = max(0, $points - $currentLevel['min_points']);
                $progressToNext = (int) round(min(100, ($earnedSinceCurrentLevel / $range) * 100));
                $pointsToNext = max(0, $nextLevel['min_points'] - $points);
            } else {
                $progressToNext = 100;
                $pointsToNext = 0;
            }

            return [
                'points' => $points,
                'points_label' => number_format($points),
                'level' => $currentLevel,
                'badge' => [
                    'name' => $currentLevel['badge_name'],
                    'icon' => $currentLevel['icon'],
                    'key' => $currentLevel['key'],
                    'accent' => $currentLevel['accent'],
                ],
                'next_level' => $nextLevel,
                'progress_to_next' => $progressToNext,
                'points_to_next' => $pointsToNext,
                'study_duration_label' => StudyDuration::formatMinutes($studyMinutes),
                'breakdown' => $pointsBreakdown,
                'metrics' => [
                    'approved_courses' => $approvedEnrollments->count(),
                    'completed_courses' => $completedEnrollments->count(),
                    'completed_materials' => $completedMaterials->count(),
                    'passed_quizzes' => $passedQuizzes->count(),
                    'certificates' => $certificateCount,
                    'active_study_days' => $activeStudyDays,
                    'study_minutes' => $studyMinutes,
                    'study_duration_label' => StudyDuration::formatMinutes($studyMinutes),
                ],
                'summary' => self::buildSummaryCopy($currentLevel, $nextLevel, $pointsToNext),
            ];
        } catch (\Throwable $exception) {
            Log::warning('Student level summary skipped: ' . $exception->getMessage(), [
                'user_id' => $user->id,
            ]);

            return self::emptyProfile();
        }
    }

    public static function attachSummaries(EloquentCollection $users): EloquentCollection
    {
        if ($users->isEmpty()) {
            return $users;
        }

        $students = $users->filter(fn ($user) => $user instanceof User && $user->isStudent())->values();

        if ($students->isNotEmpty()) {
            try {
                $students->loadMissing([
                    'enrollments:id,user_id,status,completed_at',
                    'certificates:id,user_id',
                    'materialProgress:id,user_id,course_material_id,progress_percent,started_at,last_viewed_at,completed_at,passed_at',
                    'materialProgress.material:id,estimated_duration_minutes',
                ]);
            } catch (\Throwable $exception) {
                Log::warning('Student level eager load skipped: ' . $exception->getMessage());
            }
        }

        $users->each(function (User $user) {
            $user->setAttribute(
                'student_level_summary',
                $user->isStudent() ? self::makeForUser($user) : null
            );
        });

        return $users;
    }

    public static function buildLeaderboard(int $limit = 10, ?int $highlightUserId = null): array
    {
        try {
            $students = User::students()
                ->select(['id', 'username', 'fullname', 'email', 'avatar', 'created_at'])
                ->get();

            if ($students->isEmpty()) {
                return [
                    'entries' => collect(),
                    'current_user' => null,
                    'total_students' => 0,
                ];
            }

            self::attachSummaries($students);

            $sorted = $students->sort(function (User $left, User $right) {
                $leftSummary = $left->getAttribute('student_level_summary') ?? self::emptyProfile();
                $rightSummary = $right->getAttribute('student_level_summary') ?? self::emptyProfile();

                if ($leftSummary['points'] !== $rightSummary['points']) {
                    return $rightSummary['points'] <=> $leftSummary['points'];
                }

                if ($leftSummary['metrics']['completed_courses'] !== $rightSummary['metrics']['completed_courses']) {
                    return $rightSummary['metrics']['completed_courses'] <=> $leftSummary['metrics']['completed_courses'];
                }

                if ($leftSummary['metrics']['study_minutes'] !== $rightSummary['metrics']['study_minutes']) {
                    return $rightSummary['metrics']['study_minutes'] <=> $leftSummary['metrics']['study_minutes'];
                }

                return strcmp((string) ($left->fullname ?? $left->username), (string) ($right->fullname ?? $right->username));
            })->values();

            $entries = $sorted->values()->map(function (User $student, int $index) {
                $summary = $student->getAttribute('student_level_summary') ?? self::emptyProfile();

                return [
                    'rank' => $index + 1,
                    'user' => $student,
                    'summary' => $summary,
                    'points' => $summary['points'],
                    'points_label' => $summary['points_label'],
                ];
            });

            return [
                'entries' => $entries->take($limit)->values(),
                'current_user' => $highlightUserId ? $entries->firstWhere('user.id', $highlightUserId) : null,
                'total_students' => $entries->count(),
            ];
        } catch (\Throwable $exception) {
            Log::warning('Student leaderboard skipped: ' . $exception->getMessage(), [
                'highlight_user_id' => $highlightUserId,
            ]);

            return [
                'entries' => collect(),
                'current_user' => null,
                'total_students' => 0,
            ];
        }
    }

    public static function emptyProfile(): array
    {
        $level = self::LEVELS[0];
        $nextLevel = self::LEVELS[1] ?? null;

        return [
            'points' => 0,
            'points_label' => '0',
            'level' => $level,
            'badge' => [
                'name' => $level['badge_name'],
                'icon' => $level['icon'],
                'key' => $level['key'],
                'accent' => $level['accent'],
            ],
            'next_level' => $nextLevel,
            'progress_to_next' => 0,
            'points_to_next' => $nextLevel['min_points'] ?? 0,
            'study_duration_label' => StudyDuration::formatMinutes(0),
            'breakdown' => [
                'study_minutes' => 0,
                'completed_materials' => 0,
                'passed_quizzes' => 0,
                'approved_courses' => 0,
                'completed_courses' => 0,
                'certificates' => 0,
                'active_days' => 0,
            ],
            'metrics' => [
                'approved_courses' => 0,
                'completed_courses' => 0,
                'completed_materials' => 0,
                'passed_quizzes' => 0,
                'certificates' => 0,
                'active_study_days' => 0,
                'study_minutes' => 0,
                'study_duration_label' => StudyDuration::formatMinutes(0),
            ],
            'summary' => self::buildSummaryCopy($level, $nextLevel, $nextLevel['min_points'] ?? 0),
        ];
    }

    protected static function resolveLevel(int $points): array
    {
        return collect(self::LEVELS)
            ->reverse()
            ->first(fn ($level) => $points >= $level['min_points']) ?? self::LEVELS[0];
    }

    protected static function resolveNextLevel(int $points): ?array
    {
        return collect(self::LEVELS)
            ->first(fn ($level) => $level['min_points'] > $points);
    }

    protected static function buildSummaryCopy(array $currentLevel, ?array $nextLevel, int $pointsToNext): string
    {
        if (! $nextLevel) {
            return 'Bạn đã đạt cấp cao nhất hiện tại. Hãy tiếp tục học để giữ phong độ này.';
        }

        return $currentLevel['headline'] . ' Còn ' . number_format($pointsToNext) . ' điểm để lên ' . $nextLevel['title'] . '.';
    }
}