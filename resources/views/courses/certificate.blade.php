@extends('layouts.app')

@section('title', 'Chung chi ' . $course->title)

@section('content')
<div class="container py-5">
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-body p-4 p-lg-5" style="background: linear-gradient(135deg, #f8fbff 0%, #eef7f2 100%);">
            <div class="text-center mb-4">
                <div class="mb-3"><i class="fas fa-award fa-3x text-warning"></i></div>
                <h1 class="fw-bold mb-2">Chung nhan hoan thanh</h1>
                <p class="text-muted mb-0">Khai Tri Edu xac nhan hoc vien da hoan thanh khoa hoc va co the doi chieu cong khai bang ma chung chi.</p>
            </div>

            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <div class="text-center text-lg-start py-2 py-lg-4">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Cap cho hoc vien</div>
                        <h2 class="fw-semibold mb-3">{{ $enrollment->user->fullname ?? $enrollment->user->username }}</h2>
                        <p class="fs-5 mb-2">Da hoan thanh khoa hoc</p>
                        <h3 class="text-primary fw-bold mb-4">{{ $course->title }}</h3>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100 bg-white">
                                <div class="text-muted small mb-1">Ma chung chi</div>
                                <div class="fw-semibold">{{ $certificate->certificate_no }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100 bg-white">
                                <div class="text-muted small mb-1">Ngay cap</div>
                                <div class="fw-semibold">{{ optional($certificate->issued_at)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100 bg-white">
                                <div class="text-muted small mb-1">Email hoc vien</div>
                                <div class="fw-semibold">{{ $enrollment->user->email }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1"><i class="fas fa-shield-check me-2 text-primary"></i>Thong tin xac thuc</h5>
                                    <p class="text-muted mb-0">Dung ma chung chi, duong dan tra cuu va hash ben duoi de doi chieu tinh hop le cua chung chi.</p>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">{{ $verification['status_label'] }}</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">Duong dan tra cuu</div>
                                        <div class="fw-semibold small text-break">{{ $verification['verification_url'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">Ma hash SHA-256</div>
                                        <div class="fw-semibold small text-break">{{ $verification['hash'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">Trang thai</div>
                                        <div class="fw-semibold">{{ $verification['status_label'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">Cap nhat luc</div>
                                        <div class="fw-semibold">{{ $verification['issued_at_label'] ?? optional($certificate->issued_at)->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="small text-uppercase text-muted fw-semibold mb-2">QR tra cuu chung chi</div>
                            <img
                                src="{{ $verification['qr_url'] }}"
                                alt="QR chung chi {{ $certificate->certificate_no }}"
                                class="img-fluid rounded-4 border bg-white p-2 mb-3"
                                style="max-width: 240px;"
                            >
                            <p class="text-muted small mb-3">Quet ma de mo trang tra cuu cong khai cua chung chi nay.</p>
                            <a href="{{ route('certificates.verify', ['code' => $certificate->certificate_no]) }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-qrcode me-2"></i>Tra cuu cong khai
                            </a>
                            <button type="button" class="btn btn-success w-100" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>In chung chi
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                <a href="{{ route('courses.learn', $course) }}" class="btn btn-outline-primary">
                    <i class="fas fa-book-open me-2"></i>Quay lai trang hoc
                </a>
                <a href="{{ route('certificates.verify', ['code' => $certificate->certificate_no]) }}" class="btn btn-outline-dark">
                    <i class="fas fa-link me-2"></i>Xem trang xac thuc
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
