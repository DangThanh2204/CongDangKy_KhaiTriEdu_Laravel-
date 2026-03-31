@extends('layouts.app')

@section('title', 'Trang chủ')
@section('page-class', 'page-home')

@push('styles')
    @vite('resources/css/pages/home.css')
@endpush

@section('content')
    @php
        $featuredCourses = \App\Models\Course::published()
            ->with('category')
            ->withCount('modules')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        $latestPosts = collect();

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('posts')) {
                $latestPosts = \App\Models\Post::published()
                    ->with(['author', 'category'])
                    ->orderByDesc('created_at')
                    ->limit(3)
                    ->get();
            }
        } catch (\Throwable $exception) {
            $latestPosts = collect();
        }

        $portalDashboardUrl = route('login');
        $portalDashboardLabel = 'Tạo tài khoản học viên';
        $portalDashboardDescription = 'Đăng ký tài khoản để lưu khóa học, lịch học và tiến độ học tập của bạn.';

        if (auth()->check()) {
            $portalDashboardUrl = auth()->user()->isAdmin()
                ? route('admin.dashboard')
                : (auth()->user()->isStaff()
                    ? route('staff.dashboard')
                    : (auth()->user()->isInstructor()
                        ? route('instructor.dashboard')
                        : route('student.dashboard')));
            $portalDashboardLabel = 'Mở bảng điều khiển';
            $portalDashboardDescription = 'Quay lại dashboard để xem các khóa đã đăng ký, tiến độ học và thông báo mới.';
        }

        $introSlides = collect([
            [
                'image' => asset('images/hero.jpg'),
                'eyebrow' => 'Cổng đăng ký trực tuyến',
                'title' => 'Tra cứu khóa học, xem đợt mở và bắt đầu hành trình học tại Khai Trí.',
                'description' => 'Bạn có thể xem rõ nhóm ngành, thời lượng, module, hình thức học và trạng thái tuyển sinh ngay từ khi chưa đăng ký.',
                'highlights' => ['Tra cứu nhanh', 'Đăng ký minh bạch', 'Theo dõi tiến độ'],
                'primary_text' => 'Tìm khóa học',
                'primary_url' => route('courses.index'),
                'secondary_text' => 'Đăng ký tư vấn',
                'secondary_url' => '#contact',
            ],
            [
                'image' => asset('images/course-web.jpg'),
                'eyebrow' => 'Nhóm ngành công nghệ',
                'title' => 'Các khóa công nghệ được sắp theo module để học viên biết rõ mình sẽ học gì trước khi đăng ký.',
                'description' => 'Từ web, lập trình nền tảng đến thực hành dự án, nội dung đều được trình bày rõ ràng để người học dễ chọn đúng lộ trình.',
                'highlights' => ['Lập trình thực chiến', 'Module rõ ràng', 'Có bài tập'],
                'primary_text' => 'Xem khóa công nghệ',
                'primary_url' => route('courses.index', ['filter' => 'popular']),
                'secondary_text' => 'Khóa nổi bật',
                'secondary_url' => route('courses.index', ['filter' => 'featured']),
            ],
            [
                'image' => asset('images/course-design.jpg'),
                'eyebrow' => 'Thiết kế sáng tạo',
                'title' => 'Khóa thiết kế được giới thiệu ngắn gọn, trực quan và tập trung vào kỹ năng ứng dụng.',
                'description' => 'Người học có thể xem nhanh độ khó, thời lượng, module và hình thức học để quyết định sớm mà không bị rối thông tin.',
                'highlights' => ['Trực quan', 'Dễ tiếp cận', 'Ứng dụng thực tế'],
                'primary_text' => 'Khám phá khóa học',
                'primary_url' => route('courses.index'),
                'secondary_text' => 'Tin tức đào tạo',
                'secondary_url' => route('news.index'),
            ],
            [
                'image' => asset('images/course-english.jpg'),
                'eyebrow' => 'Tiếng Anh ứng dụng',
                'title' => 'Học viên có thể nhìn thấy rõ từng kỹ năng như Nghe, Nói, Đọc, Viết ngay trong cấu trúc khóa học.',
                'description' => 'Điều đó giúp việc đăng ký khóa phù hợp với mục tiêu cá nhân nhanh hơn và dễ hiểu hơn so với kiểu giới thiệu chung chung.',
                'highlights' => ['Theo kỹ năng', 'Dễ theo dõi', 'Có lộ trình'],
                'primary_text' => 'Tìm khóa phù hợp',
                'primary_url' => route('courses.index'),
                'secondary_text' => 'Liên hệ tư vấn',
                'secondary_url' => '#contact',
            ],
            [
                'image' => asset('images/course-office.jpg'),
                'eyebrow' => 'Tin học văn phòng',
                'title' => 'Các khóa nền tảng cho học tập và công việc được trình bày ngắn gọn để người mới dễ chọn và dễ bắt đầu.',
                'description' => 'Bạn có thể biết trước thời lượng ước tính, số module và cách học online hay offline trước khi bấm đăng ký.',
                'highlights' => ['Word - Excel - PowerPoint', 'Học nhanh', 'Ứng dụng ngay'],
                'primary_text' => 'Xem nội dung học',
                'primary_url' => route('courses.index'),
                'secondary_text' => 'Đăng ký tài khoản',
                'secondary_url' => route('register'),
            ],
            [
                'image' => asset('images/laptrinh1.jpg'),
                'eyebrow' => 'Lớp offline rõ ràng',
                'title' => 'Các đợt học offline hiển thị đủ lịch học, giáo viên, ngày khai giảng và số chỗ còn lại để bạn gửi yêu cầu đúng lớp.',
                'description' => 'Luồng đăng ký offline giữ trạng thái chờ duyệt, còn thông tin đợt học luôn được hiển thị rõ để người học không bị mơ hồ.',
                'highlights' => ['Lịch học rõ', 'Biết số chỗ', 'Có giáo viên'],
                'primary_text' => 'Xem lớp offline',
                'primary_url' => route('courses.index', ['delivery_mode' => 'offline']),
                'secondary_text' => 'Xem khóa học',
                'secondary_url' => '#courses',
            ],
            [
                'image' => asset('images/laptrinh2.jpg'),
                'eyebrow' => 'Theo dõi sau đăng ký',
                'title' => 'Từ lúc đăng ký đến khi hoàn thành khóa học, học viên vẫn theo dõi được toàn bộ hành trình ngay trên hệ thống.',
                'description' => 'Dashboard hiển thị tiến độ, thời lượng, cấp độ học viên, khóa đã đăng ký và các cập nhật mới trên cùng một nơi.',
                'highlights' => ['Dashboard học viên', 'Tiến độ rõ ràng', 'Chứng chỉ - cấp độ'],
                'primary_text' => 'Mở dashboard',
                'primary_url' => $portalDashboardUrl,
                'secondary_text' => 'Khóa học nổi bật',
                'secondary_url' => route('courses.index', ['filter' => 'featured']),
            ],
        ]);

        $portalQuickLinks = collect([
            [
                'icon' => 'fa-magnifying-glass',
                'title' => 'Tra cứu khóa học',
                'description' => 'Tìm theo tên khóa, nhóm ngành hoặc kỹ năng bạn muốn học.',
                'url' => route('courses.index'),
            ],
            [
                'icon' => 'fa-play-circle',
                'title' => 'Học online ngay',
                'description' => 'Khóa online đăng ký xong có thể bắt đầu học ngay trên hệ thống.',
                'url' => route('courses.index', ['delivery_mode' => 'online']),
            ],
            [
                'icon' => 'fa-school',
                'title' => 'Xem lớp offline',
                'description' => 'Theo dõi lịch học, giáo viên, số chỗ và gửi yêu cầu đăng ký.',
                'url' => route('courses.index', ['delivery_mode' => 'offline']),
            ],
            [
                'icon' => auth()->check() ? 'fa-gauge-high' : 'fa-user-plus',
                'title' => $portalDashboardLabel,
                'description' => $portalDashboardDescription,
                'url' => $portalDashboardUrl,
            ],
        ]);
    @endphp

    <section id="home" class="home-banner-section">
        <div class="container">
            <div class="home-portal-shell">
                <div class="home-banner-head">
                    <div class="home-banner-copy">
                        <span class="home-banner-kicker">Cổng đăng ký khóa học trực tuyến</span>
                        <h1 class="home-banner-title">Khai Trí Edu</h1>
                        <p class="home-banner-subtitle">Tra cứu khóa học, xem đợt học mở, biết trước module và hình thức học để đăng ký đúng chương trình phù hợp với bạn.</p>
                    </div>

                    <form action="{{ route('courses.index') }}" method="GET" class="home-portal-search" role="search">
                        <label for="homeCourseSearch" class="visually-hidden">Tìm khóa học</label>
                        <div class="home-portal-search-input">
                            <i class="fas fa-magnifying-glass"></i>
                            <input type="text" id="homeCourseSearch" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên khóa học, nhóm ngành hoặc kỹ năng...">
                        </div>
                        <div class="home-portal-search-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Tra cứu khóa học
                            </button>
                            <a href="{{ route('courses.index', ['filter' => 'featured']) }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-star me-2"></i>Khóa nổi bật
                            </a>
                        </div>
                    </form>
                </div>

                <div class="home-portal-quick-actions" aria-label="Lối tắt cổng đăng ký">
                    @foreach($portalQuickLinks as $quickLink)
                        <a href="{{ $quickLink['url'] }}" class="home-portal-quick-link">
                            <span class="home-portal-quick-icon"><i class="fas {{ $quickLink['icon'] }}"></i></span>
                            <span class="home-portal-quick-body">
                                <strong>{{ $quickLink['title'] }}</strong>
                                <small>{{ $quickLink['description'] }}</small>
                            </span>
                        </a>
                    @endforeach
                </div>

                <div class="home-banner-frame" data-home-banner>
                    <div class="home-banner-stage">
                        @foreach($introSlides as $slideIndex => $slide)
                            <article class="home-banner-slide {{ $slideIndex === 0 ? 'is-active' : '' }}" data-banner-slide>
                                <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}" class="home-banner-image">
                                <div class="home-banner-overlay"></div>

                                <div class="home-banner-content">
                                    <span class="home-banner-badge">{{ $slide['eyebrow'] }}</span>
                                    <h2>{{ $slide['title'] }}</h2>
                                    <p>{{ $slide['description'] }}</p>

                                    <div class="home-banner-highlights">
                                        @foreach($slide['highlights'] as $highlight)
                                            <span>{{ $highlight }}</span>
                                        @endforeach
                                    </div>

                                    <div class="home-banner-actions">
                                        <a href="{{ $slide['primary_url'] }}" class="btn btn-primary btn-lg">{{ $slide['primary_text'] }}</a>
                                        <a href="{{ $slide['secondary_url'] }}" class="btn btn-outline-light btn-lg">{{ $slide['secondary_text'] }}</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach

                        <button type="button" class="home-banner-arrow home-banner-arrow-prev" data-banner-prev aria-label="Slide trước">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="home-banner-arrow home-banner-arrow-next" data-banner-next aria-label="Slide sau">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="home-banner-dots" aria-label="Điều hướng banner">
                        @foreach($introSlides as $slideIndex => $slide)
                            <button type="button" class="home-banner-dot {{ $slideIndex === 0 ? 'is-active' : '' }}" data-banner-dot="{{ $slideIndex }}" aria-label="Đi tới slide {{ $slideIndex + 1 }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="courses" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="fw-bold mb-3">Khóa học nổi bật</h2>
                    <p class="text-muted lead mb-0">Chọn nhanh chương trình phù hợp và xem ngay hình thức đào tạo, thời lượng, tình trạng đăng ký.</p>
                </div>
            </div>

            <div class="row g-4">
                @forelse($featuredCourses as $course)
                    @php
                        $courseEnrollments = collect();

                        if (Auth::check()) {
                            $courseEnrollments = \App\Models\CourseEnrollment::query()
                                ->forCourse($course)
                                ->where('user_id', Auth::id())
                                ->get(['status']);
                        }

                        $isEnrolled = $courseEnrollments->contains(fn ($enrollment) => in_array($enrollment->status, ['approved', 'completed'], true));
                        $isPending = $courseEnrollments->contains('status', 'pending');
                    @endphp

                    <div class="col-md-6 col-xl-3">
                        <div class="card course-card h-100">
                            <div class="course-image home-course-media">
                                @if($course->thumbnail)
                                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="home-course-thumb">
                                @else
                                    <i class="fas fa-book fa-2x"></i>
                                @endif

                                @if($course->is_featured)
                                    <div class="course-badge">Nổi bật</div>
                                @endif
                            </div>

                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <span class="badge bg-primary">{{ $course->category->name ?? 'Nhóm ngành' }}</span>
                                    <span class="badge {{ $course->isOffline() ? 'bg-warning text-dark' : 'bg-success' }}">{{ $course->delivery_mode_label }}</span>
                                </div>

                                <h5 class="card-title fw-bold home-card-title">{{ \Illuminate\Support\Str::limit($course->title, 54) }}</h5>
                                <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($course->short_description ?: $course->description, 90) }}</p>

                                <div class="small text-muted d-grid gap-1 mb-3">
                                    <div><i class="fas fa-clock me-2"></i>{{ $course->estimated_duration_label }}</div>
                                    <div><i class="fas fa-layer-group me-2"></i>{{ $course->modules_count ?? 0 }} module</div>
                                    <div><i class="fas fa-users me-2"></i>{{ $course->students_count }} học viên</div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 mt-auto">
                                    <span class="text-primary fw-bold h5 mb-0">{{ number_format($course->final_price) }} VND</span>
                                    @if($course->sale_price && $course->sale_price < $course->price)
                                        <span class="text-muted text-decoration-line-through small">{{ number_format($course->price) }} VND</span>
                                    @endif
                                </div>

                                <div class="d-grid gap-2">
                                    @auth
                                        @if($isEnrolled)
                                            <a href="{{ route('student.dashboard') }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-check me-1"></i>Đã đăng ký
                                            </a>
                                        @elseif($isPending)
                                            <button class="btn btn-warning btn-sm text-dark" disabled>
                                                <i class="fas fa-clock me-1"></i>Chờ admin duyệt
                                            </button>
                                        @elseif($course->isOffline())
                                            <a href="{{ route('courses.show', $course) }}#intakes" class="btn btn-primary btn-sm">
                                                <i class="fas fa-paper-plane me-1"></i>Gửi yêu cầu đăng ký
                                            </a>
                                        @else
                                            <form action="{{ route('courses.enroll', $course) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                                    <i class="fas fa-plus me-1"></i>Đăng ký học ngay
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập để đăng ký
                                        </a>
                                    @endauth

                                    <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-info-circle me-1"></i>Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Đang cập nhật khóa học mới.</p>
                        @auth
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Thêm khóa học mới
                                </a>
                            @endif
                        @endauth
                    </div>
                @endforelse
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-list me-2"></i>Xem tất cả khóa học
                </a>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="fw-bold mb-3">Vì sao học viên chọn Khai Trí?</h2>
                    <p class="text-muted lead mb-0">Mô hình khóa học rõ ràng theo nhóm ngành, khóa học, module và đợt học giúp bạn dễ chọn và dễ theo dõi tiến độ.</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <h5 class="fw-bold">Lộ trình theo module</h5>
                            <p class="text-muted mb-0">Mỗi khóa học được chia thành từng module kỹ năng rõ ràng để bạn biết mình sẽ học gì và cần bao lâu.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-laptop-house"></i>
                            </div>
                            <h5 class="fw-bold">Online và offline linh hoạt</h5>
                            <p class="text-muted mb-0">Khóa online có thể vào học ngay, khóa offline quản lý theo đợt học, giáo viên, lịch học và số lượng chỗ rõ ràng.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <h5 class="fw-bold">Theo dõi tiến độ dễ dàng</h5>
                            <p class="text-muted mb-0">Học viên xem được thời lượng ước tính, trạng thái đăng ký, đợt học hiện tại và tiến độ hoàn thành ngay trên hệ thống.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="news" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="fw-bold mb-3">Tin tức mới nhất</h2>
                    <p class="text-muted lead mb-0">Cập nhật nhanh thông báo, bài viết và các hoạt động mới của trung tâm.</p>
                </div>
            </div>

            <div class="row g-4">
                @forelse($latestPosts as $post)
                    <div class="col-lg-4 col-md-6">
                        <div class="card course-card h-100">
                            <div class="course-image home-news-media">
                                @if($post->featured_image)
                                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="home-news-thumb">
                                @else
                                    <i class="fas fa-newspaper fa-2x"></i>
                                @endif

                                @if($post->is_featured)
                                    <div class="course-badge home-news-badge">Nổi bật</div>
                                @endif
                            </div>

                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary">{{ $post->category->name ?? 'Tin tức' }}</span>
                                    <small class="text-muted">{{ optional($post->published_at ?? $post->created_at)->format('d/m/Y') }}</small>
                                </div>

                                <h5 class="card-title fw-bold home-card-title">{{ \Illuminate\Support\Str::limit($post->title, 60) }}</h5>
                                <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 110) }}</p>

                                <div class="d-flex justify-content-between align-items-center text-muted small mt-auto mb-3">
                                    <span><i class="fas fa-eye me-1"></i>{{ $post->view_count }} lượt xem</span>
                                    <span><i class="fas fa-user me-1"></i>{{ $post->author->name ?? 'Admin' }}</span>
                                </div>

                                <a href="{{ route('news.show', $post->slug) }}" class="btn btn-outline-primary btn-sm mt-auto">
                                    Đọc tiếp <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Chưa có tin tức mới để hiển thị.</p>
                    </div>
                @endforelse
            </div>

            @if($latestPosts->isNotEmpty())
                <div class="text-center mt-5">
                    <a href="{{ route('news.index') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-newspaper me-2"></i>Xem tất cả tin tức
                    </a>
                </div>
            @endif
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2 class="fw-bold text-primary mb-1">5,000+</h2>
                    <p class="text-muted mb-0">Học viên</p>
                </div>
                <div class="col-6 col-md-3">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-book"></i>
                    </div>
                    <h2 class="fw-bold text-primary mb-1">50+</h2>
                    <p class="text-muted mb-0">Khóa học</p>
                </div>
                <div class="col-6 col-md-3">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h2 class="fw-bold text-primary mb-1">100+</h2>
                    <p class="text-muted mb-0">Giảng viên</p>
                </div>
                <div class="col-6 col-md-3">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-award"></i>
                    </div>
                    <h2 class="fw-bold text-primary mb-1">98%</h2>
                    <p class="text-muted mb-0">Hài lòng</p>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card contact-card shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-3">Đăng ký tư vấn khóa học</h2>
                            <p class="text-center text-muted mb-4">Để lại thông tin, đội ngũ Khai Trí sẽ liên hệ tư vấn miễn phí và gợi ý khóa học phù hợp cho bạn.</p>

                            <form id="contactForm">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" required placeholder="Nhập họ và tên">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" required placeholder="Nhập số điện thoại">
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" placeholder="Nhập email">
                                    </div>
                                    <div class="col-12">
                                        <label for="course" class="form-label">Khóa học quan tâm</label>
                                        <select class="form-select" id="course">
                                            <option selected>Chọn khóa học...</option>
                                            @foreach($featuredCourses as $course)
                                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="message" class="form-label">Nhu cầu học tập</label>
                                        <textarea class="form-control" id="message" rows="3" placeholder="Mô tả ngắn nhu cầu học tập của bạn..."></textarea>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-submit btn-lg px-5">
                                            <i class="fas fa-paper-plane me-2"></i>Gửi thông tin
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const banner = document.querySelector('[data-home-banner]');
            if (!banner) {
                return;
            }

            const slides = Array.from(banner.querySelectorAll('[data-banner-slide]'));
            const dots = Array.from(banner.querySelectorAll('[data-banner-dot]'));
            const prevButton = banner.querySelector('[data-banner-prev]');
            const nextButton = banner.querySelector('[data-banner-next]');

            if (slides.length <= 1) {
                return;
            }

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
            let intervalId = null;

            if (activeIndex < 0) {
                activeIndex = 0;
                slides[0].classList.add('is-active');
            }

            const showSlide = (index) => {
                const normalizedIndex = (index + slides.length) % slides.length;
                activeIndex = normalizedIndex;

                slides.forEach((slide, slideIndex) => {
                    slide.classList.toggle('is-active', slideIndex === normalizedIndex);
                });

                dots.forEach((dot, dotIndex) => {
                    const isActive = dotIndex === normalizedIndex;
                    dot.classList.toggle('is-active', isActive);
                    dot.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            };

            const stopAutoplay = () => {
                if (intervalId) {
                    window.clearInterval(intervalId);
                    intervalId = null;
                }
            };

            const startAutoplay = () => {
                if (reducedMotion) {
                    return;
                }

                stopAutoplay();
                intervalId = window.setInterval(() => {
                    showSlide(activeIndex + 1);
                }, 5000);
            };

            prevButton?.addEventListener('click', function () {
                showSlide(activeIndex - 1);
                startAutoplay();
            });

            nextButton?.addEventListener('click', function () {
                showSlide(activeIndex + 1);
                startAutoplay();
            });

            dots.forEach((dot, index) => {
                dot.addEventListener('click', function () {
                    showSlide(index);
                    startAutoplay();
                });
            });

            banner.addEventListener('mouseenter', stopAutoplay);
            banner.addEventListener('mouseleave', startAutoplay);
            banner.addEventListener('focusin', stopAutoplay);
            banner.addEventListener('focusout', startAutoplay);

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    stopAutoplay();
                } else {
                    startAutoplay();
                }
            });

            startAutoplay();
        });
    </script>
@endpush