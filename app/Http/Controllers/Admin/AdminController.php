<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\SystemLog;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\FireflyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function __construct(protected FireflyService $firefly)
    {
    }

    /**
     * Hiển thị dashboard admin.
     */
    public function dashboard()
    {
        $today = now();
        $currentMonthStart = $today->copy()->startOfMonth();
        $currentMonthEnd = $today->copy()->endOfMonth();

        $stats = [
            'total_users' => 0,
            'total_courses' => 0,
            'published_courses' => 0,
            'total_enrollments' => 0,
            'total_admins' => 0,
            'total_staff' => 0,
            'total_instructors' => 0,
            'total_students' => 0,
            'verified_users' => 0,
            'unverified_users' => 0,
            'today_registrations' => 0,
            'weekly_registrations' => 0,
            'today_enrollments' => 0,
            'monthly_enrollments' => 0,
            'today_course_openings' => 0,
            'monthly_course_openings' => 0,
            'pending_enrollments' => 0,
            'approved_enrollments' => 0,
            'completed_enrollments' => 0,
            'full_classes' => 0,
            'upcoming_classes' => 0,
            'online_classes' => 0,
            'offline_classes' => 0,
            'online_ratio' => 0,
            'offline_ratio' => 0,
            'pending_ratio' => 0,
            'approved_ratio' => 0,
            'wallet_revenue' => 0,
            'vnpay_revenue' => 0,
            'total_revenue' => 0,
        ];

        $usersByRole = collect();
        $recentRegistrations = collect();
        $recentUsers = collect();
        $monthlyRegistrations = collect();

        $dailyEnrollmentTrend = $this->buildDailyTrend(CourseEnrollment::class, 14);
        $dailyCourseTrend = $this->buildDailyTrend(Course::class, 14);
        $monthlyEnrollmentTrend = $this->buildMonthlyTrend(CourseEnrollment::class, 12);
        $monthlyCourseTrend = $this->buildMonthlyTrend(Course::class, 12);
        $securitySnapshot = $this->securityAlertSnapshot();
        $blockchainSummary = $this->buildBlockchainSummary();

        try {
            $userTable = (new User())->getTable();
            $courseTable = (new Course())->getTable();
            $classTable = (new CourseClass())->getTable();
            $enrollmentTable = (new CourseEnrollment())->getTable();
            $paymentTable = (new Payment())->getTable();
            $walletTransactionTable = (new WalletTransaction())->getTable();
            $hasVerifiedColumn = Schema::hasTable($userTable) && Schema::hasColumn($userTable, 'is_verified');

            if (Schema::hasTable($userTable)) {
                $stats['total_users'] = User::count();
                $stats['total_admins'] = User::where('role', 'admin')->count();
                $stats['total_staff'] = User::where('role', 'staff')->count();
                $stats['total_instructors'] = User::where('role', 'instructor')->count();
                $stats['total_students'] = User::where('role', 'student')->count();
                $stats['today_registrations'] = User::whereDate('created_at', $today)->count();
                $stats['weekly_registrations'] = User::whereBetween('created_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])->count();
                $stats['verified_users'] = $hasVerifiedColumn ? User::where('is_verified', true)->count() : 0;
                $stats['unverified_users'] = $hasVerifiedColumn ? User::where('is_verified', false)->count() : 0;

                $usersByRole = User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get()
                    ->pluck('count', 'role');

                $recentRegistrations = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->where('created_at', '>=', $today->copy()->subDays(7))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                $recentUsers = User::query()
                    ->latest('created_at')
                    ->take(6)
                    ->get();

                $monthlyRegistrations = User::select(
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->whereYear('created_at', date('Y'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->pluck('count', 'month');
            }

            if (Schema::hasTable($courseTable)) {
                $stats['total_courses'] = Course::count();
                $stats['published_courses'] = Schema::hasColumn($courseTable, 'status')
                    ? Course::published()->count()
                    : Course::count();
                $stats['today_course_openings'] = Course::whereDate('created_at', $today)->count();
                $stats['monthly_course_openings'] = Course::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count();
            }

            if (Schema::hasTable($enrollmentTable)) {
                $stats['total_enrollments'] = CourseEnrollment::count();
                $stats['today_enrollments'] = CourseEnrollment::whereDate('created_at', $today)->count();
                $stats['monthly_enrollments'] = CourseEnrollment::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count();
                $stats['pending_enrollments'] = CourseEnrollment::pending()->count();
                $stats['approved_enrollments'] = CourseEnrollment::approved()->count();
                $stats['completed_enrollments'] = CourseEnrollment::completed()->count();
            }

            if (Schema::hasTable($classTable)) {
                $hasClassStatusColumn = Schema::hasColumn($classTable, 'status');
                $hasClassStartDateColumn = Schema::hasColumn($classTable, 'start_date');
                $hasWaitlistColumns = Schema::hasTable($enrollmentTable)
                    && Schema::hasColumn($enrollmentTable, 'waitlist_promoted_at')
                    && Schema::hasColumn($enrollmentTable, 'seat_hold_expires_at');

                $classes = CourseClass::query()
                    ->with('course:id,delivery_mode')
                    ->get();

                $approvedCounts = collect();
                $heldSeatCounts = collect();

                if (Schema::hasTable($enrollmentTable)) {
                    $approvedCounts = CourseEnrollment::query()
                        ->selectRaw('class_id, COUNT(*) as aggregate_count')
                        ->whereIn('status', ['approved', 'completed'])
                        ->groupBy('class_id')
                        ->pluck('aggregate_count', 'class_id');

                    if ($hasWaitlistColumns) {
                        $heldSeatCounts = CourseEnrollment::query()
                            ->selectRaw('class_id, COUNT(*) as aggregate_count')
                            ->where('status', 'pending')
                            ->whereNotNull('waitlist_promoted_at')
                            ->whereNotNull('seat_hold_expires_at')
                            ->where('seat_hold_expires_at', '>', now())
                            ->groupBy('class_id')
                            ->pluck('aggregate_count', 'class_id');
                    }
                }

                $activeClasses = $hasClassStatusColumn
                    ? $classes->where('status', 'active')->values()
                    : $classes->values();
                $classPool = $activeClasses->isNotEmpty() ? $activeClasses : $classes;

                $stats['full_classes'] = $classPool->filter(function (CourseClass $class) use ($approvedCounts, $heldSeatCounts) {
                    $maxStudents = (int) ($class->max_students ?? 0);

                    if ($maxStudents <= 0) {
                        return false;
                    }

                    $approved = (int) ($approvedCounts[$class->id] ?? 0);
                    $held = (int) ($heldSeatCounts[$class->id] ?? 0);

                    return ($approved + $held) >= $maxStudents;
                })->count();

                $stats['upcoming_classes'] = $hasClassStartDateColumn
                    ? $classPool->filter(function (CourseClass $class) use ($today) {
                        return $class->start_date
                            && $class->start_date->between($today->copy()->startOfDay(), $today->copy()->addDays(30)->endOfDay());
                    })->count()
                    : 0;

                $stats['online_classes'] = $classPool->filter(function (CourseClass $class) {
                    return ($class->course?->delivery_mode ?? 'offline') === 'online';
                })->count();
                $stats['offline_classes'] = max($classPool->count() - $stats['online_classes'], 0);
            }

            if (Schema::hasTable($paymentTable)
                && Schema::hasColumn($paymentTable, 'status')
                && Schema::hasColumn($paymentTable, 'method')
                && Schema::hasColumn($paymentTable, 'amount')) {
                $completedPayments = Payment::query()->where('status', 'completed');
                $stats['wallet_revenue'] = (float) (clone $completedPayments)->where('method', 'wallet')->sum('amount');
                $stats['vnpay_revenue'] = (float) (clone $completedPayments)->where('method', 'vnpay')->sum('amount');
            }

            if (Schema::hasTable($walletTransactionTable)
                && Schema::hasColumn($walletTransactionTable, 'type')
                && Schema::hasColumn($walletTransactionTable, 'status')
                && Schema::hasColumn($walletTransactionTable, 'amount')) {
                $stats['vnpay_revenue'] += (float) WalletTransaction::query()
                    ->where('type', 'deposit')
                    ->where('status', 'completed')
                    ->get()
                    ->filter(function (WalletTransaction $transaction) {
                        return $transaction->paymentMethod() === WalletTransaction::VNPAY_METHOD;
                    })
                    ->sum(function (WalletTransaction $transaction) {
                        return (float) $transaction->amount;
                    });
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $classModeTotal = max($stats['online_classes'] + $stats['offline_classes'], 1);
        $decisionTotal = max($stats['pending_enrollments'] + $stats['approved_enrollments'], 1);
        $stats['online_ratio'] = round(($stats['online_classes'] / $classModeTotal) * 100, 1);
        $stats['offline_ratio'] = round(($stats['offline_classes'] / $classModeTotal) * 100, 1);
        $stats['pending_ratio'] = round(($stats['pending_enrollments'] / $decisionTotal) * 100, 1);
        $stats['approved_ratio'] = round(($stats['approved_enrollments'] / $decisionTotal) * 100, 1);
        $stats['total_revenue'] = $stats['wallet_revenue'] + $stats['vnpay_revenue'];

        $securityAlertsCount = $securitySnapshot['count'];
        $latestSecurityAlert = $securitySnapshot['latest'];

        $dashboardTrend = [
            'daily' => [
                'labels' => $dailyEnrollmentTrend['labels'],
                'enrollments' => $dailyEnrollmentTrend['data'],
                'courses' => $dailyCourseTrend['data'],
                'meta' => [
                    'range_label' => $dailyEnrollmentTrend['range_label'],
                    'enrollments_total' => $dailyEnrollmentTrend['total'],
                    'courses_total' => $dailyCourseTrend['total'],
                ],
            ],
            'monthly' => [
                'labels' => $monthlyEnrollmentTrend['labels'],
                'enrollments' => $monthlyEnrollmentTrend['data'],
                'courses' => $monthlyCourseTrend['data'],
                'meta' => [
                    'range_label' => $monthlyEnrollmentTrend['range_label'],
                    'enrollments_total' => $monthlyEnrollmentTrend['total'],
                    'courses_total' => $monthlyCourseTrend['total'],
                ],
            ],
        ];

        return view('admin.dashboard', compact(
            'stats',
            'usersByRole',
            'recentRegistrations',
            'recentUsers',
            'monthlyRegistrations',
            'dashboardTrend',
            'securityAlertsCount',
            'latestSecurityAlert',
            'blockchainSummary'
        ));
    }

    /**
     * Lấy dữ liệu cho biểu đồ (API).
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'registrations');

        switch ($type) {
            case 'role_distribution':
                $data = User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get();

                return response()->json([
                    'labels' => $data->pluck('role'),
                    'data' => $data->pluck('count'),
                    'colors' => ['#2c5aa0', '#28a745', '#ff6b35', '#6f42c1', '#20c997'],
                ]);

            case 'monthly_registrations':
                $currentYear = date('Y');
                $data = User::select(
                        DB::raw('MONTH(created_at) as month'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->whereYear('created_at', $currentYear)
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                $monthlyData = [];
                for ($month = 1; $month <= 12; $month++) {
                    $monthData = $data->where('month', $month)->first();
                    $monthlyData[] = $monthData ? $monthData->count : 0;
                }

                return response()->json([
                    'labels' => ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
                    'data' => $monthlyData,
                ]);

            case 'weekly_activity':
                $data = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->where('created_at', '>=', now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                return response()->json([
                    'labels' => $data->pluck('date'),
                    'data' => $data->pluck('count'),
                ]);
        }

        return response()->json(['error' => 'Invalid chart type'], 400);
    }

    /**
     * Hiển thị profile admin.
     */
    public function profile()
    {
        $user = auth()->user();

        return view('admin.profile', compact('user'));
    }

    /**
     * Cập nhật profile admin.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'fullname' => $request->fullname,
            'email' => $request->email,
        ];

        if ($request->filled('current_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
            }

            $data['password'] = Hash::make($request->new_password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Hiển thị hệ thống logs đơn giản.
     */
    public function systemLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logFile)) {
            $logs = $this->readLogFile($logFile, 100);
        }

        return view('admin.system.logs', compact('logs'));
    }

    /**
     * Return count of recent security alerts (JSON) for dashboard polling.
     */
    public function alertsCount()
    {
        $count = $this->securityAlertSnapshot()['count'];

        return response()->json(['count' => $count]);
    }

    private function securityAlertSnapshot(): array
    {
        try {
            if (! Schema::hasTable((new SystemLog())->getTable())) {
                return [
                    'count' => 0,
                    'latest' => null,
                ];
            }

            return [
                'count' => SystemLog::where('category', 'security')
                    ->where('action', 'security_alert')
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
                'latest' => SystemLog::where('category', 'security')
                    ->where('action', 'security_alert')
                    ->latest()
                    ->first(),
            ];
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'count' => 0,
                'latest' => null,
            ];
        }
    }

    private function buildDailyTrend(string $modelClass, int $days = 14): array
    {
        $endDate = now()->startOfDay();
        $startDate = $endDate->copy()->subDays($days - 1);
        $labels = [];
        $data = [];

        for ($cursor = $startDate->copy(); $cursor->lte($endDate); $cursor->addDay()) {
            $labels[] = $cursor->format('d/m');
            $data[] = 0;
        }

        try {
            $rows = $modelClass::query()
                ->selectRaw('DATE(created_at) as aggregate_date, COUNT(*) as aggregate_count')
                ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                ->groupBy('aggregate_date')
                ->orderBy('aggregate_date')
                ->pluck('aggregate_count', 'aggregate_date');

            foreach ($labels as $index => $label) {
                $cursor = $startDate->copy()->addDays($index);
                $data[$index] = (int) ($rows[$cursor->toDateString()] ?? 0);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'range_label' => $days . ' ng?y g?n nh?t',
        ];
    }


    private function buildMonthlyTrend(string $modelClass, int $months = 12): array
    {
        $endMonth = now()->startOfMonth();
        $startMonth = $endMonth->copy()->subMonths($months - 1);
        $labels = [];
        $data = [];

        for ($cursor = $startMonth->copy(); $cursor->lte($endMonth); $cursor->addMonth()) {
            $labels[] = $cursor->format('m/y');
            $data[] = 0;
        }

        try {
            $rows = $modelClass::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as aggregate_month, COUNT(*) as aggregate_count")
                ->whereBetween('created_at', [$startMonth->copy()->startOfMonth(), $endMonth->copy()->endOfMonth()])
                ->groupBy('aggregate_month')
                ->orderBy('aggregate_month')
                ->pluck('aggregate_count', 'aggregate_month');

            foreach ($labels as $index => $label) {
                $cursor = $startMonth->copy()->addMonths($index);
                $data[$index] = (int) ($rows[$cursor->format('Y-m')] ?? 0);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => array_sum($data),
            'range_label' => $months . ' th?ng g?n nh?t',
        ];
    }


    /**
     * Đọc file log.
     */
    private function readLogFile($filePath, $lines = 100)
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();

        $start = max(0, $lastLine - $lines);
        $file->seek($start);

        $logContent = [];
        while (! $file->eof()) {
            $line = $file->current();
            if (trim($line)) {
                $logContent[] = $this->formatLogLine($line);
            }
            $file->next();
        }

        return array_reverse($logContent);
    }

    /**
     * Format log line.
     */
    private function formatLogLine($line)
    {
        preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(\w+)\.(\w+): (.*)/', $line, $matches);

        if (count($matches) >= 5) {
            return [
                'timestamp' => $matches[1],
                'env' => $matches[2],
                'level' => $matches[3],
                'message' => $matches[4],
                'formatted' => "<strong>[{$matches[1]}]</strong> <span class=\"log-level-{$matches[3]}\">{$matches[3]}</span>: {$matches[4]}",
            ];
        }

        return [
            'timestamp' => now()->toDateTimeString(),
            'env' => 'local',
            'level' => 'info',
            'message' => $line,
            'formatted' => '<strong>[' . now()->toDateTimeString() . ']</strong> info: ' . $line,
        ];
    }

    /**
     * Xóa hệ thống logs.
     */
    public function clearLogs()
    {
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        return redirect()
            ->route('admin.system.logs')
            ->with('success', 'Đã xóa tất cả logs!');
    }

    /**
     * Download system logs.
     */
    public function downloadLogs()
    {
        $logFile = storage_path('logs/laravel.log');

        if (! file_exists($logFile)) {
            return redirect()
                ->route('admin.system.logs')
                ->with('error', 'Log file không tồn tại!');
        }

        return response()->download($logFile, 'laravel-log-' . date('Y-m-d') . '.log');
    }

    /**
     * System information.
     */
    public function systemInfo()
    {
        $systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'database_driver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'Bật' : 'Tắt',
            'storage_free' => $this->formatBytes(disk_free_space(storage_path())),
            'storage_total' => $this->formatBytes(disk_total_space(storage_path())),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'peak_memory_usage' => $this->formatBytes(memory_get_peak_usage(true)),
        ];

        return view('admin.system.info', compact('systemInfo'));
    }

    /**
     * Format bytes to human readable.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Quick actions - xử lý các hành động nhanh.
     */
    public function quickAction(Request $request)
    {
        $action = $request->get('action');

        switch ($action) {
            case 'clear_cache':
                \Artisan::call('cache:clear');
                $message = 'Đã xóa cache hệ thống!';
                break;

            case 'clear_view':
                \Artisan::call('view:clear');
                $message = 'Đã xóa cached views!';
                break;

            case 'migrate':
                \Artisan::call('migrate', ['--force' => true]);
                $message = 'Đã chạy migrations!';
                break;

            default:
                return back()->with('error', 'Hành động không hợp lệ!');
        }

        return back()->with('success', $message);
    }

    /**
     * Quản lý tin tức.
     */
    public function newsIndex(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $category = $request->get('category');

        $posts = Post::with(['author', 'category'])
            ->when($search, function ($query, $searchTerm) {
                return $query->where('title', 'like', "%{$searchTerm}%");
            })
            ->when($status, function ($query, $statusValue) {
                return $query->where('status', $statusValue);
            })
            ->when($category, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $categories = PostCategory::active()->get();

        $totalPosts = Post::count();
        $publishedPosts = Post::where('status', 'published')->count();
        $draftPosts = Post::where('status', 'draft')->count();
        $totalViews = Post::sum('view_count');

        return view('admin.news.index', compact(
            'posts',
            'categories',
            'totalPosts',
            'publishedPosts',
            'draftPosts',
            'totalViews'
        ));
    }
}