@extends('layouts.admin')

@section('title', 'Khuyến mãi & voucher')
@section('page-class', 'page-admin-promotions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Khuyến mãi & voucher</h1>
            <p class="text-muted mb-0">Quản lý voucher học viên mới, ưu đãi đăng ký sớm và combo nhiều khóa ngay trong cổng đăng ký.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge text-bg-light border">{{ $discountCodes->count() }} mã hiện có</span>
            <span class="badge text-bg-primary">Early bird {{ ($settings['promotion_early_bird_enabled'] ?? '0') === '1' ? 'bật' : 'tắt' }}</span>
            <span class="badge text-bg-success">Combo {{ ($settings['promotion_combo_enabled'] ?? '0') === '1' ? 'bật' : 'tắt' }}</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-2">Có lỗi cần kiểm tra:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4 align-items-start">
        <div class="col-xl-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h2 class="h5 mb-1">Ưu đãi tự động</h2>
                    <p class="text-muted small mb-0">Áp dụng không cần nhập mã, hệ thống sẽ tự chọn mức giảm phù hợp nhất.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.promotions.settings.update') }}" method="POST" class="row g-4">
                        @csrf
                        @method('PUT')

                        <div class="col-12">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                                    <div>
                                        <h3 class="h6 mb-1">Đăng ký sớm</h3>
                                        <p class="text-muted small mb-0">Giảm tự động cho lớp còn cách ngày khai giảng đủ xa.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="promotion_early_bird_enabled" name="promotion_early_bird_enabled" value="1" {{ ($settings['promotion_early_bird_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="promotion_early_bird_enabled">Bật</label>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Số ngày tối thiểu</label>
                                        <input type="number" name="promotion_early_bird_days_before_start" class="form-control" min="1" max="365" value="{{ old('promotion_early_bird_days_before_start', $settings['promotion_early_bird_days_before_start'] ?? 14) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Kiểu giảm</label>
                                        <select name="promotion_early_bird_discount_type" class="form-select">
                                            <option value="percent" {{ old('promotion_early_bird_discount_type', $settings['promotion_early_bird_discount_type'] ?? 'percent') === 'percent' ? 'selected' : '' }}>Phần trăm</option>
                                            <option value="fixed" {{ old('promotion_early_bird_discount_type', $settings['promotion_early_bird_discount_type'] ?? 'percent') === 'fixed' ? 'selected' : '' }}>Số tiền cố định</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mức giảm</label>
                                        <input type="number" name="promotion_early_bird_discount_value" class="form-control" min="0" step="0.01" value="{{ old('promotion_early_bird_discount_value', $settings['promotion_early_bird_discount_value'] ?? 10) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                                    <div>
                                        <h3 class="h6 mb-1">Combo nhiều khóa</h3>
                                        <p class="text-muted small mb-0">Tự giảm khi học viên đã có khóa cùng lộ trình hoặc cùng nhóm ngành.</p>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="promotion_combo_enabled" name="promotion_combo_enabled" value="1" {{ ($settings['promotion_combo_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="promotion_combo_enabled">Bật</label>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Số khóa đã có</label>
                                        <input type="number" name="promotion_combo_required_courses" class="form-control" min="1" max="20" value="{{ old('promotion_combo_required_courses', $settings['promotion_combo_required_courses'] ?? 1) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Kiểu giảm</label>
                                        <select name="promotion_combo_discount_type" class="form-select">
                                            <option value="percent" {{ old('promotion_combo_discount_type', $settings['promotion_combo_discount_type'] ?? 'percent') === 'percent' ? 'selected' : '' }}>Phần trăm</option>
                                            <option value="fixed" {{ old('promotion_combo_discount_type', $settings['promotion_combo_discount_type'] ?? 'percent') === 'fixed' ? 'selected' : '' }}>Số tiền cố định</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mức giảm</label>
                                        <input type="number" name="promotion_combo_discount_value" class="form-control" min="0" step="0.01" value="{{ old('promotion_combo_discount_value', $settings['promotion_combo_discount_value'] ?? 8) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu cấu hình ưu đãi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h2 class="h5 mb-1">Danh sách voucher</h2>
                    <p class="text-muted small mb-0">Admin tạo voucher riêng cho học viên mới, đợt chiến dịch hoặc ưu đãi công khai.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã</th>
                                    <th>Nội dung</th>
                                    <th>Phạm vi</th>
                                    <th>Lượt dùng</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($discountCodes as $discountCode)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $discountCode->code }}</div>
                                            <div class="small text-muted">{{ $discountCode->value_label }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $discountCode->title }}</div>
                                            <div class="small text-muted">{{ $discountCode->description ?: 'Không có mô tả thêm.' }}</div>
                                            <div class="small text-muted mt-1">
                                                {{ $discountCode->audience_label }}
                                                @if($discountCode->is_public)
                                                    <span class="badge text-bg-light border ms-1">Công khai</span>
                                                @endif
                                                @if($discountCode->can_stack_with_auto)
                                                    <span class="badge text-bg-light border ms-1">Cho phép cộng với ưu đãi tự động</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $discountCode->scope_label }}</div>
                                            <div class="small text-muted">
                                                @if($discountCode->course)
                                                    {{ $discountCode->course->title }}
                                                @elseif($discountCode->category)
                                                    {{ $discountCode->category->name }}
                                                @elseif($discountCode->series_key)
                                                    {{ $discountCode->series_key }}
                                                @else
                                                    Áp dụng toàn hệ thống
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $discountCode->usage_count }}{{ $discountCode->usage_limit ? ' / ' . $discountCode->usage_limit : '' }}</div>
                                            <div class="small text-muted">Mỗi người: {{ $discountCode->per_user_limit ?: 'không giới hạn' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $discountCode->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $discountCode->status_label }}</span>
                                            <div class="small text-muted mt-1">
                                                @if($discountCode->starts_at)
                                                    Từ {{ $discountCode->starts_at->format('d/m/Y H:i') }}
                                                @endif
                                                @if($discountCode->ends_at)
                                                    <div>Đến {{ $discountCode->ends_at->format('d/m/Y H:i') }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                                <form action="{{ route('admin.promotions.codes.toggle', $discountCode) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        {{ $discountCode->is_active ? 'Tắt' : 'Bật' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.promotions.codes.destroy', $discountCode) }}" method="POST" onsubmit="return confirm('Xóa mã giảm giá này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Chưa có voucher nào được tạo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card shadow-sm border-0 sticky-xl-top">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h2 class="h5 mb-1">Tạo voucher mới</h2>
                    <p class="text-muted small mb-0">Dùng cho học viên mới, chiến dịch tuyển sinh hoặc mã công khai hiển thị trên trang khóa học.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.promotions.codes.store') }}" method="POST" class="row g-3">
                        @csrf

                        <div class="col-md-7">
                            <label class="form-label">Tên voucher</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Mã</label>
                            <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code') }}" placeholder="VD: NEW10" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Mô tả ngắn để admin và học viên hiểu mã này dùng trong trường hợp nào.">{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Đối tượng</label>
                            <select name="audience" class="form-select">
                                <option value="all" {{ old('audience') === 'all' ? 'selected' : '' }}>Tất cả học viên</option>
                                <option value="new_student" {{ old('audience') === 'new_student' ? 'selected' : '' }}>Chỉ học viên mới</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phạm vi áp dụng</label>
                            <select name="scope_type" class="form-select" id="discountScopeType">
                                <option value="all" {{ old('scope_type') === 'all' ? 'selected' : '' }}>Toàn hệ thống</option>
                                <option value="course" {{ old('scope_type') === 'course' ? 'selected' : '' }}>Theo khóa học</option>
                                <option value="category" {{ old('scope_type') === 'category' ? 'selected' : '' }}>Theo nhóm ngành</option>
                                <option value="series" {{ old('scope_type') === 'series' ? 'selected' : '' }}>Theo lộ trình / series</option>
                            </select>
                        </div>
                        <div class="col-12 d-none" data-scope-target="course">
                            <label class="form-label">Khóa học áp dụng</label>
                            <select name="course_id" class="form-select">
                                <option value="">Chọn khóa học</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-none" data-scope-target="category">
                            <label class="form-label">Nhóm ngành áp dụng</label>
                            <select name="category_id" class="form-select">
                                <option value="">Chọn nhóm ngành</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-none" data-scope-target="series">
                            <label class="form-label">Series / lộ trình</label>
                            <input type="text" name="series_key" class="form-control" value="{{ old('series_key') }}" placeholder="Ví dụ: tin-hoc-van-phong">
                            <div class="form-text">Nếu khóa học có cùng series_key thì voucher sẽ áp dụng được.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kiểu giảm</label>
                            <select name="value_type" class="form-select">
                                <option value="percent" {{ old('value_type') === 'percent' ? 'selected' : '' }}>Phần trăm</option>
                                <option value="fixed" {{ old('value_type') === 'fixed' ? 'selected' : '' }}>Số tiền cố định</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mức giảm</label>
                            <input type="number" name="value" class="form-control" min="0.01" step="0.01" value="{{ old('value') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Đơn tối thiểu</label>
                            <input type="number" name="min_order_amount" class="form-control" min="0" step="0.01" value="{{ old('min_order_amount') }}" placeholder="Không bắt buộc">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới hạn tổng lượt dùng</label>
                            <input type="number" name="usage_limit" class="form-control" min="1" value="{{ old('usage_limit') }}" placeholder="Không giới hạn">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới hạn mỗi học viên</label>
                            <input type="number" name="per_user_limit" class="form-control" min="1" value="{{ old('per_user_limit', 1) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bắt đầu áp dụng</label>
                            <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kết thúc áp dụng</label>
                            <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}">
                        </div>
                        <div class="col-12 d-grid gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="can_stack_with_auto" name="can_stack_with_auto" {{ old('can_stack_with_auto') ? 'checked' : '' }}>
                                <label class="form-check-label" for="can_stack_with_auto">Cho phép cộng thêm với ưu đãi tự động</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_public" name="is_public" {{ old('is_public') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public">Hiển thị công khai để học viên nhìn thấy mã trên trang khóa học</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Bật ngay sau khi tạo</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-ticket me-2"></i>Tạo voucher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scopeSelect = document.getElementById('discountScopeType');
        const scopeTargets = document.querySelectorAll('[data-scope-target]');

        const toggleScopeTargets = () => {
            const value = scopeSelect ? scopeSelect.value : 'all';

            scopeTargets.forEach((target) => {
                const shouldShow = target.dataset.scopeTarget === value;
                target.classList.toggle('d-none', !shouldShow);
            });
        };

        toggleScopeTargets();
        scopeSelect?.addEventListener('change', toggleScopeTargets);
    });
</script>
@endpush