@extends('layouts.app')

@section('title', 'Bảng điều khiển học viên')
@section('page-class', 'page-student-dashboard')

@push('styles')
    @vite('resources/css/pages/student/dashboard.css')
@endpush

@section('content')
    @php
        $currentLevel = $studentLevel['level'];
        $nextLevel = $studentLevel['next_level'];
        $currentLeaderboardEntry = $leaderboard['current_user'];
    @endphp

    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="card-title mb-0">Hành trình học tập của bạn</h4>
                <a href="{{ route('courses.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Đăng ký khóa học mới
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-4 g-3 align-items-stretch">
                    <div class="col-lg-8">
                        <div class="border rounded p-4 h-100 student-dashboard-hero">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <h5 class="fw-bold mb-0">Xin chào, <strong>{{ $user->fullname }}</strong></h5>
                                        <span class="student-level-pill level-{{ $currentLevel['key'] }}">
                                            <i class="{{ $currentLevel['icon'] }} me-1"></i>{{ $currentLevel['badge_name'] }}
                                        </span>
                                    </div>
                                    <p class="mb-0">Theo dõi khóa học, module, đợt học và tiến độ học tập của bạn tại đây. Học càng đều, bạn càng lên cấp nhanh hơn.</p>
                                </div>
                                <div class="student-hero-points text-lg-end">
                                    <div class="small text-white-50 mb-1">Điểm học tập hiện tại</div>
                                    <div class="student-hero-score">{{ $studentLevel['points_label'] }}</div>
                                </div>
                            </div>

                            <div class="student-hero-meta mt-4">
                                <span><i class="fas fa-clock me-2"></i>{{ $studentLevel['metrics']['study_duration_label'] }} đã học</span>
                                <span><i class="fas fa-layer-group me-2"></i>{{ number_format($studentLevel['metrics']['completed_materials']) }} nội dung hoàn thành</span>
                                <span><i class="fas fa-award me-2"></i>{{ number_format($studentLevel['metrics']['certificates']) }} chứng chỉ</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="row text-center g-2 h-100">
                            <div class="col-6">
                                <div class="border rounded p-3 feature-card h-100">
                                    <h5 class="fw-bold text-primary mb-1">{{ $approvedCourses->count() }}</h5>
                                    <small class="text-muted">Đang học</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 feature-card h-100">
                                    <h5 class="fw-bold text-warning mb-1">{{ $pendingCourses->count() }}</h5>
                                    <small class="text-muted">Chờ duyệt</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 feature-card h-100">
                                    <h5 class="fw-bold text-success mb-1">{{ $completedCourses->count() }}</h5>
                                    <small class="text-muted">Hoàn thành</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 feature-card h-100">
                                    <h5 class="fw-bold text-info mb-1">{{ $studentLevel['metrics']['certificates'] }}</h5>
                                    <small class="text-muted">Chứng chỉ</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="student-rank-panel mb-4">
                    <div class="student-level-card level-{{ $currentLevel['key'] }}">
                        <div class="student-level-header">
                            <div class="student-level-identity">
                                <span class="student-level-icon">
                                    <i class="{{ $currentLevel['icon'] }}"></i>
                                </span>
                                <div>
                                    <div class="student-level-label">Huy hiệu hiện tại</div>
                                    <h5 class="mb-1">{{ $currentLevel['badge_name'] }}</h5>
                                    <p class="mb-0">{{ $studentLevel['summary'] }}</p>
                                </div>
                            </div>
                            <div class="student-level-points-box">
                                <span>Điểm hiện tại</span>
                                <strong>{{ $studentLevel['points_label'] }}</strong>
                            </div>
                        </div>

                        <div class="student-level-progress-wrap">
                            <div class="student-level-progress-head">
                                <span>
                                    @if($nextLevel)
                                        Tiến độ lên {{ $nextLevel['badge_name'] }}
                                    @else
                                        Bạn đã ở mốc cao nhất
                                    @endif
                                </span>
                                <strong>{{ $studentLevel['progress_to_next'] }}%</strong>
                            </div>
                            <div class="progress student-level-progress-bar">
                                <div class="progress-bar" role="progressbar" style="width: {{ $studentLevel['progress_to_next'] }}%" aria-valuenow="{{ $studentLevel['progress_to_next'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="student-level-progress-note">
                                @if($nextLevel)
                                    Còn <strong>{{ number_format($studentLevel['points_to_next']) }} điểm</strong> để lên <strong>{{ $nextLevel['badge_name'] }}</strong>.
                                @else
                                    Hệ thống hiện chưa có mốc cao hơn. Hãy tiếp tục giữ phong độ học tập này.
                                @endif
                            </div>
                        </div>

                        <div class="student-level-badges">
                            <span><i class="fas fa-clock me-2"></i>{{ $studentLevel['breakdown']['study_minutes'] }} điểm từ thời lượng học</span>
                            <span><i class="fas fa-check-circle me-2"></i>{{ $studentLevel['breakdown']['completed_materials'] }} điểm từ nội dung hoàn thành</span>
                            <span><i class="fas fa-question-circle me-2"></i>{{ $studentLevel['breakdown']['passed_quizzes'] }} điểm từ quiz đạt</span>
                            <span><i class="fas fa-certificate me-2"></i>{{ $studentLevel['breakdown']['certificates'] }} điểm từ chứng chỉ</span>
                        </div>
                    </div>

                    <div class="student-level-metrics-grid">
                        <article class="student-level-metric-card">
                            <span>Thời gian đã học</span>
                            <strong>{{ $studentLevel['metrics']['study_duration_label'] }}</strong>
                            <small>Tính theo thời lượng ước tính của nội dung bạn đã học.</small>
                        </article>
                        <article class="student-level-metric-card">
                            <span>Nội dung hoàn thành</span>
                            <strong>{{ number_format($studentLevel['metrics']['completed_materials']) }}</strong>
                            <small>Mỗi nội dung hoàn thành sẽ cộng thêm điểm học tập.</small>
                        </article>
                        <article class="student-level-metric-card">
                            <span>Quiz đã đạt</span>
                            <strong>{{ number_format($studentLevel['metrics']['passed_quizzes']) }}</strong>
                            <small>Quiz đạt giúp bạn tăng cấp nhanh hơn.</small>
                        </article>
                        <article class="student-level-metric-card">
                            <span>Khóa hoàn thành</span>
                            <strong>{{ number_format($studentLevel['metrics']['completed_courses']) }}</strong>
                            <small>Khóa học hoàn thành được cộng thưởng lớn.</small>
                        </article>
                        <article class="student-level-metric-card">
                            <span>Chứng chỉ đã nhận</span>
                            <strong>{{ number_format($studentLevel['metrics']['certificates']) }}</strong>
                            <small>Chứng chỉ là mốc học tập nổi bật của bạn.</small>
                        </article>
                        <article class="student-level-metric-card">
                            <span>Ngày học tích cực</span>
                            <strong>{{ number_format($studentLevel['metrics']['active_study_days']) }}</strong>
                            <small>Số ngày bạn có hoạt động học tập trên hệ thống.</small>
                        </article>
                    </div>
                </div>

                <div class="student-leaderboard-card mb-4">
                    <div class="student-leaderboard-header">
                        <div>
                            <h5 class="mb-1">Bảng xếp hạng top học viên</h5>
                            <p class="text-muted mb-0">Xếp hạng dựa trên điểm học tập, khóa hoàn thành và mức độ học đều đặn.</p>
                        </div>
                        @if($currentLeaderboardEntry)
                            <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2">Hạng của bạn: #{{ $currentLeaderboardEntry['rank'] }}</span>
                        @endif
                    </div>

                    <div class="student-leaderboard-list">
                        @forelse($leaderboard['entries'] as $entry)
                            @php
                                $entryUser = $entry['user'];
                                $entrySummary = $entry['summary'];
                                $entryLevel = $entrySummary['level'];
                                $isCurrentUser = $entryUser->id === $user->id;
                            @endphp
                            <div class="student-leaderboard-item {{ $isCurrentUser ? 'is-current' : '' }}">
                                <div class="student-leaderboard-rank rank-{{ min($entry['rank'], 4) }}">#{{ $entry['rank'] }}</div>
                                <div class="student-leaderboard-user">
                                    <div class="student-leaderboard-name-row">
                                        <strong>{{ $entryUser->fullname ?: $entryUser->username }}</strong>
                                        <span class="student-level-pill level-{{ $entryLevel['key'] }} student-level-pill-inline">
                                            <i class="{{ $entryLevel['icon'] }} me-1"></i>{{ $entryLevel['title'] }}
                                        </span>
                                    </div>
                                    <div class="student-leaderboard-meta">
                                        <span>{{ $entry['points_label'] }} điểm</span>
                                        <span>{{ number_format($entrySummary['metrics']['completed_courses']) }} khóa hoàn thành</span>
                                        <span>{{ $entrySummary['metrics']['study_duration_label'] }} đã học</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="student-leaderboard-empty">Chưa có dữ liệu xếp hạng học viên.</div>
                        @endforelse
                    </div>

                    @if($currentLeaderboardEntry && $currentLeaderboardEntry['rank'] > $leaderboard['entries']->count())
                        <div class="student-leaderboard-footnote">
                            Bạn hiện đang ở vị trí <strong>#{{ $currentLeaderboardEntry['rank'] }}</strong> trên tổng số <strong>{{ number_format($leaderboard['total_students']) }}</strong> học viên.
                        </div>
                    @endif
                </div>

                <div class="border rounded p-4 mb-4 student-dashboard-wallet">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="fw-bold mb-2">Số dư ví</h5>
                            <p class="mb-0">Số dư hiện tại của bạn là <strong>{{ number_format($user->balance, 2) }} VND</strong>.</p>
                        </div>
                        <a href="{{ route('wallet.index') }}" class="btn btn-outline-primary">Quản lý ví / Nạp tiền</a>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h5 class="mb-0">Khóa học của tôi</h5>
                    <span class="badge bg-primary px-3 py-2">{{ $enrollments->count() }} đăng ký</span>
                </div>

                @if($enrollments->isNotEmpty())
                    <div class="row">
                        @foreach($enrollments as $enrollment)
                            @php
                                $course = $enrollment->course;
                                $class = $enrollment->class;
                                $isOffline = $course?->isOffline();
                                $canLearn = $enrollment->isApproved() || $enrollment->isCompleted();
                                $canCancel = ! $enrollment->isCompleted() && ! $enrollment->isRejected() && ! $enrollment->isCancelled();
                                $canChangeClass = $isOffline && $enrollment->isApproved() && $class;
                                $badgeTextClass = $enrollment->status_color === 'warning' ? 'text-dark' : '';
                            @endphp
                            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                <div class="card course-card h-100 shadow-sm border-0">
                                    <div class="position-relative">
                                        @if($course && $course->thumbnail_url && filter_var($course->thumbnail_url, FILTER_VALIDATE_URL))
                                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="card-img-top student-dashboard-thumb">
                                        @else
                                            <div class="card-img-top d-flex align-items-center justify-content-center bg-primary text-white student-dashboard-thumb-placeholder">
                                                <i class="fas fa-book fa-3x"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <h6 class="card-title fw-bold mb-0">
                                                <a href="{{ route('courses.show', $course) }}" class="text-decoration-none text-dark">{{ $course->title }}</a>
                                            </h6>
                                            <span class="badge bg-{{ $enrollment->status_color }} {{ $badgeTextClass }}">{{ $enrollment->status_text }}</span>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mb-2 small">
                                            <span class="badge bg-light text-dark border">{{ $course->delivery_mode_label }}</span>
                                            <span class="badge bg-light text-dark border">{{ $course->category->name ?? 'Chưa phân loại' }}</span>
                                        </div>

                                        <div class="small text-muted mb-3">
                                            <div><i class="fas fa-calendar me-1"></i>Đăng ký lúc {{ optional($enrollment->created_at)->format('d/m/Y') }}</div>
                                            <div><i class="fas fa-clock me-1"></i>{{ $course->estimated_duration_label }}</div>
                                        </div>

                                        @if($isOffline && $class)
                                            <div class="border rounded p-3 mb-3 bg-light-subtle">
                                                <div class="small text-muted mb-1">Đợt học hiện tại</div>
                                                <div class="fw-bold">{{ $class->name }}</div>
                                                <div class="small text-muted">{{ optional($class->start_date)->format('d/m/Y') }} - {{ optional($class->end_date)->format('d/m/Y') }}</div>
                                                <div class="small text-muted">{{ $class->schedule_text ?: 'Chưa cập nhật lịch học' }}</div>
                                                @if($canChangeClass)
                                                    <button class="btn btn-outline-secondary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#changeClassModal-{{ $enrollment->id }}">Đổi đợt học</button>
                                                @endif
                                            </div>
                                        @endif

                                        @if($enrollment->notes)
                                            <div class="small text-muted border rounded p-2 mb-3 bg-light">
                                                <strong>Ghi chú:</strong> {{ $enrollment->notes }}
                                            </div>
                                        @endif

                                        <div class="mt-auto d-flex gap-2 flex-wrap">
                                            @if($canLearn)
                                                <a href="{{ route('courses.learn', $course) }}" class="btn btn-success btn-sm flex-fill">
                                                    <i class="fas fa-play me-1"></i>{{ $enrollment->isCompleted() ? 'Xem lại khóa học' : 'Vào học' }}
                                                </a>
                                            @endif

                                            @if($canCancel)
                                                <form action="{{ route('courses.unenroll', $course) }}" method="POST" class="flex-fill">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Bạn có chắc muốn hủy đăng ký?')">
                                                        Hủy đăng ký
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($canChangeClass)
                                @php
                                    $allowChange = \App\Models\Setting::get('allow_class_change', '1');
                                    $otherClasses = \App\Models\CourseClass::where('course_id', $course->id)
                                        ->where('status', 'active')
                                        ->where('id', '!=', $class->id)
                                        ->orderBy('start_date')
                                        ->get();
                                @endphp
                                <div class="modal fade" id="changeClassModal-{{ $enrollment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Đổi đợt học - {{ $course->title }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                            </div>
                                            <div class="modal-body">
                                                @if(!$allowChange || (string) $allowChange === '0')
                                                    <div class="alert alert-warning mb-0">Chức năng đổi đợt học hiện đang bị khóa.</div>
                                                @elseif($otherClasses->isEmpty())
                                                    <div class="text-center text-muted">Không có đợt học thay thế để đổi.</div>
                                                @else
                                                    <div class="row g-3">
                                                        @foreach($otherClasses as $cls)
                                                            @php
                                                                $remaining = $cls->remaining_slots;
                                                                $isFull = $cls->is_full;
                                                            @endphp
                                                            <div class="col-12">
                                                                <div class="card border-0 bg-light-subtle">
                                                                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                                                                        <div>
                                                                            <h6 class="mb-1">{{ $cls->name }}</h6>
                                                                            <div class="small text-muted">{{ optional($cls->start_date)->format('d/m/Y') }} - {{ optional($cls->end_date)->format('d/m/Y') }}</div>
                                                                            <div class="small text-muted">{{ $cls->schedule_text ?: 'Chưa cập nhật lịch học' }}</div>
                                                                            <div class="small text-muted">{{ is_null($remaining) ? 'Không giới hạn chỗ' : 'Còn ' . $remaining . ' chỗ' }}</div>
                                                                        </div>
                                                                        <div>
                                                                            @if($isFull)
                                                                                <button class="btn btn-secondary" disabled>Đã đầy</button>
                                                                            @else
                                                                                <form action="{{ route('courses.enroll.change', [$course, $enrollment]) }}" method="POST">
                                                                                    @csrf
                                                                                    <input type="hidden" name="new_class_id" value="{{ $cls->id }}">
                                                                                    <button type="submit" class="btn btn-primary">Chọn đợt này</button>
                                                                                </form>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="feature-icon mx-auto mb-3"><i class="fas fa-book"></i></div>
                        <h4 class="text-muted">Bạn chưa có đăng ký khóa học nào</h4>
                        <p class="text-muted mb-4">Hãy khám phá và chọn khóa học phù hợp để bắt đầu tích điểm và mở huy hiệu đầu tiên.</p>
                        <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Khám phá khóa học
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection