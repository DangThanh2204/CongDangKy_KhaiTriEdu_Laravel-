<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ $siteName }}</title>
    <meta name="description" content="{{ $siteTagline }}">

    <script>
        (function () {
            try {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = saved === 'dark' || (!saved && prefersDark);

                if (isDark) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                } else {
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                }
            } catch (error) {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>

    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $siteFavicon) }}">
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @auth
        @include('layouts.partials.browser-session-guard')
    @endauth
</head>
<body class="@yield('page-class')">
    <nav class="navbar navbar-expand-xxl navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                @if($siteLogo)
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="site-brand-logo">
                @else
                    <i class="fas fa-graduation-cap me-2"></i>
                @endif
                {{ $siteName }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Mở menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse portal-navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-xxl-center navbar-main-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link navbar-primary-link {{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">
                            <i class="fas fa-book-open me-1"></i>Khóa học
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('courses.intakes') || request()->delivery_mode === 'online' || request()->delivery_mode === 'offline' ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-layer-group me-1"></i>Hình thức học
                        </a>
                        <ul class="dropdown-menu navbar-dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->delivery_mode === 'online' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->except('page'), ['delivery_mode' => 'online'])) }}">
                                    <i class="fas fa-play-circle me-2"></i>Học online ngay
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->delivery_mode === 'offline' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->except('page'), ['delivery_mode' => 'offline'])) }}">
                                    <i class="fas fa-school me-2"></i>Lớp offline / đợt học
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('courses.intakes') ? 'active' : '' }}" href="{{ route('courses.intakes') }}">
                                    <i class="fas fa-calendar-alt me-2"></i>Lịch khai giảng / đợt học mở
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->filter === 'featured' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->except('page'), ['filter' => 'featured'])) }}">
                                    <i class="fas fa-star me-2"></i>Khóa học nổi bật
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('about') || request()->routeIs('news.*') || request()->routeIs('contact') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-compass me-1"></i>Khám phá
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end navbar-dropdown-menu">
                            <li><a class="dropdown-item {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}"><i class="fas fa-info-circle me-2"></i>Giới thiệu trung tâm</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}"><i class="fas fa-newspaper me-2"></i>Tin tức đào tạo</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}"><i class="fas fa-envelope me-2"></i>Liên hệ tư vấn</a></li>
                        </ul>
                    </li>
                    @auth
                        <li class="nav-item dropdown notification-nav-item">
                            <a class="nav-link notification-bell-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="notification-bell-icon">
                                    <i class="fas fa-bell"></i>
                                </span>
                                @if($unreadNotificationsCount > 0)
                                    <span class="notification-bell-badge">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow-sm border-0 p-0">
                                <div class="notification-dropdown-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 fw-bold">Thông báo</h6>
                                        <small class="text-muted">Các cập nhật mới nhất dành cho bạn</small>
                                    </div>
                                    @if($unreadNotificationsCount > 0)
                                        <span class="badge rounded-pill bg-danger">{{ $unreadNotificationsCount }}</span>
                                    @endif
                                </div>

                                <div class="notification-dropdown-list">
                                    @forelse($appNotifications as $notification)
                                        @php($notificationData = $notification->data ?? [])
                                        <a href="{{ route('notifications.visit', $notification->id) }}"
                                           class="dropdown-item notification-dropdown-item {{ is_null($notification->read_at) ? 'is-unread' : '' }}">
                                            <div class="notification-item-icon bg-{{ $notificationData['variant'] ?? 'primary' }}">
                                                <i class="{{ $notificationData['icon'] ?? 'fas fa-bell' }}"></i>
                                            </div>
                                            <div class="notification-item-body">
                                                <div class="notification-item-title">{{ $notificationData['title'] ?? 'Thông báo mới' }}</div>
                                                <div class="notification-item-message">{{ \Illuminate\Support\Str::limit($notificationData['message'] ?? '', 110) }}</div>
                                                <small class="notification-item-time">{{ optional($notification->created_at)->diffForHumans() }}</small>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="notification-empty-state text-center text-muted py-4 px-3">
                                            <i class="fas fa-bell-slash mb-2"></i>
                                            <p class="mb-0">Chưa có thông báo mới.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="notification-dropdown-footer d-flex justify-content-between align-items-center gap-2">
                                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                                    @if($unreadNotificationsCount > 0)
                                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-link text-decoration-none">Đánh dấu đã đọc</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->fullname ?? Auth::user()->username }}
                                <span class="badge bg-{{ Auth::user()->roleBadgeClass() }} ms-1">{{ Auth::user()->roleLabel() }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end navbar-dropdown-menu">
                                @if(Auth::user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</a></li>
                                @elseif(Auth::user()->isInstructor())
                                    <li><a class="dropdown-item" href="{{ route('instructor.dashboard') }}"><i class="fas fa-chalkboard-teacher me-2"></i>Giảng viên Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('instructor.classes.index') }}"><i class="fas fa-users-class me-2"></i>Lớp tôi đang dạy</a></li>
                                    <li><a class="dropdown-item" href="{{ route('wallet.index') }}"><i class="fas fa-wallet me-2"></i>Ví của tôi</a></li>
                                @else
                                    <li>
                                        <a class="dropdown-item fw-bold" href="{{ route('student.dashboard') }}">
                                            <i class="fas fa-book me-2"></i>Khóa học của tôi
                                            @if($pendingEnrollmentCount > 0)
                                                <span class="badge bg-warning float-end">{{ $pendingEnrollmentCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li><a class="dropdown-item" href="{{ route('student.application-status') }}"><i class="fas fa-file-waveform me-2"></i>Tra cứu hồ sơ</a></li>
                                    <li><a class="dropdown-item" href="{{ route('wallet.index') }}"><i class="fas fa-wallet me-2"></i>Ví của tôi</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user-circle me-2"></i>Thông tin cá nhân</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" id="logout-form" data-browser-session-logout="manual">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link navbar-ghost-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link navbar-cta-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký học
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                <div class="container">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
                <div class="container">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')

    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">
                        @if($siteLogo)
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="footer-brand-logo me-2" style="height: 28px; width: auto;">
                        @else
                            <i class="fas fa-graduation-cap me-2"></i>
                        @endif
                        {{ $siteName }}
                    </h5>
                    <p class="text-light">{{ $siteTagline ?: 'Nơi ươm mầm tri thức, khai sáng tương lai với các chương trình đào tạo chất lượng cao.' }}</p>
                    <div class="social-links mt-4 d-flex gap-3">
                        <a href="{{ $facebookUrl ?: '#' }}" class="facebook" title="Facebook" @if(!$facebookUrl) aria-disabled="true" @else target="_blank" rel="noopener" @endif><i class="fab fa-facebook-f"></i></a>
                        <a href="{{ $youtubeUrl ?: '#' }}" class="youtube" title="YouTube" @if(!$youtubeUrl) aria-disabled="true" @else target="_blank" rel="noopener" @endif><i class="fab fa-youtube"></i></a>
                        <a href="{{ $tiktokUrl ?: '#' }}" class="tiktok" title="TikTok" @if(!$tiktokUrl) aria-disabled="true" @else target="_blank" rel="noopener" @endif><i class="fab fa-tiktok"></i></a>
                        <a href="{{ $zaloUrl ?: '#' }}" class="zalo" title="Zalo" @if(!$zaloUrl) aria-disabled="true" @else target="_blank" rel="noopener" @endif><i class="fas fa-comment-dots"></i></a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">Liên hệ</h5>
                    <div class="contact-info">
                        <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>{{ $contactAddress ?: 'Đường Ung Văn Khiêm, Phường Long Xuyên, Tỉnh An Giang' }}</p>
                        <p class="mb-2"><i class="fas fa-phone me-2"></i>{{ $contactPhone ?: '(028) 1234 5678' }}</p>
                        <p class="mb-2"><i class="fas fa-envelope me-2"></i>{{ $contactEmail ?: 'dhthanh2004@gmail.com' }}</p>
                        <p class="mb-0"><i class="fas fa-clock me-2"></i>Thứ 2 - Thứ 7: 7:00-11:00 13:00-17:00</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">Liên kết nhanh</h5>
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="{{ route('home') }}#home" class="text-light text-decoration-none">Trang chủ</a></li>
                                <li class="mb-2"><a href="{{ route('courses.index') }}" class="text-light text-decoration-none">Khóa học</a></li>
                                <li class="mb-2"><a href="{{ route('courses.intakes') }}" class="text-light text-decoration-none">Lịch khai giảng</a></li>
                                <li class="mb-2"><a href="{{ route('about') }}" class="text-light text-decoration-none">Giới thiệu</a></li>
                                <li class="mb-2"><a href="{{ route('news.index') }}" class="text-light text-decoration-none">Tin tức</a></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="{{ route('contact') }}" class="text-light text-decoration-none">Liên hệ</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 Hệ Thống Giáo Dục Khai Trí. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <button id="themeToggle" class="theme-toggle-floating" type="button" aria-label="Chuyển chế độ sáng/tối" title="Chuyển chế độ sáng/tối">
        <i id="themeIcon" class="fas fa-moon"></i>
    </button>

    <div class="fab-container">
        <button class="fab" data-bg="orange" onclick="showQuickContact()">
            <i class="fas fa-headset"></i>
        </button>
    </div>

    <button class="back-to-top" onclick="scrollToTop()">
        <i class="fas fa-chevron-up"></i>
    </button>

    <div class="chat-widget">
        <button class="chat-button" onclick="toggleChat()">
            <i class="fas fa-comment-dots"></i>
        </button>

        <div class="chat-box" id="chatBox">
            <div class="chat-header">
                <h6><i class="fas fa-robot me-2"></i>Trợ lý ảo Khai Trí</h6>
                <button class="chat-close" onclick="toggleChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="chat-body" id="chatBody">
                <div class="chat-message">
                    <div class="avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <p>Xin chào! Tôi có thể giúp gì cho bạn? Hãy chọn một trong các tùy chọn bên dưới:</p>
                    </div>
                </div>

                <div class="chat-message">
                    <div class="avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <div class="quick-options">
    <button class="btn btn-sm btn-outline-primary mb-2 me-2" onclick="quickResponse('Tôi muốn tư vấn khóa học')">Tư vấn khóa học</button>
    <button class="btn btn-sm btn-outline-primary mb-2 me-2" onclick="quickResponse('Tôi muốn biết học phí')">Học phí</button>
    <button class="btn btn-sm btn-outline-primary mb-2" onclick="quickResponse('Tôi muốn đăng ký học')">Đăng ký học</button>
</div>
                    </div>
                </div>
            </div>

            <div class="chat-input">
                <input type="text" id="chatInput" placeholder="Nhập câu hỏi của bạn..." onkeypress="handleChatInput(event)">
                <button onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        const assistantConfig = {
            chatUrl: @json(route('assistant.chat')),
            historyUrl: @json(route('assistant.history')),
            csrfToken: @json(csrf_token()),
        };

        let assistantHistoryLoaded = false;
        let assistantHistoryLoading = false;
        let assistantSending = false;
        let assistantDefaultMarkup = '';

        document.addEventListener('DOMContentLoaded', function () {
            autoCloseAlerts();
            loadTheme();
            bindLogoutConfirm();
            bindDropdownHover();
            bindResponsiveNavbarHover();
            bindBackToTop();
            bindRevealAnimations();
            bindContactFormDemo();
            bindAnchorScrolling();
        });

        function autoCloseAlerts() {
            document.querySelectorAll('.alert').forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-100%)';
                    setTimeout(() => {
                        if (window.bootstrap && bootstrap.Alert) {
                            bootstrap.Alert.getOrCreateInstance(alert).close();
                        } else {
                            alert.classList.remove('show');
                            alert.style.display = 'none';
                        }
                    }, 800);
                }, 4200);
            });
        }

        function loadTheme() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            let savedTheme = null;

            try {
                savedTheme = localStorage.getItem('theme');
            } catch (error) {}

            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme || (prefersDark ? 'dark' : 'light');
            const isDark = theme === 'dark';

            document.body.classList.toggle('dark', isDark);
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
            if (document.body) {
                document.body.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
            }

            if (themeIcon) {
                themeIcon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
            }

            if (themeToggle) {
                themeToggle.setAttribute('aria-label', isDark ? 'Chuyển sang chế độ sáng' : 'Chuyển sang chế độ tối');
            }
        }

        function bindLogoutConfirm() {
            const logoutForm = document.getElementById('logout-form');
            if (!logoutForm) {
                return;
            }

            logoutForm.addEventListener('submit', function (event) {
                event.preventDefault();
                if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
                    this.submit();
                }
            });
        }

        function bindDropdownHover() {
            const desktopNavbar = window.matchMedia('(min-width: 1200px)');

            document.querySelectorAll('.navbar-nav .nav-item.dropdown').forEach(item => {
                item.addEventListener('mouseenter', () => {
                    if (!desktopNavbar.matches) {
                        return;
                    }

                    const toggleEl = item.querySelector('[data-bs-toggle="dropdown"]');
                    if (toggleEl) {
                        bootstrap.Dropdown.getOrCreateInstance(toggleEl).show();
                    }
                });

                item.addEventListener('mouseleave', () => {
                    if (!desktopNavbar.matches) {
                        return;
                    }

                    const toggleEl = item.querySelector('[data-bs-toggle="dropdown"]');
                    if (toggleEl) {
                        bootstrap.Dropdown.getOrCreateInstance(toggleEl).hide();
                    }
                });
            });
        }

        function bindResponsiveNavbarHover() {
            const navbar = document.querySelector('.navbar');
            const toggler = navbar?.querySelector('.navbar-toggler');
            const collapseEl = navbar?.querySelector('#navbarNav');

            if (!navbar || !toggler || !collapseEl || !window.bootstrap?.Collapse) {
                return;
            }

            const hoverableCollapsedNavbar = window.matchMedia('(max-width: 1399.98px) and (hover: hover) and (pointer: fine)');
            const collapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
            let closeTimer = null;

            const clearCloseTimer = () => {
                if (closeTimer) {
                    clearTimeout(closeTimer);
                    closeTimer = null;
                }
            };

            const showMenu = () => {
                if (!hoverableCollapsedNavbar.matches) {
                    return;
                }

                clearCloseTimer();
                collapse.show();
            };

            const hideMenu = () => {
                if (!hoverableCollapsedNavbar.matches) {
                    return;
                }

                clearCloseTimer();
                closeTimer = setTimeout(() => {
                    collapse.hide();
                }, 120);
            };

            const syncMode = () => {
                clearCloseTimer();

                if (!hoverableCollapsedNavbar.matches) {
                    collapse.hide();
                }
            };

            toggler.addEventListener('mouseenter', showMenu);
            toggler.addEventListener('mouseleave', hideMenu);
            collapseEl.addEventListener('mouseenter', showMenu);
            collapseEl.addEventListener('mouseleave', hideMenu);
            navbar.addEventListener('mouseleave', hideMenu);
            navbar.addEventListener('mouseenter', clearCloseTimer);

            if (typeof hoverableCollapsedNavbar.addEventListener === 'function') {
                hoverableCollapsedNavbar.addEventListener('change', syncMode);
            } else if (typeof hoverableCollapsedNavbar.addListener === 'function') {
                hoverableCollapsedNavbar.addListener(syncMode);
            }

            window.addEventListener('resize', syncMode);
        }

        function bindBackToTop() {
            const backToTop = document.querySelector('.back-to-top');
            if (!backToTop) {
                return;
            }

            window.addEventListener('scroll', function () {
                if (window.scrollY > 300) {
                    backToTop.classList.add('show');
                } else {
                    backToTop.classList.remove('show');
                }
            });
        }

        function bindRevealAnimations() {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 100);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            document.querySelectorAll('.feature-card, .course-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
            });
        }

        function bindContactFormDemo() {
            const contactForm = document.querySelector('#contact form');
            if (!contactForm) {
                return;
            }

            contactForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.innerHTML = '<div class="loading-spinner me-2"></div>Đang gửi...';
                submitBtn.disabled = true;

                setTimeout(() => {
                    showEnhancedAlert('Cảm ơn bạn! Chúng tôi sẽ liên hệ trong vòng 24h.', 'success');
                    contactForm.reset();
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            });
        }

        function bindAnchorScrolling() {
            document.querySelectorAll('a[href*="#"]').forEach(link => {
                link.addEventListener('click', function (event) {
                    const href = this.getAttribute('href');
                    if (!href || !href.includes('#')) {
                        return;
                    }

                    const [path, hash] = href.split('#');
                    if (path === "{{ route('home') }}" && hash) {
                        event.preventDefault();
                        window.location.href = "{{ route('home') }}";
                        setTimeout(() => {
                            const target = document.getElementById(hash);
                            if (target) {
                                target.scrollIntoView({ behavior: 'smooth' });
                            }
                        }, 100);
                    }
                });
            });
        }

        function showEnhancedAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            const main = document.querySelector('main');
            if (!main) {
                return;
            }

            main.insertBefore(alertDiv, main.firstChild);

            setTimeout(() => {
                alertDiv.style.transition = 'all 0.5s ease';
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.parentNode.removeChild(alertDiv);
                    }
                }, 500);
            }, 5000);
        }

        function toggleChat() {
            const chatBox = document.getElementById('chatBox');
            if (!chatBox) {
                return;
            }

            const isOpening = !chatBox.classList.contains('active');
            chatBox.classList.toggle('active');

            if (isOpening) {
                loadChatHistory();
            }
        }

        async function loadChatHistory(force = false) {
            const chatBody = document.getElementById('chatBody');
            if (!chatBody) {
                return;
            }

            if (!assistantDefaultMarkup) {
                assistantDefaultMarkup = chatBody.innerHTML;
            }

            if ((assistantHistoryLoaded && !force) || assistantHistoryLoading) {
                return;
            }

            assistantHistoryLoading = true;

            try {
                const response = await fetch(assistantConfig.historyUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('history_request_failed');
                }

                const data = await response.json();
                chatBody.innerHTML = '';

                if (!Array.isArray(data.messages) || data.messages.length === 0) {
                    renderDefaultChatBody();
                } else {
                    data.messages.forEach(renderStoredMessage);
                }

                assistantHistoryLoaded = true;
            } catch (error) {
                renderDefaultChatBody();
            } finally {
                assistantHistoryLoading = false;
            }
        }

        function renderDefaultChatBody() {
            const chatBody = document.getElementById('chatBody');
            if (!chatBody) {
                return;
            }

            chatBody.innerHTML = assistantDefaultMarkup;
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        async function quickResponse(message) {
            const input = document.getElementById('chatInput');
            if (!input) {
                return;
            }

            input.value = message;
            await sendMessage();
        }

        async function sendMessage(forcedMessage = null) {
            if (assistantSending) {
                return;
            }

            const input = document.getElementById('chatInput');
            const message = (forcedMessage ?? input?.value ?? '').trim();
            if (!message) {
                return;
            }

            const chatBody = document.getElementById('chatBody');
            if (!assistantDefaultMarkup && chatBody) {
                assistantDefaultMarkup = chatBody.innerHTML;
            }

            if (chatBody && chatBody.innerHTML === assistantDefaultMarkup) {
                chatBody.innerHTML = '';
            }

            addMessage(message, 'user');

            if (input) {
                input.value = '';
            }

            assistantSending = true;
            const typingIndicator = addTypingIndicator();

            try {
                const response = await fetch(assistantConfig.chatUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': assistantConfig.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        message,
                        current_url: window.location.href,
                        page_title: document.title,
                    }),
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'chat_request_failed');
                }

                removeTypingIndicator(typingIndicator);
                addMessage(data.message, 'assistant', data.recommended_courses || []);
                assistantHistoryLoaded = true;
            } catch (error) {
                removeTypingIndicator(typingIndicator);
                addMessage('Mình chưa kết nối ổn với trợ lý lúc này. Bạn thử lại sau ít phút hoặc để lại nhu cầu học để trung tâm hỗ trợ nhé.', 'assistant');
            } finally {
                assistantSending = false;
            }
        }

        function handleChatInput(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        }

        function addMessage(text, sender, recommendations = []) {
            const chatBody = document.getElementById('chatBody');
            if (!chatBody) {
                return;
            }

            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${sender}`;

            const avatarIcon = sender === 'user' ? 'fas fa-user' : 'fas fa-robot';
            const bubble = sender === 'user'
                ? 'linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%)'
                : 'linear-gradient(135deg, #2c5aa0 0%, #1e3a8a 100%)';

            messageDiv.innerHTML = `
                <div class="avatar" style="background: ${bubble}">
                    <i class="${avatarIcon}"></i>
                </div>
                <div class="message-content">
                    <p>${formatMessage(text)}</p>
                    ${renderRecommendations(recommendations)}
                </div>
            `;

            chatBody.appendChild(messageDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function addTypingIndicator() {
            const chatBody = document.getElementById('chatBody');
            if (!chatBody) {
                return null;
            }

            const indicator = document.createElement('div');
            indicator.className = 'chat-message assistant typing-indicator';
            indicator.innerHTML = `
                <div class="avatar" style="background: linear-gradient(135deg, #2c5aa0 0%, #1e3a8a 100%)">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <p>Trợ lý đang trả lời...</p>
                </div>
            `;

            chatBody.appendChild(indicator);
            chatBody.scrollTop = chatBody.scrollHeight;
            return indicator;
        }

        function removeTypingIndicator(indicator) {
            if (indicator && indicator.parentNode) {
                indicator.parentNode.removeChild(indicator);
            }
        }

        function renderStoredMessage(message) {
            addMessage(
                message.message || '',
                message.role === 'user' ? 'user' : 'assistant',
                Array.isArray(message.recommended_courses) ? message.recommended_courses : []
            );
        }

        function renderRecommendations(recommendations) {
            if (!Array.isArray(recommendations) || recommendations.length === 0) {
                return '';
            }

            return `
                <div class="assistant-recommendations mt-2">
                    ${recommendations.map((course) => `
                        <div class="border rounded p-2 mb-2 bg-white">
                            <div class="fw-bold text-dark">${escapeHtml(course.title || '')}</div>
                            <div class="small text-muted">${escapeHtml(course.category || '')} - ${escapeHtml(course.learning_type || '')}</div>
                            <div class="small text-muted">${escapeHtml(course.price_label || 'Liên hệ tư vấn')}</div>
                            <div class="small text-muted mb-2">Phù hợp vì ${escapeHtml(course.reason || 'đúng nhu cầu hiện tại')}</div>
                            <a href="${escapeHtml(course.url || "#")}" class="btn btn-sm btn-primary">Xem khóa học</a>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatMessage(value) {
            return escapeHtml(value).replace(/\n/g, '<br>');
        }

        function showQuickContact() {
            const phone = '02812345678';
            if (confirm(`Gọi ngay đến số ${phone} để được tư vấn?`)) {
                window.location.href = `tel:${phone}`;
            }
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>

