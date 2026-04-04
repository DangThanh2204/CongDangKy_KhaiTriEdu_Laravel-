<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Student\ApplicationStatusController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::middleware('staff')
        ->prefix('staff')
        ->group(function () {
            Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');
        });

    Route::middleware('student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
        Route::get('/ho-so-cua-toi', [ApplicationStatusController::class, 'index'])->name('student.application-status');

        Route::post('/courses/{course}/enrollments/{enrollment}/change-class', [EnrollmentController::class, 'changeClass'])
            ->name('courses.enroll.change');

        Route::post('/courses/{course}/reviews', [CourseController::class, 'storeReview'])->name('courses.reviews.store');
        Route::post('/courses/{course}/reviews/{review}/reply', [CourseController::class, 'replyReview'])->name('courses.reviews.reply');
        Route::post('/courses/{course}/materials/{material}/complete', [CourseController::class, 'completeMaterial'])->name('courses.materials.complete');
        Route::post('/courses/{course}/materials/{material}/quiz', [CourseController::class, 'submitMaterialQuiz'])->name('courses.materials.quiz.submit');
        Route::get('/courses/{course}/certificate', [CourseController::class, 'certificate'])->name('courses.certificate');

        Route::prefix('quizzes')->group(function () {
            Route::get('/{quiz}/take', [QuizController::class, 'take'])->name('quizzes.take');
            Route::post('/{quiz}/start', [QuizController::class, 'start'])->name('quizzes.start');
            Route::post('/attempt/{attempt}/answer', [QuizController::class, 'saveAnswer'])->name('quizzes.save-answer');
            Route::post('/attempt/{attempt}/complete', [QuizController::class, 'complete'])->name('quizzes.complete');
            Route::get('/attempt/{attempt}/result', [QuizController::class, 'result'])->name('quizzes.result');
        });
    });
});