@extends('layouts.app')

@section('title', 'Tra c?u ch?ng ch? blockchain')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <span class="badge rounded-pill text-bg-primary px-3 py-2 mb-3">Hyperledger FireFly</span>
                            <h1 class="fw-bold mb-2">Tra c?u ch?ng ch? blockchain</h1>
                            <p class="text-muted mb-0">Nh?p m? ch?ng ch? ?? ki?m tra t?nh tr?ng h?p l?, hash x?c th?c v? b?ng ch?ng ?? neo l?n FireFly.</p>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">V? d?</div>
                            <div class="fw-semibold">KTE-20260405-ABC123</div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('certificates.verify') }}" class="row g-3 align-items-end">
                        <div class="col-lg-9">
                            <label for="code" class="form-label fw-semibold">M? ch?ng ch?</label>
                            <input
                                type="text"
                                id="code"
                                name="code"
                                class="form-control form-control-lg"
                                placeholder="Nh?p m? ch?ng ch? c?n x?c th?c"
                                value="{{ $code }}"
                            >
                        </div>
                        <div class="col-lg-3 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Tra c?u
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($code !== '' && ! $certificate)
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="fas fa-circle-xmark me-2"></i>Kh?ng t?m th?y ch?ng ch? ph? h?p v?i m? <strong>{{ $code }}</strong>.
                </div>
            @endif

            @if($certificate)
                <div class="card border-0 shadow-lg overflow-hidden">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                            <div>
                                <h2 class="fw-bold mb-1">Ch?ng ch? h?p l?</h2>
                                <p class="text-muted mb-0">Th?ng tin ch?ng ch? n?y kh?p v?i d? li?u tr?n h? th?ng Khai Tr? Edu.</p>
                            </div>
                            @if($verification['is_blockchain_verified'])
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">?? x?c th?c tr?n blockchain</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">C? ch?ng ch? nh?ng ch?a c? proof FireFly</span>
                            @endif
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">M? ch?ng ch?</div>
                                    <div class="fw-semibold">{{ $certificate->certificate_no }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Ng?y c?p</div>
                                    <div class="fw-semibold">{{ optional($certificate->issued_at)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">H?c vi?n</div>
                                    <div class="fw-semibold">{{ $certificate->user->fullname ?: $certificate->user->username }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">??t h?c</div>
                                    <div class="fw-semibold">{{ $certificate->enrollment?->courseClass?->name ?? 'Ch?a g?n l?p' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-body-tertiary mb-4">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">Kh?a h?c</div>
                                        <div class="fw-semibold">{{ $certificate->course->title ?? 'Kh?ng x?c ??nh' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">H?nh th?c h?c</div>
                                        <div class="fw-semibold text-capitalize">{{ $certificate->course->learning_type ?? 'online' }}</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-muted small mb-1">SHA-256 verification hash</div>
                                        <code class="small text-break">{{ $verification['hash'] }}</code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">FireFly message</div>
                                    <div class="fw-semibold text-break">{{ $verification['firefly_message_id'] ?? 'Ch?a c?' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">Blockchain tx</div>
                                    <div class="fw-semibold text-break">{{ $verification['firefly_tx_id'] ?? 'Ch?a c?' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">Tr?ng th?i FireFly</div>
                                    <div class="fw-semibold">{{ $verification['firefly_state'] ?? data_get($verification['audit'], 'message', 'Ch?a ghi nh?n') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
