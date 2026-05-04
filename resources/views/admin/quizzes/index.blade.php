@extends('layouts.admin')

@section('page-title', 'Quản lý Quiz')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Danh sách Quiz</h4>
                    <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo Quiz mới
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tiêu đề</th>
                                    <th>Khóa học</th>
                                    <th>Loại</th>
                                    <th>Trạng thái</th>
                                    <th>Câu hỏi</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quizzes as $quiz)
                                <tr>
                                    <td>{{ $quiz->id }}</td>
                                    <td>{{ $quiz->title }}</td>
                                    <td>{{ $quiz->course->title ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $quiz->type === 'exam' ? 'danger' : 'info' }}">
                                            {{ ucfirst(str_replace('_', ' ', $quiz->type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $quiz->is_active ? 'success' : 'secondary' }}">
                                            {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $quiz->questions->count() }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.quizzes.questions', $quiz) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-list"></i> Câu hỏi
                                            </a>
                                            <a href="{{ route('admin.quizzes.attempts', $quiz) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-chart-bar"></i> Kết quả
                                            </a>
                                            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa quiz này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Chưa có quiz nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $quizzes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection