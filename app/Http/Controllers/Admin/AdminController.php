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
use App\Services\BlockchainSyncService;
use App\Services\FireflyService;
use App\Services\FireflyConsortiumService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class AdminController extends Controller
{
    public function __construct(
        protected FireflyService $firefly,
        protected FireflyConsortiumService $consortium,
    ) {
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
                $users = User::query()->get();

                $stats['total_users'] = User::count();
                $stats['total_admins'] = User::where('role', 'admin')->count();
                $stats['total_staff'] = User::where('role', 'staff')->count();
                $stats['total_instructors'] = User::where('role', 'instructor')->count();
                $stats['total_students'] = User::where('role', 'student')->count();
                $stats['today_registrations'] = User::whereDate('created_at', $today)->count();
                $stats['weekly_registrations'] = User::whereBetween('created_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])->count();
                $stats['verified_users'] = $hasVerifiedColumn ? User::where('is_verified', true)->count() : 0;
                $stats['unverified_users'] = $hasVerifiedColumn ? User::where('is_verified', false)->count() : 0;

                $usersByRole = $users
                    ->groupBy('role')
                    ->map(fn (Collection $group) => $group->count())
                    ->sortKeys();

                $recentRegistrations = $users
                    ->filter(function (User $user) use ($today) {
                        return $user->created_at && $user->created_at->gte($today->copy()->subDays(7));
                    })
                    ->groupBy(fn (User $user) => $user->created_at->format('Y-m-d'))
                    ->map(fn (Collection $group) => $group->count())
                    ->sortKeys();

                $recentUsers = User::query()
                    ->latest('created_at')
                    ->take(6)
                    ->get();

                $monthlyRegistrations = $users
                    ->filter(function (User $user) {
                        return $user->created_at && (int) $user->created_at->format('Y') === (int) date('Y');
                    })
                    ->groupBy(fn (User $user) => (int) $user->created_at->format('n'))
                    ->map(fn (Collection $group) => $group->count())
                    ->sortKeys();
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
                        ->whereIn('status', ['approved', 'completed'])
                        ->get()
                        ->groupBy('class_id')
                        ->map(fn (Collection $group) => $group->count());

                    if ($hasWaitlistColumns) {
                        $heldSeatCounts = CourseEnrollment::query()
                            ->where('status', 'pending')
                            ->whereNotNull('waitlist_promoted_at')
                            ->whereNotNull('seat_hold_expires_at')
                            ->where('seat_hold_expires_at', '>', now())
                            ->get()
                            ->groupBy('class_id')
                            ->map(fn (Collection $group) => $group->count());
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
                $data = User::query()
                    ->get()
                    ->groupBy('role')
                    ->map(fn (Collection $group) => $group->count());

                return response()->json([
                    'labels' => $data->keys()->values(),
                    'data' => $data->values(),
                    'colors' => ['#2c5aa0', '#28a745', '#ff6b35', '#6f42c1', '#20c997'],
                ]);

            case 'monthly_registrations':
                $currentYear = date('Y');
                $data = User::query()
                    ->get()
                    ->filter(function (User $user) use ($currentYear) {
                        return $user->created_at && $user->created_at->format('Y') === (string) $currentYear;
                    })
                    ->groupBy(fn (User $user) => (int) $user->created_at->format('n'))
                    ->map(fn (Collection $group) => $group->count());

                $monthlyData = [];
                for ($month = 1; $month <= 12; $month++) {
                    $monthlyData[] = (int) ($data[$month] ?? 0);
                }

                return response()->json([
                    'labels' => ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
                    'data' => $monthlyData,
                ]);

            case 'weekly_activity':
                $data = User::query()
                    ->get()
                    ->filter(function (User $user) {
                        return $user->created_at && $user->created_at->gte(now()->subDays(30));
                    })
                    ->groupBy(fn (User $user) => $user->created_at->format('Y-m-d'))
                    ->map(fn (Collection $group) => $group->count())
                    ->sortKeys();

                return response()->json([
                    'labels' => $data->keys()->values(),
                    'data' => $data->values(),
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

    public function blockchainDashboard()
    {
        $blockchainSummary = $this->buildBlockchainSummary();

        return view('admin.blockchain.index', compact('blockchainSummary'));
    }


public function syncBlockchain(BlockchainSyncService $syncService)
{
    $summary = $syncService->syncPendingRecords(20);
    $message = trim(($summary['message'] ?? 'Đã xử lý đồng bộ blockchain.') . ' Chứng chỉ: ' . ($summary['certificates_synced'] ?? 0) . ', giao dịch: ' . ($summary['transactions_synced'] ?? 0) . ', lỗi: ' . ($summary['failed'] ?? 0) . '.');

    return redirect()
        ->route('admin.blockchain.dashboard')
        ->with(($summary['success'] ?? false) ? 'success' : 'error', $message);
}

private function buildBlockchainSummary(): array
{
    $health = $this->consortium->summary();
    $summary = [
        'firefly_configured' => $this->consortium->isConfigured(),
        'firefly_health' => $health,
        'firefly_connected' => (bool) data_get($health, 'success', false),
        'consortium_enabled' => (bool) data_get($health, 'consortium_enabled', false),
        'consortium_quorum' => (int) data_get($health, 'required_quorum', 1),
        'healthy_members' => (int) data_get($health, 'healthy_members', 0),
        'configured_members' => (int) data_get($health, 'configured_members', 0),
        'members_total' => (int) data_get($health, 'members_total', 0),
        'member_statuses' => data_get($health, 'members', []),
        'token_ready' => (bool) data_get($health, 'token_ready', $this->firefly->canManageTokens()),
        'platform_identity' => (string) data_get($health, 'platform_identity', $this->firefly->getPlatformIdentity()),
        'namespace' => (string) data_get($health, 'namespace', config('services.firefly.namespace', '-')),
        'audit_topic' => (string) config('services.firefly.audit_topic', 'audit'),
        'primary_endpoint' => (string) data_get($health, 'endpoint', ''),
        'anchored_certificates' => 0,
        'pending_certificates' => 0,
        'anchored_transactions' => 0,
        'pending_transactions' => 0,
        'recent_certificates' => [],
        'recent_transactions' => [],
    ];

    try {
        $certificateTable = (new CourseCertificate())->getTable();
        if (Schema::hasTable($certificateTable)) {
            $certificates = CourseCertificate::query()->with(['course:id,title', 'user:id,fullname,username', 'enrollment.courseClass:id,name'])->latest('issued_at')->get();
            $summary['anchored_certificates'] = $certificates->filter(fn (CourseCertificate $certificate) => $this->isBlockchainAnchored($certificate->meta ?? []))->count();
            $summary['pending_certificates'] = max($certificates->count() - $summary['anchored_certificates'], 0);
            $summary['recent_certificates'] = $certificates->take(8)->map(function (CourseCertificate $certificate) {
                return [
                    'code' => $certificate->certificate_no,
                    'user' => $certificate->user?->fullname ?: $certificate->user?->username ?: 'Không rõ',
                    'course' => $certificate->course?->title ?? 'Không xác định',
                    'issued_at' => $certificate->issued_at,
                    'anchored' => $this->isBlockchainAnchored($certificate->meta ?? []),
                    'message_id' => $this->extractBlockchainMessageId($certificate->meta ?? []),
                    'tx_id' => $this->extractBlockchainTxId($certificate->meta ?? []),
                    'state' => $this->extractBlockchainState($certificate->meta ?? []),
                    'member_success_count' => $this->extractBlockchainSuccessCount($certificate->meta ?? []),
                    'required_quorum' => $this->extractBlockchainRequiredQuorum($certificate->meta ?? []),
                    'proof_ratio' => $this->buildProofRatio($certificate->meta ?? []),
                ];
            })->values()->all();
        }
    } catch (\Throwable $exception) {
        report($exception);
    }

    try {
        $walletTransactionTable = (new WalletTransaction())->getTable();
        if (Schema::hasTable($walletTransactionTable)) {
            $transactions = WalletTransaction::query()->with('wallet.user:id,fullname,username')->where('status', 'completed')->latest('created_at')->get();
            $summary['anchored_transactions'] = $transactions->filter(fn (WalletTransaction $transaction) => $this->isBlockchainAnchored($transaction->metadata ?? []))->count();
            $summary['pending_transactions'] = max($transactions->count() - $summary['anchored_transactions'], 0);
            $summary['recent_transactions'] = $transactions->take(8)->map(function (WalletTransaction $transaction) {
                return [
                    'reference' => $transaction->reference ?: ('WTX-' . $transaction->id),
                    'user' => $transaction->wallet?->user?->fullname ?: $transaction->wallet?->user?->username ?: 'Không rõ',
                    'method' => $transaction->method_label,
                    'amount' => (float) $transaction->amount,
                    'created_at' => $transaction->created_at,
                    'anchored' => $this->isBlockchainAnchored($transaction->metadata ?? []),
                    'message_id' => $this->extractBlockchainMessageId($transaction->metadata ?? []),
                    'tx_id' => $this->extractBlockchainTxId($transaction->metadata ?? []),
                    'state' => $this->extractBlockchainState($transaction->metadata ?? []),
                    'member_success_count' => $this->extractBlockchainSuccessCount($transaction->metadata ?? []),
                    'required_quorum' => $this->extractBlockchainRequiredQuorum($transaction->metadata ?? []),
                    'proof_ratio' => $this->buildProofRatio($transaction->metadata ?? []),
                ];
            })->values()->all();
        }
    } catch (\Throwable $exception) {
        report($exception);
    }

    return $summary;
}

private function isBlockchainAnchored(array $payload): bool
{
    $audit = $this->extractBlockchainAudit($payload);
    $successCount = (int) data_get($audit, 'success_count', data_get($audit, 'success') ? 1 : 0);
    $requiredQuorum = max((int) data_get($audit, 'required_quorum', $successCount > 0 ? 1 : 0), 1);

    return (bool) data_get($audit, 'success', false) || $successCount >= $requiredQuorum;
}

private function extractBlockchainAudit(array $payload): array
{
    return is_array(data_get($payload, 'blockchain_audit')) ? data_get($payload, 'blockchain_audit') : $payload;
}

private function extractBlockchainSuccessCount(array $payload): int
{
    $audit = $this->extractBlockchainAudit($payload);

    return (int) data_get($audit, 'success_count', data_get($audit, 'success') ? 1 : 0);
}

private function extractBlockchainRequiredQuorum(array $payload): int
{
    $audit = $this->extractBlockchainAudit($payload);

    return max((int) data_get($audit, 'required_quorum', $this->extractBlockchainSuccessCount($payload) > 0 ? 1 : 0), 1);
}

private function buildProofRatio(array $payload): string
{
    $audit = $this->extractBlockchainAudit($payload);
    $successCount = $this->extractBlockchainSuccessCount($payload);
    $membersTotal = max((int) data_get($audit, 'members_total', $successCount), 1);

    return $successCount . '/' . $membersTotal;
}

private function extractBlockchainMessageId(array $payload): ?string
{
    $audit = $this->extractBlockchainAudit($payload);

    return data_get($audit, 'message_id') ?? data_get($audit, 'data.header.id') ?? data_get($audit, 'data.id');
}

private function extractBlockchainTxId(array $payload): ?string
{
    $audit = $this->extractBlockchainAudit($payload);

    return data_get($audit, 'tx_id') ?? data_get($audit, 'data.tx.id') ?? data_get($audit, 'data.tx') ?? data_get($audit, 'data.blockchain.id') ?? data_get($audit, 'data.blockchain.transactionHash');
}

private function extractBlockchainState(array $payload): ?string
{
    $audit = $this->extractBlockchainAudit($payload);

    return data_get($audit, 'state') ?? data_get($audit, 'data.state') ?? data_get($audit, 'status') ?? data_get($audit, 'message');
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
                ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                ->get()
                ->filter(fn ($model) => $model->created_at)
                ->groupBy(fn ($model) => $model->created_at->format('Y-m-d'))
                ->map(fn (Collection $group) => $group->count())
                ->sortKeys();

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
            'range_label' => $days . ' ngÃ y gáº§n nháº¥t',
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
                ->whereBetween('created_at', [$startMonth->copy()->startOfMonth(), $endMonth->copy()->endOfMonth()])
                ->get()
                ->filter(fn ($model) => $model->created_at)
                ->groupBy(fn ($model) => $model->created_at->format('Y-m'))
                ->map(fn (Collection $group) => $group->count())
                ->sortKeys();

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
            'range_label' => $months . ' thÃ¡ng gáº§n nháº¥t',
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
