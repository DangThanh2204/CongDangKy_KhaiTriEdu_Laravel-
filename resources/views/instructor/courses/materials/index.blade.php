@extends('layouts.app')

@section('title', 'Quản lý nội dung: ' . $course->title)

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1"><i class="fas fa-list me-2"></i>Quản lý nội dung khóa học</h2>
            <div class="d-flex flex-wrap gap-2 small text-muted">
                <span>{{ $course->title }}</span>
                <span class="badge bg-light text-dark border">Tổng thời lượng ước tính: {{ $course->duration_label }}</span>
                <span class="badge bg-light text-dark border">{{ $course->lessons_count }} nội dung</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMaterialModal"><i class="fas fa-plus me-1"></i>Thêm nội dung</button>
            <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($materials->isNotEmpty())
                <div id="materials-list">
                    @foreach($materials as $material)
                        <div class="material-item border rounded p-3 mb-3" data-id="{{ $material->id }}" draggable="true">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="d-flex align-items-start gap-3 flex-grow-1">
                                    <div class="drag-handle pt-1" style="cursor: move;"><i class="fas fa-grip-vertical text-muted"></i></div>
                                    <span class="badge bg-primary">{{ $material->order }}</span>
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="badge bg-light text-dark border">{{ $material->type_label }}</span>
                                            <span class="badge bg-secondary">{{ $material->module?->title ?? 'Nội dung chung' }}</span>
                                            <span class="badge bg-light text-dark border"><i class="fas fa-clock me-1"></i>{{ $material->estimated_duration_label }}</span>
                                            @if($material->isMeeting())
                                                <span class="badge {{ $material->meeting_status_badge_class }}">{{ $material->meeting_status_label }}</span>
                                            @endif
                                        </div>
                                        <strong>{{ $material->title ?: 'Chưa có tiêu đề' }}</strong>
                                        @if($material->content)
                                            <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit($material->content, 140) }}</div>
                                        @endif
                                        @if($material->type === 'pdf' && $material->document_original_name)
                                            <div class="small text-muted mt-1"><i class="fas fa-paperclip me-1"></i>{{ $material->document_original_name }}</div>
                                        @endif
                                        @if($material->isMeeting())
                                            <div class="small text-muted mt-1"><i class="fas fa-calendar-alt me-1"></i>{{ $material->meeting_window_label ?? 'Chưa cấu hình giờ mở phòng học' }}</div>
                                            @if($material->meeting_note)
                                                <div class="small text-muted mt-1"><i class="fas fa-circle-info me-1"></i>{{ $material->meeting_note }}</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    @if(in_array($material->type, ['video', 'meeting'], true) && $material->external_url)
                                        <a href="{{ $material->external_url }}" target="_blank" class="btn btn-sm {{ $material->isMeeting() ? 'btn-outline-success' : 'btn-outline-primary' }}" title="{{ $material->isMeeting() ? 'Mở phòng học Meet' : 'Mở video' }}">
                                            <i class="fas {{ $material->isMeeting() ? 'fa-video' : 'fa-external-link-alt' }}"></i>
                                        </a>
                                    @endif
                                    @if($material->type === 'pdf' && $material->file_path)
                                        <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="{{ $material->document_action_label }}">
                                            <i class="{{ $material->document_icon_class }}"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="btn btn-primary" id="save-order"><i class="fas fa-save me-1"></i>Lưu thứ tự</button>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Chưa có nội dung nào</h4>
                    <p class="text-muted mb-0">Hãy thêm video YouTube, tài liệu PDF / Word, buổi học Google Meet hoặc bài tập để học viên có lộ trình học rõ ràng theo từng module.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm nội dung mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="add-material-form">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Loại nội dung</label>
                            <select name="type" class="form-select" id="material-type" required>
                                <option value="video">Video YouTube</option>
                                <option value="pdf">Tài liệu PDF / Word</option>
                                <option value="meeting">Buổi học Google Meet</option>
                                <option value="assignment">Bài tập</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Module</label>
                            <select name="course_module_id" class="form-select">
                                <option value="">Nội dung chung (chưa gắn module)</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea name="content" class="form-control" rows="3" placeholder="Mô tả ngắn để học viên hiểu nội dung sẽ học"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ước lượng thời gian (phút)</label>
                            <input type="number" name="estimated_duration_minutes" class="form-control" min="1" placeholder="Để trống để hệ thống tự tính">
                            <div class="form-text" id="duration-help-text">Video chưa đọc được độ dài tự động từ link nên nếu để trống hệ thống sẽ dùng mặc định 15 phút.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded h-100 p-3 bg-light-subtle small text-muted">
                                <div class="fw-semibold text-dark mb-2">Cách hệ thống ước lượng</div>
                                <div id="duration-estimation-notes">Video: mặc định 15 phút nếu bạn không nhập tay.</div>
                            </div>
                        </div>
                        <div class="col-12 material-fields" id="video-fields">
                            <label class="form-label">URL video YouTube</label>
                            <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        <div class="col-12 material-fields d-none" id="pdf-fields">
                            <label class="form-label">File PDF / Word</label>
                            <input type="file" name="pdf_file" class="form-control" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                            <div class="form-text">PDF sẽ được ước lượng theo số trang. File Word ưu tiên đếm số từ nếu đọc được.</div>
                        </div>
                        <div class="col-12 material-fields d-none" id="meeting-fields">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Link Google Meet</label>
                                    <input type="url" name="meeting_url" class="form-control" placeholder="https://meet.google.com/...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Giờ mở phòng học</label>
                                    <input type="datetime-local" name="meeting_starts_at" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Giờ kết thúc</label>
                                    <input type="datetime-local" name="meeting_ends_at" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Ghi chú cho học viên</label>
                                    <textarea name="meeting_note" class="form-control" rows="3" placeholder="Ví dụ: vào trước 10 phút, bật camera, chuẩn bị tai nghe..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 material-fields d-none" id="assignment-fields">
                            <label class="form-label">Nội dung bài tập</label>
                            <textarea name="assignment_content" class="form-control" rows="5" placeholder="Mô tả yêu cầu bài tập, đầu ra mong muốn, tiêu chí hoàn thành..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm nội dung</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const materialType = document.getElementById('material-type');
    const materialsList = document.getElementById('materials-list');
    const saveOrderButton = document.getElementById('save-order');
    const durationHelpText = document.getElementById('duration-help-text');
    const durationNotes = document.getElementById('duration-estimation-notes');
    let draggedElement = null;

    const applyMaterialTypeState = function () {
        if (!materialType) {
            return;
        }

        document.querySelectorAll('.material-fields').forEach((field) => field.classList.add('d-none'));
        const activeFields = document.getElementById(materialType.value + '-fields');
        if (activeFields) {
            activeFields.classList.remove('d-none');
        }

        if (materialType.value === 'video') {
            durationHelpText.textContent = 'Video chưa đọc được độ dài tự động từ link nên nếu để trống hệ thống sẽ dùng mặc định 15 phút.';
            durationNotes.textContent = 'Video: nếu bạn không nhập tay, hệ thống dùng mặc định 15 phút cho mỗi nội dung video.';
            return;
        }

        if (materialType.value === 'pdf') {
            durationHelpText.textContent = 'Nếu bạn không nhập tay, hệ thống sẽ tự ước lượng theo số trang, số từ hoặc dung lượng file.';
            durationNotes.textContent = 'Tài liệu PDF / Word: ưu tiên đọc số trang hoặc số từ để tính gần đúng thời gian học.';
            return;
        }

        if (materialType.value === 'meeting') {
            durationHelpText.textContent = 'Nếu có giờ mở và giờ kết thúc, hệ thống sẽ tự tính thời lượng buổi học. Nếu không, bạn có thể nhập tay số phút.';
            durationNotes.textContent = 'Google Meet: hệ thống ưu tiên lấy chênh lệch giữa giờ bắt đầu và giờ kết thúc để cộng vào tổng thời lượng khóa học.';
            return;
        }

        durationHelpText.textContent = 'Nếu bạn không nhập tay, hệ thống sẽ ước lượng dựa trên lượng nội dung bài tập.';
        durationNotes.textContent = 'Bài tập: hệ thống ước lượng theo độ dài mô tả bài tập để học viên biết trước thời gian cần dành ra.';
    };

    if (materialType) {
        materialType.addEventListener('change', applyMaterialTypeState);
        applyMaterialTypeState();
    }

    if (materialsList) {
        materialsList.addEventListener('dragstart', function (event) {
            const item = event.target.closest('.material-item');
            if (!item) return;
            draggedElement = item;
            event.dataTransfer.effectAllowed = 'move';
        });

        materialsList.addEventListener('dragover', function (event) {
            event.preventDefault();
            const afterElement = [...materialsList.querySelectorAll('.material-item')].find((element) => {
                const box = element.getBoundingClientRect();
                return event.clientY <= box.top + (box.height / 2);
            });

            if (!draggedElement) return;
            if (!afterElement) {
                materialsList.appendChild(draggedElement);
            } else {
                materialsList.insertBefore(draggedElement, afterElement);
            }
        });
    }

    if (saveOrderButton && materialsList) {
        saveOrderButton.addEventListener('click', function () {
            const materialIds = [...materialsList.querySelectorAll('.material-item')].map((item) => item.dataset.id);
            fetch(`{{ route('instructor.courses.materials.index', $course) }}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ material_ids: materialIds }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        location.reload();
                        return;
                    }

                    alert('Không thể lưu thứ tự nội dung.');
                });
        });
    }

    document.getElementById('add-material-form').addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch(`{{ route('instructor.courses.materials.index', $course) }}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: formData,
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw data;
                }
                return data;
            })
            .then((data) => {
                if (data.success) {
                    location.reload();
                    return;
                }

                alert(data.message || 'Không thể thêm nội dung.');
            })
            .catch((error) => {
                const validationErrors = Object.values(error.errors || {}).flat();
                alert(validationErrors[0] || error.message || 'Không thể thêm nội dung.');
            });
    });
});
</script>
@endsection
