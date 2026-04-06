@extends('layouts.app')

@section('title', 'Tra cứu chứng chỉ blockchain')

@section('content')
<div class="container py-5">
    @php
        $proofRatio = ($verification['consortium_success_count'] ?? 0) . '/' . max((int) ($verification['consortium_members_total'] ?? 0), 1);
        $requiredQuorum = $verification['consortium_required_quorum'] ?? 1;
    @endphp

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                        <div>
                            <span class="badge rounded-pill text-bg-primary px-3 py-2 mb-3">Hyperledger FireFly</span>
                            <h1 class="fw-bold mb-2">Tra cứu chứng chỉ blockchain</h1>
                            <p class="text-muted mb-0">Nhập mã chứng chỉ để kiểm tra tính hợp lệ, hash xác thực và bằng chứng đã neo lên consortium FireFly.</p>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">V? d?</div>
                            <div class="fw-semibold">KTE-20260405-ABC123</div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('certificates.verify') }}" class="row g-3 align-items-end">
                        <div class="col-lg-9">
                            <label for="code" class="form-label fw-semibold">Mã chứng chỉ</label>
                            <input
                                type="text"
                                id="code"
                                name="code"
                                class="form-control form-control-lg"
                                placeholder="Nhập mã chứng chỉ cần xác thực"
                                value="{{ $code }}"
                            >
                        </div>
                        <div class="col-lg-3 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Tra cứu
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($code !== '' && ! $certificate)
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="fas fa-circle-xmark me-2"></i>Không tìm thấy chứng chỉ phù hợp với mã <strong>{{ $code }}</strong>.
                </div>
            @endif

            @if($certificate)
                <div class="card border-0 shadow-lg overflow-hidden">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                            <div>
                                <h2 class="fw-bold mb-1">Chứng chỉ hợp lệ</h2>
                                <p class="text-muted mb-0">Thông tin chứng chỉ này khớp với dữ liệu trên hệ thống Khai Trí Edu.</p>
                            </div>
                            @if($verification['is_blockchain_verified'])
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Đã đạt quorum trên blockchain</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">Có chứng chỉ nhưng chưa đủ proof FireFly</span>
                            @endif
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Mã chứng chỉ</div>
                                    <div class="fw-semibold">{{ $certificate->certificate_no }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Ngày cấp</div>
                                    <div class="fw-semibold">{{ optional($certificate->issued_at)->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Học viên</div>
                                    <div class="fw-semibold">{{ $certificate->user->fullname ?: $certificate->user->username }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 h-100 bg-light-subtle">
                                    <div class="text-muted small mb-1">Lớp đã xếp</div>
                                    <div class="fw-semibold">{{ $certificate->enrollment?->courseClass?->name ?? 'Chưa gán lớp' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-body-tertiary mb-4">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">Khóa học</div>
                                        <div class="fw-semibold">{{ $certificate->course->title ?? 'Không xác định' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">Hình thức học</div>
                                        <div class="fw-semibold text-capitalize">{{ $certificate->course->learning_type ?? $certificate->course->delivery_mode ?? 'online' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">Proof consortium</div>
                                        <div class="fw-semibold">{{ $proofRatio }}</div>
                                        <small class="text-muted">Yêu cầu tối thiểu {{ $requiredQuorum }} proof</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small mb-1">SHA-256 verification hash</div>
                                        <code class="small text-break">{{ $verification['hash'] }}</code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">FireFly message</div>
                                    <div class="fw-semibold text-break">{{ $verification['firefly_message_id'] ?? 'Chưa có' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">Blockchain tx</div>
                                    <div class="fw-semibold text-break">{{ $verification['firefly_tx_id'] ?? 'Chưa có' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="text-muted small mb-1">Trạng thái FireFly</div>
                                    <div class="fw-semibold">{{ $verification['firefly_state'] ?? data_get($verification['audit'], 'message', 'Chưa ghi nhận') }}</div>
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
                                            <div class="alert alert-warning mb-0">Chưa có proof theo từng thành viên. Hãy cấu hình FireFly consortium hoặc chạy đồng bộ để hệ thống cập nhật kết quả.</div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 flex-wrap mt-4">
                            <a href="{{ route('courses.show', $certificate->course_id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-book-open me-2"></i>Xem khóa học
                            </a>
                            <a href="{{ $verification['verification_url'] }}" class="btn btn-outline-dark">
                                <i class="fas fa-link me-2"></i>Đường dẫn xác thực
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
