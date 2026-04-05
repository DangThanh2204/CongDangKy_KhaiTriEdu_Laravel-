@extends('layouts.app')

@section('title', 'Tra cá»©u chá»©ng chá» blockchain')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <span class="badge rounded-pill text-bg-primary px-3 py-2 mb-3">Hyperledger FireFly</span>
                            <h1 class="fw-bold mb-2">Tra cá»©u chá»©ng chá» blockchain</h1>
                            <p class="text-muted mb-0">Nháº­p mÃ£ chá»©ng chá» Äá» kiá»m tra tÃ­nh há»£p lá», hash xÃ¡c thá»±c vÃ  báº±ng chá»©ng ÄÃ£ neo lÃªn FireFly.</p>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">VÃ­ dá»¥</div>
                            <div class="fw-semibold">KTE-20260405-ABC123</div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('certificates.verify') }}" class="row g-3 align-items-end">
                        <div class="col-lg-9">
                            <label for="code" class="form-label fw-semibold">MÃ£ chá»©ng chá»</label>
                            <input
                                type="text"
                                id="code"
                                name="code"
                                class="form-control form-control-lg"
                                placeholder="Nháº­p mÃ£ chá»©ng chá» cáº§n xÃ¡c thá»±c"
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
                    <i class="fas fa-circle-xmark me-2"></i>KhÃ´ng tÃ¬m tháº¥y chá»©ng chá» phÃ¹ há»£p vá»i mÃ£ <strong>{{ $code }}</strong>.
                </div>
            @endif

            @if($certificate)
                <div class="card border-0 shadow-lg overflow-hidden">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                            <div>
                                <h2 class="fw-bold mb-1">Chá»©ng chá» há»£p lá»</h2>
                                <p class="text-muted mb-0">ThÃ´ng tin chá»©ng chá» nÃ y khá»p vá»i dá»¯ liá»u trÃªn há» thá»ng Khai TrÃ­ Edu.</p>
                            </div>
                            @if($verification['is_blockchain_verified'])
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">ÄÃ£ xÃ¡c thá»±c trÃªn blockchain</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">CÃ³ chá»©ng chá» nhÆ°ng chÆ°a cÃ³ proof FireFly</span>
                            @endif
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">MÃ£ chá»©ng chá»</div>
                                    <div class="fw-semibold">{{ $certificate->certificate_no }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">NgÃ y cáº¥p</div>
                                    <div class="fw-semibold">{{ optional($certificate->issued_at)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Há»c viÃªn</div>
                                    <div class="fw-semibold">{{ $certificate->user->fullname ?: $certificate->user->username }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Lá»p ÄÃ£ xáº¿p</div>
                                    <div class="fw-semibold">{{ $certificate->enrollment?->courseClass?->name ?? 'ChÆ°a gáº¯n lá»p' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-body-tertiary mb-4">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">KhÃ³a há»c</div>
                                        <div class="fw-semibold">{{ $certificate->course->title ?? 'KhÃ´ng xÃ¡c Äá»nh' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">HÃ¬nh thá»©c há»c</div>
                                        <div class="fw-semibold text-capitalize">{{ $certificate->course->learning_type ?? $certificate->course->delivery_mode ?? 'online' }}</div>
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
                                    <div class="fw-semibold text-break">{{ $verification['firefly_message_id'] ?? 'ChÆ°a cÃ³' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">Blockchain tx</div>
                                    <div class="fw-semibold text-break">{{ $verification['firefly_tx_id'] ?? 'ChÆ°a cÃ³' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">Tráº¡ng thÃ¡i FireFly</div>
                                    <div class="fw-semibold">{{ $verification['firefly_state'] ?? data_get($verification['audit'], 'message', 'ChÆ°a ghi nháº­n') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                            <a href="{{ route('courses.show', $certificate->course_id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-book-open me-2"></i>Xem khÃ³a há»c
                            </a>
                            <a href="{{ $verification['verification_url'] }}" class="btn btn-outline-dark">
                                <i class="fas fa-link me-2"></i>ÄÆ°á»ng dáº«n xÃ¡c thá»±c
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
