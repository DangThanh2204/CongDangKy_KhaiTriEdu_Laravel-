<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $siteName }} Admin</title>

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $siteFavicon) }}">
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        (function () {
            try {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = saved === 'dark' || (!saved && prefersDark);

                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
            } catch (error) {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
    @stack('styles')
    @auth
        @include('layouts.partials.browser-session-guard')
    @endauth
</head>
<body class="admin-layout @yield('page-class')">
    @php
        $adminPendingEnrollmentCount = data_get($adminAttentionSummary ?? [], 'pending_enrollment_count', 0);
        $adminNewReviewCount = data_get($adminAttentionSummary ?? [], 'new_review_count', 0);
        $adminPendingPaymentCount = data_get($adminAttentionSummary ?? [], 'pending_payment_count', 0);
        $adminPendingWalletTopupCount = data_get($adminAttentionSummary ?? [], 'pending_wallet_topup_count', 0);
        $adminPaymentAttentionCount = data_get($adminAttentionSummary ?? [], 'payment_attention_count', 0);
        $adminTotalAttentionCount = data_get($adminAttentionSummary ?? [], 'total_attention_count', 0);
        $adminHasAttentionItems = (bool) data_get($adminAttentionSummary ?? [], 'has_attention_items', false);

        $adminMenuGroups = [
            [
                'title' => 'Người dùng',
                'icon' => 'fas fa-users',
                'attention' => false,
                'attention_title' => null,
                'items' => [
                    [
                        'route' => 'admin.users.index',
                        'patterns' => ['admin.users.*'],
                        'label' => 'Tài khoản & vai trò',
                        'icon' => 'fas fa-user-shield',
                        'title' => 'Quản lý tài khoản người dùng',
                    ],
                ],
            ],
            [
                'title' => 'Nội dung',
                'icon' => 'fas fa-newspaper',
                'attention' => false,
                'attention_title' => null,
                'items' => [
                    [
                        'route' => 'admin.news-categories.index',
                        'patterns' => ['admin.news-categories.*'],
                        'label' => 'Danh mục tin tức',
                        'icon' => 'fas fa-folder-open',
                        'title' => 'Quản lý danh mục tin tức',
                    ],
                    [
                        'route' => 'admin.news.index',
                        'patterns' => ['admin.news.*'],
                        'label' => 'Tin tức',
                        'icon' => 'fas fa-newspaper',
                        'title' => 'Quản lý tin tức',
                    ],
                ],
            ],
            [
                'title' => 'Đào tạo',
                'icon' => 'fas fa-book-open',
                'attention' => $adminNewReviewCount > 0,
                'attention_title' => $adminNewReviewCount > 0 ? 'Có đánh giá mới cần xem' : null,
                'items' => [
                    [
                        'route' => 'admin.course-categories.index',
                        'patterns' => ['admin.course-categories.*'],
                        'label' => 'Nhóm ngành',
                        'icon' => 'fas fa-folder-tree',
                        'title' => 'Quản lý nhóm ngành',
                    ],
                    [
                        'route' => 'admin.courses.index',
                        'patterns' => ['admin.courses.*'],
                        'label' => 'Khóa học',
                        'icon' => 'fas fa-book-open',
                        'title' => 'Quản lý khóa học',
                    ],
                    [
                        'route' => 'admin.classes.index',
                        'patterns' => ['admin.classes.*'],
                        'label' => 'Đợt học & lớp',
                        'icon' => 'fas fa-chalkboard-teacher',
                        'title' => 'Quản lý đợt học và lớp học',
                    ],
                    [
                        'route' => 'admin.reviews.index',
                        'patterns' => ['admin.reviews.*'],
                        'label' => 'Đánh giá',
                        'icon' => 'fas fa-star',
                        'title' => 'Quản lý đánh giá khóa học',
                        'badge' => $adminNewReviewCount,
                        'badge_title' => $adminNewReviewCount > 0 ? $adminNewReviewCount . ' đánh giá mới' : null,
                    ],
                ],
            ],
            [
                'title' => 'Tuyển sinh & doanh thu',
                'icon' => 'fas fa-money-bill-wave',
                'attention' => ($adminPendingEnrollmentCount + $adminPaymentAttentionCount) > 0,
                'attention_title' => ($adminPendingEnrollmentCount + $adminPaymentAttentionCount) > 0 ? 'Có yêu cầu tuyển sinh hoặc thanh toán cần xử lý' : null,
                'items' => [
                    [
                        'route' => 'admin.enrollments.pending',
                        'patterns' => ['admin.enrollments.*'],
                        'label' => 'Đăng ký học',
                        'icon' => 'fas fa-user-graduate',
                        'title' => 'Quản lý đăng ký học',
                        'badge' => $adminPendingEnrollmentCount,
                        'badge_title' => $adminPendingEnrollmentCount > 0 ? $adminPendingEnrollmentCount . ' đăng ký chờ duyệt' : null,
                    ],
                    [
                        'route' => 'admin.payments.index',
                        'route_params' => ['status' => 'pending'],
                        'patterns' => ['admin.payments.*'],
                        'label' => 'Thanh toán khóa học',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'title' => 'Quản lý thanh toán khóa học',
                        'badge' => $adminPendingPaymentCount,
                        'badge_title' => $adminPendingPaymentCount > 0 ? $adminPendingPaymentCount . ' thanh toán chờ xử lý' : null,
                    ],
                    [
                        'route' => 'admin.wallet-transactions.index',
                        'route_params' => ['status' => 'pending'],
                        'patterns' => ['admin.wallet-transactions.*'],
                        'label' => 'Nạp ví thủ công',
                        'icon' => 'fas fa-wallet',
                        'title' => 'Duyệt nạp ví thủ công',
                        'badge' => $adminPendingWalletTopupCount,
                        'badge_title' => $adminPendingWalletTopupCount > 0 ? $adminPendingWalletTopupCount . ' yêu cầu nạp ví chờ duyệt' : null,
                    ],
                    [
                        'route' => 'admin.promotions.index',
                        'patterns' => ['admin.promotions.*'],
                        'label' => 'Khuyến mãi & voucher',
                        'icon' => 'fas fa-tags',
                        'title' => 'Quản lý khuyến mãi và voucher',
                    ],
                ],
            ],
            [
                'title' => 'Hệ thống & tích hợp',
                'icon' => 'fas fa-sliders',
                'attention' => false,
                'attention_title' => null,
                'items' => [
                    [
                        'route' => 'admin.system-logs.index',
                        'patterns' => ['admin.system-logs.*'],
                        'label' => 'Nhật ký hệ thống',
                        'icon' => 'fas fa-clipboard-list',
                        'title' => 'Nhật ký hệ thống',
                    ],
                    [
                        'route' => 'admin.backups.index',
                        'patterns' => ['admin.backups.*'],
                        'label' => 'Sao lưu dữ liệu',
                        'icon' => 'fas fa-shield-halved',
                        'title' => 'Sao lưu dữ liệu',
                    ],
                    [
                        'route' => 'admin.settings.index',
                        'patterns' => ['admin.settings.*'],
                        'label' => 'Cài đặt hệ thống',
                        'icon' => 'fas fa-cog',
                        'title' => 'Cài đặt hệ thống',
                    ],
                ],
            ],
        ];

        $adminAlertItems = [
            [
                'route' => route('admin.enrollments.pending'),
                'title' => 'Đăng ký chờ duyệt',
                'description' => 'Yêu cầu ghi danh offline mới',
                'count' => $adminPendingEnrollmentCount,
            ],
            [
                'route' => route('admin.reviews.index'),
                'title' => 'Đánh giá mới',
                'description' => 'Ý kiến mới từ học viên',
                'count' => $adminNewReviewCount,
            ],
            [
                'route' => route('admin.payments.index', ['status' => 'pending']),
                'title' => 'Thanh toán khóa học',
                'description' => 'Giao dịch đang chờ xử lý',
                'count' => $adminPendingPaymentCount,
            ],
            [
                'route' => route('admin.wallet-transactions.index', ['status' => 'pending']),
                'title' => 'Nạp ví thủ công',
                'description' => 'Yêu cầu topup direct và bank cần duyệt',
                'count' => $adminPendingWalletTopupCount,
            ],
        ];
    @endphp

    <nav class="admin-sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="brand">
                @if($siteLogo)
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="admin-brand-logo">
                @else
                    <i class="fas fa-graduation-cap me-2"></i>
                @endif
                <span class="brand-text">{{ $siteName }}</span>
                <small class="brand-subtitle">Admin Panel</small>
            </a>
        </div>

        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            @foreach($adminMenuGroups as $group)
                @php
                    $groupHasActiveItem = collect($group['items'])->contains(function (array $item) {
                        return collect($item['patterns'])->contains(fn (string $pattern) => request()->routeIs($pattern));
                    });
                @endphp
                <li class="sidebar-group">
                    <div class="sidebar-group-header {{ $groupHasActiveItem ? 'open' : '' }}">
                        <span class="sidebar-group-title">
                            <i class="{{ $group['icon'] }}"></i>
                            <span class="nav-text">{{ $group['title'] }}</span>
                            @if($group['attention'])
                                <span class="admin-attention-dot" title="{{ $group['attention_title'] }}"></span>
                            @endif
                        </span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <ul class="sidebar-subnav" data-group-title="{{ $group['title'] }}">
                        @foreach($group['items'] as $item)
                            @php
                                $itemIsActive = collect($item['patterns'])->contains(fn (string $pattern) => request()->routeIs($pattern));
                                $itemBadge = (int) ($item['badge'] ?? 0);
                            @endphp
                            <li class="nav-item">
                                <a href="{{ route($item['route'], $item['route_params'] ?? []) }}" class="nav-link {{ $itemIsActive ? 'active' : '' }}" title="{{ $item['title'] }}">
                                    <i class="{{ $item['icon'] }}"></i>
                                    @if($itemBadge > 0)
                                        <span class="nav-link-label">
                                            <span class="nav-text">{{ $item['label'] }}</span>
                                            <span class="admin-attention-dot" title="{{ $item['badge_title'] ?? ($itemBadge . ' mục cần xử lý') }}"></span>
                                        </span>
                                    @else
                                        <span class="nav-text">{{ $item['label'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>

        <div class="sidebar-footer">
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm mb-2 sidebar-btn" title="Về trang chủ">
                <i class="fas fa-home"></i>
                <span class="btn-text">Về trang chủ</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-100" data-browser-session-logout="manual">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm w-100 sidebar-btn" title="Đăng xuất">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="btn-text">Đăng xuất</span>
                </button>
            </form>
        </div>
    </nav>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="page-title">@yield('page-title', 'Dashboard')</h4>
            </div>

            <div class="topbar-right d-flex align-items-center">
                <button id="themeToggle" class="btn btn-sm btn-outline-secondary" title="Đổi giao diện sáng tối">
                    <i id="themeIcon" class="fas fa-moon"></i>
                </button>

                <div class="dropdown admin-alert-dropdown">
                    <button class="btn btn-sm admin-alert-bell dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Thông báo quản trị">
                        <i class="fas fa-bell"></i>
                        @if($adminHasAttentionItems)
                            <span class="admin-alert-bell-dot"></span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end admin-alert-menu">
                        <div class="admin-alert-menu-header">
                            <div>
                                <strong>Thông báo quản trị</strong>
                                <div class="small text-muted">Các mục mới cần admin kiểm tra</div>
                            </div>
                            @if($adminHasAttentionItems)
                                <span class="badge text-bg-danger">{{ $adminTotalAttentionCount > 99 ? '99+' : $adminTotalAttentionCount }}</span>
                            @endif
                        </div>
                        <div class="admin-alert-menu-list">
                            @foreach($adminAlertItems as $item)
                                <a href="{{ $item['route'] }}" class="dropdown-item admin-alert-item">
                                    <span class="item-copy">
                                        <strong>{{ $item['title'] }}</strong>
                                        <small>{{ $item['description'] }}</small>
                                    </span>
                                    <span class="badge {{ $item['count'] > 0 ? 'text-bg-danger' : 'text-bg-light' }}">{{ $item['count'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div id="notificationContainer" style="position:fixed;top:80px;right:20px;z-index:2000;pointer-events:none"></div>

                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name">{{ Auth::user()->fullname ?? Auth::user()->username }}</span>
                        <span class="user-role badge bg-danger">Admin</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const savedSidebarState = localStorage.getItem('sidebarCollapsed');

            if (savedSidebarState === 'true') {
                document.body.classList.add('sidebar-collapsed');
            } else if (savedSidebarState === 'false') {
                document.body.classList.remove('sidebar-collapsed');
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function () {
                    const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', String(isCollapsed));

                    document.querySelectorAll('.sidebar-subnav').forEach((panel) => {
                        panel.classList.remove('fixed', 'open');
                        panel.style.removeProperty('--sidebar-subnav-top');
                        panel.style.removeProperty('--sidebar-subnav-left');
                        panel.style.removeProperty('--sidebar-subnav-max-height');
                    });

                    document.querySelectorAll('.sidebar-group-header').forEach((groupHeader) => {
                        if (!groupHeader.parentElement?.querySelector('.nav-link.active')) {
                            groupHeader.classList.remove('open');
                        }
                    });
                });
            }

            function autoCloseAlerts() {
                document.querySelectorAll('.alert').forEach((alert) => {
                    setTimeout(() => {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-100%)';
                        setTimeout(() => {
                            if (window.bootstrap && bootstrap.Alert) {
                                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                                bsAlert.close();
                            } else {
                                alert.classList.remove('show');
                                alert.style.display = 'none';
                            }
                        }, 800);
                    }, 4200);
                });
            }

            function handleMobileSidebar() {
                if (window.innerWidth < 768) {
                    document.body.classList.add('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', 'true');
                } else if (savedSidebarState === null) {
                    document.body.classList.remove('sidebar-collapsed');
                }
            }

            autoCloseAlerts();
            handleMobileSidebar();
            window.addEventListener('resize', handleMobileSidebar);

            const groups = Array.from(document.querySelectorAll('.sidebar-group'));
            groups.forEach((group) => {
                const header = group.querySelector('.sidebar-group-header');
                const subnav = group.querySelector('.sidebar-subnav');

                if (!header || !subnav) {
                    return;
                }

                const positionSubnav = () => {
                    subnav.style.removeProperty('--sidebar-subnav-top');
                    subnav.style.removeProperty('--sidebar-subnav-left');
                    subnav.style.removeProperty('--sidebar-subnav-max-height');

                    const sidebar = document.querySelector('.admin-sidebar');
                    const sidebarRect = sidebar ? sidebar.getBoundingClientRect() : null;
                    const headerRect = header.getBoundingClientRect();
                    const estimatedWidth = Math.max(subnav.offsetWidth || 0, 250);
                    const estimatedHeight = Math.min(Math.max(subnav.scrollHeight || 0, 240), 420);
                    const preferredLeft = (sidebarRect ? sidebarRect.right : headerRect.right) + 18;
                    const left = Math.min(preferredLeft, window.innerWidth - estimatedWidth - 16);
                    const top = Math.min(Math.max(16, headerRect.top), window.innerHeight - estimatedHeight - 16);
                    const maxHeight = Math.max(220, window.innerHeight - top - 16);

                    subnav.classList.add('fixed');
                    subnav.style.setProperty('--sidebar-subnav-left', `${left}px`);
                    subnav.style.setProperty('--sidebar-subnav-top', `${top}px`);
                    subnav.style.setProperty('--sidebar-subnav-max-height', `${maxHeight}px`);
                };

                group.addEventListener('mouseenter', () => {
                    header.classList.add('open');
                    positionSubnav();
                    subnav.classList.add('open');
                });

                group.addEventListener('mouseleave', () => {
                    const isActiveGroup = subnav.querySelector('.nav-link.active');
                    subnav.classList.remove('open');

                    if (!isActiveGroup) {
                        header.classList.remove('open');
                    }
                });

                window.addEventListener('resize', () => {
                    if (subnav.classList.contains('open')) {
                        positionSubnav();
                    }
                });
            });

            // Bootstrap tooltips for individual icons (Dashboard, Home, Logout, etc.)
            // when the sidebar is collapsed. Bootstrap uses position:fixed via Popper,
            // so the tooltip escapes the sidebar's overflow:hidden and stays fully
            // visible on the right side. We toggle init/dispose on collapse changes.
            function refreshSidebarTooltips() {
                if (!window.bootstrap || !bootstrap.Tooltip) return;

                const collapsed = document.body.classList.contains('sidebar-collapsed');
                const targets = document.querySelectorAll(
                    '.admin-sidebar .nav-item > .nav-link[title], .admin-sidebar .sidebar-btn[title]'
                );

                targets.forEach((el) => {
                    const existing = bootstrap.Tooltip.getInstance(el);
                    if (collapsed) {
                        if (!existing) {
                            bootstrap.Tooltip.getOrCreateInstance(el, {
                                placement: 'right',
                                trigger: 'hover',
                                delay: { show: 80, hide: 50 },
                                container: 'body',
                            });
                        }
                    } else if (existing) {
                        existing.dispose();
                    }
                });
            }

            refreshSidebarTooltips();
            // Also re-evaluate when the toggle button is clicked.
            sidebarToggle?.addEventListener('click', () => {
                setTimeout(refreshSidebarTooltips, 50);
            });
            window.addEventListener('resize', () => {
                setTimeout(refreshSidebarTooltips, 100);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
