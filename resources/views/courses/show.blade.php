@extends('layouts.app')

@section('title', $course->title)

@section('page-class', 'page-course-show')

@push('styles')
    @vite('resources/css/pages/courses/show.css')
@endpush

@section('content')
@php
    $standaloneDurationMinutes = $standaloneMaterials->sum(fn ($material) => (int) $material->estimated_duration_minutes);
    $standaloneDurationLabel = $standaloneDurationMinutes > 0
        ? \App\Support\StudyDuration::formatMinutes($standaloneDurationMinutes)
        : 'Chưa ước tính';
    $requiresPaidCheckout = (float) $course->final_price > 0;
    $supportsVnpay = filled(config('services.vnpay.tmn_code')) && filled(config('services.vnpay.hash_secret'));
@endphp
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <img src="{{ $course->banner_image_url }}" class="card-img-top course-show-banner" alt="{{ $course->title }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                        <div>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-secondary">{{ $course->category->name ?? 'Chưa phân loại' }}</span>
                                <span class="badge bg-info text-dark">{{ $course->level }}</span>
                                <span class="badge bg-light text-dark border">{{ $course->modules->count() }} module</span>
                                @if($course->is_featured)
                                    <span class="badge bg-warning text-dark">Nổi bật</span>
                                @endif
                                @if($course->is_popular)
                                    <span class="badge bg-danger">Phổ biến</span>
                                @endif
                            </div>
                            <h1 class="fw-bold mb-2">{{ $course->title }}</h1>
                            <p class="lead mb-0">{{ $course->description }}</p>
                        </div>
                        <div class="text-end">
                            @if($course->sale_price)
                                <h2 class="text-primary fw-bold mb-0">{{ number_format($course->sale_price) }} VND</h2>
                                <small class="text-muted text-decoration-line-through">{{ number_format($course->price) }} VND</small>
                            @else
                                <h2 class="text-primary fw-bold mb-0">{{ number_format($course->price) }} VND</h2>
                            @endif
                        </div>
                    </div>

                    <div class="row text-center g-3">
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-3 h-100">
                                <i class="fas fa-clock text-primary mb-2"></i>
                                <div class="fw-bold">{{ $course->estimated_duration_label }}</div>
                                <small class="text-muted">Thời lượng ước tính</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-3 h-100">
                                <i class="fas fa-layer-group text-primary mb-2"></i>
                                <div class="fw-bold">{{ $course->modules->count() }}</div>
                                <small class="text-muted">Module</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-3 h-100">
                                <i class="fas fa-book text-primary mb-2"></i>
                                <div class="fw-bold">{{ $course->lessons_count }}</div>
                                <small class="text-muted">Nội dung học</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="border rounded p-3 h-100">
                                <i class="fas fa-users text-primary mb-2"></i>
                                <div class="fw-bold">{{ $course->students_count }}</div>
                                <small class="text-muted">Học viên</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Bạn sẽ học gì trong khóa này?</h4>
                    @if($course->modules->isNotEmpty())
                        <div class="row g-3">
                            @foreach($course->modules as $module)
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-1">{{ $module->title }}</h6>
                                                <div class="d-flex flex-wrap gap-2 small text-muted">
                                                    <span>{{ $module->materials->count() }} nội dung</span>
                                                    <span><i class="fas fa-clock me-1"></i>{{ $module->estimated_duration_label }}</span>
                                                </div>
                                            </div>
                                            <span class="badge bg-light text-dark border">#{{ $module->order }}</span>
                                        </div>
                                        <p class="text-muted mb-3">{{ $module->description ?: 'Module này tập trung vào một nhóm kỹ năng hoặc chủ đề cụ thể.' }}</p>
                                        @if($module->materials->isNotEmpty())
                                            <div class="small d-grid gap-2">
                                                @foreach($module->materials as $material)
                                                    <div class="d-flex justify-content-between align-items-center gap-2 border rounded px-2 py-2 bg-light-subtle">
                                                        <span class="text-truncate">
                                                            <span class="badge bg-light text-dark border me-2">{{ strtoupper($material->type) }}</span>
                                                            {{ $material->title ?: 'Nội dung đang cập nhật' }}
                                                        </span>
                                                        <span class="text-muted text-nowrap">{{ $material->estimated_duration_label }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">Khóa học này chưa cấu hình module riêng. Nội dung sẽ được cập nhật theo lộ trình học chung.</div>
                    @endif

                    @if($standaloneMaterials->isNotEmpty())
                        <div class="mt-4 border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-2">
                                <h6 class="fw-bold mb-0">Nội dung bổ sung</h6>
                                <span class="badge bg-light text-dark border">{{ $standaloneMaterials->count() }} nội dung - {{ $standaloneDurationLabel }}</span>
                            </div>
                            <div class="small d-grid gap-2">
                                @foreach($standaloneMaterials as $material)
                                    <div class="d-flex justify-content-between align-items-center gap-2 border rounded px-3 py-2 bg-light-subtle">
                                        <span class="text-truncate">
                                            <span class="badge bg-light text-dark border me-2">{{ strtoupper($material->type) }}</span>
                                            {{ $material->title ?: 'Nội dung đang cập nhật' }}
                                        </span>
                                        <span class="text-muted text-nowrap">{{ $material->estimated_duration_label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($isEnrolled)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        <h4 class="fw-bold mb-3">Đánh giá khóa học và giảng viên</h4>
                        <form action="{{ route('courses.reviews.store', $course) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Đánh giá khóa học</label>
                                    <select name="rating" class="form-select">
                                        @for($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" {{ old('rating', $userReview->rating ?? null) == $i ? 'selected' : '' }}>{{ $i }} sao</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Đánh giá giảng viên</label>
                                    <select name="instructor_rating" class="form-select">
                                        @for($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" {{ old('instructor_rating', $userReview->instructor_rating ?? null) == $i ? 'selected' : '' }}>{{ $i }} sao</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Nhận xét</label>
                                    <textarea name="comment" rows="3" class="form-control">{{ old('comment', $userReview->comment ?? '') }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Gửi đánh giá</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Nhận xét từ học viên</h4>
                    @forelse($reviews as $review)
                        <div class="pb-3 mb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <strong>{{ $review->user->fullname ?? $review->user->username }}</strong>
                                    <div class="small text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-{{ $i <= $review->rating ? 'warning' : 'muted' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="mb-2">{{ $review->comment ?? 'Không có nhận xét.' }}</p>
                            @if($review->replies->count())
                                <div class="ps-3">
                                    @foreach($review->replies as $reply)
                                        <div class="border rounded p-3 bg-light mb-2">
                                            <strong>{{ $reply->user->fullname ?? $reply->user->username }}</strong>
                                            <span class="small text-muted ms-2">{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                                            <div class="small mt-1">{{ $reply->comment }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if(Auth::check())
                                <form action="{{ route('courses.reviews.reply', [$course, $review]) }}" method="POST" class="mt-3">
                                    @csrf
                                    <textarea name="comment" rows="2" class="form-control mb-2" placeholder="Phản hồi đánh giá..."></textarea>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Gửi phản hồi</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Chưa có đánh giá nào.</p>
                    @endforelse
                    <div class="mt-4">{{ $reviews->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top course-show-sidebar" id="intakes">
                <div class="card-body">
                    <h3 class="text-primary fw-bold mb-3">
                        @if($course->sale_price)
                            {{ number_format($course->sale_price) }} VND
                        @else
                            {{ number_format($course->price) }} VND
                        @endif
                    </h3>

                    @if(session('required_topup'))
                        <div class="alert alert-warning">
                            <i class="fas fa-wallet me-2"></i>Số dư ví hiện chưa đủ để đăng ký khóa học này.
                            <a href="{{ route('wallet.index') }}" class="alert-link">Nạp thêm tiền vào ví</a>
                            rồi quay lại chọn đợt học phù hợp.
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @guest
                        <div class="alert alert-light border">
                            <i class="fas fa-user-lock me-2"></i>Vui lòng đăng nhập để đăng ký khóa học này.
                        </div>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập để đăng ký
                        </a>
                    @else
                        @if($isEnrolled)
                            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Bạn đã đăng ký khóa học này.</div>
                            <a href="{{ route('courses.learn', $course) }}" class="btn btn-success w-100 mb-2"><i class="fas fa-play me-2"></i>Vào học ngay</a>
                            <form action="{{ route('courses.unenroll', $course) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Bạn có chắc muốn hủy đăng ký khóa học này?')">Hủy đăng ký</button>
                            </form>
                        @elseif($isPending)
                            @php
                                $pendingClassName = $currentEnrollment?->courseClass?->name ?? $currentEnrollment?->class?->name;
                            @endphp
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-clock me-2"></i>
                                @if($course->isOffline() && $pendingClassName)
                                    Yêu cầu đăng ký đợt học <strong>{{ $pendingClassName }}</strong> đang chờ admin duyệt.
                                @else
                                    Yêu cầu đăng ký của bạn đang chờ admin xử lý.
                                @endif
                            </div>
                            <button class="btn btn-warning text-dark w-100" disabled>Chờ admin duyệt</button>
                        @elseif($course->isOnline())
                            <form action="{{ route('courses.enroll', $course) }}" method="POST" class="d-grid gap-2">
                                @csrf
                                @if($requiresPaidCheckout)
                                    <div>
                                        <label class="form-label small text-muted mb-1">Phương thức thanh toán</label>
                                        <select name="payment_method" class="form-select">
                                            <option value="wallet">Ví nội bộ</option>
                                            @if($supportsVnpay)
                                                <option value="vnpay">VNPay</option>
                                            @endif
                                            <option value="bank_transfer">Chuyển khoản</option>
                                            <option value="cash">Tiền mặt</option>
                                            <option value="counter">Tại quầy</option>
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="payment_method" value="wallet">
                                @endif
                                <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-plus me-2"></i>Đăng ký học ngay</button>
                            </form>
                            <small class="text-muted d-block mt-2">
                                @if($requiresPaidCheckout)
                                    Ví nội bộ hoặc VNPay sẽ xử lý nhanh. Chuyển khoản, tiền mặt và tại quầy sẽ cần thêm bước xác nhận.
                                @else
                                    Đăng ký thành công là bạn có thể vào học ngay mà không cần admin duyệt.
                                @endif
                            </small>
                        @else
                            @php $activeClasses = $classes->where('status', 'active'); @endphp
                            @if($activeClasses->isEmpty())
                                <div class="alert alert-danger mb-0">Khóa học này hiện chưa có đợt học nào đang mở đăng ký.</div>
                            @else
                                <h5 class="fw-bold mb-3">Đợt học đang mở</h5>
                                <div class="d-grid gap-3">
                                    @foreach($activeClasses as $cls)
                                        @php
                                            $scheduleLines = $cls->structured_schedule_lines;
                                            $isThisClass = isset($currentEnrollment) && $currentEnrollment->class_id == $cls->id;
                                            $isFull = ($cls->max_students ?: 0) > 0 && $cls->current_students_count >= ($cls->max_students ?: 0);
                                            $remainingSlots = $cls->max_students > 0 ? max(0, $cls->max_students - $cls->current_students_count) : null;
                                            $classInstructor = optional($cls->instructor)->fullname ?? optional($cls->instructor)->username;
                                        @endphp
                                        <div class="border rounded p-3">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <div>
                                                    <h6 class="mb-1 fw-bold">{{ $cls->name }}</h6>
                                                    <div class="small text-muted">{{ optional($cls->start_date)->format('d/m/Y') }} - {{ optional($cls->end_date)->format('d/m/Y') }}</div>
                                                </div>
                                                @if($isThisClass)
                                                    <span class="badge bg-success">Đang chọn</span>
                                                @elseif($isFull)
                                                    <span class="badge bg-secondary">Hết chỗ</span>
                                                @elseif(!is_null($remainingSlots))
                                                    <span class="badge bg-light text-dark border">Còn {{ $remainingSlots }} chỗ</span>
                                                @endif
                                            </div>
                                            @if($classInstructor)
                                                <div class="small text-muted mb-2"><i class="fas fa-chalkboard-teacher me-1"></i>{{ $classInstructor }}</div>
                                            @endif
                                            <div class="small text-muted mb-2">{{ $scheduleLines ? collect($scheduleLines)->take(2)->implode(' | ') : ($cls->schedule_text ?: 'Chưa cập nhật lịch học') }}</div>
                                            <div class="small text-muted mb-3">{{ $cls->meeting_info ?: 'Chưa cập nhật phòng học / địa điểm' }}</div>
                                            @if($isThisClass)
                                                <form action="{{ route('courses.unenroll', $course) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Bạn có chắc muốn hủy đăng ký đợt học này?')">Hủy đăng ký</button>
                                                </form>
                                            @elseif($isFull)
                                                <button class="btn btn-secondary btn-sm w-100" disabled>Đợt học đã đầy</button>
                                            @else
                                                <form action="{{ route('courses.enroll', $course) }}" method="POST" class="d-grid gap-2">
                                                    @csrf
                                                    <input type="hidden" name="class_id" value="{{ $cls->id }}">
                                                    @if($requiresPaidCheckout)
                                                        <select name="payment_method" class="form-select form-select-sm">
                                                            <option value="wallet">Ví nội bộ</option>
                                                            @if($supportsVnpay)
                                                                <option value="vnpay">VNPay</option>
                                                            @endif
                                                            <option value="bank_transfer">Chuyển khoản</option>
                                                            <option value="cash">Tiền mặt</option>
                                                            <option value="counter">Tại quầy</option>
                                                        </select>
                                                    @else
                                                        <input type="hidden" name="payment_method" value="wallet">
                                                    @endif
                                                    <button type="submit" class="btn btn-primary btn-sm w-100">Gửi yêu cầu đăng ký</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    @endguest

                    <hr class="my-4">
                    <div class="text-start small text-muted d-grid gap-2">
                        <div><strong>Nhóm ngành:</strong> {{ $course->category->name ?? 'Chưa phân loại' }}</div>
                        <div><strong>Hình thức đào tạo:</strong> {{ $course->delivery_mode_label }}</div>
                        <div><strong>Thời lượng ước tính:</strong> {{ $course->estimated_duration_label }}</div>
                        <div><strong>Module:</strong> {{ $course->modules->count() }}</div>
                        <div><strong>Nội dung học:</strong> {{ $course->lessons_count }}</div>
                        <div><strong>Giảng viên:</strong> {{ optional($course->instructor)->fullname ?? optional($course->instructor)->username ?? 'Giảng viên' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($similarCourses->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="fw-bold mb-4">Khóa học cùng nhóm ngành</h3>
                <div class="row g-4">
                    @foreach($similarCourses as $similarCourse)
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 shadow-sm border-0">
                                <img src="{{ $similarCourse->thumbnail_url }}" class="card-img-top course-show-similar-thumb" alt="{{ $similarCourse->title }}">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge bg-secondary">{{ $similarCourse->category->name ?? 'Chưa phân loại' }}</span>
                                        <span class="badge bg-light text-dark border">{{ $similarCourse->modules_count }} module</span>
                                    </div>
                                    <h6 class="fw-bold">{{ \Illuminate\Support\Str::limit($similarCourse->title, 50) }}</h6>
                                    <div class="small text-muted mb-3"><i class="fas fa-clock me-1"></i>{{ $similarCourse->estimated_duration_label }}</div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="text-primary fw-bold">
                                            @if($similarCourse->sale_price)
                                                {{ number_format($similarCourse->sale_price) }} VND
                                            @else
                                                {{ number_format($similarCourse->price) }} VND
                                            @endif
                                        </span>
                                        <a href="{{ route('courses.show', $similarCourse) }}" class="btn btn-sm btn-outline-primary">Chi tiáº¿t</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
