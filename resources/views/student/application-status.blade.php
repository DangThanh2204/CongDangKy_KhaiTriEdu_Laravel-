@extends('layouts.app')

@section('title', 'Tra cứu trạng thái hồ sơ')
@section('page-class', 'page-student-application-status')

@push('styles')
    @vite('resources/css/pages/student/application-status.css')
@endpush

@section('content')
    <div class="container py-5 student-application-page">
        <section class="application-hero mb-4">
            <div class="application-hero-copy">
                <span class="application-eyebrow">Cổng đăng ký khóa học trực tuyến</span>
                <h1>Tra cứu trạng thái hồ sơ</h1>
                <p>Theo dõi nhanh hồ sơ đã nộp, tình trạng duyệt, thanh toán và đợt học đã được ghi nhận trên hệ thống.</p>
            </div>
            <div class="application-hero-actions">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-gauge-high me-2"></i>Về dashboard
                </a>
                <a href="{{ route('courses.intakes') }}" class="btn btn-primary">
                    <i class="fas fa-calendar-check me-2"></i>Xem lịch khai giảng
                </a>
            </div>
        </section>

        <section class="application-summary-grid mb-4">
            <article class="application-summary-card tone-primary">
                <span>Hồ sơ đã nộp</span>
                <strong>{{ number_format($summary['submitted']) }}</strong>
                <small>Tổng số hồ sơ đăng ký học đã được hệ thống ghi nhận.</small>
            </article>
            <article class="application-summary-card tone-success">
                <span>Đã duyệt</span>
                <strong>{{ number_format($summary['approved']) }}</strong>
                <small>Hồ sơ đã được trung tâm xác nhận và đủ điều kiện tham gia học.</small>
            </article>
            <article class="application-summary-card tone-warning">
                <span>Đã thanh toán</span>
                <strong>{{ number_format($summary['paid']) }}</strong>
                <small>Hồ sơ đã ghi nhận thanh toán hoặc không cần thanh toán thêm.</small>
            </article>
            <article class="application-summary-card tone-info">
                <span>Đã ghi nhận đợt học</span>
                <strong>{{ number_format($summary['assigned']) }}</strong>
                <small>Hồ sơ đã được gắn lớp học hoặc đợt khai giảng cụ thể.</small>
            </article>
        </section>

        @if($applications->isNotEmpty())
            <div class="application-list">
                @foreach($applications as $application)
                    @php
                        $enrollment = $application['enrollment'];
                        $course = $application['course'];
                        $class = $application['class'];
                        $payment = $application['payment'];
                        $primaryAction = $application['primary_action'];
                    @endphp
                    <article class="application-card {{ $application['needs_attention'] ? 'needs-attention' : '' }}">
                        <div class="application-card-header">
                            <div>
                                <div class="application-card-kicker">Hồ sơ #{{ $enrollment->id }}</div>
                                <h2>{{ $course->title ?? 'Khóa học đã bị ẩn' }}</h2>
                                <div class="application-card-meta">
                                    <span><i class="fas fa-calendar-plus me-2"></i>Nộp hồ sơ: {{ optional($enrollment->created_at)->format('d/m/Y H:i') }}</span>
                                    @if($course)
                                        <span><i class="fas fa-layer-group me-2"></i>{{ $course->delivery_mode_label }}</span>
                                        <span><i class="fas fa-folder-open me-2"></i>{{ $course->category->name ?? 'Chưa phân nhóm' }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="application-card-badges">
                                <span class="badge text-bg-{{ $application['overall_status_variant'] }}">{{ $application['overall_status_label'] }}</span>
                                @if($class)
                                    <span class="badge text-bg-light border">{{ $class->name }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="application-step-grid">
                            @foreach($application['steps'] as $step)
                                <div class="application-step-card tone-{{ $step['variant'] }}">
                                    <div class="application-step-icon">
                                        <i class="{{ $step['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="application-step-title">{{ $step['title'] }}</div>
                                        <div class="application-step-label">{{ $step['label'] }}</div>
                                        <p>{{ $step['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="application-detail-grid">
                            <div class="application-detail-card">
                                <span>Học phí hồ sơ</span>
                                <strong>{{ number_format((float) ($enrollment->final_price ?? 0), 0) }}đ</strong>
                                <small>
                                    @if((float) ($enrollment->discount_amount ?? 0) > 0)
                                        Gốc {{ number_format((float) ($enrollment->base_price ?? 0), 0) }}đ, giảm {{ number_format((float) ($enrollment->discount_amount ?? 0), 0) }}đ.
                                    @else
                                        {{ (float) ($enrollment->final_price ?? 0) > 0 ? 'Đây là số tiền cần thanh toán cho hồ sơ này.' : 'Hồ sơ này hiện không yêu cầu thanh toán.' }}
                                    @endif
                                </small>
                            </div>
                            <div class="application-detail-card">
                                <span>Thanh toán gần nhất</span>
                                <strong>{{ $payment ? $application['payment_state']['label'] : 'Chưa ghi nhận' }}</strong>
                                <small>
                                    @if($payment)
                                        {{ $application['payment_method_label'] }}{{ $payment->reference ? ' - Mã ' . $payment->reference : '' }}
                                    @else
                                        Hệ thống chưa có giao dịch gắn với hồ sơ này.
                                    @endif
                                </small>
                            </div>
                            <div class="application-detail-card">
                                <span>Đợt học / lớp học</span>
                                <strong>{{ $class?->name ?? 'Chưa xếp lớp' }}</strong>
                                <small>
                                    @if($class)
                                        {{ optional($class->start_date)->format('d/m/Y') ?: 'Chưa có ngày khai giảng' }}
                                        @if($class->schedule_text)
                                            - {{ $class->schedule_text }}
                                        @endif
                                    @else
                                        Trung tâm sẽ gắn đợt học phù hợp cho bạn sau khi hồ sơ được xử lý.
                                    @endif
                                </small>
                            </div>
                        </div>

                        @if($enrollment->notes)
                            <div class="application-note-box">
                                <strong><i class="fas fa-note-sticky me-2"></i>Ghi chú từ hệ thống</strong>
                                <div>{{ $enrollment->notes }}</div>
                            </div>
                        @endif

                        <div class="application-card-footer">
                            <a href="{{ $primaryAction['url'] }}" class="btn {{ $primaryAction['class'] }}">
                                <i class="fas fa-arrow-right me-2"></i>{{ $primaryAction['label'] }}
                            </a>
                            @if($course)
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-book-open me-2"></i>Xem khóa học
                                </a>
                            @endif
                            @if(! $application['payment_state']['completed'])
                                <a href="{{ route('wallet.index') }}" class="btn btn-outline-dark">
                                    <i class="fas fa-wallet me-2"></i>Quản lý ví
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <section class="application-empty-state">
                <div class="application-empty-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h2>Bạn chưa có hồ sơ đăng ký nào</h2>
                <p>Hãy chọn khóa học hoặc lịch khai giảng phù hợp để gửi hồ sơ đầu tiên. Sau khi nộp, bạn có thể quay lại đây để theo dõi toàn bộ trạng thái.</p>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i>Khám phá khóa học
                    </a>
                    <a href="{{ route('courses.intakes') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-calendar-day me-2"></i>Xem lịch khai giảng
                    </a>
                </div>
            </section>
        @endif
    </div>
@endsection