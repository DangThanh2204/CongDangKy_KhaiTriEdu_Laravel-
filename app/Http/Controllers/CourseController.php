<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseMaterial;
use App\Models\CourseMaterialProgress;
use App\Models\CourseMaterialQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['category'])->withCount('modules')->published();

        if ($request->has('category') && $request->category) {
            $query->byCategory($request->category);
        }

        if ($request->has('level') && $request->level) {
            $query->where('level', $request->level);
        }

        if ($request->filled('delivery_mode')) {
            $query->deliveryMode($request->delivery_mode);
        }

        if ($request->filled('q')) {
            $keyword = trim($request->q);

            $query->where(function ($innerQuery) use ($keyword) {
                $innerQuery->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('short_description', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhereHas('category', function ($categoryQuery) use ($keyword) {
                        $categoryQuery->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'featured':
                    $query->featured();
                    break;
                case 'popular':
                    $query->popular();
                    break;
            }
        }

        $courses = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        $enrolledCourses = [];
        $pendingCourses = [];

        if (Auth::check()) {
            $userEnrollments = CourseEnrollment::with('course')
                ->where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->get();

            $enrolledCourses = $userEnrollments
                ->whereIn('status', ['approved', 'completed'])
                ->map(fn ($enrollment) => $enrollment->course?->id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $pendingCourses = $userEnrollments
                ->where('status', 'pending')
                ->map(fn ($enrollment) => $enrollment->course?->id)
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $categories = \App\Models\CourseCategory::orderBy('name')->get();

        return view('courses.index', compact('courses', 'enrolledCourses', 'pendingCourses', 'categories'));
    }

    public function show(Course $course)
    {
        if ($course->status !== 'published') {
            abort(404);
        }

        $isEnrolled = false;
        $isPending = false;
        $currentEnrollment = null;

        if (Auth::check()) {
            $currentEnrollment = CourseEnrollment::with('class')
                ->where('user_id', Auth::id())
                ->forCourse($course)
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->latest('id')
                ->first();

            if ($currentEnrollment) {
                $isEnrolled = in_array($currentEnrollment->status, ['approved', 'completed'], true);
                $isPending = $currentEnrollment->status === 'pending';
            }
        }

        $classes = $course->classes()
            ->with('schedules')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        $similarCourses = Course::published()
            ->where('id', '!=', $course->id)
            ->where('category_id', $course->category_id)
            ->with(['category'])
            ->withCount('modules')
            ->limit(4)
            ->get();

        $course->load([
            'category',
            'instructor',
            'modules' => function ($query) {
                $query->ordered()->with([
                    'materials' => function ($materialQuery) {
                        $materialQuery->orderBy('order');
                    },
                ]);
            },
            'materials' => function ($query) {
                $query->whereNull('course_module_id')->orderBy('order');
            },
        ]);

        $standaloneMaterials = $course->materials;

        $reviews = \App\Models\CourseReview::with(['user', 'replies.user'])
            ->where('course_id', $course->id)
            ->latest()
            ->paginate(10);

        $userReview = Auth::check()
            ? \App\Models\CourseReview::where('course_id', $course->id)->where('user_id', Auth::id())->first()
            : null;

        return view('courses.show', compact(
            'course',
            'classes',
            'isEnrolled',
            'isPending',
            'currentEnrollment',
            'similarCourses',
            'reviews',
            'userReview',
            'standaloneMaterials'
        ));
    }

    public function storeReview(Request $request, Course $course)
    {
        if ($course->status !== 'published') {
            abort(404);
        }

        $enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->whereIn('status', ['approved', 'completed'])
            ->latest('id')
            ->first();

        if (! $enrollment) {
            return redirect()->route('courses.show', $course)->with('error', 'Ban can dang ky khoa hoc de danh gia.');
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'instructor_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        \App\Models\CourseReview::updateOrCreate(
            ['course_id' => $course->id, 'user_id' => Auth::id()],
            [
                'instructor_id' => $course->instructor_id,
                'rating' => $data['rating'],
                'instructor_rating' => $data['instructor_rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        $course->updateRating();
        $course->instructor?->updateRating();

        return redirect()->route('courses.show', $course)->with('success', 'Cam on ban da danh gia khoa hoc va giang vien.');
    }

    public function replyReview(Request $request, Course $course, \App\Models\CourseReview $review)
    {
        if ($course->id !== $review->course_id) {
            abort(404);
        }

        if (! Auth::check()) {
            return redirect()->route('courses.show', $course)->with('error', 'Ban can dang nhap de phan hoi danh gia.');
        }

        $data = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        \App\Models\CourseReviewReply::create([
            'review_id' => $review->id,
            'user_id' => Auth::id(),
            'comment' => $data['comment'],
        ]);

        return redirect()->route('courses.show', $course)->with('success', 'Da gui phan hoi.');
    }

    public function learn(Course $course)
    {
        $enrollment = $this->findEnrollment($course);

        if (! $enrollment) {
            return redirect()->route('courses.show', $course)->with('error', 'Ban chua dang ky khoa hoc nay.');
        }

        $course->load([
            'modules' => function ($query) {
                $query->ordered()->with([
                    'materials' => function ($materialQuery) {
                        $materialQuery->orderBy('order');
                    },
                ]);
            },
            'materials' => function ($query) {
                $query->whereNull('course_module_id')->orderBy('order');
            },
            'category',
            'instructor',
        ]);

        $allMaterialIds = $course->modules
            ->flatMap(function ($module) {
                return $module->materials->pluck('id');
            })
            ->merge($course->materials->pluck('id'))
            ->values();

        $progressRecords = $enrollment->materialProgress()
            ->whereIn('course_material_id', $allMaterialIds)
            ->get()
            ->keyBy('course_material_id');

        $quizAttempts = $enrollment->quizAttempts()
            ->whereIn('course_material_id', $allMaterialIds)
            ->with('material')
            ->latest('completed_at')
            ->get();

        $attemptsByMaterial = $quizAttempts->groupBy('course_material_id');

        $materialSections = $this->buildMaterialSections($course, $progressRecords, $attemptsByMaterial);
        $materials = $materialSections->flatMap(fn ($section) => $section['materials'])->values();

        $totalMaterials = $materials->count();
        $completedMaterials = $progressRecords->filter(fn ($progress) => ! is_null($progress->completed_at))->count();
        $progressPercent = $totalMaterials > 0 ? (int) round(($completedMaterials / $totalMaterials) * 100) : 0;
        $allCompleted = $totalMaterials > 0 && $completedMaterials >= $totalMaterials;

        $certificate = $allCompleted
            ? $this->issueCertificateIfEligible($course, $enrollment)
            : $enrollment->certificate;

        return view('courses.learn', [
            'course' => $course,
            'enrollment' => $enrollment,
            'materials' => $materials,
            'materialSections' => $materialSections,
            'progressPercent' => $progressPercent,
            'completedMaterials' => $completedMaterials,
            'totalMaterials' => $totalMaterials,
            'quizAttempts' => $quizAttempts->take(10),
            'certificate' => $certificate,
            'allCompleted' => $allCompleted,
        ]);
    }

    protected function buildMaterialSections(Course $course, $progressRecords, $attemptsByMaterial)
    {
        $materialSections = collect();
        $sequence = 1;

        foreach ($course->modules as $module) {
            $moduleMaterials = $module->materials
                ->map(function ($material) use ($progressRecords, $attemptsByMaterial) {
                    return $this->decorateMaterial($material, $progressRecords, $attemptsByMaterial);
                })
                ->values();

            if ($moduleMaterials->isEmpty()) {
                continue;
            }

            $moduleMaterials->each(function ($material) use (&$sequence) {
                $material->setAttribute('sequence_number', $sequence++);
            });

            $materialSections->push([
                'title' => $module->title,
                'description' => $module->description,
                'materials' => $moduleMaterials,
                'module' => $module,
            ]);
        }

        $standaloneMaterials = $course->materials
            ->map(function ($material) use ($progressRecords, $attemptsByMaterial) {
                return $this->decorateMaterial($material, $progressRecords, $attemptsByMaterial);
            })
            ->values();

        if ($standaloneMaterials->isNotEmpty()) {
            $standaloneMaterials->each(function ($material) use (&$sequence) {
                $material->setAttribute('sequence_number', $sequence++);
            });

            $materialSections->push([
                'title' => 'Noi dung bo sung',
                'description' => 'Cac bai hoc chua duoc gan vao module cu the.',
                'materials' => $standaloneMaterials,
                'module' => null,
            ]);
        }

        return $materialSections;
    }

    protected function decorateMaterial($material, $progressRecords, $attemptsByMaterial)
    {
        $progress = $progressRecords->get($material->id);
        $latestAttempt = optional($attemptsByMaterial->get($material->id))->first();

        $material->setAttribute('learning_progress', $progress);
        $material->setAttribute('latest_quiz_attempt', $latestAttempt);
        $material->setAttribute('quiz_attempt_history', $attemptsByMaterial->get($material->id, collect())->take(5));

        return $material;
    }

    public function completeMaterial(Request $request, Course $course, CourseMaterial $material)
    {
        $enrollment = $this->findEnrollment($course);

        if (! $enrollment || $material->course_id !== $course->id) {
            abort(404);
        }

        if ($material->requiresQuizPass()) {
            return back()->with('error', 'Bai quiz can duoc nop va dat diem de duoc danh dau hoan thanh.');
        }

        if (! $material->canComplete()) {
            return back()->with('error', 'Buoi hoc Meet chua toi gio mo, ban chua the danh dau hoan thanh.');
        }

        CourseMaterialProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'course_material_id' => $material->id,
            ],
            [
                'user_id' => Auth::id(),
                'progress_percent' => 100,
                'started_at' => now(),
                'last_viewed_at' => now(),
                'completed_at' => now(),
            ]
        );

        $this->issueCertificateIfEligible($course, $enrollment);

        return back()->with('success', 'Da danh dau bai hoc hoan thanh.');
    }

    public function submitMaterialQuiz(Request $request, Course $course, CourseMaterial $material)
    {
        $enrollment = $this->findEnrollment($course);

        if (! $enrollment || $material->course_id !== $course->id || $material->type !== 'quiz') {
            abort(404);
        }

        $questions = collect($material->quiz_questions);

        if ($questions->isEmpty()) {
            return back()->with('error', 'Quiz nay chua co cau hoi.');
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $answers = $validated['answers'];
        $summary = [];
        $correctCount = 0;

        foreach ($questions as $index => $question) {
            $prompt = trim((string) ($question['question'] ?? ''));
            $expected = $this->normalizeQuizAnswer($question['answer'] ?? '');
            $userAnswer = $this->normalizeQuizAnswer($answers[$index] ?? '');
            $isCorrect = $expected !== '' && $userAnswer === $expected;

            if ($isCorrect) {
                $correctCount++;
            }

            $summary[] = [
                'question' => $prompt,
                'expected_answer' => $question['answer'] ?? '',
                'user_answer' => $answers[$index] ?? '',
                'is_correct' => $isCorrect,
            ];
        }

        $totalQuestions = $questions->count();
        $scorePercent = round(($correctCount / max($totalQuestions, 1)) * 100, 2);
        $passingScore = (int) data_get($material->metadata, 'passing_score', 70);
        $isPassed = $scorePercent >= $passingScore;
        $attemptNumber = $enrollment->quizAttempts()->where('course_material_id', $material->id)->count() + 1;

        CourseMaterialQuizAttempt::create([
            'enrollment_id' => $enrollment->id,
            'course_material_id' => $material->id,
            'user_id' => Auth::id(),
            'attempt_number' => $attemptNumber,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'score_percent' => $scorePercent,
            'is_passed' => $isPassed,
            'answers_summary' => $summary,
            'completed_at' => now(),
        ]);

        $progress = CourseMaterialProgress::firstOrNew([
            'enrollment_id' => $enrollment->id,
            'course_material_id' => $material->id,
        ]);

        $progress->user_id = Auth::id();
        $progress->started_at = $progress->started_at ?? now();
        $progress->last_viewed_at = now();
        $progress->quiz_attempts_count = (int) $progress->quiz_attempts_count + 1;
        $progress->best_quiz_score = max((float) ($progress->best_quiz_score ?? 0), (float) $scorePercent);
        $progress->progress_percent = $isPassed ? 100 : max((int) round($scorePercent), (int) ($progress->progress_percent ?? 0));
        $progress->meta = array_merge($progress->meta ?? [], ['last_answers_summary' => $summary]);

        if ($isPassed) {
            $progress->completed_at = now();
            $progress->passed_at = now();
        }

        $progress->save();

        $this->issueCertificateIfEligible($course, $enrollment);

        return back()->with(
            $isPassed ? 'success' : 'error',
            $isPassed
                ? "Ban da vuot qua quiz voi diem {$scorePercent}% ({$correctCount}/{$totalQuestions})."
                : "Ban duoc {$scorePercent}% ({$correctCount}/{$totalQuestions}). Hay thu lai de dat muc {$passingScore}% nha."
        );
    }

    public function certificate(Course $course)
    {
        $enrollment = $this->findEnrollment($course);

        if (! $enrollment) {
            return redirect()->route('courses.show', $course)->with('error', 'Ban chua dang ky khoa hoc nay.');
        }

        $certificate = $this->issueCertificateIfEligible($course, $enrollment);

        if (! $certificate) {
            return redirect()->route('courses.learn', $course)->with('error', 'Ban can hoan thanh toan bo noi dung truoc khi nhan chung chi.');
        }

        return view('courses.certificate', compact('course', 'enrollment', 'certificate'));
    }

    protected function findEnrollment(Course $course): ?CourseEnrollment
    {
        return CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->whereIn('status', ['approved', 'completed'])
            ->latest('id')
            ->first();
    }

    protected function issueCertificateIfEligible(Course $course, CourseEnrollment $enrollment): ?CourseCertificate
    {
        $materials = $course->materials()->orderBy('order')->get();

        if ($materials->isEmpty()) {
            return null;
        }

        $progresses = $enrollment->materialProgress()
            ->whereIn('course_material_id', $materials->pluck('id'))
            ->get()
            ->keyBy('course_material_id');

        foreach ($materials as $material) {
            $progress = $progresses->get($material->id);
            if (! $progress || is_null($progress->completed_at)) {
                return null;
            }
        }

        if (! $enrollment->isCompleted()) {
            $enrollment->complete();
        }

        return CourseCertificate::firstOrCreate(
            [
                'course_id' => $course->id,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'user_id' => $enrollment->user_id,
                'certificate_no' => 'KTE-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'issued_at' => now(),
                'meta' => [
                    'completed_materials' => $materials->count(),
                    'issued_by' => 'system',
                ],
            ]
        );
    }

    protected function normalizeQuizAnswer($value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->trim()
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }
}
