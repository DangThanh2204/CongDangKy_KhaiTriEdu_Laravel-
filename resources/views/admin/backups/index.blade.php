@extends('layouts.admin')

@section('title', 'Sao lưu dữ liệu')
@section('page-title', 'Sao lưu dữ liệu')
@section('page-class', 'page-admin-backups')

@push('styles')
    @vite('resources/css/pages/admin/backups.css')
@endpush

@section('content')
    @php
        $formatBytes = function (int $bytes): string {
            if ($bytes <= 0) {
                return '0 B';
            }

            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
            $value = $bytes / (1024 ** $power);

            return number_format($value, $power === 0 ? 0 : 2) . ' ' . $units[$power];
        };

        $lastBackupAt = $stats['last_backup_at'] ? \Illuminate\Support\Carbon::parse($stats['last_backup_at']) : null;
    @endphp

    <div class="container-fluid py-4 backups-page">
        <div class="card border-0 shadow-sm backup-hero mb-4">
            <div class="card-body p-4">
                <div class="backup-hero-wrap d-flex flex-column flex-xl-row justify-content-between gap-3 align-items-xl-center">
                    <div>
                        <div class="backup-eyebrow">Admin / Security</div>
                        <h1 class="backup-title mb-2">Sao lưu và khôi phục dữ liệu</h1>
                        <p class="backup-subtitle mb-0">Tạo bản sao lưu toàn bộ cơ sở dữ liệu và file tải lên để chủ động ứng phó khi hệ thống gặp sự cố, bị tấn công hoặc cần phục hồi khẩn cấp.</p>
                    </div>

                    <form action="{{ route('admin.backups.store') }}" method="POST" class="d-flex flex-wrap gap-2">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-database me-2"></i>Tạo bản sao lưu mới
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="fw-semibold mb-2">Không thể khôi phục dữ liệu:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="backup-stat-card">
                    <div class="backup-stat-icon"><i class="fas fa-box-archive"></i></div>
                    <div class="backup-stat-number">{{ number_format($stats['total_backups']) }}</div>
                    <div class="backup-stat-label">Bản sao lưu đang lưu trên máy chủ</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="backup-stat-card">
                    <div class="backup-stat-icon"><i class="fas fa-hard-drive"></i></div>
                    <div class="backup-stat-number">{{ $formatBytes((int) $stats['total_size']) }}</div>
                    <div class="backup-stat-label">Dung lượng bản sao lưu hiện có</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="backup-stat-card">
                    <div class="backup-stat-icon"><i class="fas fa-clock-rotate-left"></i></div>
                    <div class="backup-stat-number backup-stat-date">{{ $lastBackupAt ? $lastBackupAt->format('d/m/Y H:i') : 'Chưa có' }}</div>
                    <div class="backup-stat-label">Lần sao lưu gần nhất</div>
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="backup-section-head p-4 pb-0">
                            <h2 class="backup-section-title mb-1">Danh sách bản sao lưu</h2>
                            <p class="text-muted mb-0">Mỗi file backup gồm cơ sở dữ liệu hiện tại và toàn bộ file trong <code>storage/app/public</code>. Hãy tải file về máy hoặc lưu ra nơi an toàn sau khi tạo.</p>
                        </div>

                        @if ($backups->isEmpty())
                            <div class="backup-empty-state text-center py-5 px-4">
                                <i class="fas fa-shield-halved fa-3x text-muted mb-3"></i>
                                <h5 class="mb-2">Chưa có bản sao lưu nào</h5>
                                <p class="text-muted mb-4">Bạn nên tạo bản sao lưu đầu tiên ngay bây giờ để có phương án phục hồi khi cần.</p>
                                <form action="{{ route('admin.backups.store') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-database me-2"></i>Tạo bản sao lưu đầu tiên
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 backup-table">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">File backup</th>
                                            <th>Thời gian</th>
                                            <th>Dung lượng</th>
                                            <th>Nội dung</th>
                                            <th class="text-end pe-4">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($backups as $backup)
                                            @php
                                                $manifest = $backup['manifest'] ?? [];
                                                $generatedAt = !empty($manifest['generated_at'])
                                                    ? \Illuminate\Support\Carbon::parse($manifest['generated_at'])
                                                    : \Illuminate\Support\Carbon::parse($backup['created_at']);
                                            @endphp
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="backup-file-name">{{ $backup['name'] }}</div>
                                                    <div class="backup-file-meta text-muted small">
                                                        {{ $manifest['database'] ?? config('database.default') }}
                                                        @if(!empty(data_get($manifest, 'generated_by.name')))
                                                            · bởi {{ data_get($manifest, 'generated_by.name') }}
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>{{ $generatedAt->format('d/m/Y') }}</div>
                                                    <small class="text-muted">{{ $generatedAt->format('H:i:s') }}</small>
                                                </td>
                                                <td><strong>{{ $backup['size_label'] }}</strong></td>
                                                <td>
                                                    <div class="backup-manifest-list">
                                                        <span><i class="fas fa-table me-2"></i>{{ number_format((int) ($manifest['tables_count'] ?? 0)) }} bảng dữ liệu</span>
                                                        <span><i class="fas fa-folder-open me-2"></i>{{ number_format((int) ($manifest['public_files_count'] ?? 0)) }} file upload</span>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                                        <a href="{{ route('admin.backups.download', $backup['name']) }}" class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-download me-1"></i>Tải xuống
                                                        </a>
                                                        <form action="{{ route('admin.backups.destroy', $backup['name']) }}" method="POST" class="delete-backup-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                <i class="fas fa-trash me-1"></i>Xóa
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="backup-restore-head mb-3">
                            <h2 class="backup-section-title mb-1">Khôi phục dữ liệu</h2>
                            <p class="text-muted mb-0">Tải lên file backup <code>.zip</code> đã lưu trước đó để khôi phục toàn bộ cơ sở dữ liệu và file upload.</p>
                        </div>

                        <div class="backup-danger-box mb-3">
                            <div class="fw-semibold mb-2"><i class="fas fa-triangle-exclamation me-2"></i>Cảnh báo quan trọng</div>
                            <p class="mb-0">Khôi phục sẽ ghi đè dữ liệu hiện tại. Toàn bộ bảng trong database và file trong <code>storage/app/public</code> sẽ bị thay thế theo snapshot trong file backup.</p>
                        </div>

                        <form action="{{ route('admin.backups.restore') }}" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
                            @csrf
                            <div>
                                <label for="backup_file" class="form-label">File backup</label>
                                <input type="file" name="backup_file" id="backup_file" class="form-control @error('backup_file') is-invalid @enderror" accept=".zip" required>
                                <div class="form-text">Dung lượng tối đa 200MB. Chỉ nhận file backup do hệ thống tạo.</div>
                            </div>

                            <div>
                                <label for="restore_confirmation" class="form-label">Nhập xác nhận</label>
                                <input type="text" name="restore_confirmation" id="restore_confirmation" class="form-control @error('restore_confirmation') is-invalid @enderror" placeholder="Nhập đúng: KHOI PHUC" required>
                                <div class="form-text">Bắt buộc nhập đúng cụm <strong>KHOI PHUC</strong> để tránh thao tác nhầm.</div>
                            </div>

                            <button type="submit" class="btn btn-danger w-100" data-backup-restore-submit>
                                <i class="fas fa-rotate-left me-2"></i>Khôi phục từ file backup
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="backup-section-title mb-3">Khuyến nghị an toàn</h3>
                        <ul class="backup-tip-list mb-0">
                            <li>Sao lưu trước khi sửa dữ liệu lớn, cập nhật hệ thống hoặc import dữ liệu mới.</li>
                            <li>Tải file backup về máy cá nhân hoặc lưu thêm trên ổ đĩa ngoài/cloud an toàn.</li>
                            <li>Đặt lịch tạo backup theo ngày hoặc theo tuần để luôn có snapshot gần nhất.</li>
                            <li>Chỉ dùng chức năng khôi phục khi đã chắc chắn file backup là bản sạch và đáng tin cậy.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-backup-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!confirm('Bạn có chắc muốn xóa file backup này không?')) {
                        event.preventDefault();
                    }
                });
            });

            const restoreButton = document.querySelector('[data-backup-restore-submit]');
            if (restoreButton) {
                restoreButton.form?.addEventListener('submit', function (event) {
                    if (!confirm('Khôi phục sẽ ghi đè toàn bộ dữ liệu hiện tại. Bạn có chắc muốn tiếp tục?')) {
                        event.preventDefault();
                    }
                });
            }
        });
    </script>
@endpush
