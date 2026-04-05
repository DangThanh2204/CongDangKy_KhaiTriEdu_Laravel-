<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseImageController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::view('/partners', 'partners.index')->name('partners');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

Route::get('/news', [App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [App\Http\Controllers\NewsController::class, 'show'])->name('news.show');
Route::get('/news/category/{slug}', [App\Http\Controllers\NewsController::class, 'category'])->name('news.category');

Route::prefix('/assistant')->name('assistant.')->middleware('throttle:40,1')->group(function () {
    Route::get('/history', [AssistantController::class, 'history'])->name('history');
    Route::post('/chat', [AssistantController::class, 'chat'])->name('chat');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordOtp'])->name('password.send-otp');
    Route::get('/forgot-password/reset', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
    Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');
});

Route::get('/verify', [AuthController::class, 'showVerify'])->name('verify');
Route::post('/verify', [AuthController::class, 'verify']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend.otp');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('/auth/facebook', [SocialAuthController::class, 'redirectToFacebook'])->name('auth.facebook');
    Route::get('/auth/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback'])->name('auth.facebook.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout/browser-close', [AuthController::class, 'logoutOnBrowserClose'])->name('logout.browser-close');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/lich-khai-giang', [CourseController::class, 'intakes'])->name('courses.intakes');
Route::get('/courses/{course}/image/{type}', [CourseImageController::class, 'show'])->name('courses.image');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/payments/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payments.vnpay.return');
Route::get('/payments/vnpay/ipn', [PaymentController::class, 'vnpayIpn'])->name('payments.vnpay.ipn');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password.form');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'visit'])->name('notifications.visit');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'topUp'])->name('wallet.topup');
    Route::post('/wallet/confirm-qr', [WalletController::class, 'confirmQr'])->name('wallet.confirm-qr');
    Route::post('/wallet/sync', [WalletController::class, 'syncBalance'])->name('wallet.sync');
    Route::get('/wallet/{walletTransaction}/vnpay', [WalletController::class, 'redirectToVnpay'])->name('wallet.vnpay.redirect');

    Route::get('/payments/{payment}/vnpay', [PaymentController::class, 'redirectToVnpay'])->name('payments.vnpay.redirect');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/documents/enrollments/{enrollment}/registration-form', [DocumentController::class, 'registrationForm'])->name('documents.registration-form');
    Route::get('/documents/payments/{payment}/receipt', [DocumentController::class, 'paymentReceipt'])->name('documents.payment-receipt');

    Route::prefix('courses')->group(function () {
        Route::post('/{course}/enroll', [EnrollmentController::class, 'enroll'])->name('courses.enroll');
        Route::post('/{course}/confirm-seat-hold', [EnrollmentController::class, 'confirmSeatHold'])->name('courses.confirm-seat-hold');
        Route::post('/{course}/unenroll', [EnrollmentController::class, 'unenroll'])->name('courses.unenroll');
        Route::delete('/{course}/unenroll', [EnrollmentController::class, 'unenroll']);
        Route::get('/{course}/learn', [CourseController::class, 'learn'])->name('courses.learn');
    });
});
