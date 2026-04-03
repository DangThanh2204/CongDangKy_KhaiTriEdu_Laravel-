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
        // Apply saved theme early to avoid flash on admin pages
        (function() {
            try {
                const saved = localStorage.getItem('theme');
                if (saved === 'dark') {
                    document.documentElement.classList.add('dark');
                    // body may not exist yet, so add when available
                    if (document.body) document.body.classList.add('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                    if (document.body) document.body.setAttribute('data-bs-theme', 'dark');
                } else if (!saved && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                    if (document.body) document.body.classList.add('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                    if (document.body) document.body.setAttribute('data-bs-theme', 'dark');
                } else {
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                    if (document.body) document.body.setAttribute('data-bs-theme', 'light');
                }
            } catch (e) {
                // ignore
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
        
        <!-- Sidebar Navigation with Groups -->
        <ul class="sidebar-nav">
            <!-- Dashboard - standalone -->
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- Group: Quản lý người dùng -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span>
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Quản lý người dùng</span>
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="Quản lý người dùng">
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" title="Quản lý Users">
                            <i class="fas fa-user"></i>
                            <span class="nav-text">Users</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Group: Quản lý tin tức -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span>
                        <i class="fas fa-newspaper"></i>
                        <span class="nav-text">Quản lý tin tức</span>
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="Quản lý tin tức">
                    <li class="nav-item">
                        <a href="{{ route('admin.news-categories.index') }}" class="nav-link {{ request()->routeIs('admin.news-categories.*') ? 'active' : '' }}" title="Danh mục Tin tức">
                            <i class="fas fa-folder"></i>
                            <span class="nav-text">Danh mục</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.news.index') }}" class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}" title="Quản lý Tin tức">
                            <i class="fas fa-newspaper"></i>
                            <span class="nav-text">Tin tức</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Group: Quản lý khóa học -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span class="sidebar-group-title">
                        <i class="fas fa-book-open"></i>
                        <span class="nav-text">Quản lý khóa học</span>
                        @if($adminNewReviewCount > 0)
                            <span class="admin-attention-dot" title="Có đánh giá mới cần xem"></span>
                        @endif
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="Quản lý khóa học">
                    <li class="nav-item">
                        <a href="{{ route('admin.course-categories.index') }}" class="nav-link {{ request()->routeIs('admin.course-categories.*') ? 'active' : '' }}" title="Nhóm ngành">
                            <i class="fas fa-folder-tree"></i>
                            <span class="nav-text">Nhóm ngành</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}" title="Quản lý Khóa học">
                            <i class="fas fa-book-open"></i>
                            <span class="nav-text">Khóa học</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" title="Quản lý Đánh giá">
                            <i class="fas fa-star"></i>
                            <span class="nav-link-label">
                                <span class="nav-text">Đánh giá</span>
                                @if($adminNewReviewCount > 0)
                                    <span class="admin-attention-dot" title="{{ $adminNewReviewCount }} đánh giá mới"></span>
                                @endif
                            </span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Group: Quản lý đợt học & đăng ký -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span class="sidebar-group-title">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span class="nav-text">Quản lý đợt học</span>
                        @if($adminPendingEnrollmentCount > 0)
                            <span class="admin-attention-dot" title="Có đăng ký mới cần duyệt"></span>
                        @endif
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="Quản lý đợt học">
                    <li class="nav-item">
                        <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}" title="Quản lý đợt học">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span class="nav-text">Đợt học</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.enrollments.pending') }}" class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}" title="Quản lý Đăng ký">
                            <i class="fas fa-user-graduate"></i>
                            <span class="nav-link-label">
                                <span class="nav-text">Đăng ký</span>
                                @if($adminPendingEnrollmentCount > 0)
                                    <span class="admin-attention-dot" title="{{ $adminPendingEnrollmentCount }} đăng ký chờ duyệt"></span>
                                @endif
                            </span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Group: Quản lý thanh toán -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span class="sidebar-group-title">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">Quản lý thanh toán</span>
                        @if($adminPaymentAttentionCount > 0)
                            <span class="admin-attention-dot" title="Có thanh toán mới cần xử lý"></span>
                        @endif
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="Quản lý thanh toán">
                    <li class="nav-item">
                        <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" title="Quản lý Thanh toán khóa học">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span class="nav-link-label">
                                <span class="nav-text">Thanh toán khóa học</span>
                                @if($adminPendingPaymentCount > 0)
                                    <span class="admin-attention-dot" title="{{ $adminPendingPaymentCount }} thanh toán chờ xử lý"></span>
                                @endif
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.wallet-transactions.index', ['status' => 'pending']) }}" class="nav-link {{ request()->routeIs('admin.wallet-transactions.*') ? 'active' : '' }}" title="Duyệt nạp ví thủ công">
                            <i class="fas fa-wallet"></i>
                            <span class="nav-link-label">
                                <span class="nav-text">Nạp ví thủ công</span>
                                @if($adminPendingWalletTopupCount > 0)
                                    <span class="admin-attention-dot" title="{{ $adminPendingWalletTopupCount }} yêu cầu nạp ví chờ duyệt"></span>
                                @endif
                            </span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- System Logs -->
            <li class="nav-item">
                <a href="{{ route('admin.system-logs.index') }}" class="nav-link {{ request()->routeIs('admin.system-logs.*') ? 'active' : '' }}" title="Nhật ký Hệ thống">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-text">Nhật ký hệ thống</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.backups.index') }}" class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" title="Sao lưu dữ liệu">
                    <i class="fas fa-shield-halved"></i>
                    <span class="nav-text">Sao lưu dữ liệu</span>
                </a>
            </li>

            <!-- Cài đặt hệ thống - standalone -->
            <li class="nav-item">
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" title="Cài đặt Hệ thống">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Cài đặt Hệ thống</span>
                </a>
            </li>
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
                <button id="themeToggle" class="btn btn-sm btn-outline-secondary" title="Toggle theme">
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
                            <a href="{{ route('admin.enrollments.pending') }}" class="dropdown-item admin-alert-item">
                                <span class="item-copy">
                                    <strong>Đăng ký chờ duyệt</strong>
                                    <small>Yêu cầu ghi danh offline mới</small>
                                </span>
                                <span class="badge {{ $adminPendingEnrollmentCount > 0 ? 'text-bg-danger' : 'text-bg-light' }}">{{ $adminPendingEnrollmentCount }}</span>
                            </a>
                            <a href="{{ route('admin.reviews.index') }}" class="dropdown-item admin-alert-item">
                                <span class="item-copy">
                                    <strong>Đánh giá mới</strong>
                                    <small>Ý kiến mới từ học viên</small>
                                </span>
                                <span class="badge {{ $adminNewReviewCount > 0 ? 'text-bg-danger' : 'text-bg-light' }}">{{ $adminNewReviewCount }}</span>
                            </a>
                            <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="dropdown-item admin-alert-item">
                                <span class="item-copy">
                                    <strong>Thanh toán khóa học</strong>
                                    <small>Giao dịch đang chờ xử lý</small>
                                </span>
                                <span class="badge {{ $adminPendingPaymentCount > 0 ? 'text-bg-danger' : 'text-bg-light' }}">{{ $adminPendingPaymentCount }}</span>
                            </a>
                            <a href="{{ route('admin.wallet-transactions.index', ['status' => 'pending']) }}" class="dropdown-item admin-alert-item">
                                <span class="item-copy">
                                    <strong>Nạp ví thủ công</strong>
                                    <small>Yêu cầu topup direct và bank cần duyệt</small>
                                </span>
                                <span class="badge {{ $adminPendingWalletTopupCount > 0 ? 'text-bg-danger' : 'text-bg-light' }}">{{ $adminPendingWalletTopupCount }}</span>
                            </a>
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
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebarToggle');

        // Sidebar collapse state
        const savedSidebarState = localStorage.getItem('sidebarCollapsed');
        if (savedSidebarState === 'true') {
            document.body.classList.add('sidebar-collapsed');
        } else if (savedSidebarState === 'false') {
            document.body.classList.remove('sidebar-collapsed');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
        }

        // Auto close alerts
        function autoCloseAlerts() {
            document.querySelectorAll('.alert').forEach(alert => {
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
        autoCloseAlerts();

        // Handle mobile sidebar
        function handleMobileSidebar() {
            if (window.innerWidth < 768) {
                document.body.classList.add('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState === null) {
                    document.body.classList.remove('sidebar-collapsed');
                }
            }
        }
        handleMobileSidebar();
        window.addEventListener('resize', handleMobileSidebar);

        // Sidebar group hover behavior - show on hover, hide immediately on mouse leave
        const groups = Array.from(document.querySelectorAll('.sidebar-group'));

        groups.forEach(group => {
            const header = group.querySelector('.sidebar-group-header');
            const subnav = group.querySelector('.sidebar-subnav');

            if (!header || !subnav) return;

            // Show submenu on hover
            group.addEventListener('mouseenter', () => {
                header.classList.add('open');
                subnav.classList.add('open');
            });

            // Hide submenu immediately when mouse leaves
            group.addEventListener('mouseleave', () => {
                header.classList.remove('open');
                subnav.classList.remove('open');
            });
        });
    });
    </script>
    @stack('scripts')
</body>
</html>


