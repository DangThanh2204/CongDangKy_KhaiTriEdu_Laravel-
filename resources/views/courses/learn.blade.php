@extends('layouts.app')

@section('title', 'Học: ' . $course->title)

@section('content')
@php
    $formatSpentDuration = function (int $minutes): string {
        return $minutes > 0 ? \App\Support\StudyDuration::formatMinutes($minutes) : '0 phút';
    };

    $calculateLearnedMinutes = function ($material): int {
        $estimatedMinutes = (int) $material->estimated_duration_minutes;
        $progress = $material->learning_progress;

        if (! $progress || $estimatedMinutes <= 0) {
            return 0;
        }

        $progressPercent = $progress->completed_at
            ? 100
            : max(0, min(100, (int) ($progress->progress_percent ?? 0)));

        return (int) round($estimatedMinutes * ($progressPercent / 100));
    };

    $totalEstimatedMinutes = $materials->sum(fn ($material) => (int) $material->estimated_duration_minutes);
    $completedEstimatedMinutes = $materials->sum(fn ($material) => $calculateLearnedMinutes($material));
    $meetingMaterials = $materials
        ->where('type', 'meeting')
        ->sortBy(fn ($material) => optional($material->meeting_starts_at)->timestamp ?? PHP_INT_MAX)
        ->values();
