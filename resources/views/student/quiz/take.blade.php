@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $quiz->title }}</h4>
                            <small>{{ $quiz->course->title }}</small>
                        </div>
                        <div class="text-end">
                            <div id="timer" class="h5 mb-0 text-warning">00:00:00</div>
                            <small>Câu hỏi: {{ $questions->count() }}</small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form id="quiz-form" method="POST" action="{{ route('quizzes.complete', session('attempt_id')) }}">
                        @csrf
                        @foreach($questions as $index => $question)
                        <div class="question-card mb-4 p-3 border rounded" data-question-id="{{ $question->id }}">
                            <h5 class="question-title mb-3">
                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                {{ $question->question_text }}
                            </h5>

                            <div class="question-options">
                                @if($question->question_type === 'multiple_choice')
                                    @foreach($question->getOptionsArray() as $key => $option)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio"
                                               name="answer[{{ $question->id }}]" value="{{ $key }}"
                                               id="q{{ $question->id }}_{{ $key }}">
                                        <label class="form-check-label" for="q{{ $question->id }}_{{ $key }}">
                                            {{ $option }}
                                        </label>
                                    </div>
                                    @endforeach
                                @elseif($question->question_type === 'true_false')
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio"
                                               name="answer[{{ $question->id }}]" value="0"
                                               id="q{{ $question->id }}_true">
                                        <label class="form-check-label" for="q{{ $question->id }}_true">
                                            Đúng
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio"
                                               name="answer[{{ $question->id }}]" value="1"
                                               id="q{{ $question->id }}_false">
                                        <label class="form-check-label" for="q{{ $question->id }}_false">
                                            Sai
                                        </label>
                                    </div>
                                @else
                                    <textarea class="form-control" name="answer[{{ $question->id }}]"
                                              placeholder="Nhập câu trả lời của bạn..." rows="3"></textarea>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </form>
                </div>

                <div class="card-footer text-end">
                    <button type="button" class="btn btn-success btn-lg" onclick="submitQuiz()">
                        <i class="fas fa-paper-plane"></i> Nộp bài
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-save answers every 30 seconds
    setInterval(function() {
        saveCurrentAnswers();
    }, 30000);

    // Timer functionality
    let timeLimit = {{ $quiz->time_limit * 60 ?? 0 }};
    if (timeLimit > 0) {
        startTimer(timeLimit);
    }

    // Load saved answers
    loadSavedAnswers();
});

function saveCurrentAnswers() {
    const formData = new FormData(document.getElementById('quiz-form'));

    fetch('{{ route("quizzes.save-answer", session("attempt_id")) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Answers saved');
    })
    .catch(error => {
        console.error('Error saving answers:', error);
    });
}

function loadSavedAnswers() {
    // This would load previously saved answers if any
    // Implementation depends on how you want to handle this
}

function startTimer(duration) {
    let timer = duration;
    const timerElement = document.getElementById('timer');

    const countdown = setInterval(function() {
        const hours = Math.floor(timer / 3600);
        const minutes = Math.floor((timer % 3600) / 60);
        const seconds = timer % 60;

        timerElement.textContent =
            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        if (--timer < 0) {
            clearInterval(countdown);
            submitQuiz();
        }
    }, 1000);
}

function submitQuiz() {
    if (confirm('Bạn có chắc chắn muốn nộp bài?')) {
        document.getElementById('quiz-form').submit();
    }
}
</script>

<style>
.question-card {
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.question-card:hover {
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.question-title {
    color: #2c3e50;
    font-weight: 600;
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

#timer {
    font-family: 'Courier New', monospace;
    font-weight: bold;
}
</style>
@endsection