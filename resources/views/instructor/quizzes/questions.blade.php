@extends('layouts.app')

@section('title', 'Manage Quiz Questions')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Questions for: {{ $quiz->title }}</h4>
                    <div>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                            <i class="fas fa-plus"></i> Add Question
                        </button>
                        <a href="{{ route('instructor.quizzes.show', $quiz) }}" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-arrow-left"></i> Back to Quiz
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($questions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Question</th>
                                        <th>Type</th>
                                        <th>Points</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($questions as $index => $question)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ Str::limit($question->question_text, 80) }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</span>
                                            </td>
                                            <td>{{ $question->points }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-question-btn"
                                                            data-bs-toggle="modal" data-bs-target="#editQuestionModal"
                                                            data-question-id="{{ $question->id }}"
                                                            data-question-text="{{ $question->question_text }}"
                                                            data-question-type="{{ $question->question_type }}"
                                                            data-options="{{ json_encode($question->options) }}"
                                                            data-correct-answer="{{ $question->correct_answer }}"
                                                            data-points="{{ $question->points }}"
                                                            data-explanation="{{ $question->explanation }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('instructor.quizzes.questions.destroy', $question) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this question?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
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
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No questions found</h5>
                            <p class="text-muted">Add your first question to get started.</p>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                                <i class="fas fa-plus"></i> Add Question
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQuestionModalLabel">Add New Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('instructor.quizzes.questions.store', $quiz) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="question_text" class="form-label">Question <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="question_type" class="form-label">Question Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="question_type" name="question_type" required>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                            <option value="short_answer">Short Answer</option>
                        </select>
                    </div>

                    <div id="options_container" class="mb-3" style="display: none;">
                        <label class="form-label">Options</label>
                        <div id="options_list">
                            <div class="input-group mb-2 option-item">
                                <span class="input-group-text">A</span>
                                <input type="text" class="form-control" name="options[]" placeholder="Option A">
                            </div>
                            <div class="input-group mb-2 option-item">
                                <span class="input-group-text">B</span>
                                <input type="text" class="form-control" name="options[]" placeholder="Option B">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add_option">Add Option</button>
                    </div>

                    <div class="mb-3">
                        <label for="correct_answer" class="form-label">Correct Answer <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="correct_answer" name="correct_answer" required>
                        <small class="form-text text-muted">For multiple choice: enter the letter (A, B, C, etc.). For true/false: enter 'true' or 'false'.</small>
                    </div>

                    <div class="mb-3">
                        <label for="points" class="form-label">Points</label>
                        <input type="number" class="form-control" id="points" name="points" value="1" min="1">
                    </div>

                    <div class="mb-3">
                        <label for="explanation" class="form-label">Explanation</label>
                        <textarea class="form-control" id="explanation" name="explanation" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editQuestionModalLabel">Edit Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="editQuestionForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_question_text" class="form-label">Question <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_question_text" name="question_text" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_question_type" class="form-label">Question Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_question_type" name="question_type" required>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                            <option value="short_answer">Short Answer</option>
                        </select>
                    </div>

                    <div id="edit_options_container" class="mb-3" style="display: none;">
                        <label class="form-label">Options</label>
                        <div id="edit_options_list"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="edit_add_option">Add Option</button>
                    </div>

                    <div class="mb-3">
                        <label for="edit_correct_answer" class="form-label">Correct Answer <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_correct_answer" name="correct_answer" required>
                        <small class="form-text text-muted">For multiple choice: enter the letter (A, B, C, etc.). For true/false: enter 'true' or 'false'.</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_points" class="form-label">Points</label>
                        <input type="number" class="form-control" id="edit_points" name="points" value="1" min="1">
                    </div>

                    <div class="mb-3">
                        <label for="edit_explanation" class="form-label">Explanation</label>
                        <textarea class="form-control" id="edit_explanation" name="explanation" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle question type change for add modal
    document.getElementById('question_type').addEventListener('change', function() {
        toggleOptionsContainer(this.value, 'options_container');
    });

    // Handle question type change for edit modal
    document.getElementById('edit_question_type').addEventListener('change', function() {
        toggleOptionsContainer(this.value, 'edit_options_container');
    });

    // Add option button for add modal
    document.getElementById('add_option').addEventListener('click', function() {
        addOption('options_list');
    });

    // Add option button for edit modal
    document.getElementById('edit_add_option').addEventListener('click', function() {
        addOption('edit_options_list');
    });

    // Edit question button click
    document.querySelectorAll('.edit-question-btn').forEach(button => {
        button.addEventListener('click', function() {
            const questionId = this.getAttribute('data-question-id');
            const questionText = this.getAttribute('data-question-text');
            const questionType = this.getAttribute('data-question-type');
            const options = JSON.parse(this.getAttribute('data-options') || '[]');
            const correctAnswer = this.getAttribute('data-correct-answer');
            const points = this.getAttribute('data-points');
            const explanation = this.getAttribute('data-explanation');

            // Update form action
            document.getElementById('editQuestionForm').action = `/instructor/quizzes/questions/${questionId}`;

            // Fill form fields
            document.getElementById('edit_question_text').value = questionText;
            document.getElementById('edit_question_type').value = questionType;
            document.getElementById('edit_correct_answer').value = correctAnswer;
            document.getElementById('edit_points').value = points;
            document.getElementById('edit_explanation').value = explanation;

            // Handle options
            toggleOptionsContainer(questionType, 'edit_options_container');
            populateOptions('edit_options_list', options);
        });
    });

    function toggleOptionsContainer(questionType, containerId) {
        const container = document.getElementById(containerId);
        if (questionType === 'multiple_choice') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

    function addOption(containerId) {
        const container = document.getElementById(containerId);
        const optionCount = container.querySelectorAll('.option-item').length;
        const letter = String.fromCharCode(65 + optionCount);

        const optionDiv = document.createElement('div');
        optionDiv.className = 'input-group mb-2 option-item';
        optionDiv.innerHTML = `
            <span class="input-group-text">${letter}</span>
            <input type="text" class="form-control" name="options[]" placeholder="Option ${letter}">
            <button type="button" class="btn btn-outline-danger remove-option" onclick="this.parentElement.remove()">×</button>
        `;

        container.appendChild(optionDiv);
    }

    function populateOptions(containerId, options) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        options.forEach((option, index) => {
            const letter = String.fromCharCode(65 + index);
            const optionDiv = document.createElement('div');
            optionDiv.className = 'input-group mb-2 option-item';
            optionDiv.innerHTML = `
                <span class="input-group-text">${letter}</span>
                <input type="text" class="form-control" name="options[]" value="${option}" placeholder="Option ${letter}">
                <button type="button" class="btn btn-outline-danger remove-option" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(optionDiv);
        });
    }
});
</script>
@endsection