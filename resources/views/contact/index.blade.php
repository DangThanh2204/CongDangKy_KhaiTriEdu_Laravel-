@extends('layouts.app')

@section('title', 'Liên Hệ')

@section('content')
<!-- Banner Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Liên Hệ Với Chúng Tôi</h1>
                <p class="lead mb-0">Chúng tôi luôn sẵn sàng nghe và hỗ trợ bạn với mọi câu hỏi hoặc yêu cầu</p>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-envelope fa-5x opacity-50"></i>
            </div>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section class="py-5">
    <div class="container">
        <!-- Contact Info Grid -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="contact-info-box text-center">
                    <div class="contact-icon mb-3">
                        <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-bold">Địa Chỉ</h5>
                    <p class="text-muted">
                        @if($contactAddress)
                            {{ $contactAddress }}
                        @else
                            Hà Nội, Việt Nam
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info-box text-center">
                    <div class="contact-icon mb-3">
                        <i class="fas fa-phone fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-bold">Điện Thoại</h5>
                    @if($contactPhone)
                        <p class="text-muted">
                            <a href="tel:{{ $contactPhone }}" class="text-primary text-decoration-none">
                                {{ $contactPhone }}
                            </a>
                        </p>
                    @else
                        <p class="text-muted">+84 (0) 123 456 789</p>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-info-box text-center">
                    <div class="contact-icon mb-3">
                        <i class="fas fa-envelope fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-bold">Email</h5>
                    @if($contactEmail)
                        <p class="text-muted">
                            <a href="mailto:{{ $contactEmail }}" class="text-primary text-decoration-none">
                                {{ $contactEmail }}
                            </a>
                        </p>
                    @else
                        <p class="text-muted">contact@khatriedu.com</p>
                    @endif
                </div>
            </div>
        </div>

        <hr class="my-5">

        <!-- Contact Form and Info -->
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-7 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">Gửi Thông Tin Cho Chúng Tôi</h2>
                
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5>Có lỗi xảy ra:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('contact.send') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Họ và Tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name') }}" 
                            placeholder="Nhập họ và tên" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-bold">Số Điện Thoại <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                            id="phone" name="phone" value="{{ old('phone') }}" 
                            placeholder="Nhập số điện thoại" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                            id="email" name="email" value="{{ old('email') }}" 
                            placeholder="Nhập email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="course_id" class="form-label fw-bold">Khóa Học Quan Tâm</label>
                        <select class="form-select @error('course_id') is-invalid @enderror" id="course_id" name="course_id">
                            <option value="">-- Chọn khóa học --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>
                                    {{ $course->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label fw-bold">Nội Dung Tin Nhắn</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" 
                            id="message" name="message" rows="5" 
                            placeholder="Vui lòng mô tả nhu cầu hoặc câu hỏi của bạn...">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-paper-plane me-2"></i>Gửi Thông Tin
                    </button>
                </form>
            </div>

            <!-- Side Info -->
            <div class="col-lg-5">
                <!-- Working Hours -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-clock text-primary me-2"></i>Giờ Làm Việc
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2 pb-2 border-bottom">
                                <strong>Thứ Hai - Thứ Sáu:</strong><br>
                                <span class="text-muted">08:00 - 18:00</span>
                            </li>
                            <li class="mb-2 pb-2 border-bottom">
                                <strong>Thứ Bảy:</strong><br>
                                <span class="text-muted">09:00 - 17:00</span>
                            </li>
                            <li>
                                <strong>Chủ Nhật:</strong><br>
                                <span class="text-muted">10:00 - 16:00</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-share-alt text-primary me-2"></i>Theo Dõi Chúng Tôi
                        </h5>
                        <div class="social-links-contact">
                            @if($facebookUrl)
                            <a href="{{ $facebookUrl }}" target="_blank" class="social-btn facebook" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            @endif

                            @if($twitterUrl)
                            <a href="{{ $twitterUrl }}" target="_blank" class="social-btn twitter" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            @endif

                            @if($instagramUrl)
                            <a href="{{ $instagramUrl }}" target="_blank" class="social-btn instagram" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            @endif

                            @if(!$facebookUrl && !$twitterUrl && !$instagramUrl)
                            <p class="text-muted">Sắp có thêm các liên kết mạng xã hội...</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- FAQ -->
                <div class="card mt-4 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-question-circle text-primary me-2"></i>Câu Hỏi Thường Gặp
                        </h5>
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Tôi phải có kinh nghiệm trước không?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body py-2 px-0">
                                        Không, tất cả khóa học đều được thiết kế cho người mới bắt đầu.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Giao chứng chỉ như thế nào?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body py-2 px-0">
                                        Sau khi hoàn thành khóa học, chứng chỉ sẽ được gửi ngay qua email.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Có thể hoàn tiền không?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body py-2 px-0">
                                        Có, chúng tôi cung cấp hoàn tiền 100% trong 7 ngày nếu không hài lòng.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section (Optional) -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold text-center mb-4">Vị Trí Của Chúng Tôi</h2>
        <div class="row">
            <div class="col-12">
                <iframe width="100%" height="400" style="border: none; border-radius: 12px;"
                    src="{{ $googleMapsEmbed }}"
                    title="Bản đồ vị trí {{ $siteName }}"
                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</section>

<style>
.contact-info-box {
    padding: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.contact-info-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.contact-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: rgba(44, 90, 160, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.contact-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.contact-card:hover {
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.form-control,
.form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.1);
}

.social-links-contact {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.social-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.1rem;
}

.social-btn.facebook {
    background: linear-gradient(135deg, #3b5998 0%, #1e3a8a 100%);
}

.social-btn.twitter {
    background: linear-gradient(135deg, #1da1f2 0%, #0066cc 100%);
}

.social-btn.instagram {
    background: linear-gradient(135deg, #e4405f 0%, #c13584 100%);
}

.social-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.accordion-button {
    padding: 15px 20px;
    background: white;
    border: none;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.accordion-button:not(.collapsed) {
    background: rgba(44, 90, 160, 0.05);
    color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(44, 90, 160, 0.1);
}

.accordion-body {
    padding: 15px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 5px;
}
</style>
@endsection
