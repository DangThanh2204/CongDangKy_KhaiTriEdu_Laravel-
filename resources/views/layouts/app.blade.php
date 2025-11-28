<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Hệ Thống Giáo Dục Khai Trí</title>
    <meta name="description" content="Hệ thống giáo dục và đào tạo hàng đầu Việt Nam">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-graduation-cap me-2"></i>
                Khai Trí Edu
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#about">
                            <i class="fas fa-info-circle me-1"></i>Giới thiệu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('news.index') }}">
                            <i class="fas fa-newspaper me-1"></i>Tin tức
                        </a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('courses.index') }}">
                                <i class="fas fa-book me-1"></i>Khóa học
                            </a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#partners">
                            <i class="fas fa-handshake me-1"></i>Đối tác
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#contact">
                            <i class="fas fa-envelope me-1"></i>Liên hệ
                        </a>
                    </li>
                    
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->fullname ?? Auth::user()->username }}
                                @if(Auth::user()->isAdmin())
                                    <span class="badge bg-danger ms-1">Admin</span>
                                @elseif(Auth::user()->isStaff())
                                    <span class="badge bg-warning ms-1">Staff</span>
                                @else
                                    <span class="badge bg-info ms-1">Student</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu">
                                @if(Auth::user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                                    </a></li>
                                @elseif(Auth::user()->isStaff())
                                    <li><a class="dropdown-item" href="{{ route('staff.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i>Staff Dashboard
                                    </a></li>
                                @else
                                    @php
                                        $userEnrollments = \App\Models\CourseEnrollment::with('course')
                                            ->where('user_id', Auth::id())
                                            ->where('status', 'approved')
                                            ->orderBy('created_at', 'desc')
                                            ->limit(5)
                                            ->get();
                                        
                                        $pendingCount = \App\Models\CourseEnrollment::where('user_id', Auth::id())
                                            ->where('status', 'pending')
                                            ->count();
                                    @endphp
                                    <li>
                                        <a class="dropdown-item fw-bold" href="{{ route('student.dashboard') }}">
                                            <i class="fas fa-book me-2"></i>Khóa học của tôi
                                            @if($pendingCount > 0)
                                                <span class="badge bg-warning float-end">{{ $pendingCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endif
                                
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-user-circle me-2"></i>Thông tin cá nhân
                                </a></li>
                                <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-cog me-2"></i>Cài đặt
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
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
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký
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

    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-graduation-cap me-2"></i>Khai Trí Edu
                    </h5>
                    <p class="text-light">Nơi ươm mầm tri thức, khai sáng tương lai với các chương trình đào tạo chất lượng cao.</p>
                    <div class="social-links mt-4 d-flex gap-3">
                        <a href="#" class="facebook" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="youtube" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="tiktok" title="TikTok">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="#" class="zalo" title="Zalo">
                            <i class="fab fa-facebook-messenger"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">Liên hệ</h5>
                    <div class="contact-info">
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            ĐƯỜNG UNG VĂN KHIÊM, PHƯỜNG LONG XUYÊN, TỈNH AN GIANG
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            (028) 1234 5678
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            dhthanh2004@gmail.com
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Thứ 2 - Thứ 7: 7:00-11:00 13:00-17:00
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">Liên kết nhanh</h5>
                    <div class="row">
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="{{ route('home') }}#home" class="text-light text-decoration-none">Trang chủ</a></li>
                                <li class="mb-2"><a href="{{ route('home') }}#courses" class="text-light text-decoration-none">Khóa học</a></li>
                                <li class="mb-2"><a href="{{ route('home') }}#about" class="text-light text-decoration-none">Giới thiệu</a></li>
                                <li class="mb-2"><a href="{{ route('news.index') }}" class="text-light text-decoration-none">Tin tức</a></li>
                            </ul>
                        </div>
                        <div class="col-6">
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="{{ route('home') }}#partners" class="text-light text-decoration-none">Đối tác</a></li>
                                <li class="mb-2"><a href="{{ route('home') }}#contact" class="text-light text-decoration-none">Liên hệ</a></li>
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
                            <button class="btn btn-sm btn-outline-primary mb-2 me-2" onclick="quickResponse('Tôi muốn tư vấn khóa học')">📚 Tư vấn khóa học</button>
                            <button class="btn btn-sm btn-outline-primary mb-2 me-2" onclick="quickResponse('Tôi muốn biết học phí')">💵 Học phí</button>
                            <button class="btn btn-sm btn-outline-primary mb-2" onclick="quickResponse('Tôi muốn đăng ký học')">📝 Đăng ký học</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const logoutForm = document.getElementById('logout-form');
        if (logoutForm) {
            logoutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
                    this.submit();
                }
            });
        }

        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'all 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getInstance(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    }
                }, 500);
            }, 5000);
        });

        const backToTop = document.querySelector('.back-to-top');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .course-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });

        const contactForm = document.querySelector('#contact form');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
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

        document.querySelectorAll('a[href*="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href.includes('#')) {
                    const [path, hash] = href.split('#');
                    if (path === "{{ route('home') }}" && hash) {
                        e.preventDefault();
                        window.location.href = "{{ route('home') }}";
                        setTimeout(() => {
                            const target = document.getElementById(hash);
                            if (target) {
                                target.scrollIntoView({ behavior: 'smooth' });
                            }
                        }, 100);
                    }
                }
            });
        });
    });

    function showEnhancedAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const main = document.querySelector('main');
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
        chatBox.classList.toggle('active');
    }

    function quickResponse(message) {
        addMessage(message, 'user');
        
        setTimeout(() => {
            let response = '';
            switch(message) {
                case 'Tôi muốn tư vấn khóa học':
                    response = 'Chúng tôi có các khóa học về Tin học văn phòng, Lập trình Web, Tiếng Anh, Thiết kế đồ họa. Bạn quan tâm khóa học nào?';
                    break;
                case 'Tôi muốn biết học phí':
                    response = 'Học phí từ 2.5 - 5 triệu tùy khóa học. Liên hệ 028 1234 5678 để được tư vấn chi tiết!';
                    break;
                case 'Tôi muốn đăng ký học':
                    response = 'Bạn có thể đăng ký online tại website hoặc đến trực tiếp cơ sở. Tôi có thể kết nối bạn với nhân viên tư vấn?';
                    break;
                default:
                    response = 'Cảm ơn bạn đã quan tâm! Nhân viên chúng tôi sẽ liên hệ lại ngay.';
            }
            addMessage(response, 'bot');
        }, 1000);
    }

    function sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if (message) {
            addMessage(message, 'user');
            input.value = '';
            
            setTimeout(() => {
                addMessage('Cảm ơn câu hỏi của bạn! Tôi đang kết nối với nhân viên tư vấn...', 'bot');
            }, 1500);
        }
    }

    function handleChatInput(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function addMessage(text, sender) {
        const chatBody = document.getElementById('chatBody');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}`;
        
        const avatarIcon = sender === 'user' ? 'fas fa-user' : 'fas fa-robot';
        const avatarBg = sender === 'user' ? 'gradient-accent' : 'gradient-primary';
        
        messageDiv.innerHTML = `
            <div class="avatar" style="background: ${sender === 'user' ? 'linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%)' : 'linear-gradient(135deg, #2c5aa0 0%, #1e3a8a 100%)'}">
                <i class="${avatarIcon}"></i>
            </div>
            <div class="message-content">
                <p>${text}</p>
            </div>
        `;
        
        chatBody.appendChild(messageDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
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