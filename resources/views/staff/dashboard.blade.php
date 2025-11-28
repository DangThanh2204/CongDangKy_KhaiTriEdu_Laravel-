{{-- File: resources/views/staff/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Staff Dashboard</h4>
                </div>
                <div class="card-body">
                    <p>Xin chào, {{ $user->fullname }} ({{ $user->role }})</p>
                    <p>Đây là trang dành cho Nhân viên.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection