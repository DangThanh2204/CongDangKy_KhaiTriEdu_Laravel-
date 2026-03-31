@extends('layouts.app')

@section('title', 'Tạo bài kiểm tra mới')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Tạo bài kiểm tra mới cho khóa học: {{ $course->title }}</h3>
                    <a href="{{ route('instructor.courses.quiz.index', $course) }}" class="text-muted mb-3 d-block">← Quay lại danh sách bài kiểm tra</a>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('instructor.courses.quiz.store', $course) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tiêu đề bài kiểm tra</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Danh sách câu hỏi</label>
                            <p class="text-muted">Thêm các câu hỏi và đáp án đúng cho bài kiểm tra.</p>
                            <div id="questions-container">
                                <div class="question-item mb-3 border p-3 rounded">
                                    <div class="mb-2">
                                        <label class="form-label">Câu hỏi 1</label>
                                        <input type="text" name="questions[0][question]" class="form-control" placeholder="Nhập câu hỏi..." value="{{ old('questions.0.question') }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Đáp án đúng</label>
                                        <input type="text" name="questions[0][answer]" class="form-control" placeholder="Nhập đáp án đúng..." value="{{ old('questions.0.answer') }}" required>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-question" style="display: none;">Xóa câu hỏi</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="add-question">Thêm câu hỏi</button>
                        </div>

                        <button type="submit" class="btn btn-primary">Lưu bài kiểm tra</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let questionCount = 1;

        document.getElementById('add-question').addEventListener('click', function () {
            const container = document.getElementById('questions-container');
            const questionItem = document.createElement('div');
            questionItem.className = 'question-item mb-3 border p-3 rounded';
            questionItem.innerHTML = `
                <div class="mb-2">
                    <label class="form-label">Câu hỏi ${questionCount + 1}</label>
                    <input type="text" name="questions[${questionCount}][question]" class="form-control" placeholder="Nhập câu hỏi..." required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Đáp án đúng</label>
                    <input type="text" name="questions[${questionCount}][answer]" class="form-control" placeholder="Nhập đáp án đúng..." required>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm remove-question">Xóa câu hỏi</button>
            `;
            container.appendChild(questionItem);
            questionCount++;

            // Show remove buttons for all questions if more than 1
            updateRemoveButtons();
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-question')) {
                e.target.closest('.question-item').remove();
                questionCount--;
                updateRemoveButtons();
                renumberQuestions();
            }
        });

        function updateRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-question');
            removeButtons.forEach(btn => {
                btn.style.display = removeButtons.length > 1 ? 'block' : 'none';
            });
        }

        function renumberQuestions() {
            const questionItems = document.querySelectorAll('.question-item');
            questionItems.forEach((item, index) => {
                const label = item.querySelector('.form-label');
                label.textContent = `Câu hỏi ${index + 1}`;
                const questionInput = item.querySelector('input[name*="[question]"]');
                const answerInput = item.querySelector('input[name*="[answer]"]');
                questionInput.name = `questions[${index}][question]`;
                answerInput.name = `questions[${index}][answer]`;
            });
        }

        updateRemoveButtons();
    });
</script>
@endpush
@endsection