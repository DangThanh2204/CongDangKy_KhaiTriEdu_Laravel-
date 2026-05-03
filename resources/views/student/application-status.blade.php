@extends('layouts.app')

@section('title', 'Tra cứu hồ sơ của tôi')
@section('page-class', 'page-student-application-status')

@push('styles')
    @vite('resources/css/pages/student/application-status.css')
@endpush

@section('content')
    @php
        $hasPaymentHistoryRoute = \Illuminate\Support\Facades\Route::has('student.payments.index');
    @endphp

    <div class="container-fluid py-4">
        {{-- Personalized hero --}}
        <div class="card border-0 shadow-sm student-hero-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="text-white-50 small mb-1">Hồ sơ của</div>
                        <h2 class="text-white fw-bold mb-1">{{ $user->fullname ?? $user->username }}</h2>
                        <p class="text-white-50 mb-0">Theo dõi nhanh trạng thái duyệt, thanh toán và lớp học cho từng hồ sơ bạn đã nộp.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-gauge-high me-1"></i>Dashboard
                        </a>
                        @if($hasPaymentHistoryRoute)
                            <a href="{{ route('student.payments.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-receipt me-1"></i>Thanh toán
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stat-tile stat-tile-blue">
                    <div class="stat-tile-icon"><i class="fas fa-folder"></i></div>
                    <div class="stat-tile-body">
                        <div class="stat-tile-value">{{ number_format($summary['submitted']) }}</div>
                        <div class="stat-tile-label">Hồ sơ đã nộp</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-tile stat-tile-green">
                    <div class="stat-tile-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-tile-body">
                        <div class="stat-tile-value">{{ number_format($summary['approved']) }}</div>
                        <div class="stat-tile-label">Đã duyệt</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-tile stat-tile-amber">
                    <div class="stat-tile-icon"><i class="fas fa-coins"></i></div>
                    <div class="stat-tile-body">
                        <div class="stat-tile-value">{{ number_format($summary['paid']) }}</div>
                        <div class="stat-tile-label">Đã thanh toán</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-tile stat-tile-purple">
                    <div class="stat-tile-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-tile-body">
                        <div class="stat-tile-value">{{ number_format($summary['assigned']) }}</div>
                        <div class="stat-tile-label">Đã có lớp</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Application cards --}}
        @if($applications->isNotEmpty())
            <div class="row g-3">
                @foreach($applications as $application)
                    @php
                        $enrollment = $application['enrollment'];
                        $course = $application['course'];
                        $class = $application['class'];
                        $payment = $application['payment'];
                        $primaryAction = $application['primary_action'];
                        $certificate = $enrollment->certificate;
                    @endphp
                    <div class="col-12">
                        <div class="card border-0 shadow-sm application-simple-card {{ $application['needs_attention'] ? 'needs-attention' : '' }}">
                            <div class="card-body p-4">
                                {{-- Header row --}}
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="text-muted small mb-1">Hồ sơ #{{ $enrollment->id }} · Nộp {{ optional($enrollment->created_at)->format('d/m/Y') }}</div>
                                        <h5 class="fw-bold mb-2">{{ $course?->title ?? 'Khóa học đã bị ẩn' }}</h5>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge text-bg-{{ $application['overall_status_variant'] }} px-3 py-2">{{ $application['overall_status_label'] }}</span>
                                            @if($course)
                                                <span class="badge bg-light text-dark border">{{ $course->delivery_mode_label }}</span>
                                                <span class="badge bg-light text-dark border">{{ $course->category->name ?? 'Chưa phân nhóm' }}</span>
                                            @endif
                                            @if($class)
                                                <span class="badge bg-light text-dark border"><i class="fas fa-users me-1"></i>{{ $class->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Step timeline --}}
                                <div class="application-step-grid mb-3">
                                    @foreach($application['steps'] as $step)
                                        <div class="application-step-card tone-{{ $step['variant'] }}">
                                            <div class="application-step-icon">
                                                <i class="{{ $step['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div class="application-step-title">{{ $step['title'] }}</div>
                                                <div class="application-step-label">{{ $step['label'] }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Compact info row (price + payment + class) --}}
                                <div class="row g-3 mb-3 application-info-row">
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="text-muted small mb-1"><i class="fas fa-money-bill-wave me-1"></i>Học phí</div>
                                            <div class="fw-bold">{{ number_format((float) ($enrollment->final_price ?? 0), 0, ',', '.') }}đ</div>
                                            @if((float) ($enrollment->discount_amount ?? 0) > 0)
                                                <small class="text-success">Giảm {{ number_format((float) ($enrollment->discount_amount ?? 0), 0, ',', '.') }}đ</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="text-muted small mb-1"><i class="fas fa-credit-card me-1"></i>Thanh toán</div>
                                            <div class="fw-bold">{{ $payment ? $application['payment_state']['label'] : 'Chưa ghi nhận' }}</div>
                                            @if($payment)
                                                <small class="text-muted">{{ $application['payment_method_label'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="text-muted small mb-1"><i class="fas fa-calendar-check me-1"></i>Lớp học</div>
                                            <div class="fw-bold">{{ $class?->name ?? 'Chưa xếp lớp' }}</div>
                                            @if($class && $class->start_date)
                                                <small class="text-muted">Khai giảng {{ optional($class->start_date)->format('d/m/Y') }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Note if any --}}
                                @if($enrollment->notes)
                                    <div class="alert alert-light border small mb-3">
                                        <i class="fas fa-note-sticky me-2 text-warning"></i><strong>Ghi chú:</strong> {{ $enrollment->notes }}
                                    </div>
                                @endif

                                {{-- Action buttons --}}
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ $primaryAction['url'] }}" class="btn {{ $primaryAction['class'] }} btn-sm">
                                        <i class="fas fa-arrow-right me-1"></i>{{ $primaryAction['label'] }}
                                    </a>
                                    <a href="{{ route('documents.registration-form', $enrollment) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-file-pdf me-1"></i>Phiếu đăng ký
                                    </a>
                                    @if($payment && $payment->isCompleted())
                                        <a href="{{ route('documents.payment-receipt', $payment) }}" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-file-invoice-dollar me-1"></i>Biên nhận
                                        </a>
                                    @endif
                                    @if($certificate?->certificate_no)
                                        <a href="{{ route('certificates.verify', ['code' => $certificate->certificate_no]) }}" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-certificate me-1"></i>Chứng chỉ
                                        </a>
                                    @endif
                                    @if($course)
                                        <a href="{{ route('courses.show', $course) }}" class="btn btn-link btn-sm text-decoration-none ms-auto">
                                            Xem khóa học <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <div class="mb-3">
                        <i class="fas fa-folder-open fa-4x text-muted opacity-50"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Bạn chưa có hồ sơ đăng ký nào</h4>
                    <p class="text-muted mb-4">Khám phá khóa học và đăng ký để bắt đầu hành trình học tập tại Khai Trí.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ route('courses.index') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Khám phá khóa học
                        </a>
                        <a href="{{ route('courses.intakes') }}" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-day me-2"></i>Lịch khai giảng
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
