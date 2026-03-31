@extends('layouts.app')

@section('title', 'Tạo khóa học mới')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Tạo khóa học mới</h3>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('instructor.courses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
    <label class="form-label">Hình thức đào tạo</label>
    <select name="learning_type" class="form-select">
        <option value="online" {{ old('learning_type', 'online') === 'online' ? 'selected' : '' }}>Online</option>
        <option value="offline" {{ old('learning_type') === 'offline' ? 'selected' : '' }}>Offline tại trung tâm</option>
    </select>
    <small class="text-muted">Khóa online sẽ được ghi danh tự động. Với offline, hãy tạo đợt học rõ lịch, giáo viên và sức chứa.</small>
</div>

                        <div class="mb-3" id="capacity_wrapper" style="display: none;">
                            <label class="form-label">Số lượng học viên (capacity)</label>
                            <input type="number" name="capacity" class="form-control" min="0" step="1" value="{{ old('capacity') }}" placeholder="Ví dụ: 50">
                            <small class="text-muted">Chỉ áp dụng cho khóa học Offline. Để trống nếu không giới hạn.</small>
                        </div>

                        <!-- Classes (Lớp học) - instructor can add classes when creating course -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div id="classes-container"></div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-class-btn">
                                    <i class="fas fa-plus me-1"></i>Thêm lớp mới
                                </button>
                                <div class="form-text mt-2">Mỗi lớp có thể có lịch riêng, địa điểm/link họp, số lượng tối đa và trạng thái.</div>
                            </div>
                        </div>

                        <!-- Hidden instructors template for JS -->
                        <select id="instructors-options-template" style="display:none;">
                            <option value="">-- Chọn giảng viên --</option>
                            @foreach(
                                \App\Models\User::whereIn('role', ['instructor'])->get(['id','fullname','email'])
                                as $instructor
                            )
                                <option value="{{ $instructor->id }}">{{ $instructor->fullname }} ({{ $instructor->email }})</option>
                            @endforeach
                        </select>

                        <div class="mb-3">
                            <label class="form-label">Thông báo cho học viên</label>
                            <textarea name="announcement" class="form-control" rows="3" placeholder="Thông báo quan trọng về khóa học">{{ old('announcement') }}</textarea>
                            <small class="text-muted">Thông báo sẽ hiển thị cho học viên đã đăng ký</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File PDF tài liệu (tùy chọn)</label>
                            <input type="file" name="pdf" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bài kiểm tra (quiz) cơ bản <small class="text-muted">(tùy chọn)</small></label>
                            <p class="text-muted">Thêm câu hỏi để học viên làm sau khi học xong. Bạn có thể bỏ qua phần này nếu không muốn có bài kiểm tra.</p>
                            <div id="quiz-questions">
                                <!-- Quiz questions will be added here -->
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-quiz-question">Thêm câu hỏi</button>
                            <button type="button" class="btn btn-outline-danger btn-sm mt-2 ms-2" id="remove-all-quiz" style="display: none;">Xóa tất cả quiz</button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mã/nhóm khóa học (để cho phép học lại miễn phí)</label>
                            <input type="text" name="series_key" class="form-control" value="{{ old('series_key') }}" placeholder="ví dụ: tin-hoc-co-ban">
                            <small class="text-muted">Nếu học viên đã mua khóa khác cùng mã này, họ có thể vào học miễn phí.</small>
                        </div>

                        <input type="hidden" name="status" value="draft">

                        <!-- Status is managed by admin; instructor submissions are saved as draft -->

                        <div class="alert alert-info">
                            <strong>Lưu ý:</strong> Sau khi tạo khóa học, bạn có thể quản lý bài kiểm tra chi tiết hơn tại trang quản lý bài kiểm tra riêng.
                        </div>

                        <button class="btn btn-primary">Lưu khóa học</button>
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
        let questionCount = 0;

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

        removeAllButton.addEventListener('click', function () {
            quizContainer.innerHTML = '';
            questionCount = 0;
            updateRemoveAllButton();
        });

        function updateRemoveAllButton() {
            removeAllButton.style.display = questionCount > 0 ? 'inline-block' : 'none';
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
        // Classes management (instructor create)
        const classesContainer = document.getElementById('classes-container');
        const addClassBtn = document.getElementById('add-class-btn');
        let classIndex = 0;
        const instructorsOptions = document.getElementById('instructors-options-template') ? document.getElementById('instructors-options-template').innerHTML : '';

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
                        <textarea name="classes[${index}][meeting_info]" class="form-control" rows="2" placeholder="Zoom link hoặc địa điểm"></textarea>
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
                e.target.closest('.class-item').remove();
            }
        });
    });
</script>
@endpush
