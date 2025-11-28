<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Hiển thị dashboard admin
     */
    public function dashboard()
    {
        // Thống kê tổng quan
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_staff' => User::where('role', 'staff')->count(),
            'total_instructors' => User::where('role', 'instructor')->count(), 
            'total_students' => User::where('role', 'student')->count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'unverified_users' => User::where('is_verified', false)->count(),
            'today_registrations' => User::whereDate('created_at', today())->count(),
            'weekly_registrations' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        // Thống kê user theo role (cho biểu đồ)
        $usersByRole = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role');

        // User mới đăng ký trong 7 ngày qua (cho biểu đồ)
        $recentRegistrations = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // User mới nhất (cho activity feed)
        $recentUsers = User::with([])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Thống kê đăng ký theo tháng (năm nay)
        $monthlyRegistrations = User::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');

        return view('admin.dashboard', compact(
            'stats',
            'usersByRole',
            'recentRegistrations',
            'recentUsers',
            'monthlyRegistrations'
        ));
    }

    /**
     * Lấy dữ liệu cho biểu đồ (API)
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
                    'colors' => ['#2c5aa0', '#28a745', '#ff6b35', '#6f42c1', '#20c997'] // admin, staff, instructor, student, etc.
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

                // Fill missing months with 0
                $monthlyData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $monthData = $data->where('month', $i)->first();
                    $monthlyData[] = $monthData ? $monthData->count : 0;
                }

                return response()->json([
                    'labels' => ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
                    'data' => $monthlyData
                ]);

            case 'weekly_activity':
                $data = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->where('created_at', '>=', now()->subDays(30))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                return response()->json([
                    'labels' => $data->pluck('date'),
                    'data' => $data->pluck('count')
                ]);
        }

        return response()->json(['error' => 'Invalid chart type'], 400);
    }

    /**
     * Hiển thị profile admin
     */
    public function profile()
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }

    /**
     * Cập nhật profile admin
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

        // Xử lý đổi mật khẩu
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
            }
            
            $data['password'] = Hash::make($request->new_password);
        }

        // Xử lý avatar
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('admin.profile')
            ->with('success', 'Cập nhật thông tin thành công!');
    }

    /**
     * Hiển thị hệ thống logs (đơn giản)
     */
    public function systemLogs()
    {
        // Lấy file log mới nhất
        $logFile = storage_path('logs/laravel.log');
        $logs = [];
        
        if (file_exists($logFile)) {
            $logs = $this->readLogFile($logFile, 100); // Đọc 100 dòng cuối
        }

        return view('admin.system.logs', compact('logs'));
    }

    /**
     * Đọc file log
     */
    private function readLogFile($filePath, $lines = 100)
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        
        $start = max(0, $lastLine - $lines);
        $file->seek($start);
        
        $logContent = [];
        while (!$file->eof()) {
            $line = $file->current();
            if (trim($line)) {
                $logContent[] = $this->formatLogLine($line);
            }
            $file->next();
        }
        
        return array_reverse($logContent); // Mới nhất lên đầu
    }

    /**
     * Format log line
     */
    private function formatLogLine($line)
    {
        // Phân tích dòng log Laravel
        preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(\w+)\.(\w+): (.*)/', $line, $matches);
        
        if (count($matches) >= 5) {
            return [
                'timestamp' => $matches[1],
                'env' => $matches[2],
                'level' => $matches[3],
                'message' => $matches[4],
                'formatted' => "<strong>[{$matches[1]}]</strong> <span class=\"log-level-{$matches[3]}\">{$matches[3]}</span>: {$matches[4]}"
            ];
        }
        
        return [
            'timestamp' => now()->toDateTimeString(),
            'env' => 'local',
            'level' => 'info',
            'message' => $line,
            'formatted' => "<strong>[".now()->toDateTimeString()."]</strong> info: {$line}"
        ];
    }

    /**
     * Xóa hệ thống logs
     */
    public function clearLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
        
        return redirect()->route('admin.system.logs')
            ->with('success', 'Đã xóa tất cả logs!');
    }

    /**
     * Download system logs
     */
    public function downloadLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return redirect()->route('admin.system.logs')
                ->with('error', 'Log file không tồn tại!');
        }
        
        return response()->download($logFile, 'laravel-log-' . date('Y-m-d') . '.log');
    }

    /**
     * System information
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
     * Format bytes to human readable
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
     * Quick actions - xử lý các hành động nhanh
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
     * Quản lý tin tức
     */
    public function newsIndex(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $category = $request->get('category');

        $posts = Post::with(['author', 'category'])
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($category, function ($query, $category) {
                return $query->where('category_id', $category);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $categories = PostCategory::active()->get();
        
        // Thống kê
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