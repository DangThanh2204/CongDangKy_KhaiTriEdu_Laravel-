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

            <!-- Group: QuÃ¡ÂºÂ£n lÃƒÂ½ ngÃ†Â°Ã¡Â»Âi dÃƒÂ¹ng -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span>
                        <i class="fas fa-users"></i>
                        <span class="nav-text">QuÃ¡ÂºÂ£n lÃƒÂ½ ngÃ†Â°Ã¡Â»Âi dÃƒÂ¹ng</span>
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="QuÃ¡ÂºÂ£n lÃƒÂ½ ngÃ†Â°Ã¡Â»Âi dÃƒÂ¹ng">
                    <li class="nav-item">
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" title="QuÃ¡ÂºÂ£n lÃƒÂ½ Users">
                            <i class="fas fa-user"></i>
                            <span class="nav-text">Users</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Group: QuÃ¡ÂºÂ£n lÃƒÂ½ tin tÃ¡Â»Â©c -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span>
                        <i class="fas fa-newspaper"></i>
                        <span class="nav-text">QuÃ¡ÂºÂ£n lÃƒÂ½ tin tÃ¡Â»Â©c</span>
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="QuÃ¡ÂºÂ£n lÃƒÂ½ tin tÃ¡Â»Â©c">
                    <li class="nav-item">
                        <a href="{{ route('admin.news-categories.index') }}" class="nav-link {{ request()->routeIs('admin.news-categories.*') ? 'active' : '' }}" title="Danh mÃ¡Â»Â¥c Tin tÃ¡Â»Â©c">
                            <i class="fas fa-folder"></i>
                            <span class="nav-text">Danh mÃ¡Â»Â¥c</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.news.index') }}" class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}" title="QuÃ¡ÂºÂ£n lÃƒÂ½ Tin tÃ¡Â»Â©c">
                            <i class="fas fa-newspaper"></i>
                            <span class="nav-text">Tin tÃ¡Â»Â©c</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Group: QuÃ¡ÂºÂ£n lÃƒÂ½ khÃƒÂ³a hÃ¡Â»Âc -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span class="sidebar-group-title">
                        <i class="fas fa-book-open"></i>
                        <span class="nav-text">QuÃ¡ÂºÂ£n lÃƒÂ½ khÃƒÂ³a hÃ¡Â»Âc</span>
                        @if($adminNewReviewCount > 0)
                            <span class="admin-attention-dot" title="CÃƒÂ³ Ã„â€˜ÃƒÂ¡nh giÃƒÂ¡ mÃ¡Â»â€ºi cÃ¡ÂºÂ§n xem"></span>
                        @endif
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="QuÃ¡ÂºÂ£n lÃƒÂ½ khÃƒÂ³a hÃ¡Â»Âc">
                    <li class="nav-item">
                        <a href="{{ route('admin.course-categories.index') }}" class="nav-link {{ request()->routeIs('admin.course-categories.*') ? 'active' : '' }}" title="NhÃƒÂ³m ngÃƒÂ nh">
                            <i class="fas fa-folder-tree"></i>
                            <span class="nav-text">NhÃƒÂ³m ngÃƒÂ nh</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}" title="QuÃ¡ÂºÂ£n lÃƒÂ½ KhÃƒÂ³a hÃ¡Â»Âc">
                            <i class="fas fa-book-open"></i>
                            <span class="nav-text">KhÃƒÂ³a hÃ¡Â»Âc</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" title="QuÃ¡ÂºÂ£n lÃƒÂ½ Ã„ÂÃƒÂ¡nh giÃƒÂ¡">
                            <i class="fas fa-star"></i>
                            <span class="nav-link-label">
                                <span class="nav-text">Ã„ÂÃƒÂ¡nh giÃƒÂ¡</span>
                                @if($adminNewReviewCount > 0)
                                    <span class="admin-attention-dot" title="{{ $adminNewReviewCount }} Ã„â€˜ÃƒÂ¡nh giÃƒÂ¡ mÃ¡Â»â€ºi"></span>
                                @endif
                            </span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Group: QuÃ¡ÂºÂ£n lÃƒÂ½ Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc & Ã„â€˜Ã„Æ’ng kÃƒÂ½ -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span class="sidebar-group-title">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span class="nav-text">QuÃ¡ÂºÂ£n lÃƒÂ½ Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc</span>
                        @if($adminPendingEnrollmentCount > 0)
                            <span class="admin-attention-dot" title="CÃƒÂ³ Ã„â€˜Ã„Æ’ng kÃƒÂ½ mÃ¡Â»â€ºi cÃ¡ÂºÂ§n duyÃ¡Â»â€¡t"></span>
                        @endif
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="QuÃ¡ÂºÂ£n lÃƒÂ½ Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc">
                    <li class="nav-item">
                        <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}" title="QuÃ¡ÂºÂ£n lÃƒÂ½ Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span class="nav-text">Ã„ÂÃ¡Â»Â£t hÃ¡Â»Âc</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.enrollments.pending') }}" class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}" title="QuÃ¡ÂºÂ£n lÃƒÂ½ Ã„ÂÃ„Æ’ng kÃƒÂ½">
                            <i class="fas fa-user-graduate"></i>
                            <span class="nav-link-label">
                                <span class="nav-text">Ã„ÂÃ„Æ’ng kÃƒÂ½</span>
                                @if($adminPendingEnrollmentCount > 0)
                                    <span class="admin-attention-dot" title="{{ $adminPendingEnrollmentCount }} Ã„â€˜Ã„Æ’ng kÃƒÂ½ chÃ¡Â»Â duyÃ¡Â»â€¡t"></span>
                                @endif
                            </span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Group: QuÃ¡ÂºÂ£n lÃƒÂ½ thanh toÃƒÂ¡n -->
            <li class="sidebar-group">
                <div class="sidebar-group-header">
                    <span class="sidebar-group-title">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">QuÃ¡ÂºÂ£n lÃƒÂ½ thanh toÃƒÂ¡n</span>
                        @if($adminPaymentAttentionCount > 0)
                            <span class="admin-attention-dot" title="CÃƒÂ³ thanh toÃƒÂ¡n mÃ¡Â»â€ºi cÃ¡ÂºÂ§n xÃ¡Â»Â­ lÃƒÂ½"></span>
                        @endif
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="sidebar-subnav" data-group-title="QuÃ¡ÂºÂ£n lÃƒÂ½ thanh toÃƒÂ¡n">
                    <li class="nav-item">
                        <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" title="QuÃ¡ÂºÂ£n lÃƒÂ½ Thanh toÃƒÂ¡n khÃƒÂ³a hÃ¡Â»Âc">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span class="nav-link-label">
                                <span class="nav-text">Thanh toÃƒÂ¡n khÃƒÂ³a hÃ¡Â»Âc</span>
                                @if($adminPendingPaymentCount > 0)
                                    <span class="admin-attention-dot" title="{{ $adminPendingPaymentCount }} thanh toÃƒÂ¡n chÃ¡Â»Â xÃ¡Â»Â­ lÃƒÂ½"></span>
                                @endif
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.wallet-transactions.index', ['status' => 'pending']) }}" class="nav-link {{ request()->routeIs('admin.wallet-transactions.*') ? 'active' : '' }}" title="DuyÃ¡Â»â€¡t nÃ¡ÂºÂ¡p vÃƒÂ­ thÃ¡Â»Â§ cÃƒÂ´ng">
                            <i class="fas fa-wallet"></i>
                            <span class="nav-link-label">
                                <span class="nav-text">NÃ¡ÂºÂ¡p vÃƒÂ­ thÃ¡Â»Â§ cÃƒÂ´ng</span>
                                @if($adminPendingWalletTopupCount > 0)
                                    <span class="admin-attention-dot" title="{{ $adminPendingWalletTopupCount }} yÃƒÂªu cÃ¡ÂºÂ§u nÃ¡ÂºÂ¡p vÃƒÂ­ chÃ¡Â»Â duyÃ¡Â»â€¡t"></span>
                                @endif
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.promotions.index') }}" class="nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}" title="Khuyáº¿n mÃ£i & voucher">
                            <i class="fas fa-tags"></i>
                            <span class="nav-text">Khuyáº¿n mÃ£i & voucher</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- System Logs -->
            <li class="nav-item">
                <a href="{{ route('admin.system-logs.index') }}" class="nav-link {{ request()->routeIs('admin.system-logs.*') ? 'active' : '' }}" title="NhÃ¡ÂºÂ­t kÃƒÂ½ HÃ¡Â»â€¡ thÃ¡Â»â€˜ng">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-text">NhÃ¡ÂºÂ­t kÃƒÂ½ hÃ¡Â»â€¡ thÃ¡Â»â€˜ng</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.backups.index') }}" class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" title="Sao lÃ†Â°u dÃ¡Â»Â¯ liÃ¡Â»â€¡u">
                    <i class="fas fa-shield-halved"></i>
                    <span class="nav-text">Sao lÃ†Â°u dÃ¡Â»Â¯ liÃ¡Â»â€¡u</span>
                </a>
            </li>

            <!-- CÃƒÂ i Ã„â€˜Ã¡ÂºÂ·t hÃ¡Â»â€¡ thÃ¡Â»â€˜ng - standalone -->
            <li class="nav-item">
                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" title="CÃƒÂ i Ã„â€˜Ã¡ÂºÂ·t HÃ¡Â»â€¡ thÃ¡Â»â€˜ng">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">CÃƒÂ i Ã„â€˜Ã¡ÂºÂ·t HÃ¡Â»â€¡ thÃ¡Â»â€˜ng</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm mb-2 sidebar-btn" title="VÃ¡Â»Â trang chÃ¡Â»Â§">
                <i class="fas fa-home"></i>
                <span class="btn-text">VÃ¡Â»Â trang chÃ¡Â»Â§</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-100" data-browser-session-logout="manual">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm w-100 sidebar-btn" title="Ã„ÂÃ„Æ’ng xuÃ¡ÂºÂ¥t">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="btn-text">Ã„ÂÃ„Æ’ng xuÃ¡ÂºÂ¥t</span>
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
                    <button class="btn btn-sm admin-alert-bell dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="ThÃƒÂ´ng bÃƒÂ¡o quÃ¡ÂºÂ£n trÃ¡Â»â€¹">
                        <i class="fas fa-bell"></i>
                        @if($adminHasAttentionItems)
                            <span class="admin-alert-bell-dot"></span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end admin-alert-menu">
                        <div class="admin-alert-menu-header">
                            <div>
                                <strong>ThÃƒÂ´ng bÃƒÂ¡o quÃ¡ÂºÂ£n trÃ¡Â»â€¹</strong>
                                <div class="small text-muted">CÃƒÂ¡c mÃ¡Â»Â¥c mÃ¡Â»â€ºi cÃ¡ÂºÂ§n admin kiÃ¡Â»Æ’m tra</div>
                            </div>
                            @if($adminHasAttentionItems)
                                <span class="badge text-bg-danger">{{ $adminTotalAttentionCount > 99 ? '99+' : $adminTotalAttentionCount }}</span>
                            @endif
                        </div>
                        <div class="admin-alert-menu-list">
                            <a href="{{ route('admin.enrollments.pending') }}" class="dropdown-item admin-alert-item">
                                <span class="item-copy">
                                    <strong>Ã„ÂÃ„Æ’ng kÃƒÂ½ chÃ¡Â»Â duyÃ¡Â»â€¡t</strong>
                                    <small>YÃƒÂªu cÃ¡ÂºÂ§u ghi danh offline mÃ¡Â»â€ºi</small>
                                </span>
                                <span class="badge {{ $adminPendingEnrollmentCount > 0 ? 'text-bg-danger' : 'text-bg-light' }}">{{ $adminPendingEnrollmentCount }}</span>
                            </a>
                            <a href="{{ route('admin.reviews.index') }}" class="dropdown-item admin-alert-item">
                                <span class="item-copy">
                                    <strong>Ã„ÂÃƒÂ¡nh giÃƒÂ¡ mÃ¡Â»â€ºi</strong>
                                    <small>ÃƒÂ kiÃ¡ÂºÂ¿n mÃ¡Â»â€ºi tÃ¡Â»Â« hÃ¡Â»Âc viÃƒÂªn</small>
                                </span>
                                <span class="badge {{ $adminNewReviewCount > 0 ? 'text-bg-danger' : 'text-bg-light' }}">{{ $adminNewReviewCount }}</span>
                            </a>
                            <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="dropdown-item admin-alert-item">
                                <span class="item-copy">
                                    <strong>Thanh toÃƒÂ¡n khÃƒÂ³a hÃ¡Â»Âc</strong>
                                    <small>Giao dÃ¡Â»â€¹ch Ã„â€˜ang chÃ¡Â»Â xÃ¡Â»Â­ lÃƒÂ½</small>
                                </span>
                                <span class="badge {{ $adminPendingPaymentCount > 0 ? 'text-bg-danger' : 'text-bg-light' }}">{{ $adminPendingPaymentCount }}</span>
                            </a>
                            <a href="{{ route('admin.wallet-transactions.index', ['status' => 'pending']) }}" class="dropdown-item admin-alert-item">
                                <span class="item-copy">
                                    <strong>NÃ¡ÂºÂ¡p vÃƒÂ­ thÃ¡Â»Â§ cÃƒÂ´ng</strong>
                                    <small>YÃƒÂªu cÃ¡ÂºÂ§u topup direct vÃƒÂ  bank cÃ¡ÂºÂ§n duyÃ¡Â»â€¡t</small>
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


