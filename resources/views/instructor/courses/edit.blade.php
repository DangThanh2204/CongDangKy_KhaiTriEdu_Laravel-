@extends('layouts.app')

@section('title', 'Chỉnh sửa khóa học')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Chỉnh sửa khóa học</h3>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('instructor.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Tiêu đề khóa học</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $course->title) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Danh mục khóa học</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Chọn danh mục</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả đầy đủ</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description', $course->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá (VND)</label>
                                <input type="number" name="price" class="form-control" step="1000" min="0" value="{{ old('price', $course->price) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá khuyến mãi (nếu có)</label>
                                <input type="number" name="sale_price" class="form-control" step="1000" min="0" value="{{ old('sale_price', $course->sale_price) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link video (YouTube)</label>
                            <input type="url" name="video_url" class="form-control" value="{{ old('video_url', $course->video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File PDF tài liệu (tùy chọn)</label>
                            <input type="file" name="pdf" class="form-control">
                            @if($course->pdf_path)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $course->pdf_path) }}" target="_blank">Xem tệp hiện tại</a>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bài kiểm tra (quiz) cơ bản <small class="text-muted">(tùy chọn)</small></label>
                            <p class="text-muted">Thêm câu hỏi để học viên làm sau khi học xong. Bạn có thể bỏ qua hoặc xóa phần này nếu không muốn có bài kiểm tra.</p>
                            @php
                                $quizMaterial = $course->materials()->where('type', 'quiz')->first();
                                $quizQuestions = $quizMaterial?->metadata['questions'] ?? [];
                            @endphp
                            <div id="quiz-questions">
                                @if(count($quizQuestions) > 0)
                                    @foreach($quizQuestions as $index => $q)
                                        <div class="quiz-question mb-3 border p-3 rounded">
                                            <input type="text" name="quiz_questions[{{ $index }}][question]" class="form-control mb-2" value="{{ $q['question'] ?? '' }}" placeholder="Câu hỏi {{ $index + 1 }}">
                                            <input type="text" name="quiz_questions[{{ $index }}][answer]" class="form-control mb-2" value="{{ $q['answer'] ?? '' }}" placeholder="Đáp án (ví dụ: A)">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-question">Xóa câu hỏi này</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-quiz-question">Thêm câu hỏi</button>
                            @if(count($quizQuestions) > 0)
                                <button type="button" class="btn btn-outline-danger btn-sm mt-2 ms-2" id="remove-all-quiz">Xóa tất cả quiz</button>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mã/nhóm khóa học (để cho phép học lại miễn phí)</label>
                            <input type="text" name="series_key" class="form-control" value="{{ old('series_key', $course->series_key) }}" placeholder="ví dụ: tin-hoc-co-ban">
                            <small class="text-muted">Nếu học viên đã mua khóa khác cùng mã này, họ có thể vào học miễn phí.</small>
                        </div>

                        <!-- Classes (Lớp học) -->
                        <div class="mb-3 card">
                            <div class="card-body">
                                <div id="classes-container">
                                    @foreach($course->classes as $i => $cls)
                                    <div class="class-item mb-3 border p-3 rounded" data-index="{{ $i }}">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h6 class="mb-0">Lớp hiện có #{{ $i+1 }} - {{ $cls->name }}</h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-class">Xóa lớp</button>
                                        </div>
                                        <input type="hidden" name="classes[{{ $i }}][id]" value="{{ $cls->id }}">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Tên lớp</label>
                                                <input type="text" name="classes[{{ $i }}][name]" class="form-control" value="{{ old('classes.'.$i.'.name', $cls->name) }}">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Giảng viên</label>
                                                <select name="classes[{{ $i }}][instructor_id]" class="form-select">
                                                    <option value="">-- Chọn giảng viên --</option>
                                                    @foreach(\App\Models\User::whereIn('role', ['instructor'])->get(['id','fullname','email']) as $instructor)
                                                        <option value="{{ $instructor->id }}" {{ (old('classes.'.$i.'.instructor_id', $cls->instructor_id) == $instructor->id) ? 'selected' : '' }}>{{ $instructor->fullname }} ({{ $instructor->email }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Ngày bắt đầu</label>
                                                <input type="date" name="classes[{{ $i }}][start_date]" class="form-control" value="{{ old('classes.'.$i.'.start_date', $cls->start_date?->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Ngày kết thúc</label>
                                                <input type="date" name="classes[{{ $i }}][end_date]" class="form-control" value="{{ old('classes.'.$i.'.end_date', $cls->end_date?->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <label class="form-label">Lịch (mô tả)</label>
                                                <textarea name="classes[{{ $i }}][schedule]" class="form-control" rows="2">{{ old('classes.'.$i.'.schedule', $cls->schedule) }}</textarea>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <label class="form-label">Thông tin buổi học (link/địa điểm)</label>
                                                <textarea name="classes[{{ $i }}][meeting_info]" class="form-control" rows="2">{{ old('classes.'.$i.'.meeting_info', $cls->meeting_info) }}</textarea>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Số lượng tối đa</label>
                                                <input type="number" name="classes[{{ $i }}][max_students]" class="form-control" value="{{ old('classes.'.$i.'.max_students', $cls->max_students) }}" min="0">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Giá lớp (tùy chọn)</label>
                                                <input type="number" name="classes[{{ $i }}][price_override]" class="form-control" value="{{ old('classes.'.$i.'.price_override', $cls->price_override) }}" min="0" step="1000">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Trạng thái</label>
                                                <select name="classes[{{ $i }}][status]" class="form-select">
                                                    <option value="active" {{ (old('classes.'.$i.'.status', $cls->status) == 'active') ? 'selected' : '' }}>Đang mở</option>
                                                    <option value="draft" {{ (old('classes.'.$i.'.status', $cls->status) == 'draft') ? 'selected' : '' }}>Bản nháp</option>
                                                    <option value="closed" {{ (old('classes.'.$i.'.status', $cls->status) == 'closed') ? 'selected' : '' }}>Đã đóng</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-class-btn">Thêm lớp mới</button>
                            </div>
                        </div>

                        <input type="hidden" name="status" value="draft">

                        <!-- Status is managed by admin; instructor edits save as draft for admin review -->

                        <div class="alert alert-info">
                            <strong>Quản lý bài kiểm tra:</strong> Bạn có thể quản lý bài kiểm tra chi tiết hơn tại
                            <a href="{{ route('instructor.courses.quiz.index', $course) }}" class="alert-link">trang quản lý bài kiểm tra riêng</a>.
                        </div>

                        <button class="btn btn-primary">Cập nhật khóa học</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quizContainer = document.getElementById('quiz-questions');
        const addButton = document.getElementById('add-quiz-question');
        const removeAllButton = document.getElementById('remove-all-quiz');
        let questionCount = quizContainer.querySelectorAll('.quiz-question').length;

        addButton.addEventListener('click', function () {
            const wrapper = document.createElement('div');
            wrapper.className = 'quiz-question mb-3 border p-3 rounded';

            const questionInput = document.createElement('input');
            questionInput.type = 'text';
            questionInput.name = `quiz_questions[${questionCount}][question]`;
            questionInput.className = 'form-control mb-2';
            questionInput.placeholder = `Câu hỏi ${questionCount + 1}`;

            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.name = `quiz_questions[${questionCount}][answer]`;
            answerInput.className = 'form-control mb-2';
            answerInput.placeholder = 'Đáp án (ví dụ: A)';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-danger btn-sm remove-question';
            removeBtn.textContent = 'Xóa câu hỏi này';

            wrapper.appendChild(questionInput);
            wrapper.appendChild(answerInput);
            wrapper.appendChild(removeBtn);

            quizContainer.appendChild(wrapper);
            questionCount += 1;
            updateRemoveAllButton();
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-question')) {
                e.target.closest('.quiz-question').remove();
                questionCount -= 1;
                updateRemoveAllButton();
                renumberQuestions();
            }
        });

        if (removeAllButton) {
            removeAllButton.addEventListener('click', function () {
                quizContainer.innerHTML = '';
                questionCount = 0;
                updateRemoveAllButton();
            });
        }

        function updateRemoveAllButton() {
            if (removeAllButton) {
                removeAllButton.style.display = questionCount > 0 ? 'inline-block' : 'none';
            }
        }

        function renumberQuestions() {
            const questions = quizContainer.querySelectorAll('.quiz-question');
            questions.forEach((question, index) => {
                const questionInput = question.querySelector('input[name*="[question]"]');
                const answerInput = question.querySelector('input[name*="[answer]"]');
                questionInput.name = `quiz_questions[${index}][question]`;
                answerInput.name = `quiz_questions[${index}][answer]`;
                questionInput.placeholder = `Câu hỏi ${index + 1}`;
            });
        }

        // Toggle capacity field visibility
        const learningTypeSelect = document.querySelector('select[name="learning_type"]');
        const capacityWrapper = document.getElementById('capacity_wrapper');

        function toggleCapacity() {
            if (!learningTypeSelect) return;
            if (!capacityWrapper) return;
            if (learningTypeSelect.value === 'offline') {
                capacityWrapper.style.display = '';
            } else {
                capacityWrapper.style.display = 'none';
            }
        }

        if (learningTypeSelect) {
            learningTypeSelect.addEventListener('change', toggleCapacity);
            toggleCapacity();
        }

        updateRemoveAllButton();
        
        // Classes management for instructor edit view
        const classesContainer = document.getElementById('classes-container');
        const addClassBtn = document.getElementById('add-class-btn');
        let classIndex = classesContainer ? classesContainer.querySelectorAll('.class-item').length : 0;

        const instructorsOptions = `@foreach(\App\Models\User::whereIn('role', ['instructor'])->get(['id','fullname','email']) as $instructor)<option value="{{ $instructor->id }}">{{ $instructor->fullname }} ({{ $instructor->email }})</option>@endforeach`;

        function createClassForm(index) {
            const wrapper = document.createElement('div');
            wrapper.className = 'class-item mb-3 border p-3 rounded';
            wrapper.dataset.index = index;

            wrapper.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Lớp mới #${index + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-class">Xóa lớp</button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Tên lớp</label>
                        <input type="text" name="classes[${index}][name]" class="form-control" placeholder="Ví dụ: Lớp Sáng Thứ 2">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Giảng viên</label>
                        <select name="classes[${index}][instructor_id]" class="form-select">
                            <option value="">-- Chọn giảng viên --</option>
                            ${instructorsOptions}
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Ngày bắt đầu</label>
                        <input type="date" name="classes[${index}][start_date]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Ngày kết thúc</label>
                        <input type="date" name="classes[${index}][end_date]" class="form-control">
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Lịch (mô tả)</label>
                        <textarea name="classes[${index}][schedule]" class="form-control" rows="2" placeholder="Ví dụ: Thứ 2,5 - 18:00-20:00"></textarea>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="form-label">Thông tin buổi học (link/địa điểm)</label>
                        <textarea name="classes[${index}][meeting_info]" class="form-control" rows="2" placeholder="Zoom link hoặc địa chỉ"></textarea>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Số lượng tối đa</label>
                        <input type="number" name="classes[${index}][max_students]" class="form-control" min="0">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Giá lớp (tùy chọn)</label>
                        <input type="number" name="classes[${index}][price_override]" class="form-control" min="0" step="1000" placeholder="Nếu khác giá khóa học">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Trạng thái</label>
                        <select name="classes[${index}][status]" class="form-select">
                            <option value="active">Đang mở</option>
                            <option value="draft">Bản nháp</option>
                            <option value="closed">Đã đóng</option>
                        </select>
                    </div>
                </div>
            `;

            return wrapper;
        }

        if (addClassBtn) {
            addClassBtn.addEventListener('click', function() {
                const form = createClassForm(classIndex);
                classesContainer.appendChild(form);
                classIndex++;
            });
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList && e.target.classList.contains('remove-class')) {
                const item = e.target.closest('.class-item');
                const idInput = item.querySelector('input[name$="[id]"]');
                if (idInput) {
                    const namePrefix = idInput.name.replace(/\[id\]$/, '');
                    let hidden = item.querySelector('input[name="' + namePrefix + '[_destroy]"]');
                    if (!hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = namePrefix + '[_destroy]';
                        hidden.value = '1';
                        item.appendChild(hidden);
                    } else {
                        hidden.value = '1';
                    }
                    item.style.display = 'none';
                } else {
                    item.remove();
                }
            }
        });
    });
</script>
@endpush
