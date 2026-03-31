<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstructorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->isInstructor()) {
            abort(403, 'Chỉ dành cho giảng viên.');
        }

        return $next($request);
    }
}
