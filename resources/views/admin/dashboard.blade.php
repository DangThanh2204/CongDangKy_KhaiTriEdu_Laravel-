@extends('layouts.admin')

@section('title', 'Tổng quan admin')
@section('page-title', 'Tổng quan hệ thống')
@section('page-class', 'page-admin-dashboard')

@push('styles')
    @vite('resources/css/pages/admin/dashboard.css')
@endpush

@section('content')
    @php
        $roleLabels = [
            'admin' => 'Admin',
            'staff' => 'Nhân sự',
            'instructor' => 'Giảng viên',
            'student' => 'Học viên',
        ];
        $totalRoleUsers = max((int) $usersByRole->sum(), 1);
        $currency = static fn ($value) => number_format((float) $value, 0, ',', '.') . 'đ';
    @endphp

    <div class="dashboard-shell">
        <section class="chart-card dashboard-hero mb-4">
            <div class="dashboard-hero-copy">
                <span class="dashboard-kicker">Dashboard admin</span>
                <h2>Quan sát nhanh tình hình học viên và khóa học</h2>
                <p class="mb-0">Biểu đồ bên dưới giúp admin nhìn nhanh số lượng đăng ký học và số khóa học mở mới theo ngày hoặc theo tháng, ngay trên một màn hình gọn.</p>
                <div class="dashboard-hero-pills">
                    <span class="dashboard-pill"><i class="fas fa-user-plus"></i>{{ number_format($stats['today_enrollments']) }} đăng ký hôm nay</span>
                    <span class="dashboard-pill"><i class="fas fa-book-open"></i>{{ number_format($stats['today_course_openings']) }} khóa học mở hôm nay</span>
                    <span class="dashboard-pill"><i class="fas fa-clock"></i>{{ number_format($stats['pending_enrollments']) }} đăng ký chờ duyệt</span>
                </div>
            </div>
            <div class="dashboard-hero-summary">
                <article class="dashboard-summary-card">
                    <span class="summary-label">Trong tháng này</span>
                    <strong>{{ number_format($stats['monthly_enrollments']) }}</strong>
                    <small>lượt đăng ký học mới</small>
                </article>
                <article class="dashboard-summary-card">
                    <span class="summary-label">Khóa học mở trong tháng</span>
                    <strong>{{ number_format($stats['monthly_course_openings']) }}</strong>
                    <small>bản ghi khóa học tạo mới</small>
                </article>
            </div>
        </section>

        <div class="stats-grid mb-4">
            <article class="stat-card users">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number">{{ number_format($stats['total_users']) }}</div>
                <div class="stat-label">Tổng tài khoản</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up me-1"></i>{{ number_format($stats['today_registrations']) }} tài khoản mới hôm nay
                </div>
            </article>

            <article class="stat-card courses">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-number">{{ number_format($stats['total_enrollments']) }}</div>
                <div class="stat-label">Tổng lượt đăng ký học</div>
                <div class="stat-change positive">
                    <i class="fas fa-check-circle me-1"></i>{{ number_format($stats['approved_enrollments']) }} đã duyệt
                </div>
            </article>

            <article class="stat-card revenue">
                <div class="stat-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-number">{{ number_format($stats['published_courses']) }}</div>
                <div class="stat-label">Khóa học đang hiển thị</div>
                <div class="stat-change positive">
                    <i class="fas fa-layer-group me-1"></i>{{ number_format($stats['total_courses']) }} khóa học trong hệ thống
                </div>
            </article>

            <article class="stat-card orders">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-number">{{ number_format($stats['pending_enrollments']) }}</div>
                <div class="stat-label">Đăng ký chờ duyệt</div>
                <div class="stat-change {{ $stats['pending_enrollments'] > 0 ? 'negative' : 'positive' }}">
                    <i class="fas {{ $stats['pending_enrollments'] > 0 ? 'fa-hourglass-half' : 'fa-circle-check' }} me-1"></i>{{ number_format($stats['completed_enrollments']) }} đã hoàn thành
                </div>
            </article>
        </div>

        <section class="chart-card dashboard-admissions-card mb-4">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="chart-title">Dashboard tuyển sinh</h5>
                    <p class="dashboard-card-copy mb-0">Tổng hợp nhanh lớp đầy, lịch khai giảng, trạng thái duyệt hồ sơ và doanh thu tuyển sinh theo đúng luồng đăng ký hiện tại.</p>
                </div>
                <span class="dashboard-chip">Cập nhật theo dữ liệu đang có trên hệ thống</span>
            </div>

            <div class="dashboard-admissions-grid">
                <article class="dashboard-kpi-card is-blue">
                    <span class="dashboard-kpi-eyebrow">Đăng ký trong tháng</span>
                    <strong class="dashboard-kpi-value">{{ number_format($stats['monthly_enrollments']) }}</strong>
                    <small class="dashboard-kpi-note">{{ number_format($stats['today_enrollments']) }} hồ sơ mới trong hôm nay</small>
                </article>
                <article class="dashboard-kpi-card is-orange">
                    <span class="dashboard-kpi-eyebrow">Lớp đã kín chỗ</span>
                    <strong class="dashboard-kpi-value">{{ number_format($stats['full_classes']) }}</strong>
                    <small class="dashboard-kpi-note">Tính cả ghế đang giữ chỗ ở lớp đang mở</small>
                </article>
                <article class="dashboard-kpi-card is-slate">
                    <span class="dashboard-kpi-eyebrow">Sắp khai giảng</span>
                    <strong class="dashboard-kpi-value">{{ number_format($stats['upcoming_classes']) }}</strong>
                    <small class="dashboard-kpi-note">Các lớp dự kiến mở trong 30 ngày tới</small>
                </article>
                <article class="dashboard-kpi-card is-green">
                    <span class="dashboard-kpi-eyebrow">Doanh thu tuyển sinh</span>
                    <strong class="dashboard-kpi-value">{{ $currency($stats['total_revenue']) }}</strong>
                    <small class="dashboard-kpi-note">Tổng ghi nhận từ thanh toán ví nội bộ và VNPay</small>
                </article>
            </div>

            <div class="dashboard-ratio-grid">
                <article class="dashboard-ratio-card">
                    <div class="dashboard-ratio-head">
                        <div>
                            <span class="dashboard-ratio-label">Tỷ lệ lớp online / offline</span>
                            <strong>{{ number_format($stats['online_classes']) }} online · {{ number_format($stats['offline_classes']) }} offline</strong>
                        </div>
                        <span class="dashboard-ratio-percent">{{ number_format($stats['online_ratio'], 1, ',', '.') }}%</span>
                    </div>
                    <div class="dashboard-ratio-track">
                        <span class="dashboard-ratio-fill is-online" style="width: {{ max($stats['online_ratio'], $stats['online_classes'] > 0 ? 8 : 0) }}%"></span>
                    </div>
                    <div class="dashboard-ratio-meta">
                        <span>Online {{ number_format($stats['online_ratio'], 1, ',', '.') }}%</span>
                        <span>Offline {{ number_format($stats['offline_ratio'], 1, ',', '.') }}%</span>
                    </div>
                </article>

                <article class="dashboard-ratio-card">
                    <div class="dashboard-ratio-head">
                        <div>
                            <span class="dashboard-ratio-label">Tỷ lệ pending / approved</span>
                            <strong>{{ number_format($stats['approved_enrollments']) }} đã duyệt · {{ number_format($stats['pending_enrollments']) }} chờ duyệt</strong>
                        </div>
                        <span class="dashboard-ratio-percent">{{ number_format($stats['approved_ratio'], 1, ',', '.') }}%</span>
                    </div>
                    <div class="dashboard-ratio-track">
                        <span class="dashboard-ratio-fill is-approved" style="width: {{ max($stats['approved_ratio'], $stats['approved_enrollments'] > 0 ? 8 : 0) }}%"></span>
                    </div>
                    <div class="dashboard-ratio-meta">
                        <span>Đã duyệt {{ number_format($stats['approved_ratio'], 1, ',', '.') }}%</span>
                        <span>Chờ duyệt {{ number_format($stats['pending_ratio'], 1, ',', '.') }}%</span>
                    </div>
                </article>

                <article class="dashboard-ratio-card dashboard-revenue-card">
                    <div class="dashboard-ratio-head">
                        <div>
                            <span class="dashboard-ratio-label">Doanh thu theo nguồn thu</span>
                            <strong>{{ $currency($stats['total_revenue']) }} tổng ghi nhận</strong>
                        </div>
                        <span class="dashboard-ratio-percent">{{ number_format($stats['monthly_enrollments']) }}</span>
                    </div>
                    <div class="dashboard-revenue-list">
                        <div class="dashboard-revenue-item">
                            <span>Thanh toán bằng ví</span>
                            <strong>{{ $currency($stats['wallet_revenue']) }}</strong>
                        </div>
                        <div class="dashboard-revenue-item">
                            <span>VNPay</span>
                            <strong>{{ $currency($stats['vnpay_revenue']) }}</strong>
                        </div>
                        <div class="dashboard-revenue-item is-total">
                            <span>Tổng cộng</span>
                            <strong>{{ $currency($stats['total_revenue']) }}</strong>
                        </div>
                    </div>
                </article>
            </div>
        </section>


        <section class="chart-card dashboard-admissions-card mb-4" id="blockchain-insights">
            <div class="dashboard-card-header">
                <div>
                    <h5 class="chart-title">Blockchain FireFly</h5>
                    <p class="dashboard-card-copy mb-0">Theo dÃµi nhanh chá»©ng chá» vÃ  giao dá»ch ÄÃ£ ÄÆ°á»£c neo lÃªn blockchain Äá» phá»¥c vá»¥ xÃ¡c thá»±c cÃ´ng khai.</p>
                </div>
                <a href="{{ route('admin.blockchain.dashboard') }}" class="btn btn-sm btn-outline-primary">Má» dashboard blockchain</a>
            </div>

            <div class="dashboard-admissions-grid">
                <article class="dashboard-kpi-card is-blue">
                    <span class="dashboard-kpi-eyebrow">Chá»©ng chá» ÄÃ£ neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['anchored_certificates']) }}</strong>
                    <small class="dashboard-kpi-note">CÃ³ proof thÃ nh cÃ´ng trÃªn FireFly</small>
                </article>
                <article class="dashboard-kpi-card is-orange">
                    <span class="dashboard-kpi-eyebrow">Chá»©ng chá» chá» neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['pending_certificates']) }}</strong>
                    <small class="dashboard-kpi-note">ÄÃ£ cáº¥p nhÆ°ng chÆ°a cÃ³ proof blockchain</small>
                </article>
                <article class="dashboard-kpi-card is-green">
                    <span class="dashboard-kpi-eyebrow">Giao dá»ch ÄÃ£ neo</span>
                    <strong class="dashboard-kpi-value">{{ number_format($blockchainSummary['anchored_transactions']) }}</strong>
                    <small class="dashboard-kpi-note">VÃ­ vÃ  náº¡p tiá»n ÄÃ£ ghi nháº­n message / tx id</small>
                </article>
                <article class="dashboard-kpi-card is-slate">
                    <span class="dashboard-kpi-eyebrow">FireFly hiá»n táº¡i</span>
                    <strong class="dashboard-kpi-value">{{ $blockchainSummary['firefly_connected'] ? 'Äang káº¿t ná»i' : 'ChÆ°a káº¿t ná»i' }}</strong>
                    <small class="dashboard-kpi-note">Namespace {{ $blockchainSummary['namespace'] }}</small>
                </article>
            </div>
        </section>

        <div class="charts-section dashboard-main-grid mb-4">
            <section class="chart-card dashboard-trend-card" data-admin-trend-root>
                <div class="dashboard-card-header dashboard-trend-header">
                    <div>
                        <h5 class="chart-title">Xu hướng đăng ký và mở khóa học</h5>
                        <p class="dashboard-card-copy mb-0">Dữ liệu được lấy theo thời điểm tạo bản ghi đăng ký và khóa học trên hệ thống.</p>
                    </div>
                    <div class="dashboard-range-switch" role="group" aria-label="Chọn kiểu xem biểu đồ">
                        <button type="button" class="dashboard-range-button is-active" data-trend-range="daily">Theo ngày</button>
                        <button type="button" class="dashboard-range-button" data-trend-range="monthly">Theo tháng</button>
                    </div>
                </div>

                <div class="dashboard-trend-summary">
                    <article class="dashboard-trend-metric">
                        <span>Tổng đăng ký trong kỳ</span>
                        <strong data-trend-summary="enrollments-total">0</strong>
                        <small data-trend-summary="range-label">14 ngày gần nhất</small>
                    </article>
                    <article class="dashboard-trend-metric">
                        <span>Tổng khóa học mở trong kỳ</span>
                        <strong data-trend-summary="courses-total">0</strong>
                        <small data-trend-summary="average-label">Trung bình 0 / mốc</small>
                    </article>
                    <article class="dashboard-trend-metric">
                        <span>Mốc cao nhất</span>
                        <strong data-trend-summary="peak-value">0</strong>
                        <small data-trend-summary="peak-label">đăng ký hoặc khóa học trong một mốc</small>
                    </article>
                </div>

                <div class="dashboard-chart-frame">
                    <canvas id="adminTrendChart" class="dashboard-trend-canvas" height="320" aria-label="Biểu đồ xu hướng đăng ký và mở khóa học" role="img"></canvas>
                </div>

                <div class="dashboard-trend-legend">
                    <span class="legend-item"><span class="legend-dot legend-dot-enrollment"></span>Học viên đăng ký</span>
                    <span class="legend-item"><span class="legend-dot legend-dot-course"></span>Khóa học mở mới</span>
                </div>
            </section>

            <div class="dashboard-side-stack">
                <section class="chart-card dashboard-info-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="chart-title">Trạng thái nhanh</h5>
                            <p class="dashboard-card-copy mb-0">Các chỉ số cần nhìn nhanh trong ca làm việc hôm nay.</p>
                        </div>
                    </div>
                    <div class="dashboard-status-list">
                        <div class="dashboard-status-item">
                            <span>Tài khoản đã xác thực</span>
                            <strong>{{ number_format($stats['verified_users']) }}</strong>
                        </div>
                        <div class="dashboard-status-item">
                            <span>Tài khoản chưa xác thực</span>
                            <strong>{{ number_format($stats['unverified_users']) }}</strong>
                        </div>
                        <div class="dashboard-status-item">
                            <span>Đăng ký user trong tuần</span>
                            <strong>{{ number_format($stats['weekly_registrations']) }}</strong>
                        </div>
                        <div class="dashboard-status-item">
                            <span>Học viên hệ thống</span>
                            <strong>{{ number_format($stats['total_students']) }}</strong>
                        </div>
                    </div>
                </section>

                <section class="chart-card dashboard-info-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="chart-title">Thao tác nhanh</h5>
                            <p class="dashboard-card-copy mb-0">Đi thẳng đến các khu vực admin hay dùng nhất.</p>
                        </div>
                    </div>
                    <div class="dashboard-action-grid">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-users me-2"></i>Quản lý tài khoản
                        </a>
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-book-open me-2"></i>Quản lý khóa học
                        </a>
                        <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-clock me-2"></i>Duyệt đăng ký
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-cog me-2"></i>Cài đặt hệ thống
                        </a>
                    </div>
                </section>

                <section class="chart-card dashboard-info-card">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="chart-title">Cảnh báo bảo mật</h5>
                            <p class="dashboard-card-copy mb-0">Theo dõi nhanh các cảnh báo trong 24 giờ gần nhất.</p>
                        </div>
                        <a href="{{ route('admin.system-logs.index', ['category' => 'security', 'action' => 'security_alert']) }}" class="btn btn-sm btn-outline-danger">
                            Xem log
                            <span id="security-alert-badge" class="badge bg-danger ms-2">{{ $securityAlertsCount }}</span>
                        </a>
                    </div>

                    @if($latestSecurityAlert)
                        <div class="dashboard-alert-box">
                            <strong>{{ $latestSecurityAlert->details['type'] ?? $latestSecurityAlert->details['message'] ?? $latestSecurityAlert->action }}</strong>
                            <small>Ghi nhận {{ $latestSecurityAlert->created_at->diffForHumans() }}</small>
                            <span>IP: {{ $latestSecurityAlert->ip ?? 'Chưa rõ' }}</span>
                            <span>Người dùng: {{ $latestSecurityAlert->user?->fullname ?? 'Hệ thống' }}</span>
                        </div>
                    @else
                        <div class="dashboard-empty-state">
                            <i class="fas fa-shield-halved"></i>
                            <p class="mb-0">Chưa có cảnh báo mới trong 24 giờ gần nhất.</p>
                        </div>
                    @endif
                </section>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <section class="chart-card h-100">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="chart-title">Tài khoản mới gần đây</h5>
                            <p class="dashboard-card-copy mb-0">Danh sách 6 tài khoản vừa được tạo gần nhất trên hệ thống.</p>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                    </div>

                    <div class="activity-list">
                        @forelse($recentUsers as $user)
                            <div class="activity-item">
                                <div class="activity-icon {{ $user->is_verified ? 'success' : 'warning' }}">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">{{ $user->fullname ?: $user->name ?: $user->email }}</div>
                                    <div class="activity-desc d-flex flex-wrap align-items-center gap-2">
                                        <span class="badge text-bg-light">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span>
                                        <span>{{ $user->email }}</span>
                                        @unless($user->is_verified)
                                            <span class="badge bg-warning text-dark">Chưa xác thực</span>
                                        @endunless
                                    </div>
                                    <div class="activity-time">{{ $user->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty-state py-4">
                                <i class="fas fa-users"></i>
                                <p class="mb-0">Chưa có tài khoản mới để hiển thị.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-xl-5">
                <section class="chart-card h-100">
                    <div class="dashboard-card-header">
                        <div>
                            <h5 class="chart-title">Phân bổ tài khoản</h5>
                            <p class="dashboard-card-copy mb-0">Tỷ trọng từng vai trò và số lượng user đăng ký trong năm nay.</p>
                        </div>
                    </div>

                    <div class="dashboard-role-list">
                        @forelse($usersByRole as $role => $count)
                            @php $percentage = round(($count / $totalRoleUsers) * 100, 1); @endphp
                            <div class="dashboard-role-item">
                                <div class="dashboard-role-copy">
                                    <strong>{{ $roleLabels[$role] ?? ucfirst($role) }}</strong>
                                    <small>{{ number_format($count) }} tài khoản</small>
                                </div>
                                <div class="dashboard-role-meta">{{ $percentage }}%</div>
                                <div class="progress dashboard-role-progress" role="progressbar" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty-state py-4">
                                <i class="fas fa-chart-pie"></i>
                                <p class="mb-0">Chưa có dữ liệu phân bổ tài khoản.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="dashboard-month-grid mt-4">
                        @foreach(range(1, 12) as $month)
                            <article class="dashboard-month-card">
                                <strong>{{ number_format($monthlyRegistrations[$month] ?? 0) }}</strong>
                                <span>Th{{ str_pad((string) $month, 2, '0', STR_PAD_LEFT) }}</span>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartRoot = document.querySelector('[data-admin-trend-root]');
            const chartCanvas = document.getElementById('adminTrendChart');
            const securityAlertBadge = document.getElementById('security-alert-badge');
            const trendData = @json($dashboardTrend, JSON_UNESCAPED_UNICODE);

            if (!chartRoot || !chartCanvas) {
                return;
            }

            const summaryTargets = {
                enrollmentsTotal: chartRoot.querySelector('[data-trend-summary="enrollments-total"]'),
                coursesTotal: chartRoot.querySelector('[data-trend-summary="courses-total"]'),
                rangeLabel: chartRoot.querySelector('[data-trend-summary="range-label"]'),
                averageLabel: chartRoot.querySelector('[data-trend-summary="average-label"]'),
                peakValue: chartRoot.querySelector('[data-trend-summary="peak-value"]'),
                peakLabel: chartRoot.querySelector('[data-trend-summary="peak-label"]'),
            };
            const rangeButtons = chartRoot.querySelectorAll('[data-trend-range]');
            const context = chartCanvas.getContext('2d');
            let activeRange = 'daily';
            let resizeTimer = null;

            function getThemeValue(name, fallback) {
                const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
                return value || fallback;
            }

            function formatNumber(value) {
                return Number(value || 0).toLocaleString('vi-VN');
            }

            function hexToRgba(hex, alpha) {
                const normalized = hex.replace('#', '');
                if (normalized.length !== 6) {
                    return `rgba(37, 99, 235, ${alpha})`;
                }

                const red = parseInt(normalized.substring(0, 2), 16);
                const green = parseInt(normalized.substring(2, 4), 16);
                const blue = parseInt(normalized.substring(4, 6), 16);
                return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
            }

            function updateSummary(dataset) {
                const maxPoint = Math.max(...dataset.enrollments, ...dataset.courses, 0);
                const averageCourses = dataset.courses.length ? (dataset.meta.courses_total / dataset.courses.length) : 0;

                summaryTargets.enrollmentsTotal.textContent = formatNumber(dataset.meta.enrollments_total);
                summaryTargets.coursesTotal.textContent = formatNumber(dataset.meta.courses_total);
                summaryTargets.rangeLabel.textContent = dataset.meta.range_label;
                summaryTargets.averageLabel.textContent = `Trung bình ${averageCourses.toFixed(1).replace('.', ',')} / mốc`;
                summaryTargets.peakValue.textContent = formatNumber(maxPoint);
                summaryTargets.peakLabel.textContent = 'đăng ký hoặc khóa học trong một mốc';
            }

            function buildPoints(series, labels, plot, maxValue) {
                if (!labels.length) {
                    return [];
                }

                const stepX = labels.length > 1 ? plot.width / (labels.length - 1) : 0;

                return series.map((value, index) => ({
                    x: plot.left + (labels.length > 1 ? stepX * index : plot.width / 2),
                    y: plot.top + plot.height - ((value / maxValue) * plot.height),
                    value,
                    label: labels[index],
                }));
            }

            function drawSeries(points, color, baseline, shouldFill) {
                if (!points.length) {
                    return;
                }

                if (shouldFill) {
                    context.beginPath();
                    context.moveTo(points[0].x, baseline);
                    points.forEach((point) => context.lineTo(point.x, point.y));
                    context.lineTo(points[points.length - 1].x, baseline);
                    context.closePath();
                    context.fillStyle = hexToRgba(color, 0.10);
                    context.fill();
                }

                context.beginPath();
                points.forEach((point, index) => {
                    if (index === 0) {
                        context.moveTo(point.x, point.y);
                    } else {
                        context.lineTo(point.x, point.y);
                    }
                });
                context.strokeStyle = color;
                context.lineWidth = 3;
                context.lineCap = 'round';
                context.lineJoin = 'round';
                context.stroke();

                points.forEach((point) => {
                    context.beginPath();
                    context.arc(point.x, point.y, 4, 0, Math.PI * 2);
                    context.fillStyle = '#ffffff';
                    context.fill();
                    context.lineWidth = 2.5;
                    context.strokeStyle = color;
                    context.stroke();
                });
            }

            function renderChart() {
                const dataset = trendData[activeRange] || trendData.daily;
                const labels = dataset.labels || [];
                const allValues = [...dataset.enrollments, ...dataset.courses];
                const highestValue = Math.max(...allValues, 1);
                const gridSteps = 4;
                const niceMax = Math.max(gridSteps, Math.ceil(highestValue / gridSteps) * gridSteps);
                const width = chartCanvas.clientWidth || chartCanvas.parentElement.clientWidth || 760;
                const height = 320;
                const dpr = window.devicePixelRatio || 1;
                const plot = {
                    left: 46,
                    right: 20,
                    top: 20,
                    bottom: 42,
                };

                chartCanvas.width = Math.floor(width * dpr);
                chartCanvas.height = Math.floor(height * dpr);
                chartCanvas.style.height = `${height}px`;
                context.setTransform(dpr, 0, 0, dpr, 0, 0);
                context.clearRect(0, 0, width, height);

                plot.width = width - plot.left - plot.right;
                plot.height = height - plot.top - plot.bottom;

                const enrollmentColor = getThemeValue('--trend-enrollment', '#2563eb');
                const courseColor = getThemeValue('--trend-course', '#f97316');
                const gridColor = getThemeValue('--trend-grid', 'rgba(148, 163, 184, 0.24)');
                const axisColor = getThemeValue('--trend-axis', '#64748b');

                context.font = '12px "Segoe UI", sans-serif';
                context.textBaseline = 'middle';
                context.fillStyle = axisColor;
                context.strokeStyle = gridColor;
                context.lineWidth = 1;

                for (let step = 0; step <= gridSteps; step += 1) {
                    const y = plot.top + plot.height - ((plot.height / gridSteps) * step);
                    const value = Math.round((niceMax / gridSteps) * step);

                    context.beginPath();
                    context.moveTo(plot.left, y);
                    context.lineTo(width - plot.right, y);
                    context.stroke();
                    context.fillText(String(value), 8, y);
                }

                const labelInterval = labels.length > 10 ? 2 : 1;
                labels.forEach((label, index) => {
                    if (index % labelInterval !== 0 && index !== labels.length - 1) {
                        return;
                    }

                    const x = labels.length > 1
                        ? plot.left + ((plot.width / (labels.length - 1)) * index)
                        : plot.left + (plot.width / 2);

                    context.textAlign = 'center';
                    context.fillText(label, x, height - 14);
                });

                const enrollmentPoints = buildPoints(dataset.enrollments, labels, plot, niceMax);
                const coursePoints = buildPoints(dataset.courses, labels, plot, niceMax);

                drawSeries(enrollmentPoints, enrollmentColor, plot.top + plot.height, true);
                drawSeries(coursePoints, courseColor, plot.top + plot.height, false);
                updateSummary(dataset);
            }

            rangeButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    activeRange = button.dataset.trendRange || 'daily';
                    rangeButtons.forEach((item) => item.classList.toggle('is-active', item === button));
                    renderChart();
                });
            });

            window.addEventListener('resize', function () {
                window.clearTimeout(resizeTimer);
                resizeTimer = window.setTimeout(renderChart, 120);
            });

            const themeObserver = new MutationObserver(renderChart);
            themeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class', 'data-bs-theme'],
            });

            async function fetchAlertsCount() {
                try {
                    const response = await fetch('{{ route('admin.alerts.count') }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    if (securityAlertBadge) {
                        securityAlertBadge.textContent = payload.count || 0;
                    }
                } catch (error) {
                    // ignore polling errors
                }
            }

            renderChart();
            fetchAlertsCount();
            window.setInterval(fetchAlertsCount, 60000);
        });
    </script>
@endpush