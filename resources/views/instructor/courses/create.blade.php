@extends('layouts.app')

@section('title', 'TÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡o khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">TÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡o khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi</h3>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('instructor.courses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
    <label class="form-label">Hình thức đào tạo</label>
    <select name="learning_type" class="form-select">
        <option value="online" {{ old('learning_type', 'online') === 'online' ? 'selected' : '' }}>Online</option>
        <option value="offline" {{ old('learning_type') === 'offline' ? 'selected' : '' }}>Offline tại trung tâm</option>
    </select>
    <small class="text-muted">Khóa online sẽ được ghi danh tự động. Với offline, hãy tạo đợt học rõ lịch, giáo viên và sức chứa.</small>
</div>

                        <div class="mb-3" id="capacity_wrapper" style="display: none;">
                            <label class="form-label">SÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ lÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â°ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â£ng hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn (capacity)</label>
                            <input type="number" name="capacity" class="form-control" min="0" step="1" value="{{ old('capacity') }}" placeholder="VÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ dÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥: 50">
                            <small class="text-muted">ChÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â° ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p dÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥ng cho khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc Offline. ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ trÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ng nÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿u khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng giÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡n.</small>
                        </div>

                        <!-- Classes (LÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc) - instructor can add classes when creating course -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div id="classes-container"></div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-class-btn">
                                    <i class="fas fa-plus me-1"></i>ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi
                                </button>
                                <div class="form-text mt-2">MÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Âi lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ch riÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªng, ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹a ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“iÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m/link hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âp, sÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ lÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â°ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â£ng tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“i ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“a vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â  trÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡ng thÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡i.</div>
                            </div>
                        </div>

                        <!-- Hidden instructors template for JS -->
                        <select id="instructors-options-template" style="display:none;">
                            <option value="">-- ChÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân giÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£ng viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn --</option>
                            @foreach(
                                \App\Models\User::whereIn('role', ['instructor'])->get(['id','fullname','email'])
                                as $instructor
                            )
                                <option value="{{ $instructor->id }}">{{ $instructor->fullname }} ({{ $instructor->email }})</option>
                            @endforeach
                        </select>

                        <div class="mb-3">
                            <label class="form-label">ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡o cho hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn</label>
                            <textarea name="announcement" class="form-control" rows="3" placeholder="ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡o quan trÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âng vÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc">{{ old('announcement') }}</textarea>
                            <small class="text-muted">ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡o sÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â½ hiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢n thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ cho hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€ Ã¢â‚¬â„¢ng kÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â½</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File PDF tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i liÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡u (tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¹y chÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân)</label>
                            <input type="file" name="pdf" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">BÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i kiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m tra (quiz) cÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â¡ bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n <small class="text-muted">(tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¹y chÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân)</small></label>
                            <p class="text-muted">ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âi ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â m sau khi hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc xong. BÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡n cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â qua phÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â§n nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y nÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿u khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng muÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“n cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i kiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m tra.</p>
                            <div id="quiz-questions">
                                <!-- Quiz questions will be added here -->
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-quiz-question">ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âi</button>
                            <button type="button" class="btn btn-outline-danger btn-sm mt-2 ms-2" id="remove-all-quiz" style="display: none;">XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥t cÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£ quiz</button>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£/nhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³m khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc (ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ cho phÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©p hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡i miÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦n phÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­)</label>
                            <input type="text" name="series_key" class="form-control" value="{{ old('series_key') }}" placeholder="vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ dÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥: tin-hoc-co-ban">
                            <small class="text-muted">NÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ mua khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡c cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¹ng mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y, hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â o hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc miÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦n phÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­.</small>
                        </div>

                        <input type="hidden" name="status" value="draft">

                        <!-- Status is managed by admin; instructor submissions are saved as draft -->

                        <div class="alert alert-info">
                            <strong>LÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â°u ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â½:</strong> Sau khi tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡o khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc, bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡n cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ quÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â½ bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i kiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m tra chi tiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿t hÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â¡n tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡i trang quÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â½ bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i kiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m tra riÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªng.
                        </div>

                        <button class="btn btn-primary">LÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â°u khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quizContainer = document.getElementById('quiz-questions');
        const addButton = document.getElementById('add-quiz-question');
        const removeAllButton = document.getElementById('remove-all-quiz');
        let questionCount = 0;

        addButton.addEventListener('click', function () {
            const wrapper = document.createElement('div');
            wrapper.className = 'quiz-question mb-3 border p-3 rounded';

            const questionInput = document.createElement('input');
            questionInput.type = 'text';
            questionInput.name = `quiz_questions[${questionCount}][question]`;
            questionInput.className = 'form-control mb-2';
            questionInput.placeholder = `CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âi ${questionCount + 1}`;

            const answerInput = document.createElement('input');
            answerInput.type = 'text';
            answerInput.name = `quiz_questions[${questionCount}][answer]`;
            answerInput.className = 'form-control mb-2';
            answerInput.placeholder = 'ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡n (vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ dÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥: A)';

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-danger btn-sm remove-question';
            removeBtn.textContent = 'XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âi nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y';

            wrapper.appendChild(questionInput);
            wrapper.appendChild(answerInput);
            wrapper.appendChild(removeBtn);

            quizContainer.appendChild(wrapper);
            questionCount += 1;
            updateRemoveAllButton();
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-question')) {
                e.target.closest('.quiz-question').remove();
                questionCount -= 1;
                updateRemoveAllButton();
                renumberQuestions();
            }
        });

        removeAllButton.addEventListener('click', function () {
            quizContainer.innerHTML = '';
            questionCount = 0;
            updateRemoveAllButton();
        });

        function updateRemoveAllButton() {
            removeAllButton.style.display = questionCount > 0 ? 'inline-block' : 'none';
        }

        function renumberQuestions() {
            const questions = quizContainer.querySelectorAll('.quiz-question');
            questions.forEach((question, index) => {
                const questionInput = question.querySelector('input[name*="[question]"]');
                const answerInput = question.querySelector('input[name*="[answer]"]');
                questionInput.name = `quiz_questions[${index}][question]`;
                answerInput.name = `quiz_questions[${index}][answer]`;
                questionInput.placeholder = `CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âi ${index + 1}`;
            });
        }

        // Toggle capacity field visibility
        const learningTypeSelect = document.querySelector('select[name="learning_type"]');
        const capacityWrapper = document.getElementById('capacity_wrapper');

        function toggleCapacity() {
            if (!learningTypeSelect) return;
            if (learningTypeSelect.value === 'offline') {
                capacityWrapper.style.display = '';
            } else {
                capacityWrapper.style.display = 'none';
            }
        }

        if (learningTypeSelect) {
            learningTypeSelect.addEventListener('change', toggleCapacity);
            toggleCapacity();
        }
        // Classes management (instructor create)
        const classesContainer = document.getElementById('classes-container');
        const addClassBtn = document.getElementById('add-class-btn');
        let classIndex = 0;
        const instructorsOptions = document.getElementById('instructors-options-template') ? document.getElementById('instructors-options-template').innerHTML : '';

        function createClassForm(index) {
            const wrapper = document.createElement('div');
            wrapper.className = 'class-item mb-3 border p-3 rounded';
            wrapper.dataset.index = index;

            wrapper.innerHTML = `
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">LÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi #${index + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-class">XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp</button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">TÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp</label>
                        <input type="text" name="classes[${index}][name]" class="form-control" placeholder="VÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ dÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥: LÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp SÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ng ThÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â© 2">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">GiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£ng viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn</label>
                        <select name="classes[${index}][instructor_id]" class="form-select">
                            <option value="">-- ChÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân giÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£ng viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn --</option>
                            ${instructorsOptions}
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">NgÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¯t ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â§u</label>
                        <input type="date" name="classes[${index}][start_date]" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">NgÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y kÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿t thÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºc</label>
                        <input type="date" name="classes[${index}][end_date]" class="form-control">
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="form-label">LÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ch (mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£)</label>
                        <textarea name="classes[${index}][schedule]" class="form-control" rows="2" placeholder="VÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ dÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥: ThÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â© 2,5 - 18:00-20:00"></textarea>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="form-label">ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng tin buÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢i hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc (link/ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹a ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“iÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m)</label>
                        <textarea name="classes[${index}][meeting_info]" class="form-control" rows="2" placeholder="Zoom link hoÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â·c ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹a ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“iÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m"></textarea>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">SÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ lÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â°ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â£ng tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“i ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“a</label>
                        <input type="number" name="classes[${index}][max_students]" class="form-control" min="0">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">GiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp (tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¹y chÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân)</label>
                        <input type="number" name="classes[${index}][price_override]" class="form-control" min="0" step="1000" placeholder="NÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿u khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡c giÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">TrÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡ng thÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡i</label>
                        <select name="classes[${index}][status]" class="form-select">
                            <option value="active">ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚Âang mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€¦Ã‚Â¸</option>
                            <option value="draft">BÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n nhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p</option>
                            <option value="closed">ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ng</option>
                        </select>
                    </div>
                </div>
            `;

            return wrapper;
        }

        if (addClassBtn) {
            addClassBtn.addEventListener('click', function() {
                const form = createClassForm(classIndex);
                classesContainer.appendChild(form);
                classIndex++;
            });
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList && e.target.classList.contains('remove-class')) {
                e.target.closest('.class-item').remove();
            }
        });
    });
</script>
@endpush
