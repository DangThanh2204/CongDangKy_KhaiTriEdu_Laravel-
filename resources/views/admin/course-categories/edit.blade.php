@extends('layouts.admin')

@section('title', 'Chỉnh sửa nhóm ngành')
@section('page-title', 'Chỉnh sửa nhóm ngành')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa nhóm ngành: <span class="text-primary">{{ $courseCategory->name }}</span></h5></div>
    <div class="card-body">
        <form action="{{ route('admin.course-categories.update', $courseCategory) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card mb-4"><div class="card-header bg-light py-2"><h6 class="card-title mb-0">Thông tin cơ bản</h6></div><div class="card-body"><div class="mb-3"><label for="name" class="form-label fw-bold">Tên nhóm ngành</label><input type="text" class="form-control" id="name" name="name" value="{{ old('name', $courseCategory->name) }}" required></div><div class="mb-3"><label for="description" class="form-label fw-bold">Mô tả</label><textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $courseCategory->description) }}</textarea></div><div class="mb-0"><label for="parent_id" class="form-label fw-bold">Nhóm ngành cha</label><select class="form-select" id="parent_id" name="parent_id"><option value="">-- Không có --</option>@foreach($parentCategories as $parent)<option value="{{ $parent->id }}" {{ old('parent_id', $courseCategory->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>@endforeach</select></div></div></div>
                    <div class="card"><div class="card-header bg-light py-2"><h6 class="card-title mb-0">Hiển thị</h6></div><div class="card-body"><div class="row g-3"><div class="col-md-6"><label for="icon" class="form-label fw-bold">Icon</label><input type="text" class="form-control" id="icon" name="icon" value="{{ old('icon', $courseCategory->icon) }}"></div><div class="col-md-3"><label for="order" class="form-label fw-bold">Thứ tự</label><input type="number" class="form-control" id="order" name="order" value="{{ old('order', $courseCategory->order) }}" min="0"></div><div class="col-md-3"><label for="color" class="form-label fw-bold">Màu sắc</label><input type="color" class="form-control form-control-color w-100" id="color" name="color" value="{{ old('color', $courseCategory->color) }}"></div></div></div></div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-4"><div class="card-header bg-light py-2"><h6 class="card-title mb-0">Thông tin</h6></div><div class="card-body small text-muted d-grid gap-2"><div><strong>Slug:</strong> /{{ $courseCategory->slug }}</div><div><strong>Số khóa học:</strong> {{ $courseCategory->courses_count }}</div><div><strong>Ngày tạo:</strong> {{ $courseCategory->created_at->format('d/m/Y H:i') }}</div><div><strong>Cập nhật:</strong> {{ $courseCategory->updated_at->format('d/m/Y H:i') }}</div></div></div>
                    <div class="card mb-4"><div class="card-header bg-light py-2"><h6 class="card-title mb-0">Trạng thái</h6></div><div class="card-body"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $courseCategory->is_active) ? 'checked' : '' }}><label class="form-check-label" for="is_active">Hiển thị nhóm ngành trên website</label></div></div></div>
                    @if($courseCategory->courses_count == 0)
                        <div class="card border-danger"><div class="card-body"><div class="text-danger fw-semibold mb-2">Vùng nguy hiểm</div><form action="{{ route('admin.course-categories.destroy', $courseCategory) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa nhóm ngành {{ $courseCategory->name }}?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger w-100"><i class="fas fa-trash me-2"></i>Xóa nhóm ngành</button></form></div></div>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4"><a href="{{ route('admin.course-categories.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại</a><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Cập nhật nhóm ngành</button></div>
        </form>
    </div>
</div>
@endsection
