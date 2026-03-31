@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $video->title }}</h4>
                            <small>{{ $video->course->title }}</small>
                        </div>
                        <div class="text-end">
                            <small>Thời lượng: {{ $video->duration ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="video-container" class="ratio ratio-16x9">
                        @if($video->status === 'processed')
                            <video id="video-player" class="w-100" controls preload="metadata">
                                <source src="{{ asset('storage/videos/' . $video->hls_playlist_path) }}" type="application/x-mpegURL">
                                <p>Trình duyệt của bạn không hỗ trợ video HTML5.</p>
                            </video>
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 bg-dark text-white">
                                <div class="text-center">
                                    <i class="fas fa-video fa-3x mb-3"></i>
                                    <h5>Video đang được xử lý</h5>
                                    <p class="mb-0">Vui lòng quay lại sau.</p>
                                    <div class="progress mt-3" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                             style="width: {{ $video->processing_progress ?? 0 }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Video Description -->
            <div class="card shadow mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Mô tả</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $video->description ?? 'Không có mô tả' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Video Playlist -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Danh sách video</h5>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    @foreach($courseVideos as $courseVideo)
                    <div class="video-item p-3 border-bottom {{ $courseVideo->id === $video->id ? 'bg-light' : '' }}"
                         data-video-id="{{ $courseVideo->id }}">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <img src="{{ $courseVideo->thumbnail_path ? asset('storage/thumbnails/' . $courseVideo->thumbnail_path) : asset('images/video-placeholder.jpg') }}"
                                     alt="Thumbnail" class="rounded" style="width: 80px; height: 45px; object-fit: cover;">
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 small">
                                    <a href="{{ route('videos.watch', $courseVideo->id) }}" class="text-decoration-none">
                                        {{ $courseVideo->title }}
                                    </a>
                                </h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $courseVideo->duration ?? 'N/A' }}</small>
                                    @if($courseVideo->id === $video->id)
                                        <i class="fas fa-play text-primary"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Video Progress -->
            <div class="card shadow mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Tiến độ học tập</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Hoàn thành khóa học</span>
                            <span class="small">{{ $courseProgress }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $courseProgress }}%"></div>
                        </div>
                    </div>

                    <div class="text-center">
                        <small class="text-muted">
                            {{ $watchedVideos }}/{{ $totalVideos }} video đã xem
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if($video->status === 'processed')
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('video-player');
    const videoSrc = video.querySelector('source').src;

    if (Hls.isSupported()) {
        const hls = new Hls({
            enableWorker: true,
            lowLatencyMode: true,
            backBufferLength: 90
        });

        hls.loadSource(videoSrc);
        hls.attachMedia(video);

        hls.on(Hls.Events.MANIFEST_PARSED, function() {
            console.log('HLS manifest loaded');
        });

        hls.on(Hls.Events.ERROR, function(event, data) {
            console.error('HLS error:', data);
            if (data.fatal) {
                switch(data.type) {
                    case Hls.ErrorTypes.NETWORK_ERROR:
                        hls.startLoad();
                        break;
                    case Hls.ErrorTypes.MEDIA_ERROR:
                        hls.recoverMediaError();
                        break;
                    default:
                        hls.destroy();
                        break;
                }
            }
        });
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        // Native HLS support (Safari)
        video.src = videoSrc;
    }

    // Track video progress
    let progressTracked = false;
    video.addEventListener('timeupdate', function() {
        const progress = (video.currentTime / video.duration) * 100;

        if (progress >= 90 && !progressTracked) {
            // Mark video as watched
            fetch('{{ route("videos.mark-watched", $video->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    progressTracked = true;
                    console.log('Video marked as watched');
                }
            })
            .catch(error => {
                console.error('Error marking video as watched:', error);
            });
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName.toLowerCase() !== 'input') {
            switch(e.key) {
                case ' ':
                case 'k':
                    e.preventDefault();
                    video.paused ? video.play() : video.pause();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    video.currentTime -= 10;
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    video.currentTime += 10;
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    video.volume = Math.min(1, video.volume + 0.1);
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    video.volume = Math.max(0, video.volume - 0.1);
                    break;
                case 'f':
                    e.preventDefault();
                    if (video.requestFullscreen) {
                        video.requestFullscreen();
                    }
                    break;
                case 'm':
                    e.preventDefault();
                    video.muted = !video.muted;
                    break;
            }
        }
    });

    // Auto-play next video (optional)
    video.addEventListener('ended', function() {
        const currentItem = document.querySelector('.video-item.bg-light');
        const nextItem = currentItem ? currentItem.nextElementSibling : null;

        if (nextItem && nextItem.classList.contains('video-item')) {
            const nextVideoId = nextItem.dataset.videoId;
            if (nextVideoId) {
                // Auto-redirect to next video after 3 seconds
                setTimeout(function() {
                    window.location.href = '{{ url("videos/watch") }}/' + nextVideoId;
                }, 3000);
            }
        }
    });
});
</script>
@endif
@endpush

<style>
.video-item {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.video-item:hover {
    background-color: #f8f9fa;
}

.video-item a {
    color: inherit;
}

.video-item a:hover {
    color: #007bff;
}

#video-container {
    background: #000;
}

#video-player {
    max-height: 70vh;
}

@media (max-width: 768px) {
    .col-lg-8, .col-lg-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    #video-player {
        max-height: 50vh;
    }
}
</style>
@endsection