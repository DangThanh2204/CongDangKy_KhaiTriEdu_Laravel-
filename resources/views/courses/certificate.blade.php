@extends('layouts.app')

@section('title', 'Ch?ng ch? ' . $course->title)

@section('content')
<div class="container py-5">
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-body p-4 p-lg-5" style="background: linear-gradient(135deg, #f8fbff 0%, #eef7f2 100%);">
            <div class="text-center mb-4">
                <div class="mb-3"><i class="fas fa-award fa-3x text-warning"></i></div>
                <h1 class="fw-bold mb-2">Ch?ng nh?n ho?n th?nh</h1>
                <p class="text-muted mb-0">Khai Tr? Edu x?c nh?n h?c vi?n ?? ho?n th?nh kh?a h?c v? c? th? ki?m ch?ng c?ng khai qua blockchain.</p>
            </div>

            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <div class="text-center text-lg-start py-2 py-lg-4">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">C?p cho h?c vi?n</div>
                        <h2 class="fw-semibold mb-3">{{ $enrollment->user->fullname ?? $enrollment->user->username }}</h2>
                        <p class="fs-5 mb-2">?? ho?n th?nh kh?a h?c</p>
                        <h3 class="text-primary fw-bold mb-4">{{ $course->title }}</h3>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100 bg-white">
                                <div class="text-muted small mb-1">M? ch?ng ch?</div>
                                <div class="fw-semibold">{{ $certificate->certificate_no }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100 bg-white">
                                <div class="text-muted small mb-1">Ng?y c?p</div>
                                <div class="fw-semibold">{{ $certificate->issued_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100 bg-white">
                                <div class="text-muted small mb-1">Email h?c vi?n</div>
                                <div class="fw-semibold">{{ $enrollment->user->email }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1"><i class="fas fa-link me-2 text-primary"></i>X?c th?c blockchain</h5>
                                    <p class="text-muted mb-0">Ch?ng ch? c? m? tra c?u c?ng khai, m? QR v? hash SHA-256 ?? ph?c v? ki?m ch?ng tr?n Hyperledger FireFly.</p>
                                </div>
                                @if($verification['is_blockchain_verified'])
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">?? neo l?n blockchain</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">Ch?a c? x?c th?c FireFly</span>
                                @endif
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">???ng d?n tra c?u</div>
                                        <div class="fw-semibold small text-break">{{ $verification['verification_url'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">SHA-256 verification hash</div>
                                        <div class="fw-semibold small text-break">{{ $verification['hash'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">FireFly message</div>
                                        <div class="fw-semibold text-break">{{ $verification['firefly_message_id'] ?? 'Ch?a c?' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">Blockchain tx</div>
                                        <div class="fw-semibold text-break">{{ $verification['firefly_tx_id'] ?? 'Ch?a c?' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">Tr?ng th?i</div>
                                        <div class="fw-semibold">{{ $verification['firefly_state'] ?? data_get($verification['audit'], 'message', 'Ch?a ghi nh?n') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="small text-uppercase text-muted fw-semibold mb-2">QR tra c?u ch?ng ch?</div>
                            <img
                                src="{{ $verification['qr_url'] }}"
                                alt="QR ch?ng ch? {{ $certificate->certificate_no }}"
                                class="img-fluid rounded-4 border bg-white p-2 mb-3"
                                style="max-width: 240px;"
                            >
                            <p class="text-muted small mb-3">Qu?t m? ?? m? th?ng trang x?c th?c blockchain c?a ch?ng ch? n?y.</p>
                            <a href="{{ route('certificates.verify', ['code' => $certificate->certificate_no]) }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-qrcode me-2"></i>Tra c?u c?ng khai
                            </a>
                            <button type="button" class="btn btn-success w-100" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>In ch?ng ch?
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                <a href="{{ route('courses.learn', $course) }}" class="btn btn-outline-primary">
                    <i class="fas fa-book-open me-2"></i>Quay l?i trang h?c
                </a>
                <a href="{{ route('certificates.verify', ['code' => $certificate->certificate_no]) }}" class="btn btn-outline-dark">
                    <i class="fas fa-shield-alt me-2"></i>Xem trang x?c th?c
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
