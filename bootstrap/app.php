<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            require base_path('routes/admin.php');
            require base_path('routes/student.php');
            require base_path('routes/instructor.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Trợ lý chat dùng AJAX và đã có throttle 40 request/phút.
        // Bỏ CSRF cho route này để tránh 419 khi session file bị xoá
        // trên môi trường có filesystem ephemeral (vd: Render free tier).
        $middleware->validateCsrfTokens(except: [
            'assistant/chat',
        ]);

        // Đăng ký middleware aliases
        $middleware->alias([
            'admin' => App\Http\Middleware\AdminMiddleware::class,
            'student' => App\Http\Middleware\StudentMiddleware::class,
            'instructor' => App\Http\Middleware\InstructorMiddleware::class,
        ]);

        // Hoặc bạn có thể thêm vào middleware groups nếu cần
        // $middleware->web(append: [
        //     // Thêm middleware vào group web nếu cần
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render free tier wipes session files on every container restart, which
        // invalidates the CSRF token already on the page. Without this handler
        // the user sees a raw 419 "Page Expired" — particularly on the logout
        // POST since that's the form most likely to be open across a deploy.
        // Redirect them to login with a friendly message instead.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
                ], 419);
            }

            // If the user is already trying to logout, just send them home as if
            // it succeeded — the session is gone anyway.
            if ($request->is('logout') || $request->is('logout/*')) {
                try {
                    auth()->logout();
                    $request->session()->invalidate();
                } catch (\Throwable $t) {
                    // session may already be unusable; ignore.
                }

                return redirect()->route('home');
            }

            return redirect()
                ->route('login')
                ->with('warning', 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
        });
    })->create();
