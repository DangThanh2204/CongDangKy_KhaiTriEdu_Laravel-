@extends('layouts.app')

@section('title', 'Manage Videos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">My Videos</h4>
                    <a href="{{ route('instructor.videos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Upload New Video
                    </a>
                </div>
                <div class="card-body">
                    @if($videos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Course</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($videos as $video)
                                        <tr>
                                            <td>
                                                @if($video->thumbnail_path)
                                                    <img src="{{ asset('storage/' . $video->thumbnail_path) }}" alt="Thumbnail" class="img-thumbnail" style="width: 60px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 40px; border-radius: 4px;">
                                                        <i class="fas fa-video"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $video->title }}</td>
                                            <td>{{ $video->course ? $video->course->title : 'N/A' }}</td>
                                            <td>{{ $video->duration ? gmdate('i:s', $video->duration) : 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $video->status === 'published' ? 'success' : ($video->status === 'processing' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($video->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $video->views ?? 0 }}</td>
                                            <td>{{ $video->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('instructor.videos.show', $video) }}" class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('instructor.videos.edit', $video) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('instructor.videos.destroy', $video) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this video?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $videos->links() }}
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-video fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No videos found</h5>
                            <p class="text-muted">Upload your first video to get started.</p>
                            <a href="{{ route('instructor.videos.create') }}" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Video
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection