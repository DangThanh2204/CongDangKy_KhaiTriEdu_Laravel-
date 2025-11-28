<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đăng ký middleware aliases
        $middleware->alias([
            'admin' => App\Http\Middleware\AdminMiddleware::class,
            'staff' => App\Http\Middleware\StaffMiddleware::class,
            'student' => App\Http\Middleware\StudentMiddleware::class,
        ]);

        // Hoặc bạn có thể thêm vào middleware groups nếu cần
        // $middleware->web(append: [
        //     // Thêm middleware vào group web nếu cần
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();