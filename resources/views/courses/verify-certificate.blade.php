@extends('layouts.app')

@section('title', 'Tra cuu chung chi')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <span class="badge rounded-pill text-bg-primary px-3 py-2 mb-3">Khai Tri Edu</span>
                            <h1 class="fw-bold mb-2">Tra cuu chung chi</h1>
                            <p class="text-muted mb-0">Nhap ma chung chi de kiem tra tinh hop le va doi chieu thong tin cap phat tren he thong.</p>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">Vi du</div>
                            <div class="fw-semibold">KTE-20260405-ABC123</div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('certificates.verify') }}" class="row g-3 align-items-end">
                        <div class="col-lg-9">
                            <label for="code" class="form-label fw-semibold">Ma chung chi</label>
                            <input
                                type="text"
                                id="code"
                                name="code"
                                class="form-control form-control-lg"
                                placeholder="Nhap ma chung chi can xac thuc"
                                value="{{ $code }}"
                            >
                        </div>
                        <div class="col-lg-3 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Tra cuu
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($code !== '' && ! $certificate)
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="fas fa-circle-xmark me-2"></i>Khong tim thay chung chi phu hop voi ma <strong>{{ $code }}</strong>.
                </div>
            @endif

            @if($certificate && $verification)
                <div class="card border-0 shadow-lg overflow-hidden">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                            <div>
                                <h2 class="fw-bold mb-1">Chung chi hop le</h2>
                                <p class="text-muted mb-0">Thong tin ben duoi khop voi du lieu da luu tren he thong Khai Tri Edu.</p>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">{{ $verification['status_label'] }}</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Ma chung chi</div>
                                    <div class="fw-semibold">{{ $certificate->certificate_no }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Ngay cap</div>
                                    <div class="fw-semibold">{{ optional($certificate->issued_at)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Hoc vien</div>
                                    <div class="fw-semibold">{{ $certificate->user->fullname ?: $certificate->user->username }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Lop da xep</div>
                                    <div class="fw-semibold">{{ $certificate->enrollment?->courseClass?->name ?? 'Chua gan lop' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-body-tertiary mb-4">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">Khoa hoc</div>
                                        <div class="fw-semibold">{{ $certificate->course->title ?? 'Khong xac dinh' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">Hinh thuc hoc</div>
                                        <div class="fw-semibold text-capitalize">{{ $certificate->course->learning_type ?? $certificate->course->delivery_mode ?? 'online' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">Duong dan xac thuc</div>
                                        <div class="fw-semibold text-break">{{ $verification['verification_url'] }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">Ma hash SHA-256</div>
                                        <code class="small text-break">{{ $verification['hash'] }}</code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">Trang thai</div>
                                    <div class="fw-semibold">{{ $verification['status_label'] }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">Cap nhat luc</div>
                                    <div class="fw-semibold">{{ $verification['issued_at_label'] }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                            <a href="{{ route('courses.show', $certificate->course_id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-book-open me-2"></i>Xem khoa hoc
                            </a>
                            <a href="{{ $verification['verification_url'] }}" class="btn btn-outline-dark">
                                <i class="fas fa-link me-2"></i>Duong dan xac thuc
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
