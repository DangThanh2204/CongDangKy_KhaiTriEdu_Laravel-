@extends('layouts.app')

@section('title', 'Video Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ $video->title }}</h4>
                    <div>
                        <a href="{{ route('instructor.videos.edit', $video) }}" class="btn btn-secondary me-2">
                            <i class="fas fa-edit"></i> Edit Video
                        </a>
                        <a href="{{ route('instructor.videos.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Back to Videos
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Video Player -->
                            <div class="mb-4">
                                @if($video->video_path)
                                    <video controls class="w-100" style="max-height: 400px;" poster="{{ $video->thumbnail_path ? asset('storage/' . $video->thumbnail_path) : '' }}">
                                        <source src="{{ asset('storage/' . $video->video_path) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                @else
                                    <div class="bg-dark text-white d-flex align-items-center justify-content-center" style="height: 300px; border-radius: 8px;">
                                        <div class="text-center">
                                            <i class="fas fa-video fa-3x mb-3"></i>
                                            <p>Video file not available</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Video Info -->
                            <h5>Description</h5>
                            <p>{{ $video->description ?: 'No description provided.' }}</p>

                            @if($video->course)
                                <h5>Course</h5>
                                <p><a href="{{ route('courses.show', $video->course) }}">{{ $video->course->title }}</a></p>
                            @endif

                            @if($video->tags)
                                <h5>Tags</h5>
                                <div>
                                    @foreach(explode(',', $video->tags) as $tag)
                                        <span class="badge bg-secondary me-1">{{ trim($tag) }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <!-- Video Details -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Video Details</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Status:</strong>
                                            <span class="badge bg-{{ $video->status === 'published' ? 'success' : ($video->status === 'processing' ? 'warning' : 'secondary') }} ms-1">
                                                {{ ucfirst($video->status) }}
                                            </span>
                                        </li>
                                        <li><strong>Level:</strong> {{ ucfirst($video->level) }}</li>
                                        <li><strong>Price:</strong> {{ $video->price > 0 ? number_format($video->price) . ' VND' : 'Free' }}</li>
                                        <li><strong>Free Preview:</strong> {{ $video->is_free ? 'Yes' : 'No' }}</li>
                                        @if($video->duration)
                                            <li><strong>Duration:</strong> {{ gmdate('i:s', $video->duration) }}</li>
                                        @endif
                                        @if($video->file_size)
                                            <li><strong>File Size:</strong> {{ number_format($video->file_size / 1024 / 1024, 2) }} MB</li>
                                        @endif
                                        <li><strong>Category:</strong> {{ $video->category ? $video->category->name : 'N/A' }}</li>
                                        <li><strong>Created:</strong> {{ $video->created_at->format('M d, Y H:i') }}</li>
                                        <li><strong>Last Updated:</strong> {{ $video->updated_at->format('M d, Y H:i') }}</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Statistics -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Statistics</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Views:</strong> {{ $video->views ?? 0 }}</li>
                                        <li><strong>Likes:</strong> {{ $video->likes ?? 0 }}</li>
                                        <li><strong>Comments:</strong> {{ $video->comments ?? 0 }}</li>
                                        <li><strong>Rating:</strong> {{ $video->rating ?? 'N/A' }}/5</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- File Information -->
                            @if($video->video_path || $video->thumbnail_path)
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">File Information</h6>
                                        <ul class="list-unstyled">
                                            @if($video->video_path)
                                                <li><strong>Video File:</strong> {{ basename($video->video_path) }}</li>
                                            @endif
                                            @if($video->thumbnail_path)
                                                <li><strong>Thumbnail:</strong> {{ basename($video->thumbnail_path) }}</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-center gap-2">
                                @if($video->status === 'draft')
                                    <form action="{{ route('instructor.videos.update', $video) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="published">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-upload"></i> Publish Video
                                        </button>
                                    </form>
                                @elseif($video->status === 'published')
                                    <form action="{{ route('instructor.videos.update', $video) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="draft">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-pause"></i> Unpublish Video
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('instructor.videos.edit', $video) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Details
                                </a>

                                <form action="{{ route('instructor.videos.destroy', $video) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this video? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete Video
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection