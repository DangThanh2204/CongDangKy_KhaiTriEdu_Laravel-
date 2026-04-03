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
        $activeClasses = $classes->where('status', 'active');
        $pendingClassName = $currentEnrollment?->courseClass?->name ?? $currentEnrollment?->class?->name;
        $currentEnrollmentIsWaitlisted = ($currentEnrollment?->isWaitlisted()) ?? false;
        $currentEnrollmentIsSeatHeld = ($currentEnrollment?->hasActiveSeatHold()) ?? false;
        $currentEnrollmentWaitlistPosition = $currentEnrollment?->waitlist_position;
        $seatHoldEndsAt = $currentEnrollment?->seat_hold_expires_at;
        $walletBalance = 0;

        if (Auth::check()) {
            $walletBalance = (float) (Auth::user()->wallet->balance ?? Auth::user()->getOrCreateWallet()->balance);
        }

        $walletShortage = $requiresPaidCheckout ? max(0, (float) $course->final_price - $walletBalance) : 0;
        $walletEnough = ! $requiresPaidCheckout || $walletShortage <= 0;
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
                                    <span class="badge bg-info text-dark">{{ ucfirst($course->level) }}</span>
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
                                <h2 class="text-primary fw-bold mb-0">{{ number_format($course->final_price) }} VND</h2>
                                @if($course->sale_price && $course->sale_price < $course->price)
                                    <small class="text-muted text-decoration-line-through">{{ number_format($course->price) }} VND</small>
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
                                            <p class="text-muted mb-3">
                                                {{ $module->description ?: 'Module này tập trung vào một nhóm kỹ năng hoặc chủ đề cụ thể.' }}
                                            </p>

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
                            <div class="alert alert-light border mb-0">
                                Khóa học này chưa cấu hình module riêng. Nội dung sẽ được cập nhật theo lộ trình học chung.
                            </div>
                        @endif

                        @if($standaloneMaterials->isNotEmpty())
                            <div class="mt-4 border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-2">
                                    <h6 class="fw-bold mb-0">Nội dung bổ sung</h6>
                                    <span class="badge bg-light text-dark border">
                                        {{ $standaloneMaterials->count() }} nội dung - {{ $standaloneDurationLabel }}
                                    </span>
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
                        <h3 class="text-primary fw-bold mb-3">{{ number_format($course->final_price) }} VND</h3>

                        @if(session('required_topup'))
                            <div class="alert alert-warning">
                                <i class="fas fa-wallet me-2"></i>Số dư ví hiện chưa đủ để đăng ký khóa học này.
                                <a href="{{ route('wallet.index') }}" class="alert-link">Nạp thêm tiền vào ví</a>
                                rồi quay lại đăng ký.
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
                            @if($requiresPaidCheckout)
                                <div class="border rounded p-3 bg-light-subtle mb-3">
                                    <div class="small text-muted mb-1">Thanh toán khóa học</div>
                                    <div class="fw-semibold">Chỉ dùng số dư ví nội bộ</div>
                                    <div class="small text-muted mt-2">Số dư ví hiện tại: <strong>{{ number_format($walletBalance, 0) }}đ</strong></div>
                                    @if(! $walletEnough)
                                        <div class="small text-danger mt-1">Bạn cần nạp thêm {{ number_format($walletShortage, 0) }}đ để mua khóa học này.</div>
                                    @endif
                                </div>
                            @endif

                            @if($isEnrolled)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>Bạn đã đăng ký khóa học này.
                                </div>
                                <a href="{{ route('courses.learn', $course) }}" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-play me-2"></i>Vào học ngay
                                </a>
                                <form action="{{ route('courses.unenroll', $course) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Bạn có chắc muốn hủy đăng ký khóa học này?')">
                                        Hủy đăng ký
                                    </button>
                                </form>
                            @elseif($isPending)
                                @if($currentEnrollmentIsSeatHeld)
                                    <div class="alert alert-primary mb-3">
                                        <i class="fas fa-hourglass-half me-2"></i>
                                        Bạn đang được giữ chỗ 24h cho đợt học <strong>{{ $pendingClassName }}</strong>
                                        đến <strong>{{ optional($seatHoldEndsAt)->format('d/m/Y H:i') }}</strong>.
                                    </div>
                                    @if($requiresPaidCheckout && ! $walletEnough)
                                        <a href="{{ route('wallet.index') }}" class="btn btn-primary w-100">
                                            <i class="fas fa-wallet me-2"></i>Nạp ví để giữ chỗ
                                        </a>
                                        <small class="text-muted d-block mt-2">Bạn cần nạp thêm {{ number_format($walletShortage, 0) }}đ trước khi hết hạn giữ chỗ.</small>
                                    @else
                                        <form action="{{ route('courses.confirm-seat-hold', $course) }}" method="POST" class="d-grid gap-2">
                                            @csrf
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-check-circle me-2"></i>Xác nhận giữ chỗ
                                            </button>
                                        </form>
                                        <small class="text-muted d-block mt-2">Sau khi xác nhận, yêu cầu đăng ký sẽ tiếp tục vào bước chờ admin duyệt hoặc kích hoạt học ngay tùy loại khóa học.</small>
                                    @endif
                                @elseif($currentEnrollmentIsWaitlisted)
                                    <div class="alert alert-dark mb-3">
                                        <i class="fas fa-list-ol me-2"></i>
                                        Bạn đang ở hàng chờ cho đợt học <strong>{{ $pendingClassName }}</strong>
                                        @if($currentEnrollmentWaitlistPosition)
                                            với vị trí <strong>#{{ $currentEnrollmentWaitlistPosition }}</strong>.
                                        @endif
                                    </div>
                                    <button class="btn btn-dark w-100" disabled>Đang chờ tới lượt</button>
                                @else
                                    <div class="alert alert-warning mb-3">
                                        <i class="fas fa-clock me-2"></i>
                                        @if($course->isOffline() && $pendingClassName)
                                            Yêu cầu đăng ký đợt học <strong>{{ $pendingClassName }}</strong> đang chờ admin duyệt.
                                        @else
                                            Yêu cầu đăng ký của bạn đang chờ admin xử lý.
                                        @endif
                                    </div>
                                    <button class="btn btn-warning text-dark w-100" disabled>Chờ admin duyệt</button>
                                @endif
                            @elseif($course->isOnline())
                                @if($requiresPaidCheckout && ! $walletEnough)
                                    <a href="{{ route('wallet.index') }}" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-wallet me-2"></i>Nạp tiền vào ví
                                    </a>
                                    <small class="text-muted d-block mt-2">Nạp đủ tiền vào ví rồi quay lại bấm đăng ký học ngay.</small>
                                @else
                                    <form action="{{ route('courses.enroll', $course) }}" method="POST" class="d-grid gap-2">
                                        @csrf
                                        <input type="hidden" name="payment_method" value="wallet">
                                        <button type="submit" class="btn btn-primary btn-lg w-100">
                                            <i class="fas fa-plus me-2"></i>{{ $requiresPaidCheckout ? 'Thanh toán ví và đăng ký' : 'Đăng ký học ngay' }}
                                        </button>
                                    </form>
                                    <small class="text-muted d-block mt-2">
                                        @if($requiresPaidCheckout)
                                            Hệ thống sẽ trừ tiền trực tiếp từ ví nội bộ và kích hoạt quyền học ngay sau khi đăng ký thành công.
                                        @else
                                            Đăng ký thành công là bạn có thể vào học ngay mà không cần admin duyệt.
                                        @endif
                                    </small>
                                @endif
                            @else
                                @if($activeClasses->isEmpty())
                                    <div class="alert alert-danger mb-0">Khóa học này hiện chưa có đợt học nào đang mở đăng ký.</div>
                                @else
                                    <h5 class="fw-bold mb-3">Đợt học đang mở</h5>
                                    <div class="d-grid gap-3">
                                        @foreach($activeClasses as $cls)
                                            @php
                                                $scheduleLines = $cls->structured_schedule_lines;
                                                $isThisClass = isset($currentEnrollment) && $currentEnrollment->class_id == $cls->id;
                                                $isHeldClass = $isThisClass && $currentEnrollmentIsSeatHeld;
                                                $isWaitlistClass = $isThisClass && $currentEnrollmentIsWaitlisted;
                                                $isFull = $cls->is_full;
                                                $remainingSlots = $cls->remaining_slots;
                                                $waitlistCount = $cls->waitlist_count;
                                                $classInstructor = optional($cls->instructor)->fullname ?? optional($cls->instructor)->username;
                                            @endphp

                                            <div class="border rounded p-3">
                                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold">{{ $cls->name }}</h6>
                                                        <div class="small text-muted">
                                                            {{ optional($cls->start_date)->format('d/m/Y') }} - {{ optional($cls->end_date)->format('d/m/Y') }}
                                                        </div>
                                                    </div>
                                                    @if($isHeldClass)
                                                        <span class="badge bg-primary">Giữ chỗ 24h</span>
                                                    @elseif($isWaitlistClass)
                                                        <span class="badge bg-dark">Trong hàng chờ</span>
                                                    @elseif($isThisClass)
                                                        <span class="badge bg-success">Đang chọn</span>
                                                    @elseif($isFull)
                                                        <span class="badge bg-secondary">Đã đầy</span>
                                                    @elseif(!is_null($remainingSlots))
                                                        <span class="badge bg-light text-dark border">Còn {{ $remainingSlots }} chỗ</span>
                                                    @endif
                                                </div>

                                                @if($classInstructor)
                                                    <div class="small text-muted mb-2"><i class="fas fa-chalkboard-teacher me-1"></i>{{ $classInstructor }}</div>
                                                @endif

                                                <div class="small text-muted mb-2">
                                                    {{ $scheduleLines ? collect($scheduleLines)->take(2)->implode(' | ') : ($cls->schedule_text ?: 'Chưa cập nhật lịch học') }}
                                                </div>
                                                <div class="small text-muted mb-3">{{ $cls->meeting_info ?: 'Chưa cập nhật phòng học / địa điểm' }}</div>

                                                @if($isHeldClass)
                                                    @if($requiresPaidCheckout && ! $walletEnough)
                                                        <a href="{{ route('wallet.index') }}" class="btn btn-primary btn-sm w-100">
                                                            <i class="fas fa-wallet me-1"></i>Nạp ví để giữ chỗ
                                                        </a>
                                                    @else
                                                        <form action="{{ route('courses.confirm-seat-hold', $course) }}" method="POST" class="d-grid gap-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                                Xác nhận giữ chỗ
                                                            </button>
                                                        </form>
                                                    @endif
                                                @elseif($isThisClass)
                                                    <form action="{{ route('courses.unenroll', $course) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Bạn có chắc muốn hủy đăng ký đợt học này?')">
                                                            {{ $isWaitlistClass ? 'Rời hàng chờ' : 'Hủy đăng ký' }}
                                                        </button>
                                                    </form>
                                                @elseif($isFull)
                                                    <form action="{{ route('courses.enroll', $course) }}" method="POST" class="d-grid gap-2">
                                                        @csrf
                                                        <input type="hidden" name="class_id" value="{{ $cls->id }}">
                                                        <button type="submit" class="btn btn-outline-dark btn-sm w-100">
                                                            Vào hàng chờ
                                                        </button>
                                                    </form>
                                                    @if($waitlistCount > 0)
                                                        <div class="small text-muted mt-2">Hiện có {{ $waitlistCount }} học viên đang chờ.</div>
                                                    @endif
                                                @elseif($requiresPaidCheckout && ! $walletEnough)
                                                    <a href="{{ route('wallet.index') }}" class="btn btn-primary btn-sm w-100">
                                                        <i class="fas fa-wallet me-1"></i>Nạp ví rồi đăng ký
                                                    </a>
                                                @else
                                                    <form action="{{ route('courses.enroll', $course) }}" method="POST" class="d-grid gap-2">
                                                        @csrf
                                                        <input type="hidden" name="class_id" value="{{ $cls->id }}">
                                                        <input type="hidden" name="payment_method" value="wallet">
                                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                                            {{ $requiresPaidCheckout ? 'Thanh toán ví và gửi yêu cầu' : 'Gửi yêu cầu đăng ký' }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted d-block mt-3">Khi lớp đầy, học viên sẽ được đưa vào hàng chờ. Khi có chỗ trống, hệ thống sẽ tự giữ chỗ 24h cho người kế tiếp để xác nhận đăng ký.</small>
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
                                            <span class="text-primary fw-bold">{{ number_format($similarCourse->final_price) }} VND</span>
                                            <a href="{{ route('courses.show', $similarCourse) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a>
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
