@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="fas fa-video"></i> Video bài giảng</h4>
                            <small>Danh sách video của khóa học</small>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Tìm kiếm video..." style="width: 200px;">
                            <select id="statusFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">Tất cả trạng thái</option>
                                <option value="processed">Sẵn sàng</option>
                                <option value="processing">Đang xử lý</option>
                                <option value="failed">Lỗi</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Course Progress Overview -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-primary">{{ $totalVideos }}</h5>
                                    <small class="text-muted">Tổng video</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-success">{{ $watchedVideos }}</h5>
                                    <small class="text-muted">Đã xem</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-warning">{{ $processingVideos }}</h5>
                                    <small class="text-muted">Đang xử lý</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-info">{{ $courseProgress }}%</h5>
                                    <small class="text-muted">Hoàn thành</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Videos Grid -->
                    <div class="row" id="videosContainer">
                        @forelse($videos as $video)
                        <div class="col-lg-4 col-md-6 mb-4 video-card"
                             data-title="{{ $video->title }}"
                             data-status="{{ $video->status }}">
                            <div class="card h-100 border">
                                <div class="position-relative">
                                    <div class="ratio ratio-16x9">
                                        @if($video->thumbnail_path)
                                            <img src="{{ asset('storage/thumbnails/' . $video->thumbnail_path) }}"
                                                 class="card-img-top" alt="Thumbnail" style="object-fit: cover;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-secondary text-white">
                                                <i class="fas fa-video fa-2x"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge {{ $video->getStatusBadgeClass() }}">
                                            {{ $video->getStatusText() }}
                                        </span>
                                    </div>

                                    <!-- Progress Overlay -->
                                    @if($video->isWatchedByStudent())
                                        <div class="position-absolute bottom-0 start-0 w-100">
                                            <div class="progress" style="height: 4px; border-radius: 0;">
                                                <div class="progress-bar bg-success" style="width: 100%"></div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="card-body">
                                    <h6 class="card-title mb-2">
                                        <a href="{{ $video->status === 'processed' ? route('videos.watch', $video->id) : '#' }}"
                                           class="text-decoration-none {{ $video->status !== 'processed' ? 'text-muted' : '' }}">
                                            {{ $video->title }}
                                        </a>
                                    </h6>

                                    <p class="card-text small text-muted mb-2">
                                        {{ Str::limit($video->description, 80) }}
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center small text-muted">
                                        <span><i class="fas fa-clock"></i> {{ $video->duration ?? 'N/A' }}</span>
                                        <span><i class="fas fa-weight-hanging"></i> {{ $video->getFileSizeFormatted() }}</span>
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            {{ $video->created_at->format('d/m/Y') }}
                                        </small>

                                        @if($video->status === 'processed')
                                            <a href="{{ route('videos.watch', $video->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-play"></i> Xem ngay
                                            </a>
                                        @elseif($video->status === 'processing')
                                            <button class="btn btn-warning btn-sm" disabled>
                                                <i class="fas fa-spinner fa-spin"></i> Đang xử lý
                                            </button>
                                        @else
                                            <button class="btn btn-danger btn-sm" disabled>
                                                <i class="fas fa-exclamation-triangle"></i> Lỗi
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Không có video nào</h5>
                                <p class="text-muted">Khóa học này chưa có video bài giảng nào.</p>
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
    const videoCards = document.querySelectorAll('.video-card');

    function filterVideos() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        videoCards.forEach(card => {
            const title = card.dataset.title.toLowerCase();
            const status = card.dataset.status;

            const matchesSearch = title.includes(searchTerm);
            const matchesStatus = !statusValue || status === statusValue;

            card.style.display = matchesSearch && matchesStatus ? 'block' : 'none';
        });
    }

    searchInput.addEventListener('input', filterVideos);
    statusFilter.addEventListener('change', filterVideos);

    // Auto-refresh processing videos every 30 seconds
    setInterval(function() {
        const processingCards = document.querySelectorAll('.video-card[data-status="processing"]');
        if (processingCards.length > 0) {
            location.reload();
        }
    }, 30000);
});
</script>

<style>
.video-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.video-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.card-img-top {
    border-radius: 0;
}

.badge {
    font-size: 0.75em;
}

.progress {
    border-radius: 0;
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