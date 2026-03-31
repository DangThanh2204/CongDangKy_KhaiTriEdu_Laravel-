@extends('layouts.admin')

@section('page-title', 'Quản lý Video')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Danh sách Video</h4>
                    <a href="{{ route('admin.videos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Upload Video mới
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tiêu đề</th>
                                    <th>Khóa học</th>
                                    <th>Trạng thái</th>
                                    <th>Kích thước</th>
                                    <th>Thời lượng</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($videos as $video)
                                <tr>
                                    <td>{{ $video->id }}</td>
                                    <td>{{ $video->title }}</td>
                                    <td>{{ $video->course->title ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $video->processing_status === 'completed' ? 'success' : ($video->processing_status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($video->processing_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $video->file_size_formatted }}</td>
                                    <td>{{ $video->formatted_duration }}</td>
                                    <td>
                                        <div class="btn-group">
                                            @if($video->isProcessed())
                                            <a href="{{ route('admin.videos.stream', $video) }}" class="btn btn-sm btn-success" target="_blank">
                                                <i class="fas fa-play"></i> Xem
                                            </a>
                                            @elseif($video->processing_status === 'pending')
                                            <button class="btn btn-sm btn-warning process-video" data-video-id="{{ $video->id }}">
                                                <i class="fas fa-cog"></i> Process
                                            </button>
                                            @endif
                                            <a href="{{ route('admin.videos.edit', $video) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.videos.destroy', $video) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa video này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Chưa có video nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $videos->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.process-video').forEach(button => {
        button.addEventListener('click', function() {
            const videoId = this.dataset.videoId;
            const btn = this;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            fetch(`/admin/videos/${videoId}/process`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xử lý video');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-cog"></i> Process';
            });
        });
    });
});
</script>
@endsection