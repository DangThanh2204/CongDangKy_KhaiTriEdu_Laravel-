@extends('layouts.app')

@section('title', 'Chứng chỉ ' . $course->title)

@section('content')
<div class="container py-5">
    @php
        $proofRatio = ($verification['consortium_success_count'] ?? 0) . '/' . max((int) ($verification['consortium_members_total'] ?? 0), 1);
        $requiredQuorum = $verification['consortium_required_quorum'] ?? 1;
    @endphp

    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-body p-4 p-lg-5" style="background: linear-gradient(135deg, #f8fbff 0%, #eef7f2 100%);">
            <div class="text-center mb-4">
                <div class="mb-3"><i class="fas fa-award fa-3x text-warning"></i></div>
                <h1 class="fw-bold mb-2">Chứng nhận hoàn thành</h1>
                <p class="text-muted mb-0">Khai Trí Edu xác nhận học viên đã hoàn thành khóa học và có thể kiểm chứng công khai qua blockchain consortium.</p>
            </div>

            <div class="row g-4 align-items-start">
                <div class="col-lg-8">
                    <div class="text-center text-lg-start py-2 py-lg-4">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Cấp cho học viên</div>
                        <h2 class="fw-semibold mb-3">{{ $enrollment->user->fullname ?? $enrollment->user->username }}</h2>
                        <p class="fs-5 mb-2">Đã hoàn thành khóa học</p>
                        <h3 class="text-primary fw-bold mb-4">{{ $course->title }}</h3>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100 bg-white">
                                <div class="text-muted small mb-1">Mã chứng chỉ</div>
                                <div class="fw-semibold">{{ $certificate->certificate_no }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100 bg-white">
                                <div class="text-muted small mb-1">Ngày cấp</div>
                                <div class="fw-semibold">{{ $certificate->issued_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100 bg-white">
                                <div class="text-muted small mb-1">Email học viên</div>
                                <div class="fw-semibold">{{ $enrollment->user->email }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1"><i class="fas fa-link me-2 text-primary"></i>Xác thực blockchain consortium</h5>
                                    <p class="text-muted mb-0">Chứng chỉ có mã tra cứu công khai, mã QR và hash SHA-256 để phục vụ kiểm chứng trên mạng Hyperledger FireFly nhiều thành viên.</p>
                                </div>
                                @if($verification['is_blockchain_verified'])
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Đã đạt quorum</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">Chưa đủ quorum</span>
                                @endif
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">Đường dẫn tra cứu</div>
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
                                        <div class="text-muted small mb-1">Proof consortium</div>
                                        <div class="fw-semibold">{{ $proofRatio }}</div>
                                        <small class="text-muted">Yêu cầu tối thiểu {{ $requiredQuorum }} proof</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">FireFly message</div>
                                        <div class="fw-semibold text-break">{{ $verification['firefly_message_id'] ?? 'Chưa có' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">Blockchain tx</div>
                                        <div class="fw-semibold text-break">{{ $verification['firefly_tx_id'] ?? 'Chưa có' }}</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                        <div class="text-muted small mb-1">Trạng thái</div>
                                        <div class="fw-semibold">{{ $verification['firefly_state'] ?? data_get($verification['audit'], 'message', 'Chưa ghi nhận') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Kết quả theo từng thành viên consortium</h5>
                            <div class="row g-3">
                                @forelse($verification['consortium_member_results'] as $member)
                                    <div class="col-md-6">
                                        <div class="border rounded-4 p-3 h-100 {{ $member['success'] ? 'border-success bg-success-subtle' : 'border-warning bg-warning-subtle' }}">
                                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                                <div>
                                                    <div class="fw-semibold">{{ $member['label'] }}</div>
                                                    <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $member['role']) }}</div>
                                                </div>
                                                <span class="badge {{ $member['success'] ? 'bg-success' : 'bg-warning text-dark' }}">{{ $member['success'] ? 'OK' : 'Pending' }}</span>
                                            </div>
                                            <div class="small text-break mb-1"><strong>Endpoint:</strong> {{ $member['endpoint'] ?: 'Chưa khai báo' }}</div>
                                            <div class="small text-break mb-1"><strong>Message:</strong> {{ $member['message_id'] ?? 'Chưa có' }}</div>
                                            <div class="small text-break mb-1"><strong>Tx:</strong> {{ $member['tx_id'] ?? 'Chưa có' }}</div>
                                            <div class="small text-break mb-0"><strong>State:</strong> {{ $member['state'] ?? 'Chưa ghi nhận' }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-warning mb-0">Chưa có kết quả proof theo từng thành viên. Hệ thống sẽ hiển thị sau khi FireFly consortium được cấu hình và đồng bộ thành công.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="small text-uppercase text-muted fw-semibold mb-2">QR tra cứu chứng chỉ</div>
                            <img
                                src="{{ $verification['qr_url'] }}"
                                alt="QR chứng chỉ {{ $certificate->certificate_no }}"
                                class="img-fluid rounded-4 border bg-white p-2 mb-3"
                                style="max-width: 240px;"
                            >
                            <p class="text-muted small mb-3">Quét mã để mở thẳng trang xác thực blockchain consortium của chứng chỉ này.</p>
                            <a href="{{ route('certificates.verify', ['code' => $certificate->certificate_no]) }}" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-qrcode me-2"></i>Tra cứu công khai
                            </a>
                            <button type="button" class="btn btn-success w-100" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>In chứng chỉ
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                <a href="{{ route('courses.learn', $course) }}" class="btn btn-outline-primary">
                    <i class="fas fa-book-open me-2"></i>Quay lại trang học
                </a>
                <a href="{{ route('certificates.verify', ['code' => $certificate->certificate_no]) }}" class="btn btn-outline-dark">
                    <i class="fas fa-shield-alt me-2"></i>Xem trang xác thực
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
