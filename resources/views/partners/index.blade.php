@extends('layouts.app')

@section('title', 'Doi Tac')

@section('content')
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">&#272;&#7889;i T&#225;c &#272;&#7891;ng H&#224;nh C&#249;ng Khai Tr&#237; Education</h1>
                <p class="lead mb-0">Ket noi doanh nghiep, to chuc dao tao va cong dong hoc vien de mo rong co hoi hoc tap va nghe nghiep.</p>
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
                    <i class="fas fa-building me-2"></i>Mang luoi hop tac
                </h2>
                <p class="text-muted lead">
                    Khai Tri Education hop tac cung doanh nghiep, trung tam dao tao va chuyen gia de xay dung chuong trinh hoc bam sat thuc te.
                </p>
                <p class="text-muted mb-0">
                    Moi doi tac gop phan giup hoc vien tiep can kien thuc moi, moi truong thuc hanh tot hon va co hoi viec lam ro rang hon sau khi hoan thanh khoa hoc.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="partner-highlight h-100">
                    <div class="row g-3 text-center">
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">20+</h3><p class="mb-0 text-muted">Don vi dao tao</p></div></div>
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">50+</h3><p class="mb-0 text-muted">Chuyen gia dong hanh</p></div></div>
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">100+</h3><p class="mb-0 text-muted">Co hoi thuc chien</p></div></div>
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">5,000+</h3><p class="mb-0 text-muted">Hoc vien huong loi</p></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-briefcase fa-2x text-primary"></i></div><h5 class="fw-bold">Doanh nghiep</h5><p class="text-muted small mb-0">Dong hanh xay dung ky nang thuc te va dinh huong nghe nghiep cho hoc vien.</p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-school fa-2x text-primary"></i></div><h5 class="fw-bold">Trung tam dao tao</h5><p class="text-muted small mb-0">Cung phat trien chuong trinh hoc va tai nguyen giang day chat luong.</p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-user-tie fa-2x text-primary"></i></div><h5 class="fw-bold">Chuyen gia</h5><p class="text-muted small mb-0">Chia se kinh nghiem, co van lo trinh hoc tap va ho tro thuc hanh.</p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-certificate fa-2x text-primary"></i></div><h5 class="fw-bold">Don vi chung nhan</h5><p class="text-muted small mb-0">Nang cao gia tri dau ra cho hoc vien sau khi hoan thanh khoa hoc.</p></div></div></div>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <h2 class="fw-bold text-primary mb-3"><i class="fas fa-link me-2"></i>Muon tro thanh doi tac?</h2>
                <p class="text-muted mb-3">Neu don vi cua ban muon hop tac cung Khai Tri Education trong dao tao, tuyen dung hoac to chuc chuong trinh hoc thuc te, chung toi luon san sang trao doi.</p>
                <p class="text-muted mb-0">Hay lien he de cung xay dung cac hoat dong mang lai gia tri thuc cho hoc vien va cong dong.</p>
            </div>
            <div class="col-lg-5">
                <div class="partner-cta">
                    <h5 class="fw-bold mb-3">Ket noi voi chung toi</h5>
                    <p class="text-muted mb-4">Doi ngu Khai Tri se phan hoi va trao doi chi tiet ve mo hinh hop tac phu hop.</p>
                    <a href="{{ route('contact') }}" class="btn btn-primary btn-lg w-100 mb-3"><i class="fas fa-envelope me-2"></i>Lien he hop tac</a>
                    <a href="{{ route('about') }}" class="btn btn-outline-primary btn-lg w-100"><i class="fas fa-info-circle me-2"></i>Tim hieu ve Khai Tri</a>
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