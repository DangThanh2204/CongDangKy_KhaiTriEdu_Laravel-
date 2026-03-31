<?php

use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\DashboardController;
use App\Http\Controllers\Instructor\EnrollmentController;
use App\Http\Controllers\Instructor\QuizController;
use App\Http\Controllers\Instructor\VideoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'instructor'])
    ->prefix('instructor')
    ->as('instructor.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('courses', CourseController::class)->except(['show', 'destroy']);
        Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');

        Route::prefix('courses/{course}/materials')->name('courses.materials.')->group(function () {
            Route::get('/', [CourseController::class, 'materialsIndex'])->name('index');
            Route::post('/', [CourseController::class, 'materialsStore'])->name('store');
            Route::patch('/', [CourseController::class, 'materialsUpdateOrder'])->name('update-order');
        });

        Route::prefix('courses/{course}/quiz')->name('courses.quiz.')->group(function () {
            Route::get('/', [CourseController::class, 'quizIndex'])->name('index');
            Route::get('/create', [CourseController::class, 'quizCreate'])->name('create');
            Route::post('/', [CourseController::class, 'quizStore'])->name('store');
            Route::get('/{material}/edit', [CourseController::class, 'quizEdit'])->name('edit');
            Route::put('/{material}', [CourseController::class, 'quizUpdate'])->name('update');
            Route::delete('/{material}', [CourseController::class, 'quizDestroy'])->name('destroy');
        });

        Route::prefix('courses/{course}/enrollments')->name('courses.enrollments.')->group(function () {
            Route::get('/', [EnrollmentController::class, 'index'])->name('index');
            Route::patch('/{enrollment}/approve', [EnrollmentController::class, 'approve'])->name('approve');
            Route::patch('/{enrollment}/reject', [EnrollmentController::class, 'reject'])->name('reject');
            Route::delete('/{enrollment}', [EnrollmentController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('quizzes')->name('quizzes.')->group(function () {
            Route::get('/', [QuizController::class, 'index'])->name('index');
            Route::get('/create', [QuizController::class, 'create'])->name('create');
            Route::post('/', [QuizController::class, 'store'])->name('store');
            Route::get('/{quiz}', [QuizController::class, 'show'])->name('show');
            Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->name('edit');
            Route::put('/{quiz}', [QuizController::class, 'update'])->name('update');
            Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy');
            Route::get('/{quiz}/questions', [QuizController::class, 'questions'])->name('questions');
            Route::post('/{quiz}/questions', [QuizController::class, 'storeQuestion'])->name('questions.store');
            Route::put('/questions/{question}', [QuizController::class, 'updateQuestion'])->name('questions.update');
            Route::delete('/questions/{question}', [QuizController::class, 'destroyQuestion'])->name('questions.destroy');
        });

        Route::prefix('videos')->name('videos.')->group(function () {
            Route::get('/', [VideoController::class, 'index'])->name('index');
            Route::get('/create', [VideoController::class, 'create'])->name('create');
            Route::post('/', [VideoController::class, 'store'])->name('store');
            Route::get('/{video}', [VideoController::class, 'show'])->name('show');
            Route::get('/{video}/edit', [VideoController::class, 'edit'])->name('edit');
            Route::put('/{video}', [VideoController::class, 'update'])->name('update');
            Route::delete('/{video}', [VideoController::class, 'destroy'])->name('destroy');
        });
    });
