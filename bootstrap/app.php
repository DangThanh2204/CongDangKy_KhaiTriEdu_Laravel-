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
        // Dang ky middleware aliases
        $middleware->alias([
            'admin' => App\Http\Middleware\AdminMiddleware::class,
            'staff' => App\Http\Middleware\StaffMiddleware::class,
            'student' => App\Http\Middleware\StudentMiddleware::class,
            'instructor' => App\Http\Middleware\InstructorMiddleware::class,
        ]);

        // Hoac ban co the them vao middleware groups neu can
        // $middleware->web(append: [
        //     // Them middleware vao group web neu can
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
