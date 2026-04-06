@extends('layouts.admin')

@section('title', 'Blockchain Consortium')
@section('page-title', 'Blockchain Consortium')
@section('page-class', 'page-admin-dashboard page-admin-blockchain')

@push('styles')
    @vite('resources/css/pages/admin/dashboard.css')
@endpush

@section('content')
    @php
        $currency = static fn ($value) => number_format((float) $value, 0, ',', '.') . '?';
        $healthMessage = data_get($blockchainSummary, 'firefly_health.message');
        $configuredMembers = max((int) $blockchainSummary['configured_members'], (int) $blockchainSummary['members_total']);
        $totalPending = (int) ($blockchainSummary['pending_certificates'] + $blockchainSummary['pending_transactions']);
    @endphp

    <div class="dashboard-shell">
        <section class="chart-card dashboard-hero mb-4">
            <div class="dashboard-hero-copy">
                <span class="dashboard-kicker">Hyperledger FireFly Consortium</span>
                <h2>Theo dõi mô hình 2-3 thành viên cho Khai Trí, đối tác đào tạo và đơn vị xác thực</h2>
                <p class="mb-0">Trang này giúp admin theo dõi từng thành viên FireFly đang online hay chưa, hệ thống đã đạt quorum để neo proof hay chưa, và bản ghi nào vẫn đang chờ đồng bộ đa bản.</p>
                <div class="dashboard-hero-pills">
                    <span class="dashboard-pill"><i class="fas fa-network-wired"></i>{{ number_format($blockchainSummary['healthy_members']) }}/{{ number_format(max($configuredMembers, 1)) }} thành viên online</span>
                    <span class="dashboard-pill"><i class="fas fa-shield-halved"></i>Quorum {{ number_format($blockchainSummary['consortium_quorum']) }}/{{ number_format(max($configuredMembers, 1)) }}</span>
                    <span class="dashboard-pill"><i class="fas fa-clock"></i>{{ number_format($totalPending) }} bản ghi chờ neo</span>
                </div>
            </div>
            <div class="dashboard-hero-summary">
                <article class="dashboard-summary-card">
                    <span class="summary-label">Trạng thái consortium</span>
                    <strong>{{ $blockchainSummary['firefly_connected'] ? 'Đủ quorum' : 'Chưa đủ quorum' }}</strong>
                    <small>{{ $healthMessage ?: 'Đang kiểm tra số thành viên online thực tế' }}</small>
                </article>
                <article class="dashboard-summary-card">
                    <span class="summary-label">Đầu mối phát hành</span>
                    <strong>{{ $blockchainSummary['platform_identity'] ?: 'platform' }}</strong>
                    <small>{{ $blockchainSummary['primary_endpoint'] ?: 'Chưa khai báo endpoint chính' }}</small>
                </article>
            </div>
        </section>

        <section class="chart-card dashboard-admissions-card mb-4">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="chart-title">Tổng quan neo blockchain</h5>
                    <p class="dashboard-card-copy mb-0">Các chỉ số dưới đây phản ánh số chứng chỉ và giao dịch ví đã đủ proof theo mô hình consortium thay vì chỉ neo lên một endpoint đơn lẻ.</p>
                </div>
                <form method="POST" action="{{ route('admin.blockchain.sync') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-rotate me-2"></i>Đồng bộ bản ghi chưa neo
                    </button>
                </form>
            </div>

            <div class="dashboard-admissions-grid">
                <article class="dashboard-kpi-card is-blue">
                    <span class="dashboard-kpi-eyebrow">Chứng chỉ đạt quorum</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['anchored_certificates']) }}</strong>
                    <small class="dashboard-kpi-note">Chứng chỉ đã có đủ proof từ các thành viên cần thiết</small>
                </article>
                <article class="dashboard-kpi-card is-orange">
                    <span class="dashboard-kpi-eyebrow">Chứng chỉ chờ neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['pending_certificates']) }}</strong>
                    <small class="dashboard-kpi-note">Đã cấp chứng chỉ nhưng proof consortium chưa đạt đủ quorum</small>
                </article>
                <article class="dashboard-kpi-card is-green">
                    <span class="dashboard-kpi-eyebrow">Giao dịch đạt quorum</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['anchored_transactions']) }}</strong>
                    <small class="dashboard-kpi-note">Ví và giao dịch đã có đủ proof trên các thành viên FireFly</small>
                </article>
                <article class="dashboard-kpi-card is-slate">
                    <span class="dashboard-kpi-eyebrow">Giao dịch cần kiểm tra</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['pending_transactions']) }}</strong>
                    <small class="dashboard-kpi-note">Giao dịch hoàn tất nhưng proof consortium chưa đạt yêu cầu</small>
                </article>
            </div>
        </section>

        <section class="chart-card dashboard-admissions-card mb-4">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="chart-title">Thành viên consortium</h5>
                    <p class="dashboard-card-copy mb-0">Mỗi thành viên có thể là đơn vị phát hành, đối tác đào tạo hoặc đơn vị xác thực. Dashboard này hiển thị sức khỏe của từng endpoint FireFly thật.</p>
                </div>
                <span class="dashboard-chip">Namespace {{ $blockchainSummary['namespace'] ?: '-' }}</span>
            </div>

            <div class="row g-3">
                @forelse($blockchainSummary['member_statuses'] as $member)
                    <div class="col-xl-4 col-md-6">
                        <article class="dashboard-kpi-card {{ $member['success'] ? 'is-green' : ($member['configured'] ? 'is-orange' : 'is-slate') }} h-100">
                            <span class="dashboard-kpi-eyebrow">{{ $member['label'] }}</span>
                            <strong class="dashboard-kpi-value">{{ ucfirst(str_replace('_', ' ', $member['role'])) }}</strong>
                            <small class="dashboard-kpi-note">{{ $member['endpoint'] ?: 'Chưa khai báo endpoint' }}</small>
                            <div class="dashboard-ratio-meta mt-3">
                                <span>Auth: {{ strtoupper($member['auth_mode'] ?: 'none') }}</span>
                                <span>{{ $member['success'] ? 'Đang kết nối' : ($member['message'] ?: 'Chưa sẵn sàng') }}</span>
                            </div>
                            <div class="dashboard-ratio-meta mt-2">
                                <span>Namespace: {{ $member['namespace'] ?: '-' }}</span>
                                <span>{{ $member['token_ready'] ? 'Token ready' : 'Audit only' }}</span>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning mb-0">Chưa có thành viên FireFly nào được cấu hình. Hãy thêm <code>FIREFLY_CONSORTIUM_MEMBERS</code> hoặc dùng cấu hình thành viên chính hiện có.</div>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="row g-4">
            <div class="col-xl-6">
                <section class="chart-card h-100">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="chart-title">Chứng chỉ gần đây</h5>
                            <p class="dashboard-card-copy mb-0">Xem từng chứng chỉ đã có bao nhiêu proof và đã đạt quorum hay chưa.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Mã chứng chỉ</th>
                                    <th>Học viên</th>
                                    <th>Proof</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blockchainSummary['recent_certificates'] as $certificate)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $certificate['code'] }}</div>
                                            <small class="text-muted">{{ optional($certificate['issued_at'])->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $certificate['user'] }}</div>
                                            <small class="text-muted">{{ $certificate['course'] }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $certificate['proof_ratio'] }}</div>
                                            <small class="text-muted">Yêu cầu {{ $certificate['required_quorum'] }} proof</small>
                                        </td>
                                        <td>
                                            @if($certificate['anchored'])
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Đã đạt quorum</span>
                                                <div class="small text-muted mt-1">{{ $certificate['tx_id'] ?? $certificate['message_id'] ?? 'C? proof' }}</div>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Chờ neo thêm</span>
                                                <div class="small text-muted mt-1">{{ $certificate['state'] ?? 'Chưa ghi nhận proof' }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Chưa có chứng chỉ nào để hiển thị.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-xl-6">
                <section class="chart-card h-100">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="chart-title">Giao dịch gần đây</h5>
                            <p class="dashboard-card-copy mb-0">Theo dõi topup hoặc chi tiêu ví đã được neo trên bao nhiêu thành viên trong consortium.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tham chiếu</th>
                                    <th>Học viên</th>
                                    <th>Số tiền</th>
                                    <th>Proof</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blockchainSummary['recent_transactions'] as $transaction)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $transaction['reference'] }}</div>
                                            <small class="text-muted">{{ $transaction['method'] }}</small>
                                        </td>
                                        <td>{{ $transaction['user'] }}</td>
                                        <td>{{ $currency($transaction['amount']) }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $transaction['proof_ratio'] }}</div>
                                            <small class="text-muted">Yêu cầu {{ $transaction['required_quorum'] }} proof</small>
                                        </td>
                                        <td>
                                            @if($transaction['anchored'])
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Đã đạt quorum</span>
                                                <div class="small text-muted mt-1">{{ $transaction['tx_id'] ?? $transaction['message_id'] ?? 'C? proof' }}</div>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Chờ neo thêm</span>
                                                <div class="small text-muted mt-1">{{ $transaction['state'] ?? 'Chưa ghi nhận proof' }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Chưa có giao dịch hoàn thành để hiển thị.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