@endphp
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-book-open me-2"></i>{{ $course->title }}</h2>
            <p class="text-muted mb-0">Giảng viên: {{ optional($course->instructor)->fullname ?? optional($course->instructor)->username ?? 'Giảng viên' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($certificate)
                <a href="{{ route('courses.certificate', $course) }}" class="btn btn-outline-success"><i class="fas fa-certificate me-2"></i>Xem chứng chỉ</a>
            @endif
            <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại khóa học</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                        <div>
                            <h4 class="mb-1">Tiến độ học tập</h4>
                            <p class="text-muted mb-0">{{ $completedMaterials }}/{{ $totalMaterials }} bài đã hoàn thành • Đã học khoảng {{ $formatSpentDuration($completedEstimatedMinutes) }} / {{ $formatSpentDuration($totalEstimatedMinutes) }}</p>
                        </div>
                        <span class="badge bg-primary fs-6">{{ $progressPercent }}%</span>
                    </div>
                    <div class="progress" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $progressPercent }}%"></div>
                    </div>
                    @if($allCompleted)
                        <div class="alert alert-success mt-3 mb-0"><i class="fas fa-trophy me-2"></i>Bạn đã hoàn thành toàn bộ khóa học. Chứng chỉ đã sẵn sàng.</div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h4 class="mb-3">Nội dung học tập theo module</h4>
                    @forelse($materialSections as $section)
                        @php
                            $sectionEstimatedMinutes = $section['materials']->sum(fn ($material) => (int) $material->estimated_duration_minutes);
                            $sectionCompletedMinutes = $section['materials']->sum(fn ($material) => $calculateLearnedMinutes($material));
                        @endphp
                        <div class="mb-4">
                            <div class="border rounded-4 p-3 mb-3 bg-light-subtle">
                                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                    <div>
                                        <h5 class="mb-1">{{ $section['title'] }}</h5>
                                        <p class="text-muted mb-0">{{ $section['description'] }}</p>
                                    </div>
                                    <div class="text-end small text-muted">
                                        <div>{{ $section['materials']->count() }} bài • {{ $formatSpentDuration($sectionEstimatedMinutes) }}</div>
                                        <div>Đã học khoảng {{ $formatSpentDuration($sectionCompletedMinutes) }}</div>
                                    </div>
                                </div>
                            </div>

                            @foreach($section['materials'] as $material)
                                @php
                                    $progress = $material->learning_progress;
                                    $latestAttempt = $material->latest_quiz_attempt;
                                    $attemptHistory = $material->quiz_attempt_history;
                                    $isCompleted = $progress && $progress->completed_at;
                                    $materialLearnedMinutes = $calculateLearnedMinutes($material);
                                @endphp
                                <div class="border rounded-4 p-4 mb-3 {{ $isCompleted ? 'border-success bg-success-subtle' : 'border-light-subtle' }}" id="material-{{ $material->id }}">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3 flex-wrap">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                                <span class="badge bg-dark">Bài {{ $material->sequence_number }}</span>
                                                <span class="badge bg-{{ $material->type === 'quiz' ? 'warning text-dark' : ($material->isMeeting() ? 'info text-dark' : ($isCompleted ? 'success' : 'secondary')) }}">{{ $material->type_label }}</span>
                                                <span class="badge bg-light text-dark border"><i class="fas fa-clock me-1"></i>{{ $material->estimated_duration_label }}</span>
                                                @if($isCompleted)
                                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Hoàn thành</span>
                                                @endif
                                            </div>
                                            <h5 class="mb-1">{{ $material->title ?: 'Chưa có tiêu đề' }}</h5>
                                            @if($material->content)
                                                <p class="text-muted mb-0">{{ $material->content }}</p>
                                            @endif
                                            @if($material->isMeeting())
                                                <div class="alert alert-light border mt-3 mb-0">
                                                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                                        <div>
                                                            <div class="fw-semibold"><i class="fas fa-video me-2 text-primary"></i>Buổi học trực tiếp qua Google Meet</div>
                                                            <div class="small text-muted mt-1">{{ $material->meeting_window_label ?? 'Giảng viên sẽ cập nhật giờ mở phòng học sau.' }}</div>
                                                            @if($material->meeting_note)
                                                                <div class="small text-muted mt-1">{{ $material->meeting_note }}</div>
                                                            @endif
                                                        </div>
                                                        <span class="badge {{ $material->meeting_status_badge_class }}">{{ $material->meeting_status_label }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-end small text-muted">
                                            <div>Đã học khoảng: {{ $formatSpentDuration($materialLearnedMinutes) }}</div>
                                            @if($progress && $progress->last_viewed_at)
                                                <div>Lần học gần nhất: {{ $progress->last_viewed_at->format('d/m/Y H:i') }}</div>
                                            @endif
                                            @if($progress && !is_null($progress->best_quiz_score))
                                                <div>Điểm cao nhất: {{ number_format((float) $progress->best_quiz_score, 0) }}%</div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($material->type === 'video' && data_get($material->metadata, 'url'))
                                        <div class="ratio ratio-16x9 mb-3"><iframe src="{{ youtube_embed_url(data_get($material->metadata, 'url')) }}" allowfullscreen></iframe></div>
                                        @if(!$isCompleted)
                                            <form method="POST" action="{{ route('courses.materials.complete', [$course, $material]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-2"></i>Đánh dấu đã xem xong</button>
                                            </form>
                                        @endif
                                    @elseif($material->type === 'pdf' && $material->file_path)
                                        <div class="mb-3">
                                            <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" class="btn btn-outline-danger">
                                                <i class="{{ $material->document_icon_class }} me-2"></i>{{ $material->document_action_label }}
                                            </a>
                                        </div>
                                        @if(!$isCompleted)
                                            <form method="POST" action="{{ route('courses.materials.complete', [$course, $material]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-2"></i>Đánh dấu đã học xong</button>
                                            </form>
                                        @endif
                                    @elseif($material->isMeeting())
                                        <div class="mb-3">
                                            @if($material->meeting_url)
                                                @if($material->canJoinMeeting())
                                                    <a href="{{ $material->meeting_url }}" target="_blank" class="btn btn-success">
                                                        <i class="fas fa-video me-2"></i>Vào phòng học Meet
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-outline-secondary" disabled>
                                                        <i class="fas fa-clock me-2"></i>Link Meet sẽ mở đúng giờ
                                                    </button>
                                                @endif
                                            @else
                                                <div class="alert alert-warning mb-0">Giảng viên chưa cập nhật link Google Meet cho buổi học này.</div>
                                            @endif
                                        </div>
                                        @if(!$isCompleted)
                                            @if($material->canComplete())
                                                <form method="POST" action="{{ route('courses.materials.complete', [$course, $material]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-2"></i>Đánh dấu đã tham gia buổi học</button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-outline-secondary" disabled><i class="fas fa-clock me-2"></i>Chưa tới giờ để điểm danh hoàn thành</button>
                                            @endif
                                        @endif
                                    @elseif($material->type === 'assignment')
                                        <div class="alert alert-info"><i class="fas fa-tasks me-2"></i>{{ data_get($material->metadata, 'content', 'Bài tập đang được cập nhật.') }}</div>
                                        @if(!$isCompleted)
                                            <form method="POST" action="{{ route('courses.materials.complete', [$course, $material]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-2"></i>Đánh dấu đã hoàn thành</button>
                                            </form>
                                        @endif
                                    @elseif($material->type === 'quiz')
                                        @php
                                            $questions = collect($material->quiz_questions);
                                            $passingScore = data_get($material->metadata, 'passing_score', 70);
                                        @endphp
                                        <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div>
                                                <strong>Quiz {{ $questions->count() }} câu</strong><br>
                                                <small>Cần đạt từ {{ $passingScore }}% để hoàn thành.</small>
                                            </div>
                                            @if($latestAttempt)
                                                <span class="badge bg-{{ $latestAttempt->is_passed ? 'success' : 'danger' }} fs-6">Lần gần nhất: {{ number_format((float) $latestAttempt->score_percent, 0) }}%</span>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('courses.materials.quiz.submit', [$course, $material]) }}">
                                            @csrf
                                            @foreach($questions as $questionIndex => $question)
                                                <div class="mb-3 p-3 bg-light rounded-3">
                                                    <label class="form-label fw-semibold">Câu {{ $questionIndex + 1 }}. {{ $question['question'] ?? 'Câu hỏi' }}</label>
                                                    <input type="text" name="answers[{{ $questionIndex }}]" class="form-control" placeholder="Nhập câu trả lời của bạn" required>
                                                </div>
                                            @endforeach
                                            <button type="submit" class="btn btn-warning text-dark fw-semibold"><i class="fas fa-pen-to-square me-2"></i>Nộp quiz</button>
                                        </form>
                                        @if($attemptHistory && $attemptHistory->isNotEmpty())
                                            <div class="table-responsive mt-4">
                                                <table class="table table-sm align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Lần</th>
                                                            <th>Điểm</th>
                                                            <th>Đúng</th>
                                                            <th>Kết quả</th>
                                                            <th>Thời gian</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($attemptHistory as $attempt)
                                                            <tr>
                                                                <td>#{{ $attempt->attempt_number }}</td>
                                                                <td>{{ number_format((float) $attempt->score_percent, 0) }}%</td>
                                                                <td>{{ $attempt->correct_answers }}/{{ $attempt->total_questions }}</td>
                                                                <td><span class="badge bg-{{ $attempt->is_passed ? 'success' : 'danger' }}">{{ $attempt->is_passed ? 'Đạt' : 'Chưa đạt' }}</span></td>
                                                                <td>{{ optional($attempt->completed_at)->format('d/m/Y H:i') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Khóa học chưa có nội dung</h5>
                            <p class="text-muted mb-0">Giảng viên đang cập nhật tài liệu học tập.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Thông tin khóa học</h5>
                    <div class="small text-muted d-grid gap-2">
                        <div><strong>Nhóm ngành:</strong> {{ $course->category->name ?? 'Chưa phân loại' }}</div>
                        <div><strong>Đợt học:</strong> {{ $enrollment->class->name ?? 'Học trực tuyến' }}</div>
                        <div><strong>Ngày đăng ký:</strong> {{ optional($enrollment->created_at)->format('d/m/Y') }}</div>
                        <div><strong>Thời lượng ước tính:</strong> {{ $course->estimated_duration_label }}</div>
                        <div><strong>Trạng thái:</strong> <span class="badge bg-{{ $enrollment->status_color }}">{{ $enrollment->status_text }}</span></div>
                    </div>
                </div>
            </div>

            @if($meetingMaterials->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Lịch học live qua Meet</h5>
                        <div class="d-grid gap-3">
                            @foreach($meetingMaterials as $meetingMaterial)
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <div class="fw-semibold">{{ $meetingMaterial->title ?: 'Buổi học trực tuyến' }}</div>
                                            <div class="small text-muted">{{ $meetingMaterial->meeting_window_label ?? 'Giờ học sẽ cập nhật sau' }}</div>
                                        </div>
                                        <span class="badge {{ $meetingMaterial->meeting_status_badge_class }}">{{ $meetingMaterial->meeting_status_label }}</span>
                                    </div>
                                    @if($meetingMaterial->meeting_note)
                                        <div class="small text-muted mb-2">{{ $meetingMaterial->meeting_note }}</div>
                                    @endif
                                    @if($meetingMaterial->meeting_url)
                                        @if($meetingMaterial->canJoinMeeting())
                                            <a href="{{ $meetingMaterial->meeting_url }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-video me-1"></i>Vào Meet
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Chưa tới giờ mở</button>
                                        @endif
                                    @else
                                        <span class="small text-muted">Chưa có link Meet</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Tổng quan module</h5>
                    <div class="d-grid gap-3">
                        @foreach($materialSections as $section)
                            @php
                                $sectionEstimatedMinutes = $section['materials']->sum(fn ($material) => (int) $material->estimated_duration_minutes);
                                $sectionCompletedMinutes = $section['materials']->sum(fn ($material) => $calculateLearnedMinutes($material));
                            @endphp
                            <div class="border rounded p-3">
                                <div class="fw-semibold">{{ $section['title'] }}</div>
                                <div class="small text-muted">{{ $section['materials']->count() }} bài học • {{ $formatSpentDuration($sectionEstimatedMinutes) }}</div>
                                <div class="small text-muted">Đã học khoảng {{ $formatSpentDuration($sectionCompletedMinutes) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="mb-3">Lịch sử quiz gần đây</h5>
                    @if($quizAttempts->isNotEmpty())
                        <div class="d-grid gap-3">
                            @foreach($quizAttempts as $attempt)
                                <div class="border rounded-3 p-3">
                                    <div class="fw-semibold">{{ $attempt->material->title ?? 'Quiz' }}</div>
                                    <div class="small text-muted">Lần {{ $attempt->attempt_number }} • {{ optional($attempt->completed_at)->format('d/m/Y H:i') }}</div>
                                    <div class="mt-2">
                                        <span class="badge bg-{{ $attempt->is_passed ? 'success' : 'danger' }}">{{ number_format((float) $attempt->score_percent, 0) }}%</span>
                                        <span class="small text-muted ms-2">{{ $attempt->correct_answers }}/{{ $attempt->total_questions }} câu đúng</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Bạn chưa có lịch sử quiz nào.</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="mb-3">Chứng chỉ</h5>
                    @if($certificate)
                        <p class="mb-2">Bạn đã đủ điều kiện nhận chứng chỉ.</p>
                        <a href="{{ route('courses.certificate', $course) }}" class="btn btn-success w-100"><i class="fas fa-award me-2"></i>Mở chứng chỉ</a>
                    @else
                        <p class="text-muted mb-0">Hoàn thành tất cả bài học và quiz để mở khóa chứng chỉ.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
