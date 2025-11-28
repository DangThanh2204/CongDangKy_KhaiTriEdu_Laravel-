<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController; 
use App\Http\Controllers\Admin\AdminNewsController;
use App\Http\Controllers\Admin\AdminNewsCategoryController;
use App\Http\Controllers\Admin\AdminCourseCategoryController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\EnrollmentController; 
use App\Http\Controllers\Admin\AdminEnrollmentController; 
use App\Http\Controllers\NewsController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/news/category/{slug}', [NewsController::class, 'category'])->name('news.category');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/verify', [AuthController::class, 'showVerify'])->name('verify');
Route::post('/verify', [AuthController::class, 'verify']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend.otp');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::prefix('courses')->group(function () {
        Route::get('/', [App\Http\Controllers\CourseController::class, 'index'])->name('courses.index');
        Route::get('/{course}', [App\Http\Controllers\CourseController::class, 'show'])->name('courses.show');
        Route::post('/{course}/enroll', [EnrollmentController::class, 'enroll'])->name('courses.enroll');
        Route::post('/{course}/unenroll', [EnrollmentController::class, 'unenroll'])->name('courses.unenroll');
    });

    Route::middleware('admin')
        ->prefix('admin')
        ->as('admin.')
        ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::prefix('users')->as('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
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
            Route::get('/{news}/preview', [AdminNewsController::class, 'preview'])->name('preview'); // Add this
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
            Route::get('/create', [AdminCourseController::class, 'create'])->name('create');
            Route::post('/', [AdminCourseController::class, 'store'])->name('store');
            Route::get('/{course}/edit', [AdminCourseController::class, 'edit'])->name('edit');
            Route::put('/{course}', [AdminCourseController::class, 'update'])->name('update');
            Route::delete('/{course}', [AdminCourseController::class, 'destroy'])->name('destroy');
            Route::patch('/{course}/toggle-featured', [AdminCourseController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::patch('/{course}/toggle-popular', [AdminCourseController::class, 'togglePopular'])->name('toggle-popular');
        });

        Route::prefix('enrollments')->as('enrollments.')->group(function () {
            Route::get('/pending', [AdminEnrollmentController::class, 'pendingEnrollments'])->name('pending');
            Route::get('/', [AdminEnrollmentController::class, 'index'])->name('index');
            Route::patch('/{enrollment}/approve', [AdminEnrollmentController::class, 'approveEnrollment'])->name('approve');
            Route::patch('/{enrollment}/reject', [AdminEnrollmentController::class, 'rejectEnrollment'])->name('reject');
            Route::post('/bulk-approve', [AdminEnrollmentController::class, 'bulkApprove'])->name('bulk-approve');
            Route::delete('/{enrollment}', [AdminEnrollmentController::class, 'destroy'])->name('destroy');
            Route::get('/manual-create', [AdminEnrollmentController::class, 'showManualCreate'])->name('manual-create');
            Route::post('/manual-enroll', [AdminEnrollmentController::class, 'manualEnroll'])->name('manual-enroll');
        });
    });

    Route::middleware('staff')->prefix('staff')->group(function () {
        Route::get('/dashboard', function () {
            return view('staff.dashboard', ['user' => auth()->user()]);
        })->name('staff.dashboard');
    });

    Route::middleware('student')->group(function () {
        Route::get('/dashboard', function () {
            $user = auth()->user();
            
            $enrollments = \App\Models\CourseEnrollment::with('course')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            $approvedCourses = $enrollments->where('status', 'approved');
            $pendingCourses = $enrollments->where('status', 'pending');
            
            return view('student.dashboard', compact('user', 'enrollments', 'approvedCourses', 'pendingCourses'));
        })->name('student.dashboard');
    });
});