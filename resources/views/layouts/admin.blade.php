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
                'title' => 'NgÆ°á»i dÃ¹ng',
                'icon' => 'fas fa-users',
                'attention' => false,
                'attention_title' => null,
                'items' => [
                    [
                        'route' => 'admin.users.index',
                        'patterns' => ['admin.users.*'],
                        'label' => 'TÃ i khoáº£n & vai trÃ²',
                        'icon' => 'fas fa-user-shield',
                        'title' => 'Quáº£n lÃ½ tÃ i khoáº£n ngÆ°á»i dÃ¹ng',
                    ],
                ],
            ],
            [
                'title' => 'Ná»™i dung',
                'icon' => 'fas fa-newspaper',
                'attention' => false,
                'attention_title' => null,
                'items' => [
                    [
                        'route' => 'admin.news-categories.index',
                        'patterns' => ['admin.news-categories.*'],
                        'label' => 'Danh má»¥c tin tá»©c',
                        'icon' => 'fas fa-folder-open',
                        'title' => 'Quáº£n lÃ½ danh má»¥c tin tá»©c',
                    ],
                    [
                        'route' => 'admin.news.index',
                        'patterns' => ['admin.news.*'],
                        'label' => 'Tin tá»©c',
                        'icon' => 'fas fa-newspaper',
                        'title' => 'Quáº£n lÃ½ tin tá»©c',
                    ],
                ],
            ],
            [
                'title' => 'ÄÃ o táº¡o',
                'icon' => 'fas fa-book-open',
                'attention' => $adminNewReviewCount > 0,
                'attention_title' => $adminNewReviewCount > 0 ? 'CÃ³ Ä‘Ã¡nh giÃ¡ má»›i cáº§n xem' : null,
                'items' => [
                    [
                        'route' => 'admin.course-categories.index',
                        'patterns' => ['admin.course-categories.*'],
                        'label' => 'NhÃ³m ngÃ nh',
                        'icon' => 'fas fa-folder-tree',
                        'title' => 'Quáº£n lÃ½ nhÃ³m ngÃ nh',
                    ],
                    [
                        'route' => 'admin.courses.index',
                        'patterns' => ['admin.courses.*'],
                        'label' => 'KhÃ³a há»c',
                        'icon' => 'fas fa-book-open',
                        'title' => 'Quáº£n lÃ½ khÃ³a há»c',
                    ],
                    [
                        'route' => 'admin.classes.index',
                        'patterns' => ['admin.classes.*'],
                        'label' => 'Äá»£t há»c & lá»›p',
                        'icon' => 'fas fa-chalkboard-teacher',
                        'title' => 'Quáº£n lÃ½ Ä‘á»£t há»c vÃ  lá»›p há»c',
                    ],
                    [
                        'route' => 'admin.reviews.index',
                        'patterns' => ['admin.reviews.*'],
                        'label' => 'ÄÃ¡nh giÃ¡',
                        'icon' => 'fas fa-star',
                        'title' => 'Quáº£n lÃ½ Ä‘Ã¡nh giÃ¡ khÃ³a há»c',
                        'badge' => $adminNewReviewCount,
                        'badge_title' => $adminNewReviewCount > 0 ? $adminNewReviewCount . ' Ä‘Ã¡nh giÃ¡ má»›i' : null,
                    ],
                ],
            ],
            [
                'title' => 'Tuyá»ƒn sinh & doanh thu',
                'icon' => 'fas fa-money-bill-wave',
                'attention' => ($adminPendingEnrollmentCount + $adminPaymentAttentionCount) > 0,
                'attention_title' => ($adminPendingEnrollmentCount + $adminPaymentAttentionCount) > 0 ? 'CÃ³ yÃªu cáº§u tuyá»ƒn sinh hoáº·c thanh toÃ¡n cáº§n xá»­ lÃ½' : null,
                'items' => [
                    [
                        'route' => 'admin.enrollments.pending',
                        'patterns' => ['admin.enrollments.*'],
                        'label' => 'ÄÄƒng kÃ½ há»c',
                        'icon' => 'fas fa-user-graduate',
                        'title' => 'Quáº£n lÃ½ Ä‘Äƒng kÃ½ há»c',
                        'badge' => $adminPendingEnrollmentCount,
                        'badge_title' => $adminPendingEnrollmentCount > 0 ? $adminPendingEnrollmentCount . ' Ä‘Äƒng kÃ½ chá» duyá»‡t' : null,
                    ],
                    [
                        'route' => 'admin.payments.index',
                        'route_params' => ['status' => 'pending'],
                        'patterns' => ['admin.payments.*'],
                        'label' => 'Thanh toÃ¡n khÃ³a há»c',
                        'icon' => 'fas fa-file-invoice-dollar',
                        'title' => 'Quáº£n lÃ½ thanh toÃ¡n khÃ³a há»c',
                        'badge' => $adminPendingPaymentCount,
                        'badge_title' => $adminPendingPaymentCount > 0 ? $adminPendingPaymentCount . ' thanh toÃ¡n chá» xá»­ lÃ½' : null,
                    ],
                    [
                        'route' => 'admin.wallet-transactions.index',
                        'route_params' => ['status' => 'pending'],
                        'patterns' => ['admin.wallet-transactions.*'],
                        'label' => 'Náº¡p vÃ­ thá»§ cÃ´ng',
                        'icon' => 'fas fa-wallet',
                        'title' => 'Duyá»‡t náº¡p vÃ­ thá»§ cÃ´ng',
                        'badge' => $adminPendingWalletTopupCount,
                        'badge_title' => $adminPendingWalletTopupCount > 0 ? $adminPendingWalletTopupCount . ' yÃªu cáº§u náº¡p vÃ­ chá» duyá»‡t' : null,
                    ],
                    [
                        'route' => 'admin.promotions.index',
                        'patterns' => ['admin.promotions.*'],
                        'label' => 'Khuyáº¿n mÃ£i & voucher',
                        'icon' => 'fas fa-tags',
                        'title' => 'Quáº£n lÃ½ khuyáº¿n mÃ£i vÃ  voucher',
                    ],
                ],
            ],
            [
                'title' => 'Há»‡ thá»‘ng & tÃ­ch há»£p',
                'icon' => 'fas fa-sliders',
                'attention' => false,
                'attention_title' => null,
                'items' => [
                    [
                        'route' => 'admin.blockchain.dashboard',
                        'patterns' => ['admin.blockchain.*'],
                        'label' => 'Blockchain FireFly',
                        'icon' => 'fas fa-link',
                        'title' => 'Dashboard blockchain FireFly',
                    ],
                    [
                        'route' => 'admin.system-logs.index',
                        'patterns' => ['admin.system-logs.*'],
                        'label' => 'Nháº­t kÃ½ há»‡ thá»‘ng',
                        'icon' => 'fas fa-clipboard-list',
                        'title' => 'Nháº­t kÃ½ há»‡ thá»‘ng',
                    ],
                    [
                        'route' => 'admin.backups.index',
                        'patterns' => ['admin.backups.*'],
                        'label' => 'Sao lÆ°u dá»¯ liá»‡u',
                        'icon' => 'fas fa-shield-halved',
                        'title' => 'Sao lÆ°u dá»¯ liá»‡u',
                    ],
                    [
                        'route' => 'admin.settings.index',
                        'patterns' => ['admin.settings.*'],
                        'label' => 'CÃ i Ä‘áº·t há»‡ thá»‘ng',
                        'icon' => 'fas fa-cog',
                        'title' => 'CÃ i Ä‘áº·t há»‡ thá»‘ng',
                    ],
                ],
            ],
        ];

        $adminAlertItems = [
            [
                'route' => route('admin.enrollments.pending'),
                'title' => 'ÄÄƒng kÃ½ chá» duyá»‡t',
                'description' => 'YÃªu cáº§u ghi danh offline má»›i',
                'count' => $adminPendingEnrollmentCount,
            ],
            [
                'route' => route('admin.reviews.index'),
                'title' => 'ÄÃ¡nh giÃ¡ má»›i',
                'description' => 'Ã kiáº¿n má»›i tá»« há»c viÃªn',
                'count' => $adminNewReviewCount,
            ],
            [
                'route' => route('admin.payments.index', ['status' => 'pending']),
                'title' => 'Thanh toÃ¡n khÃ³a há»c',
                'description' => 'Giao dá»‹ch Ä‘ang chá» xá»­ lÃ½',
                'count' => $adminPendingPaymentCount,
            ],
            [
                'route' => route('admin.wallet-transactions.index', ['status' => 'pending']),
                'title' => 'Náº¡p vÃ­ thá»§ cÃ´ng',
                'description' => 'YÃªu cáº§u topup direct vÃ  bank cáº§n duyá»‡t',
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
                    <ul class="sidebar-subnav {{ $groupHasActiveItem ? 'open' : '' }}" data-group-title="{{ $group['title'] }}">
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
                                            <span class="admin-attention-dot" title="{{ $item['badge_title'] ?? ($itemBadge . ' má»¥c cáº§n xá»­ lÃ½') }}"></span>
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
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm mb-2 sidebar-btn" title="Vá» trang chá»§">
                <i class="fas fa-home"></i>
                <span class="btn-text">Vá» trang chá»§</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-100" data-browser-session-logout="manual">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm w-100 sidebar-btn" title="ÄÄƒng xuáº¥t">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="btn-text">ÄÄƒng xuáº¥t</span>
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
                <button id="themeToggle" class="btn btn-sm btn-outline-secondary" title="Äá»•i giao diá»‡n sÃ¡ng tá»‘i">
                    <i id="themeIcon" class="fas fa-moon"></i>
                </button>

                <div class="dropdown admin-alert-dropdown">
                    <button class="btn btn-sm admin-alert-bell dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="ThÃ´ng bÃ¡o quáº£n trá»‹">
                        <i class="fas fa-bell"></i>
                        @if($adminHasAttentionItems)
                            <span class="admin-alert-bell-dot"></span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end admin-alert-menu">
                        <div class="admin-alert-menu-header">
                            <div>
                                <strong>ThÃ´ng bÃ¡o quáº£n trá»‹</strong>
                                <div class="small text-muted">CÃ¡c má»¥c má»›i cáº§n admin kiá»ƒm tra</div>
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
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
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
                    if (!document.body.classList.contains('sidebar-collapsed')) {
                        subnav.classList.remove('fixed');
                        subnav.style.removeProperty('--sidebar-subnav-top');
                        return;
                    }

                    const rect = header.getBoundingClientRect();
                    subnav.classList.add('fixed');
                    subnav.style.setProperty('--sidebar-subnav-top', rect.top + 'px');
                };

                group.addEventListener('mouseenter', () => {
                    header.classList.add('open');
                    positionSubnav();
                    subnav.classList.add('open');
                });

                group.addEventListener('mouseleave', () => {
                    const isActiveGroup = subnav.querySelector('.nav-link.active');
                    if (document.body.classList.contains('sidebar-collapsed') || !isActiveGroup) {
                        header.classList.remove('open');
                        subnav.classList.remove('open');
                    }
                });

                window.addEventListener('resize', () => {
                    if (subnav.classList.contains('open')) {
                        positionSubnav();
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
