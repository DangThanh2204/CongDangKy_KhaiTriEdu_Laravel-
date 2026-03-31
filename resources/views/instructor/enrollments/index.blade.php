@extends('layouts.app')

@section('title', 'Học viên - ' . ($course->title ?? ''))

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3>Học viên - {{ $course->title }}</h3>
            <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-outline-secondary">&laquo; Trở về khóa học</a>
        </div>
    </div>

    @if($enrollments->count())
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Học viên</th>
                                    <th>Email</th>
                                    <th>Trạng thái</th>
                                    <th>Đăng ký lúc</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($enrollments as $enrollment)
                                    <tr>
                                        <td>{{ $enrollment->id }}</td>
                                        <td>{{ $enrollment->user->fullname ?? $enrollment->user->username }}</td>
                                        <td>{{ $enrollment->user->email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $enrollment->status_color }}">{{ $enrollment->status_text }}</span>
                                        </td>
                                        <td>{{ $enrollment->created_at ? $enrollment->created_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td class="text-end">
                                            @if($enrollment->isPending())
                                                <form action="{{ route('instructor.courses.enrollments.approve', [$course, $enrollment]) }}" method="POST" style="display:inline-block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-success">Duyệt</button>
                                                </form>
                                                <form action="{{ route('instructor.courses.enrollments.reject', [$course, $enrollment]) }}" method="POST" style="display:inline-block" class="ms-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-warning">Từ chối</button>
                                                </form>
                                            @endif

                                            <form action="{{ route('instructor.courses.enrollments.destroy', [$course, $enrollment]) }}" method="POST" style="display:inline-block" class="ms-1">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa đăng ký này?')">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                {{ $enrollments->links() }}
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <p class="text-muted">Chưa có học viên nào đăng ký khóa học này.</p>
        </div>
    @endif
</div>
@endsection
