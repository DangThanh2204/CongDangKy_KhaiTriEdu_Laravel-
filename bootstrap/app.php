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
        //
    })->create();
