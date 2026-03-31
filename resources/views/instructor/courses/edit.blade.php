@extends('layouts.app')

@section('title', 'ChÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°nh sÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â­a khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">ChÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°nh sÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â­a khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc</h3>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('instructor.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">TiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªu ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $course->title) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Danh mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥c khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">ChÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân danh mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥c</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£ ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â§y ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â§</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description', $course->description) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ (VND)</label>
                                <input type="number" name="price" class="form-control" step="1000" min="0" value="{{ old('price', $course->price) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">GiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ khuyÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿n mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£i (nÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿u cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³)</label>
                                <input type="number" name="sale_price" class="form-control" step="1000" min="0" value="{{ old('sale_price', $course->sale_price) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link video (YouTube)</label>
                            <input type="url" name="video_url" class="form-control" value="{{ old('video_url', $course->video_url) }}" placeholder="https://www.youtube.com/watch?v=...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File PDF tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i liÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡u (tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¹y chÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân)</label>
                            <input type="file" name="pdf" class="form-control">
                            @if($course->pdf_path)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $course->pdf_path) }}" target="_blank">Xem tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡p hiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡n tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡i</a>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">BÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i kiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m tra (quiz) cÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â¡ bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n <small class="text-muted">(tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¹y chÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân)</small></label>
                            <p class="text-muted">ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âi ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â m sau khi hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc xong. BÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡n cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â qua hoÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â·c xÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a phÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â§n nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y nÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿u khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng muÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“n cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i kiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m tra.</p>
                            @php
                                $quizMaterial = $course->materials()->where('type', 'quiz')->first();
                                $quizQuestions = $quizMaterial?->metadata['questions'] ?? [];
                            @endphp
                            <div id="quiz-questions">
                                @if(count($quizQuestions) > 0)
                                    @foreach($quizQuestions as $index => $q)
                                        <div class="quiz-question mb-3 border p-3 rounded">
                                            <input type="text" name="quiz_questions[{{ $index }}][question]" class="form-control mb-2" value="{{ $q['question'] ?? '' }}" placeholder="CÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âi {{ $index + 1 }}">
                                            <input type="text" name="quiz_questions[{{ $index }}][answer]" class="form-control mb-2" value="{{ $q['answer'] ?? '' }}" placeholder="ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡n (vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ dÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥: A)">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-question">XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âi nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="add-quiz-question">ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âi</button>
                            @if(count($quizQuestions) > 0)
                                <button type="button" class="btn btn-outline-danger btn-sm mt-2 ms-2" id="remove-all-quiz">XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¥t cÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£ quiz</button>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£/nhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³m khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc (ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ cho phÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©p hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡i miÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦n phÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­)</label>
                            <input type="text" name="series_key" class="form-control" value="{{ old('series_key', $course->series_key) }}" placeholder="vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­ dÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â¥: tin-hoc-co-ban">
                            <small class="text-muted">NÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿u hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ mua khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡c cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¹ng mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y, hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ vÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â o hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc miÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦n phÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­.</small>
                        </div>

                        <!-- Classes (LÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc) -->
                        <div class="mb-3 card">
                            <div class="card-body">
                                <div id="classes-container">
                                    @foreach($course->classes as $i => $cls)
                                    <div class="class-item mb-3 border p-3 rounded" data-index="{{ $i }}">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h6 class="mb-0">LÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp hiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡n cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ #{{ $i+1 }} - {{ $cls->name }}</h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-class">XÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp</button>
                                        </div>
                                        <input type="hidden" name="classes[{{ $i }}][id]" value="{{ $cls->id }}">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">TÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp</label>
                                                <input type="text" name="classes[{{ $i }}][name]" class="form-control" value="{{ old('classes.'.$i.'.name', $cls->name) }}">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">GiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£ng viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn</label>
                                                <select name="classes[{{ $i }}][instructor_id]" class="form-select">
                                                    <option value="">-- ChÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân giÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£ng viÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªn --</option>
                                                    @foreach(\App\Models\User::whereIn('role', ['instructor'])->get(['id','fullname','email']) as $instructor)
                                                        <option value="{{ $instructor->id }}" {{ (old('classes.'.$i.'.instructor_id', $cls->instructor_id) == $instructor->id) ? 'selected' : '' }}>{{ $instructor->fullname }} ({{ $instructor->email }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">NgÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y bÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¯t ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â§u</label>
                                                <input type="date" name="classes[{{ $i }}][start_date]" class="form-control" value="{{ old('classes.'.$i.'.start_date', $cls->start_date?->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">NgÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â y kÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿t thÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºc</label>
                                                <input type="date" name="classes[{{ $i }}][end_date]" class="form-control" value="{{ old('classes.'.$i.'.end_date', $cls->end_date?->format('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <label class="form-label">LÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹ch (mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£)</label>
                                                <textarea name="classes[{{ $i }}][schedule]" class="form-control" rows="2">{{ old('classes.'.$i.'.schedule', $cls->schedule) }}</textarea>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <label class="form-label">ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â´ng tin buÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢i hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc (link/ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹a ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“iÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m)</label>
                                                <textarea name="classes[{{ $i }}][meeting_info]" class="form-control" rows="2">{{ old('classes.'.$i.'.meeting_info', $cls->meeting_info) }}</textarea>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">SÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ lÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â°ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Â£ng tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“i ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“a</label>
                                                <input type="number" name="classes[{{ $i }}][max_students]" class="form-control" value="{{ old('classes.'.$i.'.max_students', $cls->max_students) }}" min="0">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">GiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp (tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¹y chÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Ân)</label>
                                                <input type="number" name="classes[{{ $i }}][price_override]" class="form-control" value="{{ old('classes.'.$i.'.price_override', $cls->price_override) }}" min="0" step="1000">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">TrÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡ng thÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡i</label>
                                                <select name="classes[{{ $i }}][status]" class="form-select">
                                                    <option value="active" {{ (old('classes.'.$i.'.status', $cls->status) == 'active') ? 'selected' : '' }}>ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚Âang mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€¦Ã‚Â¸</option>
                                                    <option value="draft" {{ (old('classes.'.$i.'.status', $cls->status) == 'draft') ? 'selected' : '' }}>BÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n nhÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡p</option>
                                                    <option value="closed" {{ (old('classes.'.$i.'.status', $cls->status) == 'closed') ? 'selected' : '' }}>ÃƒÆ’Ã¢â‚¬Å¾Ãƒâ€šÃ‚ÂÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£ ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ng</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-class-btn">ThÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªm lÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºp mÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Âºi</button>
                            </div>
                        </div>

                        <input type="hidden" name="status" value="draft">

                        <!-- Status is managed by admin; instructor edits save as draft for admin review -->

                        <div class="alert alert-info">
                            <strong>QuÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â½ bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i kiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m tra:</strong> BÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡n cÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ thÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢ quÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â½ bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i kiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m tra chi tiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¿t hÃƒÆ’Ã¢â‚¬Â Ãƒâ€šÃ‚Â¡n tÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â¡i
                            <a href="{{ route('instructor.courses.quiz.index', $course) }}" class="alert-link">trang quÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â£n lÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â½ bÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â i kiÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€ Ã¢â‚¬â„¢m tra riÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªng</a>.
                        </div>

                        <button class="btn btn-primary">CÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­p nhÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â­t khÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³a hÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»Ãƒâ€šÃ‚Âc</button>
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
        let questionCount = quizContainer.querySelectorAll('.quiz-question').length;

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

        if (removeAllButton) {
            removeAllButton.addEventListener('click', function () {
                quizContainer.innerHTML = '';
                questionCount = 0;
                updateRemoveAllButton();
            });
        }

        function updateRemoveAllButton() {
            if (removeAllButton) {
                removeAllButton.style.display = questionCount > 0 ? 'inline-block' : 'none';
            }
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
            if (!capacityWrapper) return;
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

        updateRemoveAllButton();
        
        // Classes management for instructor edit view
        const classesContainer = document.getElementById('classes-container');
        const addClassBtn = document.getElementById('add-class-btn');
        let classIndex = classesContainer ? classesContainer.querySelectorAll('.class-item').length : 0;

        const instructorsOptions = `@foreach(\App\Models\User::whereIn('role', ['instructor'])->get(['id','fullname','email']) as $instructor)<option value="{{ $instructor->id }}">{{ $instructor->fullname }} ({{ $instructor->email }})</option>@endforeach`;

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
                        <textarea name="classes[${index}][meeting_info]" class="form-control" rows="2" placeholder="Zoom link hoÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚ÂºÃƒâ€šÃ‚Â·c ÃƒÆ’Ã¢â‚¬Å¾ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“ÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¹a chÃƒÆ’Ã‚Â¡Ãƒâ€šÃ‚Â»ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°"></textarea>
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
                const item = e.target.closest('.class-item');
                const idInput = item.querySelector('input[name$="[id]"]');
                if (idInput) {
                    const namePrefix = idInput.name.replace(/\[id\]$/, '');
                    let hidden = item.querySelector('input[name="' + namePrefix + '[_destroy]"]');
                    if (!hidden) {
                        hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = namePrefix + '[_destroy]';
                        hidden.value = '1';
                        item.appendChild(hidden);
                    } else {
                        hidden.value = '1';
                    }
                    item.style.display = 'none';
                } else {
                    item.remove();
                }
            }
        });
    });
</script>
@endpush
