@extends('layouts.app')

@section('title', 'Lịch khai giảng')
@section('page-class', 'page-course-intakes')

@push('styles')
    @vite('resources/css/pages/courses/intakes.css')
@endpush

@section('content')
    @php
        $monthChoices = collect(range(0, 2))->map(function ($offset) {
            $date = now()->startOfMonth()->addMonths($offset);

            return [
                'value' => $date->format('Y-m'),
                'label' => 'Tháng ' . $date->format('m/Y'),
            ];
        });
    @endphp

    <section class="intakes-hero">
        <div class="container">
            <div class="intakes-hero-grid">
                <div>
                    <span class="intakes-kicker">Lịch khai giảng Khai Trí</span>
                    <h1>Tra cứu đợt học đang mở đăng ký theo ngày, tháng và số chỗ còn lại</h1>
                    <p class="intakes-lead">Trang này tập trung vào các lớp đang mở để học viên xem nhanh lịch khai giảng, học phí, hình thức học, số chỗ còn lại và đi thẳng tới trang đăng ký khóa học phù hợp.</p>
                    <div class="intakes-month-chips">
                        @foreach($monthChoices as $item)
                            <a href="{{ route('courses.intakes', array_merge(request()->except('page', 'month'), ['month' => $item['value']])) }}" class="intakes-chip {{ request('month') === $item['value'] ? 'active' : '' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="intakes-stat-grid">
                    <div class="intakes-stat-card">
                        <span class="stat-label">Đợt học đang mở</span>
                        <strong>{{ number_format($stats['open_intakes'] ?? 0) }}</strong>
                    </div>
                    <div class="intakes-stat-card">
                        <span class="stat-label">Khai giảng tháng này</span>
                        <strong>{{ number_format($stats['opening_this_month'] ?? 0) }}</strong>
                    </div>
                    <div class="intakes-stat-card">
                        <span class="stat-label">Lớp online</span>
                        <strong>{{ number_format($stats['online_count'] ?? 0) }}</strong>
                    </div>
                    <div class="intakes-stat-card">
                        <span class="stat-label">Lớp offline</span>
                        <strong>{{ number_format($stats['offline_count'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="intakes-filters-wrap">
        <div class="container">
            <form method="GET" action="{{ route('courses.intakes') }}" class="intakes-filter-card">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label for="q" class="form-label">Tìm nhanh</label>
                        <input type="text" id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Tên khóa học, đợt học, nhóm ngành...">
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="month" class="form-label">Theo tháng</label>
                        <input type="month" id="month" name="month" class="form-control" value="{{ request('month') }}">
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="category" class="form-label">Nhóm ngành</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">Tất cả</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="delivery_mode" class="form-label">Hình thức học</label>
                        <select id="delivery_mode" name="delivery_mode" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="online" {{ request('delivery_mode') === 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('delivery_mode') === 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="date_from" class="form-label">Từ ngày</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="date_to" class="form-label">Đến ngày</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="min_price" class="form-label">Học phí từ</label>
                        <input type="number" id="min_price" name="min_price" min="0" step="1000" class="form-control" value="{{ request('min_price') }}" placeholder="0">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="max_price" class="form-label">Học phí đến</label>
                        <input type="number" id="max_price" name="max_price" min="0" step="1000" class="form-control" value="{{ request('max_price') }}" placeholder="5000000">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="form-check intake-slots-check">
                            <input class="form-check-input" type="checkbox" value="1" id="has_slots" name="has_slots" {{ request()->boolean('has_slots') ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_slots">
                                Chỉ hiển thị lớp còn chỗ
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-2"></i>Lọc đợt học</button>
                        <a href="{{ route('courses.intakes') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate-left"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="intakes-results-section">
        <div class="container">
            <div class="intakes-results-head">
                <div>
                    <h2>Đợt học đang mở</h2>
                    <p class="text-muted mb-0">Hiển thị {{ number_format($intakes->total()) }} đợt học phù hợp bộ lọc hiện tại.</p>
                </div>
            </div>

            <div class="row g-4">
                @forelse($intakes as $intake)
                    @php
                        $course = $intake->course;
                        $scheduleLines = $intake->structured_schedule_lines;
                        $priceValue = (float) ($intake->listing_price ?? 0);
                        $remainingSlots = $intake->remaining_slots;
                    @endphp
                    <div class="col-xl-6">
                        <article class="intake-card h-100">
                            <div class="intake-card-top">
                                <div class="intake-card-badges">
                                    <span class="badge text-bg-primary">{{ $course->category->name ?? 'Nhóm ngành' }}</span>
                                    <span class="badge {{ $course->isOffline() ? 'text-bg-warning' : 'text-bg-success' }}">{{ $course->delivery_mode_label }}</span>
                                    @if($intake->is_full)
                                        <span class="badge text-bg-danger">Đã đủ chỗ</span>
                                    @else
                                        <span class="badge text-bg-light">{{ is_null($remainingSlots) ? 'Không giới hạn chỗ' : 'Còn ' . $remainingSlots . ' chỗ' }}</span>
                                    @endif
                                </div>
                                <div class="intake-price">
                                    @if($priceValue > 0)
                                        {{ number_format($priceValue) }}đ
                                    @else
                                        Miễn phí
                                    @endif
                                </div>
                            </div>

                            <div class="intake-card-body">
                                <div class="intake-card-copy">
                                    <span class="intake-class-name">{{ $intake->name }}</span>
                                    <h3>{{ $course->title }}</h3>
                                    <p>{{ \Illuminate\Support\Str::limit($course->short_description ?: strip_tags($course->description), 140) }}</p>
                                </div>

                                <div class="intake-meta-grid">
                                    <div>
                                        <span class="meta-label">Khai giảng</span>
                                        <strong>{{ optional($intake->start_date)->format('d/m/Y') ?: 'Đang cập nhật' }}</strong>
                                    </div>
                                    <div>
                                        <span class="meta-label">Kết thúc</span>
                                        <strong>{{ optional($intake->end_date)->format('d/m/Y') ?: 'Đang cập nhật' }}</strong>
                                    </div>
                                    <div>
                                        <span class="meta-label">Giảng viên</span>
                                        <strong>{{ $intake->instructor?->fullname ?? $intake->instructor?->username ?? 'Đang cập nhật' }}</strong>
                                    </div>
                                    <div>
                                        <span class="meta-label">Sức chứa</span>
                                        <strong>{{ $intake->max_students > 0 ? $intake->current_students_count . '/' . $intake->max_students : 'Không giới hạn' }}</strong>
                                    </div>
                                </div>

                                <div class="intake-schedule-box">
                                    <h4><i class="fas fa-calendar-week me-2"></i>Lịch học dự kiến</h4>
                                    @if(!empty($scheduleLines))
                                        <ul>
                                            @foreach($scheduleLines as $line)
                                                <li>{{ $line }}</li>
                                            @endforeach
                                        </ul>
                                    @elseif($intake->schedule)
                                        <p class="mb-0">{{ $intake->schedule }}</p>
                                    @else
                                        <p class="mb-0 text-muted">Trung tâm sẽ cập nhật lịch học chi tiết sau khi chốt lớp.</p>
                                    @endif

                                    @if($intake->meeting_info)
                                        <div class="intake-meeting-note">
                                            <i class="fas fa-location-dot me-2"></i>{{ $intake->meeting_info }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="intake-card-actions">
                                <a href="{{ route('courses.show', $course) }}#intakes" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Xem chi tiết và đăng ký
                                </a>
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-book-open me-2"></i>Thông tin khóa học
                                </a>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="intake-empty-state">
                            <i class="fas fa-calendar-xmark"></i>
                            <h3>Chưa có đợt học nào khớp bộ lọc</h3>
                            <p>Thử đổi tháng, bỏ bớt điều kiện lọc hoặc xem toàn bộ khóa học đang mở trên hệ thống.</p>
                            <a href="{{ route('courses.intakes') }}" class="btn btn-primary">Xem lại toàn bộ đợt học mở</a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($intakes->hasPages())
                <div class="intakes-pagination mt-4">
                    {{ $intakes->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection