<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseCertificate;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseMaterial;
use App\Models\CourseMaterialProgress;
use App\Models\CourseMaterialQuizAttempt;
use App\Models\Payment;
use App\Support\CollectionPaginator;
use App\Services\CertificateBlockchainService;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function __construct(
        protected CertificateBlockchainService $certificateBlockchain,
    ) {
    }
    public function index(Request $request)
    {
        $query = Course::with(['category', 'modules'])->published();

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
        $courses->setCollection($this->attachModulesCount($courses->getCollection()));

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

    public function intakes(Request $request)
    {
        $categories = CourseCategory::query()->orderBy('name')->get();
        $baseIntakes = $this->openIntakesBaseCollection();

        $stats = [
            'open_intakes' => $baseIntakes->count(),
            'opening_this_month' => $baseIntakes->filter(function (CourseClass $intake) {
                return $this->dateFallsBetween(
                    $intake->start_date,
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                );
            })->count(),
            'online_count' => $baseIntakes->filter(function (CourseClass $intake) {
                return ($intake->course?->delivery_mode ?? 'online') === 'online';
            })->count(),
            'offline_count' => $baseIntakes->filter(function (CourseClass $intake) {
                return ($intake->course?->delivery_mode ?? 'online') === 'offline';
            })->count(),
        ];

        $intakes = $this->applyIntakeFilters($baseIntakes, $request);
        $intakes = $this->sortClassesForDisplay($intakes);

        $intakes = CollectionPaginator::paginate(
            $intakes,
            12,
            max((int) $request->integer('page', 1), 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        return view('courses.intakes', compact('intakes', 'categories', 'stats'));
    }

    private function openIntakesBaseCollection(): Collection
    {
        $today = now()->startOfDay();

        return CourseClass::query()
            ->with(['course.category', 'instructor', 'schedules'])
            ->where('status', 'active')
            ->get()
            ->filter(function (CourseClass $intake) use ($today) {
                $course = $intake->course;

                if (! $course || $course->status !== 'published') {
                    return false;
                }

                if ($intake->end_date && $intake->end_date->copy()->endOfDay()->lt($today)) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    public function show(Course $course, PromotionService $promotionService)
    {
        if ($course->status !== 'published') {
            abort(404);
        }

        $isEnrolled = false;
        $isPending = false;
        $currentEnrollment = null;
        $currentPayment = null;
        $registrationDocumentUrl = null;
        $paymentReceiptUrl = null;

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
                $currentPayment = $this->sortPaymentsForDisplay(
                    Payment::query()
                    ->with(['user', 'courseClass.course.category', 'discountCode'])
                    ->where('user_id', Auth::id())
                    ->where('class_id', $currentEnrollment->class_id)
                    ->get()
                )->first();
                $registrationDocumentUrl = route('documents.registration-form', $currentEnrollment);

                if ($currentPayment && $currentPayment->isCompleted() && in_array($currentPayment->method, ['wallet', 'vnpay'], true)) {
                    $paymentReceiptUrl = route('documents.payment-receipt', $currentPayment);
                }
            }
        }

        $classes = $this->sortClassesForDisplay(
            $course->classes()
                ->with('schedules')
                ->get()
        );

        $similarCourses = Course::published()
            ->where('id', '!=', $course->id)
            ->where('category_id', $course->category_id)
            ->with(['category', 'modules'])
            ->limit(4)
            ->get();
        $similarCourses = $this->attachModulesCount($similarCourses);

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

        $pricingPreviewClass = $currentEnrollment?->courseClass
            ?: $classes->firstWhere('status', 'active')
            ?: $classes->first();

        $selectedDiscountCode = trim((string) session()->getOldInput('discount_code', ''));
        $selectedDiscountCode = $selectedDiscountCode !== '' ? $selectedDiscountCode : null;

        $promotionPreview = [
            'base_price' => (float) $course->final_price,
            'automatic_options' => [],
            'best_automatic' => null,
            'voucher' => null,
            'voucher_option' => null,
            'voucher_error' => null,
            'applied_items' => [],
            'discount_amount' => 0.0,
            'payable_amount' => (float) $course->final_price,
            'savings_percentage' => 0,
        ];
        $classPromotionPreviews = [];
        $publicVoucherCodes = collect();

        try {
            $promotionPreview = $promotionService->preview(Auth::user(), $course, $pricingPreviewClass, $selectedDiscountCode);
            $classPromotionPreviews = $classes->mapWithKeys(function (CourseClass $classItem) use ($promotionService, $course, $selectedDiscountCode) {
                return [
                    $classItem->id => $promotionService->preview(Auth::user(), $course, $classItem, $selectedDiscountCode),
                ];
            })->all();
            $publicVoucherCodes = $promotionService->publicVoucherHints(Auth::user(), $course, $pricingPreviewClass);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return view('courses.show', compact(
            'course',
            'classes',
            'isEnrolled',
            'isPending',
            'currentEnrollment',
            'similarCourses',
            'reviews',
            'userReview',
            'standaloneMaterials',
            'promotionPreview',
            'classPromotionPreviews',
            'publicVoucherCodes',
            'currentPayment',
            'registrationDocumentUrl',
            'paymentReceiptUrl'
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

        $verification = $this->certificateBlockchain->verificationSnapshot($certificate);

        return view('courses.certificate', compact('course', 'enrollment', 'certificate', 'verification'));
    }

    public function verifyCertificate(Request $request)
    {
        $code = Str::upper(trim((string) $request->query('code', '')));
        $certificate = null;
        $verification = null;

        if ($code !== '') {
            $certificate = CourseCertificate::with([
                'course:id,title,learning_type',
                'user:id,fullname,username,email',
                'enrollment:id,class_id,completed_at',
                'enrollment.courseClass:id,name,start_date',
            ])->get()->first(function (CourseCertificate $item) use ($code) {
                return Str::upper((string) $item->certificate_no) === $code;
            });

            if ($certificate) {
                $certificate = $this->certificateBlockchain->ensureAnchored($certificate);
                $verification = $this->certificateBlockchain->verificationSnapshot($certificate);
            }
        }

        return view('courses.verify-certificate', compact('code', 'certificate', 'verification'));
    }

    protected function findEnrollment(Course $course): ?CourseEnrollment
    {
        return CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->whereIn('status', ['approved', 'completed'])
            ->latest('id')
            ->first();
    }

    private function applyIntakeFilters(Collection $intakes, Request $request): Collection
    {
        $filtered = $intakes;

        if ($request->filled('q')) {
            $keyword = Str::lower(trim((string) $request->input('q')));

            $filtered = $filtered->filter(function (CourseClass $intake) use ($keyword) {
                $haystacks = [
                    $intake->name,
                    $intake->schedule,
                    $intake->meeting_info,
                    $intake->course?->title,
                    $intake->course?->category?->name,
                ];

                foreach ($haystacks as $value) {
                    if ($value !== null && Str::contains(Str::lower((string) $value), $keyword)) {
                        return true;
                    }
                }

                return false;
            })->values();
        }

        if ($request->filled('category')) {
            $categoryId = (int) $request->input('category');

            $filtered = $filtered->filter(function (CourseClass $intake) use ($categoryId) {
                return (int) ($intake->course?->category_id ?? 0) === $categoryId;
            })->values();
        }

        if ($request->filled('delivery_mode')) {
            $deliveryMode = (string) $request->input('delivery_mode');

            $filtered = $filtered->filter(function (CourseClass $intake) use ($deliveryMode) {
                return ($intake->course?->delivery_mode ?? 'online') === $deliveryMode;
            })->values();
        }

        if ($request->filled('month')) {
            try {
                $monthStart = Carbon::createFromFormat('Y-m', (string) $request->input('month'))->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();

                $filtered = $filtered->filter(function (CourseClass $intake) use ($monthStart, $monthEnd) {
                    $start = $intake->start_date?->copy()->startOfDay();
                    $end = $intake->end_date?->copy()->endOfDay();

                    return ($start && $start->between($monthStart, $monthEnd))
                        || ($end && $end->between($monthStart, $monthEnd))
                        || ($start && $end && $start->lt($monthStart) && $end->gt($monthEnd));
                })->values();
            } catch (\Throwable $exception) {
            }
        }

        $dateFrom = $request->filled('date_from') ? Carbon::parse((string) $request->input('date_from'))->startOfDay() : null;
        $dateTo = $request->filled('date_to') ? Carbon::parse((string) $request->input('date_to'))->endOfDay() : null;

        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        if ($dateFrom) {
            $filtered = $filtered->filter(function (CourseClass $intake) use ($dateFrom) {
                return $this->dateFallsBetween($intake->start_date, $dateFrom, null);
            })->values();
        }

        if ($dateTo) {
            $filtered = $filtered->filter(function (CourseClass $intake) use ($dateTo) {
                return $this->dateFallsBetween($intake->start_date, null, $dateTo);
            })->values();
        }

        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;

        if ($minPrice !== null) {
            $filtered = $filtered->filter(function (CourseClass $intake) use ($minPrice) {
                return (float) $intake->listing_price >= $minPrice;
            })->values();
        }

        if ($maxPrice !== null) {
            $filtered = $filtered->filter(function (CourseClass $intake) use ($maxPrice) {
                return (float) $intake->listing_price <= $maxPrice;
            })->values();
        }

        if ($request->boolean('has_slots')) {
            $filtered = $filtered->filter(function (CourseClass $intake) {
                return ! $intake->is_full;
            })->values();
        }

        return $filtered->values();
    }

    private function sortClassesForDisplay(Collection $classes): Collection
    {
        return $classes->sort(function (CourseClass $left, CourseClass $right): int {
            $statusPriority = $this->statusPriority($left->status, ['active']);
            $compare = $statusPriority <=> $this->statusPriority($right->status, ['active']);

            if ($compare !== 0) {
                return $compare;
            }

            $compare = $this->nullableDateComparison($left->start_date, $right->start_date);

            if ($compare !== 0) {
                return $compare;
            }

            return ((int) $left->id) <=> ((int) $right->id);
        })->values();
    }

    private function attachModulesCount(Collection $courses): Collection
    {
        return $courses->map(function (Course $course) {
            $modules = $course->relationLoaded('modules') ? $course->modules : $course->modules()->get();
            $course->setAttribute('modules_count', $modules->count());

            return $course;
        });
    }

    private function sortPaymentsForDisplay(Collection $payments): Collection
    {
        return $payments->sort(function (Payment $left, Payment $right): int {
            $compare = $this->statusPriority($left->status, ['completed']) <=> $this->statusPriority($right->status, ['completed']);

            if ($compare !== 0) {
                return $compare;
            }

            $compare = $this->nullableDateComparison($right->paid_at, $left->paid_at);

            if ($compare !== 0) {
                return $compare;
            }

            $compare = $this->nullableDateComparison($right->created_at, $left->created_at);

            if ($compare !== 0) {
                return $compare;
            }

            return ((int) $right->id) <=> ((int) $left->id);
        })->values();
    }

    private function statusPriority(?string $status, array $priorityOrder): int
    {
        $index = array_search($status, $priorityOrder, true);

        return $index === false ? count($priorityOrder) : $index;
    }

    private function nullableDateComparison($left, $right): int
    {
        $leftValue = $left ? $left->format('Y-m-d H:i:s.u') : null;
        $rightValue = $right ? $right->format('Y-m-d H:i:s.u') : null;

        if ($leftValue === null && $rightValue === null) {
            return 0;
        }

        if ($leftValue === null) {
            return 1;
        }

        if ($rightValue === null) {
            return -1;
        }

        return $leftValue <=> $rightValue;
    }

    private function dateFallsBetween($value, ?Carbon $from, ?Carbon $to): bool
    {
        if (! $value instanceof Carbon) {
            return false;
        }

        if ($from && $value->copy()->startOfDay()->lt($from->copy()->startOfDay())) {
            return false;
        }

        if ($to && $value->copy()->endOfDay()->gt($to->copy()->endOfDay())) {
            return false;
        }

        return true;
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

        $certificate = CourseCertificate::firstOrCreate(
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

        return $this->certificateBlockchain->ensureAnchored($certificate);
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
