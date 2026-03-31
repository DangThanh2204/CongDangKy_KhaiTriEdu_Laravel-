@extends('layouts.app')

@section('title', 'Manage Quizzes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">My Quizzes</h4>
                    <a href="{{ route('instructor.quizzes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Quiz
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($quizzes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Questions</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quizzes as $quiz)
                                        <tr>
                                            <td>{{ $quiz->title }}</td>
                                            <td>{{ Str::limit($quiz->description, 50) }}</td>
                                            <td>{{ $quiz->questions->count() }}</td>
                                            <td>
                                                <span class="badge bg-{{ $quiz->is_active ? 'success' : 'secondary' }}">
                                                    {{ $quiz->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>{{ $quiz->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('instructor.quizzes.questions', $quiz) }}" class="btn btn-sm btn-outline-warning" title="Manage Questions">
                                                        <i class="fas fa-list"></i>
                                                    </a>
                                                    <a href="{{ route('instructor.quizzes.edit', $quiz) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('instructor.quizzes.destroy', $quiz) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this quiz?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $quizzes->links() }}
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No quizzes found</h5>
                            <p class="text-muted">Create your first quiz to get started.</p>
                            <a href="{{ route('instructor.quizzes.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Quiz
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection