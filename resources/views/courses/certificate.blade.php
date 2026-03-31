@extends('layouts.app')

@section('title', 'Chung chi ' . $course->title)

@section('content')
<div class="container py-5">
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-body p-5" style="background: linear-gradient(135deg, #f8fbff 0%, #eef7f2 100%);">
            <div class="text-center mb-4">
                <div class="mb-3"><i class="fas fa-award fa-3x text-warning"></i></div>
                <h1 class="fw-bold mb-2">Chung Nhan Hoan Thanh</h1>
                <p class="text-muted mb-0">Khai Tri Edu xac nhan hoc vien da hoan thanh khoa hoc</p>
            </div>

            <div class="text-center py-4">
                <h2 class="fw-semibold mb-3">{{ $enrollment->user->fullname ?? $enrollment->user->username }}</h2>
                <p class="fs-5 mb-2">Da hoan thanh khoa hoc</p>
                <h3 class="text-primary fw-bold mb-4">{{ $course->title }}</h3>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100 bg-white">
                        <div class="text-muted small mb-1">Ma chung chi</div>
                        <div class="fw-semibold">{{ $certificate->certificate_no }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100 bg-white">
                        <div class="text-muted small mb-1">Ngay cap</div>
                        <div class="fw-semibold">{{ $certificate->issued_at->format('d/m/Y') }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100 bg-white">
                        <div class="text-muted small mb-1">Hoc vien</div>
                        <div class="fw-semibold">{{ $enrollment->user->email }}</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="{{ route('courses.learn', $course) }}" class="btn btn-outline-primary">
                    <i class="fas fa-book-open me-2"></i>Quay lai trang hoc
                </a>
                <button type="button" class="btn btn-success" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>In chung chi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
