@extends('layouts.app')

@section('title', 'Create Quiz')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Create New Quiz</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('instructor.quizzes.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="course_id" class="form-label">Course</label>
                                    <select class="form-select @error('course_id') is-invalid @enderror" id="course_id" name="course_id">
                                        <option value="">Select a course (optional)</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">Duration (minutes)</label>
                                    <input type="number" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" value="{{ old('duration', 30) }}" min="1">
                                    @error('duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="passing_score" class="form-label">Passing Score (%)</label>
                                    <input type="number" class="form-control @error('passing_score') is-invalid @enderror" id="passing_score" name="passing_score" value="{{ old('passing_score', 70) }}" min="0" max="100">
                                    @error('passing_score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="max_attempts" class="form-label">Max Attempts</label>
                                    <input type="number" class="form-control @error('max_attempts') is-invalid @enderror" id="max_attempts" name="max_attempts" value="{{ old('max_attempts', 3) }}" min="1">
                                    @error('max_attempts')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="shuffle_questions" name="shuffle_questions" value="1" {{ old('shuffle_questions') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="shuffle_questions">
                                            Shuffle Questions
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show_results" name="show_results" value="1" {{ old('show_results', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_results">
                                            Show Results After Completion
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('instructor.quizzes.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Quiz</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection