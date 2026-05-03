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
        $hasPaymentHistoryRoute = \Illuminate\Support\Facades\Route::has('student.payments.index');
    @endphp

    <div class="container-fluid py-4">
        {{-- Hero: Welcome + Level + Progress --}}
        <div class="card border-0 shadow-sm student-hero-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="text-white-50 small mb-1">Xin chào</div>
                        <h2 class="text-white fw-bold mb-2">{{ $user->fullname }}</h2>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="student-level-pill level-{{ $currentLevel['key'] }}">
                                <i class="{{ $currentLevel['icon'] }} me-1"></i>{{ $currentLevel['badge_name'] }}
                            </span>
                            <span class="text-white">·</span>
                            <span class="text-white fw-semibold">{{ $studentLevel['points_label'] }} điểm</span>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('student.application-status') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-file-waveform me-1"></i>Hồ sơ
                        </a>
                        @if($hasPaymentHistoryRoute)
                            <a href="{{ route('student.payments.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-receipt me-1"></i>Thanh toán
                            </a>
                        @endif
                        <a href="{{ route('courses.index') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-plus me-1"></i>Đăng ký mới
                        </a>
                    </div>
                </div>

                <div class="student-hero-progress">
                    <div class="d-flex justify-content-between align-items-center text-white mb-2 small">
                        <span>
                            @if($nextLevel)
                                Tiến độ lên <strong>{{ $nextLevel['badge_name'] }}</strong>
                            @else
                                Bạn đã ở mức cao nhất
                            @endif
                        </span>
                        <strong>{{ $studentLevel['progress_to_next'] }}%</strong>
                    </div>
                    <div class="progress" style="height: 8px; background: rgba(255,255,255,0.2);">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $studentLevel['progress_to_next'] }}%"></div>
                    </div>
                    @if($nextLevel)
                        <div class="text-white-50 small mt-2">
                            Còn <strong class="text-white">{{ number_format($studentLevel['points_to_next']) }} điểm</strong> để lên cấp tiếp theo
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick stats: 4 tiles with icons --}}
        <div class="row g-3 mb-4 student-stats-row">
            <div class="col-md-6 col-xl-3">
                <div class="stat-tile stat-tile-blue">
                    <div class="stat-tile-icon"><i class="fas fa-book-open"></i></div>
                    <div class="stat-tile-body">
                        <div class="stat-tile-value">{{ $approvedCourses->count() }}</div>
                        <div class="stat-tile-label">Đang học</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-tile stat-tile-amber">
                    <div class="stat-tile-icon"><i class="fas fa-hourglass-half"></i></div>
                    <div class="stat-tile-body">
                        <div class="stat-tile-value">{{ $pendingCourses->count() }}</div>
                        <div class="stat-tile-label">Chờ duyệt</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-tile stat-tile-green">
                    <div class="stat-tile-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-tile-body">
                        <div class="stat-tile-value">{{ $completedCourses->count() }}</div>
                        <div class="stat-tile-label">Hoàn thành</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-tile stat-tile-purple">
                    <div class="stat-tile-icon"><i class="fas fa-award"></i></div>
                    <div class="stat-tile-body">
                        <div class="stat-tile-value">{{ $studentLevel['metrics']['certificates'] }}</div>
                        <div class="stat-tile-label">Chứng chỉ</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Main column: Course list --}}
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-book me-2 text-primary"></i>Khóa học của tôi</h5>
                        <span class="badge bg-primary px-3 py-2">{{ $enrollments->count() }} đăng ký</span>
                    </div>
                    <div class="card-body">
                        @if($enrollments->isNotEmpty())
                            <div class="row g-3">
                                @foreach($enrollments as $enrollment)
                                    @php
                                        $course = $enrollment->course ?: $enrollment->class?->course;
                                        $class = $enrollment->class;
                                        $isOffline = $course?->isOffline();
                                        $canLearn = $enrollment->isApproved() || $enrollment->isCompleted();
                                        $canCancel = ! $enrollment->isCompleted() && ! $enrollment->isRejected() && ! $enrollment->isCancelled();
                                        $canChangeClass = $isOffline && $enrollment->isApproved() && $class;
                                        $badgeTextClass = $enrollment->status_color === 'warning' ? 'text-dark' : '';
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="card course-card h-100 shadow-sm border-0">
                                            <div class="position-relative">
                                                @if($course)
                                                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="card-img-top student-dashboard-thumb">
                                                @else
                                                    <div class="card-img-top d-flex align-items-center justify-content-center bg-primary text-white student-dashboard-thumb-placeholder">
                                                        <i class="fas fa-book fa-3x"></i>
                                                    </div>
                                                @endif
                                                <span class="position-absolute top-0 end-0 m-2 badge bg-{{ $enrollment->status_color }} {{ $badgeTextClass }}">{{ $enrollment->status_text }}</span>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <h6 class="card-title fw-bold mb-2">
                                                    @if($course)
                                                        <a href="{{ route('courses.show', $course) }}" class="text-decoration-none text-dark">{{ $course->title }}</a>
                                                    @else
                                                        <span class="text-muted">Khóa học không khả dụng</span>
                                                    @endif
                                                </h6>

                                                @if($course)
                                                    <div class="d-flex flex-wrap gap-1 mb-2 small">
                                                        <span class="badge bg-light text-dark border">{{ $course->delivery_mode_label }}</span>
                                                        <span class="badge bg-light text-dark border">{{ $course->category->name ?? 'Chưa phân loại' }}</span>
                                                    </div>
                                                @endif

                                                <div class="small text-muted mb-3">
                                                    <div><i class="fas fa-calendar me-1"></i>Đăng ký {{ optional($enrollment->created_at)->format('d/m/Y') }}</div>
                                                    @if($course)
                                                        <div><i class="fas fa-clock me-1"></i>{{ $course->estimated_duration_label }}</div>
                                                    @endif
                                                </div>

                                                @if($isOffline && $class)
                                                    <div class="border rounded p-2 mb-3 bg-light small">
                                                        <div class="fw-bold">{{ $class->name }}</div>
                                                        <div class="text-muted">{{ optional($class->start_date)->format('d/m/Y') }} - {{ optional($class->end_date)->format('d/m/Y') }}</div>
                                                        <div class="text-muted">{{ $class->schedule_text ?: 'Chưa cập nhật lịch' }}</div>
                                                        @if($canChangeClass)
                                                            <button class="btn btn-link btn-sm p-0 mt-1" data-bs-toggle="modal" data-bs-target="#changeClassModal-{{ $enrollment->id }}">Đổi đợt học</button>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="mt-auto d-flex gap-2">
                                                    @if($canLearn && $course)
                                                        <a href="{{ route('courses.learn', $course) }}" class="btn btn-success btn-sm flex-fill">
                                                            <i class="fas fa-play me-1"></i>{{ $enrollment->isCompleted() ? 'Xem lại' : 'Vào học' }}
                                                        </a>
                                                    @endif

                                                    @if($canCancel && $course)
                                                        <form action="{{ route('courses.unenroll', $course) }}" method="POST" class="flex-fill">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Hủy đăng ký?')">
                                                                Hủy
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($canChangeClass && $course)
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
                                <i class="fas fa-book fa-3x text-muted mb-3 opacity-50"></i>
                                <h5 class="text-muted">Bạn chưa đăng ký khóa học nào</h5>
                                <p class="text-muted mb-4">Khám phá các khóa học để bắt đầu hành trình học tập.</p>
                                <a href="{{ route('courses.index') }}" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Khám phá khóa học
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar: Wallet + Leaderboard --}}
            <div class="col-xl-4">
                {{-- Wallet --}}
                <div class="card border-0 shadow-sm mb-3 student-wallet-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="text-muted small">Số dư ví</div>
                                <div class="h4 fw-bold mb-0">{{ number_format($user->balance) }} VND</div>
                            </div>
                            <i class="fas fa-wallet fa-2x text-primary opacity-50"></i>
                        </div>
                        <a href="{{ route('wallet.index') }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-coins me-1"></i>Quản lý ví
                        </a>
                    </div>
                </div>

                {{-- Leaderboard --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-trophy text-warning me-2"></i>Top học viên</h6>
                        @if($currentLeaderboardEntry)
                            <span class="badge bg-primary-subtle text-primary-emphasis">#{{ $currentLeaderboardEntry['rank'] }}</span>
                        @endif
                    </div>
                    <ul class="list-group list-group-flush student-leaderboard-list-compact">
                        @forelse($leaderboard['entries']->take(5) as $entry)
                            @php
                                $entryUser = $entry['user'];
                                $entrySummary = $entry['summary'];
                                $entryLevel = $entrySummary['level'];
                                $isCurrentUser = $entryUser->id === $user->id;
                            @endphp
                            <li class="list-group-item d-flex align-items-center gap-3 {{ $isCurrentUser ? 'is-current' : '' }}">
                                <div class="leaderboard-rank rank-{{ min($entry['rank'], 4) }}">{{ $entry['rank'] }}</div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $entryUser->fullname ?: $entryUser->username }}</div>
                                    <div class="small text-muted">
                                        <i class="{{ $entryLevel['icon'] }} me-1"></i>{{ $entryLevel['title'] }} · {{ $entry['points_label'] }} điểm
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted small py-4">Chưa có dữ liệu xếp hạng.</li>
                        @endforelse
                    </ul>
                    @if($currentLeaderboardEntry && $currentLeaderboardEntry['rank'] > 5)
                        <div class="card-footer bg-white border-0 small text-muted text-center py-2">
                            Bạn đứng thứ <strong>#{{ $currentLeaderboardEntry['rank'] }}</strong> / {{ number_format($leaderboard['total_students']) }} học viên
                        </div>
                    @endif
                </div>

                {{-- Study breakdown (collapsible) --}}
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="fas fa-chart-line text-info me-2"></i>Hoạt động học tập</h6>
                        <div class="d-flex justify-content-between small py-2 border-bottom">
                            <span class="text-muted"><i class="fas fa-clock me-2"></i>Thời gian học</span>
                            <strong>{{ $studentLevel['metrics']['study_duration_label'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between small py-2 border-bottom">
                            <span class="text-muted"><i class="fas fa-layer-group me-2"></i>Nội dung hoàn thành</span>
                            <strong>{{ number_format($studentLevel['metrics']['completed_materials']) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between small py-2 border-bottom">
                            <span class="text-muted"><i class="fas fa-question-circle me-2"></i>Quiz đã đạt</span>
                            <strong>{{ number_format($studentLevel['metrics']['passed_quizzes']) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between small py-2">
                            <span class="text-muted"><i class="fas fa-calendar-check me-2"></i>Ngày học tích cực</span>
                            <strong>{{ number_format($studentLevel['metrics']['active_study_days']) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
