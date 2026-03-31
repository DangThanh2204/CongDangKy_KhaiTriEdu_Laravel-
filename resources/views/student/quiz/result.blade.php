@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Kết quả bài kiểm tra</h4>
                            <small>{{ $attempt->quiz->title }}</small>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0">{{ $attempt->score }}/{{ $attempt->total_questions }}</h3>
                            <small>{{ number_format($attempt->percentage, 1) }}%</small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Score Overview -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-primary">{{ $attempt->score }}</h5>
                                    <small class="text-muted">Câu đúng</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-danger">{{ $attempt->total_questions - $attempt->score }}</h5>
                                    <small class="text-muted">Câu sai</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-info">{{ $attempt->time_taken ?? 'N/A' }}</h5>
                                    <small class="text-muted">Thời gian</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Status -->
                    <div class="alert {{ $attempt->percentage >= 70 ? 'alert-success' : ($attempt->percentage >= 50 ? 'alert-warning' : 'alert-danger') }} mb-4">
                        <h5 class="alert-heading">
                            @if($attempt->percentage >= 70)
                                <i class="fas fa-trophy"></i> Xuất sắc!
                            @elseif($attempt->percentage >= 50)
                                <i class="fas fa-check-circle"></i> Khá tốt!
                            @else
                                <i class="fas fa-exclamation-triangle"></i> Cần cố gắng hơn!
                            @endif
                        </h5>
                        <p class="mb-0">
                            @if($attempt->percentage >= 70)
                                Bạn đã hoàn thành bài kiểm tra rất tốt! Hãy tiếp tục phát huy.
                            @elseif($attempt->percentage >= 50)
                                Bạn đã vượt qua bài kiểm tra. Hãy ôn tập thêm để cải thiện kết quả.
                            @else
                                Bạn cần ôn tập kỹ hơn và thử lại bài kiểm tra.
                            @endif
                        </p>
                    </div>

                    <!-- Detailed Results -->
                    <h5 class="mb-3">Chi tiết kết quả:</h5>
                    <div class="accordion" id="resultsAccordion">
                        @foreach($attempt->answers as $index => $answer)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button {{ $answer->is_correct ? 'bg-success text-white' : 'bg-danger text-white' }} collapsed"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                                        aria-expanded="false" aria-controls="collapse{{ $index }}">
                                    <div class="d-flex justify-content-between w-100 me-3">
                                        <span>
                                            <i class="fas {{ $answer->is_correct ? 'fa-check' : 'fa-times' }}"></i>
                                            Câu {{ $index + 1 }}: {{ Str::limit($answer->question->question_text, 50) }}
                                        </span>
                                        <span class="badge {{ $answer->is_correct ? 'bg-success' : 'bg-danger' }}">
                                            {{ $answer->is_correct ? 'Đúng' : 'Sai' }}
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse"
                                 aria-labelledby="heading{{ $index }}" data-bs-parent="#resultsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Câu hỏi:</h6>
                                            <p>{{ $answer->question->question_text }}</p>

                                            <h6>Câu trả lời của bạn:</h6>
                                            <p class="text-primary">
                                                @if($answer->question->question_type === 'multiple_choice')
                                                    {{ $answer->question->getOptionsArray()[$answer->answer_text] ?? $answer->answer_text }}
                                                @elseif($answer->question->question_type === 'true_false')
                                                    {{ $answer->answer_text == '0' ? 'Đúng' : 'Sai' }}
                                                @else
                                                    {{ $answer->answer_text }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Đáp án đúng:</h6>
                                            <p class="text-success">
                                                @if($answer->question->question_type === 'multiple_choice')
                                                    {{ $answer->question->getOptionsArray()[$answer->question->correct_answer] }}
                                                @elseif($answer->question->question_type === 'true_false')
                                                    {{ $answer->question->correct_answer == '0' ? 'Đúng' : 'Sai' }}
                                                @else
                                                    {{ $answer->question->correct_answer }}
                                                @endif
                                            </p>

                                            @if(!$answer->is_correct)
                                                <h6>Giải thích:</h6>
                                                <p class="text-muted">{{ $answer->question->explanation ?? 'Không có giải thích' }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <a href="{{ route('student.quizzes.index') }}" class="btn btn-primary me-2">
                            <i class="fas fa-list"></i> Danh sách bài kiểm tra
                        </a>
                        @if($attempt->percentage < 70)
                            <a href="{{ route('quizzes.take', $attempt->quiz_id) }}" class="btn btn-warning">
                                <i class="fas fa-redo"></i> Làm lại
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-print functionality
function printResults() {
    window.print();
}

// Share results functionality
function shareResults() {
    if (navigator.share) {
        navigator.share({
            title: 'Kết quả bài kiểm tra',
            text: `Tôi đã hoàn thành bài kiểm tra "${{ $attempt->quiz->title }}" với điểm số ${{ $attempt->score }}/${{ $attempt->total_questions }} (${{ number_format($attempt->percentage, 1) }}%)`,
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        const text = `Tôi đã hoàn thành bài kiểm tra "${{ $attempt->quiz->title }}" với điểm số ${{ $attempt->score }}/${{ $attempt->total_questions }} (${{ number_format($attempt->percentage, 1) }}%)`;
        navigator.clipboard.writeText(text).then(() => {
            alert('Đã sao chép kết quả vào clipboard!');
        });
    }
}
</script>

<style>
.accordion-button:not(.collapsed) {
    background-color: #f8f9fa !important;
    color: #212529 !important;
}

.accordion-button:focus {
    box-shadow: none;
}

.badge {
    font-size: 0.8em;
}
</style>
@endsection