@extends('layouts.app')

@section('title', '??i t?c')

@section('content')
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">??i t?c ??ng h?nh c?ng Khai Tr? Education</h1>
                <p class="lead mb-0">K?t n?i doanh nghi?p, t? ch?c ??o t?o v? c?ng ??ng h?c vi?n ?? m? r?ng c? h?i h?c t?p v? ngh? nghi?p.</p>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-handshake fa-5x opacity-50"></i>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold text-primary mb-3">
                    <i class="fas fa-building me-2"></i>M?ng l??i h?p t?c
                </h2>
                <p class="text-muted lead">
                    Khai Tr? Education h?p t?c c?ng doanh nghi?p, trung t?m ??o t?o v? chuy?n gia ?? x?y d?ng ch??ng tr?nh h?c b?m s?t th?c t?.
                </p>
                <p class="text-muted mb-0">
                    M?i ??i t?c g?p ph?n gi?p h?c vi?n ti?p c?n ki?n th?c m?i, m?i tr??ng th?c h?nh t?t h?n v? c? h?i vi?c l?m r? r?ng h?n sau khi ho?n th?nh kh?a h?c.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="partner-highlight h-100">
                    <div class="row g-3 text-center">
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">20+</h3><p class="mb-0 text-muted">??n v? ??o t?o</p></div></div>
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">50+</h3><p class="mb-0 text-muted">Chuy?n gia ??ng h?nh</p></div></div>
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">100+</h3><p class="mb-0 text-muted">C? h?i th?c chi?n</p></div></div>
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">5,000+</h3><p class="mb-0 text-muted">H?c vi?n h??ng l?i</p></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-briefcase fa-2x text-primary"></i></div><h5 class="fw-bold">Doanh nghi?p</h5><p class="text-muted small mb-0">??ng h?nh x?y d?ng k? n?ng th?c t? v? ??nh h??ng ngh? nghi?p cho h?c vi?n.</p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-school fa-2x text-primary"></i></div><h5 class="fw-bold">Trung t?m ??o t?o</h5><p class="text-muted small mb-0">C?ng ph?t tri?n ch??ng tr?nh h?c v? t?i nguy?n gi?ng d?y ch?t l??ng.</p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-user-tie fa-2x text-primary"></i></div><h5 class="fw-bold">Chuy?n gia</h5><p class="text-muted small mb-0">Chia s? kinh nghi?m, c? v?n l? tr?nh h?c t?p v? h? tr? th?c h?nh.</p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-certificate fa-2x text-primary"></i></div><h5 class="fw-bold">??n v? ch?ng nh?n</h5><p class="text-muted small mb-0">N?ng cao gi? tr? ??u ra cho h?c vi?n sau khi ho?n th?nh kh?a h?c.</p></div></div></div>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <h2 class="fw-bold text-primary mb-3"><i class="fas fa-link me-2"></i>Mu?n tr? th?nh ??i t?c?</h2>
                <p class="text-muted mb-3">N?u ??n v? c?a b?n mu?n h?p t?c c?ng Khai Tr? Education trong ??o t?o, tuy?n d?ng ho?c t? ch?c ch??ng tr?nh h?c th?c t?, ch?ng t?i lu?n s?n s?ng trao ??i.</p>
                <p class="text-muted mb-0">H?y li?n h? ?? c?ng x?y d?ng c?c ho?t ??ng mang l?i gi? tr? th?c cho h?c vi?n v? c?ng ??ng.</p>
            </div>
            <div class="col-lg-5">
                <div class="partner-cta">
                    <h5 class="fw-bold mb-3">K?t n?i v?i ch?ng t?i</h5>
                    <p class="text-muted mb-4">??i ng? Khai Tr? s? ph?n h?i v? trao ??i chi ti?t v? m? h?nh h?p t?c ph? h?p.</p>
                    <a href="{{ route('contact') }}" class="btn btn-primary btn-lg w-100 mb-3"><i class="fas fa-envelope me-2"></i>Li?n h? h?p t?c</a>
                    <a href="{{ route('about') }}" class="btn btn-outline-primary btn-lg w-100"><i class="fas fa-info-circle me-2"></i>T?m hi?u v? Khai Tr?</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.partner-highlight,
.partner-cta,
.partner-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.partner-highlight,
.partner-cta {
    padding: 30px;
    background: #fff;
}

.partner-card {
    transition: all 0.3s ease;
}

.partner-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.14);
}

.partner-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: rgba(44, 90, 160, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.mini-stat {
    padding: 18px 14px;
    border-radius: 10px;
    background: #f8f9fa;
}
</style>
@endsection