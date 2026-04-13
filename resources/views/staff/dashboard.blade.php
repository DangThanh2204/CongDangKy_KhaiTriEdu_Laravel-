@extends('layouts.app')

@section('title', 'Bảng điều phối nhân viên')
@section('page-class', 'page-staff-dashboard')

@push('styles')
    @vite('resources/css/pages/staff/dashboard.css')
@endpush

@section('content')
    <div class="container py-5 staff-dashboard-page">
        <section class="staff-hero">
            <div class="staff-hero-copy">
                <span class="staff-kicker">Trung tâm điều phối nhân viên</span>
                <h1>Bảng điều phối công việc staff</h1>
                <p>
                    Theo dõi hồ sơ mới, thanh toán đang chờ xử lý, lớp sắp khai giảng và những lớp cần can thiệp sớm
                    để staff phối hợp với admin, giảng viên và bộ phận tuyển sinh.
                </p>

                <div class="staff-hero-pills">
                    <span class="staff-pill">
                        <i class="fas fa-user-clock"></i>{{ number_format($stats['pending_enrollments']) }} hồ sơ chờ duyệt
                    </span>
                    <span class="staff-pill">
                        <i class="fas fa-wallet"></i>{{ number_format($stats['pending_payments']) }} thanh toán chờ xử lý
                    </span>
                    <span class="staff-pill">
                        <i class="fas fa-calendar-days"></i>{{ number_format($stats['upcoming_classes']) }} lớp sắp khai giảng
                    </span>
                </div>
            </div>

            <div class="staff-hero-summary">
                <article class="staff-summary-card">
                    <span>Nhân sự trực ca</span>
                    <strong>{{ $user->fullname ?: $user->username }}</strong>
                    <small>{{ now()->format('d/m/Y') }} · Staff workspace</small>
                </article>
                <article class="staff-summary-card">
                    <span>Khối lượng hôm nay</span>
                    <strong>{{ number_format($stats['new_enrollments_today']) }} hồ sơ mới</strong>
                    <small>{{ number_format($stats['payments_today']) }} giao dịch vừa tạo trong ngày</small>
                </article>
            </div>
        </section>

        <section class="staff-kpi-grid">
            <article class="staff-kpi-card tone-blue">
                <span class="staff-kpi-label">Hồ sơ chờ duyệt</span>
                <strong class="staff-kpi-value">{{ number_format($stats['pending_enrollments']) }}</strong>
                <small class="staff-kpi-note">Ưu tiên hồ sơ offline, giữ chỗ và hồ sơ tạo trong hôm nay.</small>
            </article>
            <article class="staff-kpi-card tone-orange">
                <span class="staff-kpi-label">Thanh toán pending</span>
                <strong class="staff-kpi-value">{{ number_format($stats['pending_payments']) }}</strong>
                <small class="staff-kpi-note">Tổng chờ xử lý: {{ number_format((float) $stats['pending_amount'], 0, ',', '.') }}đ.</small>
            </article>
            <article class="staff-kpi-card tone-teal">
                <span class="staff-kpi-label">Giữ chỗ / waitlist</span>
                <strong class="staff-kpi-value">{{ number_format($stats['seat_holds'] + $stats['waitlisted']) }}</strong>
                <small class="staff-kpi-note">{{ number_format($stats['seat_holds']) }} giữ chỗ, {{ number_format($stats['waitlisted']) }} trong hàng chờ.</small>
            </article>
            <article class="staff-kpi-card tone-slate">
                <span class="staff-kpi-label">Lớp cần lưu ý</span>
                <strong class="staff-kpi-value">{{ number_format($stats['classes_requiring_attention']) }}</strong>
                <small class="staff-kpi-note">{{ number_format($stats['online_classes']) }} online · {{ number_format($stats['offline_classes']) }} offline đang mở.</small>
            </article>
        </section>

        <section class="staff-quick-grid">
            @foreach($staffChecklist as $item)
                <article class="staff-quick-card tone-{{ $item['tone'] }}">
                    <div>
                        <span class="staff-quick-kicker">Checklist staff</span>
                        <h2>{{ $item['title'] }}</h2>
                        <p>{{ $item['copy'] }}</p>
                    </div>
                    <strong>{{ number_format($item['value']) }}</strong>
                </article>
            @endforeach
        </section>

        <div class="staff-main-grid">
            <section class="staff-panel" id="staff-enrollment-queue">
                <div class="staff-panel-head">
                    <div>
                        <span class="staff-panel-kicker">Ưu tiên xử lý</span>
                        <h2>Hồ sơ gần đây</h2>
                    </div>
                    <a href="{{ route('courses.intakes') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-calendar-check me-2"></i>Lịch khai giảng
                    </a>
                </div>

                <div class="staff-list">
                    @forelse($recentEnrollments as $enrollment)
                        @php
                            $course = $enrollment->course;
                            $class = $enrollment->courseClass;
                            $badgeClass = $enrollment->status_color === 'warning' ? 'text-dark' : '';
                        @endphp
                        <article class="staff-list-item">
                            <div class="staff-list-copy">
                                <div class="staff-list-top">
                                    <strong>{{ $enrollment->student?->fullname ?: $enrollment->student?->username ?: 'Học viên chưa xác định' }}</strong>
                                    <span class="badge bg-{{ $enrollment->status_color }} {{ $badgeClass }}">{{ $enrollment->status_text }}</span>
                                </div>
                                <div class="staff-list-title">{{ $course?->title ?? 'Khóa học chưa đồng bộ' }}</div>
                                <div class="staff-list-meta">
                                    <span><i class="fas fa-users-rectangle"></i>{{ $class?->name ?? 'Chưa gán lớp' }}</span>
                                    <span><i class="fas fa-folder-open"></i>{{ $course?->category?->name ?? 'Chưa phân loại' }}</span>
                                    <span><i class="fas fa-clock"></i>{{ optional($enrollment->created_at)->format('d/m/Y H:i') ?: 'Chưa có thời gian' }}</span>
                                </div>
                                @if($enrollment->discountCode?->code)
                                    <div class="staff-inline-note">
                                        <i class="fas fa-ticket-alt"></i>Mã giảm giá: {{ $enrollment->discountCode->code }}
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="staff-empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Hiện chưa có hồ sơ nào trong danh sách gần đây.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="staff-panel" id="staff-payment-queue">
                <div class="staff-panel-head">
                    <div>
                        <span class="staff-panel-kicker">Theo dõi thu phí</span>
                        <h2>Thanh toán đang chờ</h2>
                    </div>
                    <div class="staff-chip">{{ number_format($stats['pending_payments']) }} giao dịch</div>
                </div>

                <div class="staff-list">
                    @forelse($pendingPayments as $payment)
                        @php
                            $course = $payment->courseClass?->course;
                            $class = $payment->courseClass;
                        @endphp
                        <article class="staff-list-item">
                            <div class="staff-list-copy">
                                <div class="staff-list-top">
                                    <strong>{{ $payment->user?->fullname ?: $payment->user?->username ?: 'Tài khoản chưa xác định' }}</strong>
                                    <span class="badge bg-warning text-dark">{{ $payment->status_label }}</span>
                                </div>
                                <div class="staff-list-title">{{ number_format((float) $payment->amount, 0, ',', '.') }}đ · {{ $payment->method_label }}</div>
                                <div class="staff-list-meta">
                                    <span><i class="fas fa-hashtag"></i>{{ $payment->reference ?: ('PAY-' . $payment->id) }}</span>
                                    <span><i class="fas fa-book"></i>{{ $course?->title ?? 'Khóa học chưa đồng bộ' }}</span>
                                    <span><i class="fas fa-school"></i>{{ $class?->name ?? 'Chưa gán lớp' }}</span>
                                </div>
                                @if($payment->discountCode?->code)
                                    <div class="staff-inline-note">
                                        <i class="fas fa-percent"></i>Ưu đãi áp dụng: {{ $payment->discountCode->code }}
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="staff-empty-state">
                            <i class="fas fa-circle-check"></i>
                            <p>Không còn giao dịch pending nào cần staff theo dõi.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="staff-panel" id="staff-upcoming-classes">
                <div class="staff-panel-head">
                    <div>
                        <span class="staff-panel-kicker">Điều phối lớp học</span>
                        <h2>Lớp sắp khai giảng</h2>
                    </div>
                    <div class="staff-chip">30 ngày tới</div>
                </div>

                <div class="staff-list">
                    @forelse($upcomingClasses as $class)
                        <article class="staff-list-item">
                            <div class="staff-list-copy">
                                <div class="staff-list-top">
                                    <strong>{{ $class->name }}</strong>
                                    <span class="badge bg-{{ $class->status_badge }}">{{ $class->status_text }}</span>
                                </div>
                                <div class="staff-list-title">{{ $class->course?->title ?? 'Khóa học chưa đồng bộ' }}</div>
                                <div class="staff-list-meta">
                                    <span><i class="fas fa-calendar-day"></i>{{ optional($class->start_date)->format('d/m/Y') ?: 'Chưa có ngày' }}</span>
                                    <span><i class="fas fa-user-tie"></i>{{ $class->instructor?->fullname ?: 'Chưa phân công giảng viên' }}</span>
                                    <span><i class="fas fa-chair"></i>{{ is_null($class->remaining_slots) ? 'Không giới hạn' : ('Còn ' . $class->remaining_slots . ' chỗ') }}</span>
                                </div>
                                <div class="staff-inline-note">
                                    <i class="fas fa-clock"></i>{{ $class->schedule_text ?: 'Chưa cập nhật lịch học chi tiết' }}
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="staff-empty-state">
                            <i class="fas fa-calendar-xmark"></i>
                            <p>Chưa có lớp active nào sắp khai giảng trong 30 ngày tới.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="staff-panel">
                <div class="staff-panel-head">
                    <div>
                        <span class="staff-panel-kicker">Cần can thiệp sớm</span>
                        <h2>Điểm nóng hàng chờ / giữ chỗ</h2>
                    </div>
                    <div class="staff-chip">Ưu tiên xử lý</div>
                </div>

                <div class="staff-list">
                    @forelse($classHotspots as $class)
                        <article class="staff-list-item hotspot-item">
                            <div class="staff-list-copy">
                                <div class="staff-list-top">
                                    <strong>{{ $class->name }}</strong>
                                    @if($class->is_full)
                                        <span class="badge bg-danger">Đã kín chỗ</span>
                                    @elseif($class->waitlist_count > 0)
                                        <span class="badge bg-dark">Có waitlist</span>
                                    @else
                                        <span class="badge bg-primary">Đang giữ chỗ</span>
                                    @endif
                                </div>
                                <div class="staff-list-title">{{ $class->course?->title ?? 'Khóa học chưa đồng bộ' }}</div>
                                <div class="staff-hotspot-grid">
                                    <div>
                                        <span>Đã ghi danh</span>
                                        <strong>{{ number_format($class->current_students_count) }}</strong>
                                    </div>
                                    <div>
                                        <span>Giữ chỗ</span>
                                        <strong>{{ number_format($class->held_seats_count) }}</strong>
                                    </div>
                                    <div>
                                        <span>Waitlist</span>
                                        <strong>{{ number_format($class->waitlist_count) }}</strong>
                                    </div>
                                    <div>
                                        <span>Chỗ còn lại</span>
                                        <strong>{{ is_null($class->remaining_slots) ? '∞' : number_format($class->remaining_slots) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="staff-empty-state">
                            <i class="fas fa-shield-heart"></i>
                            <p>Hiện chưa có lớp nào bị dồn tải ở hàng chờ hoặc giữ chỗ.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
