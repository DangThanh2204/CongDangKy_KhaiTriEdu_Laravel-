@extends('layouts.admin')

@section('title', 'Cài đặt hệ thống')
@section('page-class', 'page-admin-settings')

@push('styles')
    @vite('resources/css/pages/admin/settings.css')
@endpush

@section('content')
@php
    $logoExists = !empty($settings['site_logo'] ?? null);
    $faviconExists = !empty($settings['site_favicon'] ?? null);
    $classChangeEnabled = isset($settings['allow_class_change']) && $settings['allow_class_change'] != '0';
@endphp

<div class="container-fluid py-4 settings-page">
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm settings-hero mb-4">
            <div class="card-body p-4 p-xl-4">
                <div class="settings-hero-wrap d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                    <div>
                        <div class="settings-eyebrow">Admin / System</div>
                        <h1 class="settings-title mb-2">Cài đặt hệ thống</h1>
                        <p class="settings-subtitle mb-0">Gom các cấu hình quan trọng vào một màn hình gọn, dễ soát và không kéo dài quá mức.</p>
                    </div>

                    <div class="settings-actions d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Lưu cài đặt
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <div class="fw-semibold mb-2">Có lỗi xảy ra:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-xxl-8">
                <div class="accordion settings-accordion" id="settingsAccordion">
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header" id="headingBasic">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBasic" aria-expanded="true" aria-controls="collapseBasic">
                                <span>
                                    <strong>Thông tin chung</strong>
                                    <small class="d-block text-muted">Tên website, slogan và footer.</small>
                                </span>
                            </button>
                        </h2>
                        <div id="collapseBasic" class="accordion-collapse collapse show" aria-labelledby="headingBasic" data-bs-parent="#settingsAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="site_name" class="form-label">Tên website <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('site_name') is-invalid @enderror" id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" required>
                                        @error('site_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="site_tagline" class="form-label">Slogan / mô tả ngắn</label>
                                        <input type="text" class="form-control @error('site_tagline') is-invalid @enderror" id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}" placeholder="VD: Nền tảng học tập trực tuyến">
                                        @error('site_tagline')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="footer_text" class="form-label">Văn bản footer</label>
                                        <textarea class="form-control @error('footer_text') is-invalid @enderror" id="footer_text" name="footer_text" rows="2" placeholder="VD: Copyright Khai Tri Education. All rights reserved.">{{ old('footer_text', $settings['footer_text'] ?? '') }}</textarea>
                                        @error('footer_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header" id="headingBrand">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBrand" aria-expanded="false" aria-controls="collapseBrand">
                                <span>
                                    <strong>Nhận diện thương hiệu</strong>
                                    <small class="d-block text-muted">Logo và favicon hiện thị trên toàn hệ thống.</small>
                                </span>
                            </button>
                        </h2>
                        <div id="collapseBrand" class="accordion-collapse collapse" aria-labelledby="headingBrand" data-bs-parent="#settingsAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="site_logo" class="form-label">Logo website</label>
                                        <input type="file" class="form-control @error('site_logo') is-invalid @enderror" id="site_logo" name="site_logo" accept="image/*">
                                        <div class="form-text">Tối đa 2MB. Định dạng: JPEG, PNG, GIF.</div>
                                        @error('site_logo')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="site_favicon" class="form-label">Favicon</label>
                                        <input type="file" class="form-control @error('site_favicon') is-invalid @enderror" id="site_favicon" name="site_favicon" accept="image/*">
                                        <div class="form-text">Tối đa 1MB. Icon hiển thị trên tab trình duyệt.</div>
                                        @error('site_favicon')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header" id="headingContact">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContact" aria-expanded="false" aria-controls="collapseContact">
                                <span>
                                    <strong>Liên hệ & mạng xã hội</strong>
                                    <small class="d-block text-muted">Email, số điện thoại, địa chỉ và social.</small>
                                </span>
                            </button>
                        </h2>
                        <div id="collapseContact" class="accordion-collapse collapse" aria-labelledby="headingContact" data-bs-parent="#settingsAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="contact_email" class="form-label">Email liên hệ</label>
                                        <input type="email" class="form-control @error('contact_email') is-invalid @enderror" id="contact_email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" placeholder="contact@example.com">
                                        @error('contact_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="contact_phone" class="form-label">Số điện thoại</label>
                                        <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" placeholder="+84 123 456 789">
                                        @error('contact_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="contact_address" class="form-label">Địa chỉ</label>
                                        <input type="text" class="form-control @error('contact_address') is-invalid @enderror" id="contact_address" name="contact_address" value="{{ old('contact_address', $settings['contact_address'] ?? '') }}" placeholder="123 Đường ABC, Thành phố XYZ">
                                        @error('contact_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="facebook_url" class="form-label">Facebook URL</label>
                                        <input type="url" class="form-control @error('facebook_url') is-invalid @enderror" id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}" placeholder="https://facebook.com/yourpage">
                                        @error('facebook_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="twitter_url" class="form-label">Twitter URL</label>
                                        <input type="url" class="form-control @error('twitter_url') is-invalid @enderror" id="twitter_url" name="twitter_url" value="{{ old('twitter_url', $settings['twitter_url'] ?? '') }}" placeholder="https://twitter.com/yourhandle">
                                        @error('twitter_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="instagram_url" class="form-label">Instagram URL</label>
                                        <input type="url" class="form-control @error('instagram_url') is-invalid @enderror" id="instagram_url" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}" placeholder="https://instagram.com/yourprofile">
                                        @error('instagram_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header" id="headingSystem">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSystem" aria-expanded="false" aria-controls="collapseSystem">
                                <span>
                                    <strong>Học vụ & trợ lý ảo</strong>
                                    <small class="d-block text-muted">Quy định đổi lớp và prompt huấn luyện AI.</small>
                                </span>
                            </button>
                        </h2>
                        <div id="collapseSystem" class="accordion-collapse collapse" aria-labelledby="headingSystem" data-bs-parent="#settingsAccordion">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <div class="col-lg-5">
                                        <div class="settings-switch-card h-100">
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="allow_class_change" name="allow_class_change" value="1" {{ $classChangeEnabled ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="allow_class_change">Cho phép học viên đổi lớp</label>
                                            </div>
                                            <p class="text-muted small mb-0">Bật tùy chọn này nếu bạn muốn học viên gửi yêu cầu chuyển sang đợt/lớp khác.</p>
                                        </div>
                                    </div>

                                    <div class="col-lg-7">
                                        <label for="class_change_deadline_days" class="form-label">Hạn đổi lớp (số ngày trước khi lớp mới bắt đầu)</label>
                                        <input type="number" class="form-control @error('class_change_deadline_days') is-invalid @enderror" id="class_change_deadline_days" name="class_change_deadline_days" value="{{ old('class_change_deadline_days', $settings['class_change_deadline_days'] ?? '0') }}" min="0">
                                        <div class="form-text">Nhập 0 nếu không muốn giới hạn thêm.</div>
                                        @error('class_change_deadline_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="ai_assistant_prompt" class="form-label">Nội dung huấn luyện trợ lý ảo</label>
                                        <textarea class="form-control @error('ai_assistant_prompt') is-invalid @enderror" id="ai_assistant_prompt" name="ai_assistant_prompt" rows="5" placeholder="VD: Ưu tiên hướng dẫn học viên cách tìm khóa học, đăng ký học, nạp ví, thanh toán và liên hệ hỗ trợ.">{{ old('ai_assistant_prompt', $settings['ai_assistant_prompt'] ?? '') }}</textarea>
                                        @error('ai_assistant_prompt')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Nhập quy tắc xưng hô, cách tư vấn và các lưu ý để AI trả lời đúng ngữ cảnh website.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4">
                <div class="settings-summary sticky-xxl-top">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                <div>
                                    <div class="settings-side-label">Tổng quan nhanh</div>
                                    <h2 class="settings-side-title mb-1">{{ $settings['site_name'] ?? 'Khai Tri Education' }}</h2>
                                    <p class="text-muted small mb-0">{{ $settings['site_tagline'] ?? 'Chưa cập nhật slogan' }}</p>
                                </div>
                                <span class="settings-summary-badge">Live</span>
                            </div>

                            <div class="settings-status-grid">
                                <div class="settings-status-chip {{ $logoExists ? 'is-active' : '' }}">
                                    <span>Logo</span>
                                    <strong>{{ $logoExists ? 'Sẵn sàng' : 'Chưa có' }}</strong>
                                </div>
                                <div class="settings-status-chip {{ $faviconExists ? 'is-active' : '' }}">
                                    <span>Favicon</span>
                                    <strong>{{ $faviconExists ? 'Sẵn sàng' : 'Chưa có' }}</strong>
                                </div>
                                <div class="settings-status-chip {{ $classChangeEnabled ? 'is-active' : '' }}">
                                    <span>Đổi lớp</span>
                                    <strong>{{ $classChangeEnabled ? 'Đang bật' : 'Đang tắt' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <h3 class="settings-panel-title">Preview hiện tại</h3>

                            <div class="settings-preview-stack">
                                <div class="settings-preview-box">
                                    <div class="settings-preview-label">Logo</div>
                                    @if ($logoExists)
                                        <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Site Logo" class="settings-preview-image settings-preview-logo">
                                    @else
                                        <div class="settings-preview-empty">Chưa có logo</div>
                                    @endif
                                </div>

                                <div class="settings-preview-box settings-preview-box--small">
                                    <div class="settings-preview-label">Favicon</div>
                                    @if ($faviconExists)
                                        <img src="{{ asset('storage/' . $settings['site_favicon']) }}" alt="Favicon" class="settings-preview-image settings-preview-favicon">
                                    @else
                                        <div class="settings-preview-empty">Chưa có favicon</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <h3 class="settings-panel-title">Liên hệ nhanh</h3>
                            <ul class="settings-mini-list list-unstyled mb-0">
                                <li>
                                    <span>Email</span>
                                    <strong>{{ $settings['contact_email'] ?: 'Chưa cập nhật' }}</strong>
                                </li>
                                <li>
                                    <span>Số điện thoại</span>
                                    <strong>{{ $settings['contact_phone'] ?: 'Chưa cập nhật' }}</strong>
                                </li>
                                <li>
                                    <span>Địa chỉ</span>
                                    <strong>{{ $settings['contact_address'] ?: 'Chưa cập nhật' }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="settings-panel-title">Gợi ý dùng nhanh</h3>
                            <ul class="settings-help-list mb-0">
                                <li>Chỉ mở section cần sửa để giữ màn hình gọn.</li>
                                <li>Logo và favicon nên có nền trong suốt để hiển thị đẹp trên header.</li>
                                <li>Prompt AI nên viết ngắn, rõ quy tắc và đúng nghiệp vụ.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
