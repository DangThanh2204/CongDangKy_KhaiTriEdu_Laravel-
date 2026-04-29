<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseMaterial;
use App\Support\StudyDuration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['category', 'modules'])
            ->where(function ($query) {
                $query->where('instructor_id', Auth::id())
                    ->orWhereHas('classes', function ($classQuery) {
                        $classQuery->where('instructor_id', Auth::id());
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('instructor.courses.index', compact('courses'));
    }

    public function create()
    {
        $categories = CourseCategory::where('is_active', true)->orderBy('name')->get();

        return view('instructor.courses.create', compact('categories'));
    }

    public function edit(Course $course)
    {
        $this->authorizeCourse($course);

        $categories = CourseCategory::where('is_active', true)->orderBy('name')->get();

        return view('instructor.courses.edit', compact('course', 'categories'));
    }

    public function show(Course $course)
    {
        $this->authorizeCourse($course);

        $course->load(['category', 'instructor', 'materials.module', 'modules']);

        return view('instructor.courses.show', compact('course'));
    }

    public function materialsIndex(Course $course)
    {
        $this->authorizeCourse($course);

        $materials = $course->materials()
            ->with('module')
            ->get()
            ->sort(function ($left, $right): int {
                $leftPriority = $left->course_module_id === null ? 1 : 0;
                $rightPriority = $right->course_module_id === null ? 1 : 0;

                if ($leftPriority !== $rightPriority) {
                    return $leftPriority <=> $rightPriority;
                }

                $leftModule = $left->course_module_id ?? 0;
                $rightModule = $right->course_module_id ?? 0;

                if ($leftModule !== $rightModule) {
                    return $leftModule <=> $rightModule;
                }

                return ((int) ($left->order ?? 0)) <=> ((int) ($right->order ?? 0));
            })
            ->values();
        $modules = $course->modules()->ordered()->get();

        return view('instructor.courses.materials.index', compact('course', 'materials', 'modules'));
    }

    public function materialsStore(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'type' => 'required|in:video,pdf,assignment,meeting',
            'course_module_id' => 'nullable|integer|exists:course_modules,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'pdf_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'assignment_content' => 'nullable|string',
            'meeting_url' => 'nullable|url|required_if:type,meeting',
            'meeting_starts_at' => 'nullable|date|required_if:type,meeting',
            'meeting_ends_at' => 'nullable|date|after:meeting_starts_at',
            'meeting_note' => 'nullable|string|max:1000',
            'estimated_duration_minutes' => 'nullable|integer|min:1|max:10000',
        ]);

        $moduleId = $validated['course_module_id'] ?? null;
        if ($moduleId && ! $course->modules()->whereKey($moduleId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Module không thuộc khóa học này.',
            ], 422);
        }

        $metadata = [];
        $filePath = null;
        $durationContent = $validated['content'] ?? null;

        if ($validated['type'] === 'video' && ! empty($validated['video_url'])) {
            $metadata['url'] = $validated['video_url'];
        } elseif ($validated['type'] === 'pdf' && $request->hasFile('pdf_file')) {
            $uploadedFile = $request->file('pdf_file');
            $filePath = $uploadedFile->store('course-materials', 'public');
            $metadata['document_original_name'] = $uploadedFile->getClientOriginalName();
        } elseif ($validated['type'] === 'assignment') {
            $metadata['content'] = $validated['assignment_content'] ?? null;
            $durationContent = $validated['assignment_content'] ?? $durationContent;
        } elseif ($validated['type'] === 'meeting') {
            $metadata['meeting_url'] = $validated['meeting_url'] ?? null;
            $metadata['meeting_starts_at'] = $validated['meeting_starts_at'] ?? null;
            $metadata['meeting_ends_at'] = $validated['meeting_ends_at'] ?? null;
            $metadata['meeting_note'] = $validated['meeting_note'] ?? null;
            $durationContent = $validated['meeting_note'] ?? $durationContent;
        }

        $durationEstimate = StudyDuration::estimateForMaterialInput(
            $validated['type'],
            $request->file('pdf_file'),
            $metadata,
            $durationContent,
            isset($validated['estimated_duration_minutes']) ? (int) $validated['estimated_duration_minutes'] : null
        );

        $maxOrder = $course->materials()->max('order') ?? 0;

        $course->materials()->create([
            'course_module_id' => $moduleId,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'file_path' => $filePath,
            'metadata' => $durationEstimate['metadata'],
            'estimated_duration_minutes' => $durationEstimate['minutes'],
            'order' => $maxOrder + 1,
        ]);

        $course->syncStudyMetrics();

        return response()->json(['success' => true]);
    }

    public function materialsUpdateOrder(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'material_ids' => 'required|array',
            'material_ids.*' => 'integer|exists:course_materials,id',
        ]);

        foreach ($validated['material_ids'] as $index => $materialId) {
            $course->materials()->where('id', $materialId)->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:courses,slug,' . $course->id,
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_url' => 'nullable|url',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
            'category_id' => 'required|exists:course_categories,id',
            'learning_type' => 'nullable|in:online,offline',
            'announcement' => 'nullable|string|max:2000',
            'quiz_questions' => 'nullable|array',
            'quiz_questions.*.question' => 'nullable|string',
            'quiz_questions.*.answer' => 'nullable|string',
            'series_key' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'classes' => 'nullable|array',
            'classes.*.id' => 'nullable|integer',
            'classes.*.name' => 'nullable|string|max:255',
            'classes.*.instructor_id' => 'nullable|exists:users,id',
            'classes.*.status' => 'nullable|in:active,inactive,draft,closed',
            'classes.*.start_date' => 'nullable|date',
            'classes.*.end_date' => 'nullable|date',
            'classes.*.schedule' => 'nullable|string',
            'classes.*.meeting_info' => 'nullable|string',
            'classes.*.max_students' => 'nullable|integer|min:0',
            'classes.*.price_override' => 'nullable|numeric|min:0',
            'classes.*._destroy' => 'nullable|boolean',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['title']);
        if (empty($slug)) {
            $slug = Str::random(10);
        }

        $course->title = $validated['title'];
        $course->slug = $slug;
        $course->description = $validated['description'] ?? $course->description ?? '';
        $course->short_description = $validated['short_description'] ?? $course->short_description ?? '';
        $course->price = $validated['price'];
        $course->sale_price = $validated['sale_price'] ?? null;
        $course->video_url = $validated['video_url'] ?? null;
        $course->category_id = $validated['category_id'];
        $course->series_key = $validated['series_key'] ?? null;
        $course->status = $validated['status'];
        $course->learning_type = $validated['learning_type'] ?? $course->learning_type ?? 'online';
        $course->announcement = $validated['announcement'] ?? $course->announcement;
        $course->instructor_id = Auth::id();

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $course->thumbnail = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        if ($request->hasFile('pdf')) {
            if ($course->pdf_path) {
                Storage::disk('public')->delete($course->pdf_path);
            }
            $course->pdf_path = $request->file('pdf')->store('course-pdfs', 'public');
        }

        $course->save();

        $this->syncDefaultQuiz($course, $request->input('quiz_questions', []));
        $this->syncClasses($course, $request->input('classes', []));
        $course->syncStudyMetrics();

        return redirect()->route('instructor.courses.index')->with('success', 'Khóa học đã được cập nhật.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:courses,slug',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_url' => 'nullable|url',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
            'category_id' => 'required|exists:course_categories,id',
            'learning_type' => 'required|in:online,offline',
            'announcement' => 'nullable|string|max:2000',
            'quiz_questions' => 'nullable|array',
            'quiz_questions.*.question' => 'nullable|string',
            'quiz_questions.*.answer' => 'nullable|string',
            'series_key' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
            'classes' => 'nullable|array',
            'classes.*.id' => 'nullable|integer',
            'classes.*.name' => 'nullable|string|max:255',
            'classes.*.instructor_id' => 'nullable|exists:users,id',
            'classes.*.status' => 'nullable|in:active,inactive,draft,closed',
            'classes.*.start_date' => 'nullable|date',
            'classes.*.end_date' => 'nullable|date',
            'classes.*.schedule' => 'nullable|string',
            'classes.*.meeting_info' => 'nullable|string',
            'classes.*.max_students' => 'nullable|integer|min:0',
            'classes.*.price_override' => 'nullable|numeric|min:0',
            'classes.*._destroy' => 'nullable|boolean',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['title']);
        if (empty($slug)) {
            $slug = Str::random(10);
        }

        $course = new Course();
        $course->title = $validated['title'];
        $course->slug = $slug;
        $course->description = $validated['description'] ?? '';
        $course->short_description = $validated['short_description'] ?? '';
        $course->price = $validated['price'];
        $course->sale_price = $validated['sale_price'] ?? null;
        $course->video_url = $validated['video_url'] ?? null;
        $course->category_id = $validated['category_id'];
        $course->series_key = $validated['series_key'] ?? null;
        $course->status = $validated['status'];
        $course->instructor_id = Auth::id();
        $course->learning_type = $validated['learning_type'];
        $course->announcement = $validated['announcement'] ?? null;
        $course->level = 'beginner';
        $course->duration = 0;
        $course->lessons_count = 0;
        $course->students_count = 0;
        $course->rating = 0;
        $course->total_rating = 0;

        if ($request->hasFile('thumbnail')) {
            $course->thumbnail = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        if ($request->hasFile('pdf')) {
            $course->pdf_path = $request->file('pdf')->store('course-pdfs', 'public');
        }

        $course->save();

        $this->syncDefaultQuiz($course, $request->input('quiz_questions', []));
        $this->syncClasses($course, $request->input('classes', []));
        $course->syncStudyMetrics();

        return redirect()->route('instructor.courses.index')->with('success', 'Khóa học đã được tạo.');
    }

    protected function syncClasses(Course $course, array $classes): void
    {
        foreach ($classes as $classData) {
            $classId = $classData['id'] ?? null;
            $name = trim((string) ($classData['name'] ?? ''));
            $payload = [
                'name' => $name,
                'instructor_id' => $classData['instructor_id'] ?? Auth::id(),
                'start_date' => $classData['start_date'] ?? null,
                'end_date' => $classData['end_date'] ?? null,
                'schedule' => $classData['schedule'] ?? null,
                'meeting_info' => $classData['meeting_info'] ?? null,
                'max_students' => $classData['max_students'] ?? 0,
                'price_override' => $classData['price_override'] ?? null,
                'status' => ($classData['status'] ?? 'active') === 'active' ? 'active' : 'inactive',
            ];

            if ($classId) {
                $class = $course->classes()->whereKey($classId)->first();
                if (! $class) {
                    continue;
                }

                if (! empty($classData['_destroy'])) {
                    if ($class->enrollments()->exists()) {
                        $class->update(['status' => 'inactive']);
                    } else {
                        $class->delete();
                    }
                    continue;
                }

                if ($name === '' || ! $this->hasEnoughClassData($payload)) {
                    continue;
                }

                $class->update($payload);
                continue;
            }

            if ($name === '' || ! $this->hasEnoughClassData($payload) || ! empty($classData['_destroy'])) {
                continue;
            }

            $course->classes()->create($payload);
        }
    }

    protected function hasEnoughClassData(array $payload): bool
    {
        return filled($payload['name'])
            && filled($payload['instructor_id'])
            && filled($payload['start_date'])
            && filled($payload['end_date']);
    }

    public function quizIndex(Course $course)
    {
        $this->authorizeCourse($course);

        $quizzes = $course->materials()->where('type', 'quiz')->orderBy('order')->get();

        return view('instructor.courses.quiz.index', compact('course', 'quizzes'));
    }

    public function quizCreate(Course $course)
    {
        $this->authorizeCourse($course);

        return view('instructor.courses.quiz.create', compact('course'));
    }

    public function quizStore(Course $course, Request $request)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.answer' => 'required|string',
        ]);

        $durationEstimate = StudyDuration::estimateForMaterialInput('quiz', null, [
            'questions' => $validated['questions'],
        ]);

        $course->materials()->create([
            'type' => 'quiz',
            'title' => $validated['title'],
            'metadata' => $durationEstimate['metadata'],
            'estimated_duration_minutes' => $durationEstimate['minutes'],
        ]);

        $course->syncStudyMetrics();

        return redirect()->route('instructor.courses.quiz.index', $course)->with('success', 'Bài kiểm tra đã được tạo.');
    }

    public function quizEdit(Course $course, CourseMaterial $material)
    {
        $this->authorizeCourse($course);

        if ($material->course_id !== $course->id || $material->type !== 'quiz') {
            abort(404);
        }

        return view('instructor.courses.quiz.edit', compact('course', 'material'));
    }

    public function quizUpdate(Course $course, CourseMaterial $material, Request $request)
    {
        $this->authorizeCourse($course);
        if ($material->course_id !== $course->id || $material->type !== 'quiz') {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.answer' => 'required|string',
        ]);

        $durationEstimate = StudyDuration::estimateForMaterialInput('quiz', null, [
            'questions' => $validated['questions'],
        ]);

        $material->update([
            'title' => $validated['title'],
            'metadata' => $durationEstimate['metadata'],
            'estimated_duration_minutes' => $durationEstimate['minutes'],
        ]);

        $course->syncStudyMetrics();

        return redirect()->route('instructor.courses.quiz.index', $course)->with('success', 'Bài kiểm tra đã được cập nhật.');
    }

    public function quizDestroy(Course $course, CourseMaterial $material)
    {
        $this->authorizeCourse($course);
        if ($material->course_id !== $course->id || $material->type !== 'quiz') {
            abort(404);
        }

        $material->delete();
        $course->syncStudyMetrics();

        return redirect()->route('instructor.courses.quiz.index', $course)->with('success', 'Bài kiểm tra đã được xóa.');
    }

    protected function authorizeCourse(Course $course): void
    {
        if (Auth::user()?->isAdmin()) {
            return;
        }

        $ownsCourse = (int) $course->instructor_id === (int) Auth::id();
        $ownsClass = $course->classes()->where('instructor_id', Auth::id())->exists();

        if (! $ownsCourse && ! $ownsClass) {
            abort(403);
        }
    }

    protected function syncDefaultQuiz(Course $course, array $quizQuestions): void
    {
        $quizQuestions = collect($quizQuestions)
            ->filter(function ($question) {
                return isset($question['question']) && trim((string) $question['question']) !== '';
            })
            ->values();

        if ($quizQuestions->isEmpty()) {
            $course->materials()->where('type', 'quiz')->delete();
            $course->syncStudyMetrics();
            return;
        }

        $durationEstimate = StudyDuration::estimateForMaterialInput('quiz', null, [
            'questions' => $quizQuestions->all(),
        ]);

        $quizMaterial = $course->materials()->firstOrCreate(
            ['type' => 'quiz'],
            ['title' => 'Bài kiểm tra tự động']
        );

        $quizMaterial->title = $quizMaterial->title ?: 'Bài kiểm tra tự động';
        $quizMaterial->metadata = $durationEstimate['metadata'];
        $quizMaterial->estimated_duration_minutes = $durationEstimate['minutes'];
        $quizMaterial->save();

        $course->syncStudyMetrics();
    }
}
