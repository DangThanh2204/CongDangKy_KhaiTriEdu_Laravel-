<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Dashboard</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="admin-layout">
    <nav class="admin-sidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="brand">
                <i class="fas fa-graduation-cap me-2"></i>
                <span class="brand-text">Khai Trí Edu</span>
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
            
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" title="Quản lý Users">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Quản lý Users</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('admin.news-categories.index') }}" class="nav-link {{ request()->routeIs('admin.news-categories.*') ? 'active' : '' }}" title="Danh mục Tin tức">
                    <i class="fas fa-folder"></i>
                    <span class="nav-text">Danh mục Tin tức</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.news.index') }}" class="nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}" title="Quản lý Tin tức">
                    <i class="fas fa-newspaper"></i>
                    <span class="nav-text">Quản lý Tin tức</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('admin.course-categories.index') }}" class="nav-link {{ request()->routeIs('admin.course-categories.*') ? 'active' : '' }}" title="Danh mục Khóa học">
                    <i class="fas fa-folder-tree"></i>
                    <span class="nav-text">Danh mục Khóa học</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.courses.index') }}" class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}" title="Quản lý Khóa học">
                    <i class="fas fa-book-open"></i>
                    <span class="nav-text">Quản lý Khóa học</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.enrollments.pending') }}" class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}" title="Quản lý Đăng ký">
                    <i class="fas fa-user-graduate"></i>
                    <span class="nav-text">Quản lý Đăng ký</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm mb-2 sidebar-btn" title="Về trang chủ">
                <i class="fas fa-home"></i>
                <span class="btn-text">Về trang chủ</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-100">
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
            
            <div class="topbar-right">
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

        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getInstance(alert);
                if (bsAlert) {
                    bsAlert.close();
                }
            }, 5000);
        });

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
    });
    </script>
</body>
</html>