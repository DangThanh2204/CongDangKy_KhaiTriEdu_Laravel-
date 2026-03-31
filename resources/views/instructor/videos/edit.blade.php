@extends('layouts.app')

@section('title', 'Edit Video')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Edit Video: {{ $video->title }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('instructor.videos.update', $video) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Video Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $video->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $video->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Course</label>
                                    <select class="form-select @error('course_id') is-invalid @enderror" id="course_id" name="course_id">
                                        <option value="">Select a course (optional)</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_id', $video->course_id) == $course->id ? 'selected' : '' }}>
                                                {{ $course->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Current Video File</label>
                                    <div class="border p-3 rounded">
                                        @if($video->video_path)
                                            <p class="mb-1"><strong>File:</strong> {{ basename($video->video_path) }}</p>
                                            <p class="mb-1"><strong>Size:</strong> {{ $video->file_size ? number_format($video->file_size / 1024 / 1024, 2) . ' MB' : 'Unknown' }}</p>
                                            <p class="mb-1"><strong>Duration:</strong> {{ $video->duration ? gmdate('i:s', $video->duration) : 'Unknown' }}</p>
                                            <small class="text-muted">Leave empty to keep current video file.</small>
                                        @else
                                            <p class="text-muted">No video file uploaded yet.</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="video_file" class="form-label">Replace Video File</label>
                                    <input type="file" class="form-control @error('video_file') is-invalid @enderror" id="video_file" name="video_file" accept="video/*">
                                    <small class="form-text text-muted">Optional. Supported formats: MP4, AVI, MOV, WMV, FLV, WebM. Maximum size: 500MB.</small>
                                    @error('video_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Current Thumbnail</label>
                                    <div class="border p-3 rounded d-flex align-items-center">
                                        @if($video->thumbnail_path)
                                            <img src="{{ asset('storage/' . $video->thumbnail_path) }}" alt="Current Thumbnail" class="img-thumbnail me-3" style="width: 100px; height: 60px; object-fit: cover;">
                                            <div>
                                                <p class="mb-0">Current thumbnail image</p>
                                                <small class="text-muted">Leave empty to keep current thumbnail.</small>
                                            </div>
                                        @else
                                            <div>
                                                <p class="text-muted mb-0">No thumbnail uploaded yet.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="thumbnail" class="form-label">Replace Thumbnail Image</label>
                                    <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/*">
                                    <small class="form-text text-muted">Optional. Recommended size: 1280x720px. Supported formats: JPG, PNG, GIF.</small>
                                    @error('thumbnail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                        <option value="">Select a category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $video->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="tags" class="form-label">Tags</label>
                                    <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags', $video->tags) }}" placeholder="Enter tags separated by commas">
                                    <small class="form-text text-muted">Example: php, laravel, tutorial</small>
                                    @error('tags')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="level" class="form-label">Difficulty Level</label>
                                    <select class="form-select @error('level') is-invalid @enderror" id="level" name="level">
                                        <option value="beginner" {{ old('level', $video->level) == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="intermediate" {{ old('level', $video->level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="advanced" {{ old('level', $video->level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                    </select>
                                    @error('level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="price" class="form-label">Price (VND)</label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $video->price) }}" min="0" step="1000">
                                    <small class="form-text text-muted">Set to 0 for free videos.</small>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_free" name="is_free" value="1" {{ old('is_free', $video->is_free) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_free">
                                            Free Preview
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Allow students to watch part of the video for free.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="draft" {{ old('status', $video->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="published" {{ old('status', $video->status) == 'published' ? 'selected' : '' }}>Published</option>
                                        <option value="private" {{ old('status', $video->status) == 'private' ? 'selected' : '' }}>Private</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Statistics</label>
                                    <div class="border p-3 rounded">
                                        <p class="mb-1"><strong>Views:</strong> {{ $video->views ?? 0 }}</p>
                                        <p class="mb-1"><strong>Likes:</strong> {{ $video->likes ?? 0 }}</p>
                                        <p class="mb-1"><strong>Created:</strong> {{ $video->created_at->format('M d, Y H:i') }}</p>
                                        <p class="mb-0"><strong>Updated:</strong> {{ $video->updated_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('instructor.videos.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Video</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// File size validation
document.getElementById('video_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const maxSize = 500 * 1024 * 1024; // 500MB in bytes

    if (file && file.size > maxSize) {
        alert('File size exceeds 500MB limit. Please choose a smaller file.');
        e.target.value = '';
    }
});
</script>
@endsection