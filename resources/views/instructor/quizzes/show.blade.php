@extends('layouts.app')

@section('title', 'Quiz Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ $quiz->title }}</h4>
                    <div>
                        <a href="{{ route('instructor.quizzes.questions', $quiz) }}" class="btn btn-warning me-2">
                            <i class="fas fa-list"></i> Manage Questions
                        </a>
                        <a href="{{ route('instructor.quizzes.edit', $quiz) }}" class="btn btn-secondary me-2">
                            <i class="fas fa-edit"></i> Edit Quiz
                        </a>
                        <a href="{{ route('instructor.quizzes.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Back to Quizzes
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Description</h5>
                            <p>{{ $quiz->description ?: 'No description provided.' }}</p>

                            @if($quiz->course)
                                <h5>Course</h5>
                                <p><a href="{{ route('courses.show', $quiz->course) }}">{{ $quiz->course->title }}</a></p>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Quiz Settings</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>Duration:</strong> {{ $quiz->duration }} minutes</li>
                                        <li><strong>Passing Score:</strong> {{ $quiz->passing_score }}%</li>
                                        <li><strong>Max Attempts:</strong> {{ $quiz->max_attempts }}</li>
                                        <li><strong>Status:</strong>
                                            <span class="badge bg-{{ $quiz->is_active ? 'success' : 'secondary' }} ms-1">
                                                {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </li>
                                        <li><strong>Shuffle Questions:</strong> {{ $quiz->shuffle_questions ? 'Yes' : 'No' }}</li>
                                        <li><strong>Show Results:</strong> {{ $quiz->show_results ? 'Yes' : 'No' }}</li>
                                        <li><strong>Created:</strong> {{ $quiz->created_at->format('M d, Y H:i') }}</li>
                                        <li><strong>Last Updated:</strong> {{ $quiz->updated_at->format('M d, Y H:i') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5>Questions ({{ $quiz->questions->count() }})</h5>
                    @if($quiz->questions->count() > 0)
                        <div class="accordion" id="questionsAccordion">
                            @foreach($quiz->questions as $index => $question)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                            Question {{ $index + 1 }}: {{ Str::limit($question->question_text, 100) }}
                                            <span class="badge bg-primary ms-2">{{ ucfirst($question->question_type) }}</span>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#questionsAccordion">
                                        <div class="accordion-body">
                                            <p><strong>Question:</strong> {{ $question->question_text }}</p>

                                            @if($question->question_type === 'multiple_choice')
                                                <strong>Options:</strong>
                                                <ul class="list-unstyled ms-3">
                                                    @foreach($question->options as $key => $option)
                                                        <li>
                                                            <span class="{{ $key === $question->correct_answer ? 'text-success fw-bold' : '' }}">
                                                                {{ chr(65 + $key) }}. {{ $option }}
                                                                @if($key === $question->correct_answer)
                                                                    <i class="fas fa-check text-success ms-1"></i>
                                                                @endif
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @elseif($question->question_type === 'true_false')
                                                <strong>Correct Answer:</strong>
                                                <span class="badge bg-{{ $question->correct_answer ? 'success' : 'danger' }}">
                                                    {{ $question->correct_answer ? 'True' : 'False' }}
                                                </span>
                                            @else
                                                <strong>Correct Answer:</strong> {{ $question->correct_answer }}
                                            @endif

                                            <p><strong>Points:</strong> {{ $question->points }}</p>
                                            <p><strong>Explanation:</strong> {{ $question->explanation ?: 'No explanation provided.' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> This quiz has no questions yet.
                            <a href="{{ route('instructor.quizzes.questions', $quiz) }}" class="alert-link">Add questions now</a>.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection