@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="fas fa-clipboard-list"></i> Bài kiểm tra của tôi</h4>
                            <small>Danh sách các bài kiểm tra đã được giao</small>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Tìm kiếm..." style="width: 200px;">
                            <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">Tất cả trạng thái</option>
                                <option value="not_started">Chưa làm</option>
                                <option value="in_progress">Đang làm</option>
                                <option value="completed">Hoàn thành</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row" id="quizzesContainer">
                        @forelse($quizzes as $quiz)
                        <div class="col-lg-6 col-xl-4 mb-4 quiz-card" data-title="{{ $quiz->title }}" data-status="{{ $quiz->getStudentStatus() }}">
                            <div class="card h-100 border-left-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $quiz->title }}</h6>
                                            <small class="text-muted">{{ $quiz->course->title }}</small>
                                        </div>
                                        <span class="badge {{ $quiz->getStatusBadgeClass() }}">
                                            {{ $quiz->getStatusText() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <p class="card-text text-muted small mb-3">
                                        {{ Str::limit($quiz->description, 100) }}
                                    </p>

                                    <div class="row text-center mb-3">
                                        <div class="col-4">
                                            <div class="small text-muted">Câu hỏi</div>
                                            <div class="h6 mb-0">{{ $quiz->questions->count() }}</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">Thời gian</div>
                                            <div class="h6 mb-0">{{ $quiz->time_limit ?? 'Không giới hạn' }}</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="small text-muted">Điểm cao nhất</div>
                                            <div class="h6 mb-0">{{ $quiz->getStudentBestScore() ?? 'N/A' }}</div>
                                        </div>
                                    </div>

                                    @if($quiz->getStudentStatus() === 'completed')
                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar {{ $quiz->getLatestAttempt()->percentage >= 70 ? 'bg-success' : ($quiz->getLatestAttempt()->percentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                 style="width: {{ $quiz->getLatestAttempt()->percentage }}%">
                                            </div>
                                        </div>
                                        <div class="text-center small text-muted mb-3">
                                            Điểm: {{ $quiz->getLatestAttempt()->score }}/{{ $quiz->getLatestAttempt()->total_questions }}
                                            ({{ number_format($quiz->getLatestAttempt()->percentage, 1) }}%)
                                        </div>
                                    @endif
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i>
                                            {{ $quiz->available_from ? \Carbon\Carbon::parse($quiz->available_from)->format('d/m/Y') : 'Không giới hạn' }}
                                            @if($quiz->available_until)
                                                - {{ \Carbon\Carbon::parse($quiz->available_until)->format('d/m/Y') }}
                                            @endif
                                        </small>

                                        <div>
                                            @if($quiz->canStudentTake())
                                                <a href="{{ route('quizzes.take', $quiz->id) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-play"></i> Làm bài
                                                </a>
                                            @elseif($quiz->getStudentStatus() === 'completed')
                                                <a href="{{ route('quizzes.result', $quiz->getLatestAttempt()->id) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> Xem kết quả
                                                </a>
                                            @else
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="fas fa-clock"></i> Chưa đến giờ
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Không có bài kiểm tra nào</h5>
                                <p class="text-muted">Bạn chưa được giao bài kiểm tra nào hoặc chưa có bài kiểm tra nào khả dụng.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const quizCards = document.querySelectorAll('.quiz-card');

    function filterQuizzes() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        quizCards.forEach(card => {
            const title = card.dataset.title.toLowerCase();
            const status = card.dataset.status;

            const matchesSearch = title.includes(searchTerm);
            const matchesStatus = !statusValue || status === statusValue;

            card.style.display = matchesSearch && matchesStatus ? 'block' : 'none';
        });
    }

    searchInput.addEventListener('input', filterQuizzes);
    statusFilter.addEventListener('change', filterQuizzes);

    // Auto-refresh every 5 minutes to check for new quizzes
    setInterval(function() {
        // You could implement an AJAX call here to refresh the quiz list
        console.log('Checking for new quizzes...');
    }, 300000);
});
</script>

<style>
.quiz-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.quiz-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.border-left-primary {
    border-left: 4px solid #007bff !important;
}

.card-footer {
    padding: 0.75rem 1rem;
}

.progress {
    border-radius: 4px;
}

@media (max-width: 768px) {
    .card-title {
        font-size: 1rem;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
}
</style>
@endsection