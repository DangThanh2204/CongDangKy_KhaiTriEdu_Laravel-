@extends('layouts.app')

@section('title', 'Thông báo của tôi')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h1 class="fw-bold mb-1">Thông báo của tôi</h1>
                    <p class="text-muted mb-0">Theo dõi nhanh các cập nhật mới về đăng ký, lớp học và ví của bạn.</p>
                </div>

                @if(( ?? 0) > 0)
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-check-double me-2"></i>Đánh dấu tất cả đã đọc
                        </button>
                    </form>
                @endif
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    @forelse($notifications as $notification)
                        @php($data = $notification->data ?? [])
                        <a href="{{ route('notifications.visit', $notification->id) }}"
                           class="d-flex gap-3 p-4 text-decoration-none border-bottom notification-page-item {{ is_null($notification->read_at) ? 'notification-page-item-unread' : '' }}">
                            <div class="notification-page-icon bg-{{ $data['variant'] ?? 'primary' }}">
                                <i class="{{ $data['icon'] ?? 'fas fa-bell' }}"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start mb-1">
                                    <h6 class="mb-0 text-dark fw-semibold">{{ $data['title'] ?? 'Thông báo mới' }}</h6>
                                    <small class="text-muted">{{ optional($notification->created_at)->diffForHumans() }}</small>
                                </div>
                                <p class="text-muted mb-2">{{ $data['message'] ?? '' }}</p>
                                @if(!empty($data['action_text']))
                                    <span class="badge rounded-pill text-bg-light border">{{ $data['action_text'] }}</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-bell-slash fa-2x mb-3"></i>
                            <p class="mb-0">Bạn chưa có thông báo nào.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            @if(method_exists($notifications, 'links'))
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection