@extends('layouts.admin')

@section('title', 'Blockchain FireFly')
@section('page-title', 'Blockchain FireFly')
@section('page-class', 'page-admin-dashboard page-admin-blockchain')

@push('styles')
    @vite('resources/css/pages/admin/dashboard.css')
@endpush

@section('content')
    @php
        $currency = static fn ($value) => number_format((float) $value, 0, ',', '.') . '?';
    @endphp

    <div class="dashboard-shell">
        <section class="chart-card dashboard-hero mb-4">
            <div class="dashboard-hero-copy">
                <span class="dashboard-kicker">Hyperledger FireFly</span>
                <h2>Theo d?i ch?ng ch? v? giao d?ch ?? neo blockchain</h2>
                <p class="mb-0">Trang n?y gi?p admin ki?m tra nhanh FireFly ?? s?n s?ng ch?a, bao nhi?u ch?ng ch? ?? ???c neo l?n blockchain v? c?c giao d?ch v? n?o ?? c? message / transaction proof.</p>
                <div class="dashboard-hero-pills">
                    <span class="dashboard-pill"><i class="fas fa-certificate"></i>{{ number_format($blockchainSummary['anchored_certificates']) }} ch?ng ch? ?? neo</span>
                    <span class="dashboard-pill"><i class="fas fa-wallet"></i>{{ number_format($blockchainSummary['anchored_transactions']) }} giao d?ch ?? neo</span>
                    <span class="dashboard-pill"><i class="fas fa-network-wired"></i>{{ $blockchainSummary['namespace'] }}</span>
                </div>
            </div>
            <div class="dashboard-hero-summary">
                <article class="dashboard-summary-card">
                    <span class="summary-label">Tr?ng th?i FireFly</span>
                    <strong>{{ $blockchainSummary['firefly_configured'] ? 'S?n s?ng' : 'Ch?a c?u h?nh' }}</strong>
                    <small>Audit topic: {{ $blockchainSummary['audit_topic'] }}</small>
                </article>
                <article class="dashboard-summary-card">
                    <span class="summary-label">B?n ghi c?n ki?m tra</span>
                    <strong>{{ number_format($blockchainSummary['pending_certificates'] + $blockchainSummary['pending_transactions']) }}</strong>
                    <small>Ch?ng ch? ho?c giao d?ch ch?a c? proof th?nh c?ng</small>
                </article>
            </div>
        </section>

        <section class="chart-card dashboard-admissions-card mb-4">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="chart-title">T?ng quan neo blockchain</h5>
                    <p class="dashboard-card-copy mb-0">Th?ng k? nhanh cho c?c b?n ghi nghi?p v? quan tr?ng ?ang ???c g?n v?i FireFly.</p>
                </div>
                <span class="dashboard-chip">Blockchain dashboard</span>
            </div>

            <div class="dashboard-admissions-grid">
                <article class="dashboard-kpi-card is-blue">
                    <span class="dashboard-kpi-eyebrow">Ch?ng ch? ?? neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['anchored_certificates']) }}</strong>
                    <small class="dashboard-kpi-note">C?c ch?ng ch? c? proof th?nh c?ng tr?n FireFly</small>
                </article>
                <article class="dashboard-kpi-card is-orange">
                    <span class="dashboard-kpi-eyebrow">Ch?ng ch? ch? neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['pending_certificates']) }}</strong>
                    <small class="dashboard-kpi-note">C? ch?ng ch? nh?ng ch?a c? proof blockchain th?nh c?ng</small>
                </article>
                <article class="dashboard-kpi-card is-green">
                    <span class="dashboard-kpi-eyebrow">Giao d?ch ?? neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['anchored_transactions']) }}</strong>
                    <small class="dashboard-kpi-note">Topup v? ho?c chi ti?u v? ?? c? message / tx id</small>
                </article>
                <article class="dashboard-kpi-card is-slate">
                    <span class="dashboard-kpi-eyebrow">Giao d?ch c?n ki?m tra</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['pending_transactions']) }}</strong>
                    <small class="dashboard-kpi-note">Giao d?ch ho?n th?nh nh?ng ch?a c? x?c nh?n blockchain th?nh c?ng</small>
                </article>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-xl-6">
                <section class="chart-card h-100">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="chart-title">Ch?ng ch? g?n ??y</h5>
                            <p class="dashboard-card-copy mb-0">C?c ch?ng ch? m?i c?p v? tr?ng th?i neo l?n FireFly.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>M? ch?ng ch?</th>
                                    <th>H?c vi?n</th>
                                    <th>Kh?a h?c</th>
                                    <th>Tr?ng th?i</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blockchainSummary['recent_certificates'] as $certificate)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $certificate['number'] }}</div>
                                            <small class="text-muted">{{ optional($certificate['issued_at'])->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>{{ $certificate['student'] }}</td>
                                        <td>
                                            <div>{{ $certificate['course'] }}</div>
                                            <small class="text-muted">{{ $certificate['class'] }}</small>
                                        </td>
                                        <td>
                                            @if($certificate['anchored'])
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">?? neo</span>
                                                <div class="small text-muted mt-1">{{ $certificate['tx_id'] ?? $certificate['message_id'] ?? 'C? proof' }}</div>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Ch? neo</span>
                                                <div class="small text-muted mt-1">{{ $certificate['state'] ?? 'Ch?a ghi nh?n proof' }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Ch?a c? ch?ng ch? n?o ?? hi?n th?.</td>
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
                            <h5 class="chart-title">Giao d?ch g?n ??y</h5>
                            <p class="dashboard-card-copy mb-0">Danh s?ch giao d?ch v? ?? ho?n th?nh v? tr?ng th?i proof tr?n FireFly.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tham chi?u</th>
                                    <th>H?c vi?n</th>
                                    <th>Ph??ng th?c</th>
                                    <th>S? ti?n</th>
                                    <th>Tr?ng th?i</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blockchainSummary['recent_transactions'] as $transaction)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $transaction['reference'] }}</div>
                                            <small class="text-muted">{{ optional($transaction['created_at'])->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>{{ $transaction['user'] }}</td>
                                        <td>{{ $transaction['method'] }}</td>
                                        <td>{{ $currency($transaction['amount']) }}</td>
                                        <td>
                                            @if($transaction['anchored'])
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">?? neo</span>
                                                <div class="small text-muted mt-1">{{ $transaction['tx_id'] ?? $transaction['message_id'] ?? 'C? proof' }}</div>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Ch? ki?m tra</span>
                                                <div class="small text-muted mt-1">{{ $transaction['state'] ?? 'Ch?a ghi nh?n proof' }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Ch?a c? giao d?ch ho?n th?nh ?? hi?n th?.</td>
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
