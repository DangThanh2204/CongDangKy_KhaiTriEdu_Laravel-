<?php

use App\Http\Controllers\Admin\AdminClassChangeLogController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminCourseCategoryController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminEnrollmentController;
use App\Http\Controllers\Admin\AdminNewsCategoryController;
use App\Http\Controllers\Admin\AdminNewsController;
use App\Http\Controllers\Admin\AdminQuizController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminVideoController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WalletTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    Route::prefix('admin')
        ->as('admin.')
        ->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/alerts-count', [AdminController::class, 'alertsCount'])->name('alerts.count');

            Route::prefix('users')->as('users.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::get('/export', [UserController::class, 'export'])->name('export');
                Route::get('/create', [UserController::class, 'create'])->name('create');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
                Route::put('/{user}', [UserController::class, 'update'])->name('update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
                Route::patch('/{user}/verify', [UserController::class, 'verifyUser'])->name('verify');
            });

            Route::prefix('news')->as('news.')->group(function () {
                Route::get('/', [AdminNewsController::class, 'index'])->name('index');
                Route::get('/create', [AdminNewsController::class, 'create'])->name('create');
                Route::post('/', [AdminNewsController::class, 'store'])->name('store');
                Route::get('/{news}/edit', [AdminNewsController::class, 'edit'])->name('edit');
                Route::get('/{news}/preview', [AdminNewsController::class, 'preview'])->name('preview');
                Route::put('/{news}', [AdminNewsController::class, 'update'])->name('update');
                Route::delete('/{news}', [AdminNewsController::class, 'destroy'])->name('destroy');
                Route::patch('/{news}/toggle-featured', [AdminNewsController::class, 'toggleFeatured'])->name('toggle-featured');
            });

            Route::prefix('news-categories')->as('news-categories.')->group(function () {
                Route::get('/', [AdminNewsCategoryController::class, 'index'])->name('index');
                Route::get('/create', [AdminNewsCategoryController::class, 'create'])->name('create');
                Route::post('/', [AdminNewsCategoryController::class, 'store'])->name('store');
                Route::get('/{category}/edit', [AdminNewsCategoryController::class, 'edit'])->name('edit');
                Route::put('/{category}', [AdminNewsCategoryController::class, 'update'])->name('update');
                Route::delete('/{category}', [AdminNewsCategoryController::class, 'destroy'])->name('destroy');
                Route::patch('/{category}/toggle-status', [AdminNewsCategoryController::class, 'toggleStatus'])->name('toggle-status');
            });

            Route::prefix('course-categories')->as('course-categories.')->group(function () {
                Route::get('/', [AdminCourseCategoryController::class, 'index'])->name('index');
                Route::get('/create', [AdminCourseCategoryController::class, 'create'])->name('create');
                Route::post('/', [AdminCourseCategoryController::class, 'store'])->name('store');
                Route::get('/{courseCategory}/edit', [AdminCourseCategoryController::class, 'edit'])->name('edit');
                Route::put('/{courseCategory}', [AdminCourseCategoryController::class, 'update'])->name('update');
                Route::delete('/{courseCategory}', [AdminCourseCategoryController::class, 'destroy'])->name('destroy');
                Route::patch('/{courseCategory}/toggle-status', [AdminCourseCategoryController::class, 'toggleStatus'])->name('toggle-status');
            });

            Route::prefix('courses')->as('courses.')->group(function () {
                Route::get('/', [AdminCourseController::class, 'index'])->name('index');
                Route::get('/export', [AdminCourseController::class, 'export'])->name('export');
                Route::get('/create', [AdminCourseController::class, 'create'])->name('create');
                Route::post('/', [AdminCourseController::class, 'store'])->name('store');
                Route::get('/{course}/edit', [AdminCourseController::class, 'edit'])->name('edit');
                Route::put('/{course}', [AdminCourseController::class, 'update'])->name('update');
                Route::delete('/{course}', [AdminCourseController::class, 'destroy'])->name('destroy');
                Route::patch('/{course}/toggle-featured', [AdminCourseController::class, 'toggleFeatured'])->name('toggle-featured');
                Route::patch('/{course}/toggle-popular', [AdminCourseController::class, 'togglePopular'])->name('toggle-popular');
            });

            Route::prefix('reviews')->as('reviews.')->group(function () {
                Route::get('/', [AdminReviewController::class, 'index'])->name('index');
                Route::delete('/{review}', [AdminReviewController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('classes')->as('classes.')->group(function () {
                Route::get('/', [ClassController::class, 'index'])->name('index');
                Route::get('/export', [ClassController::class, 'export'])->name('export');
                Route::get('/create', [ClassController::class, 'create'])->name('create');
                Route::post('/', [ClassController::class, 'store'])->name('store');
                Route::get('/{courseClass}/edit', [ClassController::class, 'edit'])->name('edit');
                Route::get('/{courseClass}', [ClassController::class, 'show'])->name('show');
                Route::put('/{courseClass}', [ClassController::class, 'update'])->name('update');
                Route::delete('/{courseClass}', [ClassController::class, 'destroy'])->name('destroy');
            });

            Route::get('/class-change-logs', [AdminClassChangeLogController::class, 'index'])->name('class-change-logs.index');

            Route::prefix('enrollments')->as('enrollments.')->group(function () {
                Route::get('/pending', [AdminEnrollmentController::class, 'pendingEnrollments'])->name('pending');
                Route::get('/pending/export', [AdminEnrollmentController::class, 'exportPending'])->name('pending.export');
                Route::get('/', [AdminEnrollmentController::class, 'index'])->name('index');
                Route::get('/export', [AdminEnrollmentController::class, 'export'])->name('export');
                Route::patch('/{enrollment}/approve', [AdminEnrollmentController::class, 'approveEnrollment'])->name('approve');
                Route::patch('/{enrollment}/reject', [AdminEnrollmentController::class, 'rejectEnrollment'])->name('reject');
                Route::post('/bulk-approve', [AdminEnrollmentController::class, 'bulkApprove'])->name('bulk-approve');
                Route::delete('/{enrollment}', [AdminEnrollmentController::class, 'destroy'])->name('destroy');
                Route::get('/manual-create', [AdminEnrollmentController::class, 'showManualCreate'])->name('manual-create');
                Route::post('/manual-enroll', [AdminEnrollmentController::class, 'manualEnroll'])->name('manual-enroll');
            });

            Route::prefix('payments')->as('payments.')->group(function () {
                Route::get('/', [PaymentController::class, 'index'])->name('index');
                Route::get('/export', [PaymentController::class, 'export'])->name('export');
                Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
                Route::patch('/{payment}/confirm', [PaymentController::class, 'confirm'])->name('confirm');
                Route::patch('/{payment}/fail', [PaymentController::class, 'fail'])->name('fail');
            });

            Route::prefix('backups')->as('backups.')->group(function () {
                Route::get('/', [AdminBackupController::class, 'index'])->name('index');
                Route::post('/', [AdminBackupController::class, 'store'])->name('store');
                Route::post('/restore', [AdminBackupController::class, 'restore'])->name('restore');
                Route::get('/{backup}/download', [AdminBackupController::class, 'download'])->name('download');
                Route::delete('/{backup}', [AdminBackupController::class, 'destroy'])->name('destroy');
            });

            Route::get('/system-logs', [SystemLogController::class, 'index'])->name('system-logs.index');
            Route::get('/system-logs/export', [SystemLogController::class, 'export'])->name('system-logs.export');

            Route::prefix('quizzes')->as('quizzes.')->group(function () {
                Route::get('/', [AdminQuizController::class, 'index'])->name('index');
                Route::get('/create', [AdminQuizController::class, 'create'])->name('create');
                Route::post('/', [AdminQuizController::class, 'store'])->name('store');
                Route::get('/{quiz}/edit', [AdminQuizController::class, 'edit'])->name('edit');
                Route::put('/{quiz}', [AdminQuizController::class, 'update'])->name('update');
                Route::delete('/{quiz}', [AdminQuizController::class, 'destroy'])->name('destroy');
                Route::get('/{quiz}/questions', [AdminQuizController::class, 'questions'])->name('questions');
                Route::post('/{quiz}/questions', [AdminQuizController::class, 'storeQuestion'])->name('questions.store');
                Route::put('/{quiz}/questions/{question}', [AdminQuizController::class, 'updateQuestion'])->name('questions.update');
                Route::delete('/{quiz}/questions/{question}', [AdminQuizController::class, 'destroyQuestion'])->name('questions.destroy');
                Route::get('/{quiz}/attempts', [AdminQuizController::class, 'attempts'])->name('attempts');
            });

            Route::prefix('videos')->as('videos.')->group(function () {
                Route::get('/', [AdminVideoController::class, 'index'])->name('index');
                Route::get('/create', [AdminVideoController::class, 'create'])->name('create');
                Route::post('/', [AdminVideoController::class, 'store'])->name('store');
                Route::get('/{video}/edit', [AdminVideoController::class, 'edit'])->name('edit');
                Route::put('/{video}', [AdminVideoController::class, 'update'])->name('update');
                Route::delete('/{video}', [AdminVideoController::class, 'destroy'])->name('destroy');
                Route::post('/{video}/process', [AdminVideoController::class, 'process'])->name('process');
                Route::get('/{video}/stream', [AdminVideoController::class, 'stream'])->name('stream');
            });

            Route::prefix('settings')->as('settings.')->group(function () {
                Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
                Route::put('/', [AdminSettingsController::class, 'update'])->name('update');
            });
        });

    Route::prefix('admin/wallet-transactions')
        ->as('admin.wallet-transactions.')
        ->group(function () {
            Route::get('/', [WalletTransactionController::class, 'index'])->name('index');
            Route::get('/export', [WalletTransactionController::class, 'export'])->name('export');
            Route::get('/{walletTransaction}', [WalletTransactionController::class, 'show'])->name('show');
            Route::patch('/{walletTransaction}/confirm', [WalletTransactionController::class, 'confirm'])->name('confirm');
            Route::patch('/{walletTransaction}/fail', [WalletTransactionController::class, 'fail'])->name('fail');
        });
});
