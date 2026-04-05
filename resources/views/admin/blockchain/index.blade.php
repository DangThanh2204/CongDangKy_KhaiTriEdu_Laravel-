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
        $healthMessage = data_get($blockchainSummary, 'firefly_health.message');
        $healthEndpoint = data_get($blockchainSummary, 'firefly_health.endpoint');
        $totalPending = (int) ($blockchainSummary['pending_certificates'] + $blockchainSummary['pending_transactions']);
    @endphp

    <div class="dashboard-shell">
        <section class="chart-card dashboard-hero mb-4">
            <div class="dashboard-hero-copy">
                <span class="dashboard-kicker">Hyperledger FireFly</span>
                <h2>Theo dÃµi chá»©ng chá» vÃ  giao dá»ch ÄÃ£ neo blockchain</h2>
                <p class="mb-0">Trang nÃ y giÃºp admin kiá»m tra FireFly Äang live hay chÆ°a, token integration ÄÃ£ sáºµn sÃ ng chÆ°a, vÃ  Äá»ng bá» láº¡i cÃ¡c chá»©ng chá» hoáº·c giao dá»ch cÅ© chÆ°a cÃ³ proof blockchain.</p>
                <div class="dashboard-hero-pills">
                    <span class="dashboard-pill"><i class="fas fa-certificate"></i>{{ number_format($blockchainSummary['anchored_certificates']) }} chá»©ng chá» ÄÃ£ neo</span>
                    <span class="dashboard-pill"><i class="fas fa-wallet"></i>{{ number_format($blockchainSummary['anchored_transactions']) }} giao dá»ch ÄÃ£ neo</span>
                    <span class="dashboard-pill"><i class="fas fa-network-wired"></i>{{ $blockchainSummary['namespace'] ?: '-' }}</span>
                </div>
            </div>
            <div class="dashboard-hero-summary">
                <article class="dashboard-summary-card">
                    <span class="summary-label">Tr?ng th?i FireFly</span>
                    <strong>{{ $blockchainSummary['firefly_connected'] ? 'Äang káº¿t ná»i' : 'ChÆ°a káº¿t ná»i' }}</strong>
                    <small>{{ $healthMessage ?: ('Platform: ' . ($blockchainSummary['platform_identity'] ?: '-')) }}</small>
                </article>
                <article class="dashboard-summary-card">
                    <span class="summary-label">Token integration</span>
                    <strong>{{ $blockchainSummary['token_ready'] ? 'Sáºµn sÃ ng' : 'ChÆ°a sáºµn sÃ ng' }}</strong>
                    <small>Audit topic: {{ $blockchainSummary['audit_topic'] ?: 'audit' }}</small>
                </article>
            </div>
        </section>

        <section class="chart-card dashboard-admissions-card mb-4">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="chart-title">T?ng quan neo blockchain</h5>
                    <p class="dashboard-card-copy mb-0">Thá»ng kÃª nhanh cho cÃ¡c báº£n ghi nghiá»p vá»¥ quan trá»ng Äang ÄÆ°á»£c gáº¯n vá»i FireFly.</p>
                </div>
                <form method="POST" action="{{ route('admin.blockchain.sync') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-rotate me-2"></i>Äá»ng bá» báº£n ghi chÆ°a neo
                    </button>
                </form>
            </div>

            <div class="dashboard-admissions-grid">
                <article class="dashboard-kpi-card is-blue">
                    <span class="dashboard-kpi-eyebrow">Chá»©ng chá» ÄÃ£ neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['anchored_certificates']) }}</strong>
                    <small class="dashboard-kpi-note">CÃ¡c chá»©ng chá» cÃ³ proof thÃ nh cÃ´ng trÃªn FireFly</small>
                </article>
                <article class="dashboard-kpi-card is-orange">
                    <span class="dashboard-kpi-eyebrow">Chá»©ng chá» chá» neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['pending_certificates']) }}</strong>
                    <small class="dashboard-kpi-note">CÃ³ chá»©ng chá» nhÆ°ng chÆ°a cÃ³ proof blockchain thÃ nh cÃ´ng</small>
                </article>
                <article class="dashboard-kpi-card is-green">
                    <span class="dashboard-kpi-eyebrow">Giao dá»ch ÄÃ£ neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['anchored_transactions']) }}</strong>
                    <small class="dashboard-kpi-note">Topup vÃ­ hoáº·c chi tiÃªu vÃ­ ÄÃ£ cÃ³ message / tx id</small>
                </article>
                <article class="dashboard-kpi-card is-slate">
                    <span class="dashboard-kpi-eyebrow">Giao d?ch c?n ki?m tra</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['pending_transactions']) }}</strong>
                    <small class="dashboard-kpi-note">Giao dá»ch hoÃ n thÃ nh nhÆ°ng chÆ°a cÃ³ xÃ¡c nháº­n blockchain thÃ nh cÃ´ng</small>
                </article>
            </div>
        </section>

        <div class="dashboard-ratio-grid mb-4">
            <article class="dashboard-ratio-card">
                <div class="dashboard-ratio-head">
                    <div>
                        <span class="dashboard-ratio-label">K?t n?i FireFly</span>
                        <strong>{{ $blockchainSummary['firefly_connected'] ? 'Äang live' : 'ChÆ°a live' }}</strong>
                    </div>
                    <span class="dashboard-ratio-percent">{{ $blockchainSummary['firefly_connected'] ? 'OK' : 'OFF' }}</span>
                </div>
                <div class="dashboard-ratio-meta">
                    <span>Namespace: {{ $blockchainSummary['namespace'] ?: '-' }}</span>
                    <span>Platform: {{ $blockchainSummary['platform_identity'] ?: '-' }}</span>
                </div>
            </article>

            <article class="dashboard-ratio-card">
                <div class="dashboard-ratio-head">
                    <div>
                        <span class="dashboard-ratio-label">Token integration</span>
                        <strong>{{ $blockchainSummary['token_ready'] ? 'Sáºµn sÃ ng mint / transfer' : 'Thiáº¿u cáº¥u hÃ¬nh token' }}</strong>
                    </div>
                    <span class="dashboard-ratio-percent">{{ $blockchainSummary['token_ready'] ? 'READY' : 'SETUP' }}</span>
                </div>
                <div class="dashboard-ratio-meta">
                    <span>Audit topic: {{ $blockchainSummary['audit_topic'] ?: 'audit' }}</span>
                    <span>{{ $healthEndpoint ?: 'ChÆ°a cÃ³ endpoint live' }}</span>
                </div>
            </article>

            <article class="dashboard-ratio-card dashboard-revenue-card">
                <div class="dashboard-ratio-head">
                    <div>
                        <span class="dashboard-ratio-label">Äá»ng bá» cáº§n xá»­ lÃ½</span>
                        <strong>{{ number_format($totalPending) }} báº£n ghi chá» Äá»ng bá»</strong>
                    </div>
                    <span class="dashboard-ratio-percent">{{ number_format($totalPending) }}</span>
                </div>
                <div class="dashboard-revenue-list">
                    <div class="dashboard-revenue-item">
                        <span>Chá»©ng chá» chá» neo</span>
                        <strong>{{ number_format($blockchainSummary['pending_certificates']) }}</strong>
                    </div>
                    <div class="dashboard-revenue-item">
                        <span>Giao d?ch ch? neo</span>
                        <strong>{{ number_format($blockchainSummary['pending_transactions']) }}</strong>
                    </div>
                    <div class="dashboard-revenue-item is-total">
                        <span>T?ng c?ng</span>
                        <strong>{{ number_format($totalPending) }}</strong>
                    </div>
                </div>
            </article>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <section class="chart-card h-100">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="chart-title">Chá»©ng chá» gáº§n ÄÃ¢y</h5>
                            <p class="dashboard-card-copy mb-0">CÃ¡c chá»©ng chá» má»i cáº¥p vÃ  tráº¡ng thÃ¡i neo lÃªn FireFly.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>MÃ£ chá»©ng chá»</th>
                                    <th>H?c vi?n</th>
                                    <th>Kh?a h?c</th>
                                    <th>Tr?ng th?i</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($blockchainSummary['recent_certificates'] as $certificate)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $certificate['code'] }}</div>
                                            <small class="text-muted">{{ optional($certificate['issued_at'])->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>{{ $certificate['user'] }}</td>
                                        <td>{{ $certificate['course'] }}</td>
                                        <td>
                                            @if($certificate['anchored'])
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">ÄÃ£ neo</span>
                                                <div class="small text-muted mt-1">{{ $certificate['tx_id'] ?? $certificate['message_id'] ?? 'CÃ³ proof' }}</div>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Ch? neo</span>
                                                <div class="small text-muted mt-1">{{ $certificate['state'] ?? 'ChÆ°a ghi nháº­n proof' }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">ChÆ°a cÃ³ chá»©ng chá» nÃ o Äá» hiá»n thá».</td>
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
                            <h5 class="chart-title">Giao dá»ch gáº§n ÄÃ¢y</h5>
                            <p class="dashboard-card-copy mb-0">Danh sÃ¡ch giao dá»ch vÃ­ ÄÃ£ hoÃ n thÃ nh vÃ  tráº¡ng thÃ¡i proof trÃªn FireFly.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tham chi?u</th>
                                    <th>H?c vi?n</th>
                                    <th>PhÆ°Æ¡ng thá»©c</th>
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
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">ÄÃ£ neo</span>
                                                <div class="small text-muted mt-1">{{ $transaction['tx_id'] ?? $transaction['message_id'] ?? 'CÃ³ proof' }}</div>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Ch? neo</span>
                                                <div class="small text-muted mt-1">{{ $transaction['state'] ?? 'ChÆ°a ghi nháº­n proof' }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">ChÆ°a cÃ³ giao dá»ch hoÃ n thÃ nh Äá» hiá»n thá».</td>
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
