<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!(auth()->user()->isStaff() || auth()->user()->isAdmin())) {
            abort(403, 'Bạn không có quyền truy cập trang nhân viên.');
        }

        return $next($request);
    }
}