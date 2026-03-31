<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CsvExportService;
use App\Support\StudentLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role');
        $status = $request->get('status');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $baseQuery = $this->filteredQuery($request);

        $users = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        StudentLevel::attachSummaries($users->getCollection());

        $stats = [
            'total_users' => (clone $baseQuery)->count(),
            'verified_users' => (clone $baseQuery)->where('is_verified', true)->count(),
            'student_users' => (clone $baseQuery)->where('role', 'student')->count(),
            'admin_users' => (clone $baseQuery)->where('role', 'admin')->count(),
        ];

        return view('admin.users.index', compact(
            'users',
            'stats',
            'search',
            'role',
            'status',
            'fromDate',
            'toDate'
        ));
    }

    public function export(Request $request, CsvExportService $csvExportService)
    {
        $users = $this->filteredQuery($request)
            ->orderBy('created_at', 'desc')
            ->get();

        StudentLevel::attachSummaries($users);

        return $csvExportService->download(
            'users-' . now()->format('Y-m-d-His') . '.csv',
            ['ID', 'Họ tên', 'Username', 'Email', 'Vai trò', 'Cấp học viên', 'Điểm học tập', 'Trạng thái', 'Ngày tạo'],
            $users->map(function (User $user) {
                $studentLevel = $user->getAttribute('student_level_summary');

                return [
                    $user->id,
                    $user->fullname,
                    $user->username,
                    $user->email,
                    $user->role,
                    $user->isStudent() ? data_get($studentLevel, 'level.title', 'Đồng') : '',
                    $user->isStudent() ? data_get($studentLevel, 'points', 0) : '',
                    $user->is_verified ? 'Đã xác thực' : 'Chưa xác thực',
                    optional($user->created_at)->format('d/m/Y H:i'),
                ];
            })
        );
    }

    protected function filteredQuery(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role');
        $status = $request->get('status');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        return User::query()
            ->when($search, function ($query, $searchTerm) {
                return $query->where(function ($innerQuery) use ($searchTerm) {
                    $innerQuery->where('username', 'like', "%{$searchTerm}%")
                        ->orWhere('fullname', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            })
            ->when($role, function ($query, $roleValue) {
                return $query->where('role', $roleValue);
            })
            ->when($status !== null, function ($query) use ($status) {
                if ($status === 'verified') {
                    return $query->where('is_verified', true);
                }

                if ($status === 'unverified') {
                    return $query->where('is_verified', false);
                }

                return $query;
            })
            ->when($fromDate, function ($query, $fromDateValue) {
                return $query->whereDate('created_at', '>=', $fromDateValue);
            })
            ->when($toDate, function ($query, $toDateValue) {
                return $query->whereDate('created_at', '<=', $toDateValue);
            });
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|min:4|unique:users',
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,staff,instructor,student',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        User::create([
            'username' => $request->username,
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => $avatarPath,
            'role' => $request->role,
            'is_verified' => true,
            'otp' => null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Tạo tài khoản thành công!');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => [
                'required',
                'min:4',
                Rule::unique('users')->ignore($user->id),
            ],
            'fullname' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|min:6|confirmed',
            'role' => 'required|in:admin,staff,instructor,student',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $data = [
            'username' => $request->username,
            'fullname' => $request->fullname,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật tài khoản thành công!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Bạn không thể xóa tài khoản của chính mình!');
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Xóa tài khoản thành công!');
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'is_verified' => ! $user->is_verified,
        ]);

        $statusText = $user->is_verified ? 'kích hoạt' : 'vô hiệu hóa';

        return redirect()->back()
            ->with('success', "Đã {$statusText} tài khoản {$user->username}!");
    }

    public function verifyUser(User $user)
    {
        $user->update([
            'is_verified' => true,
            'otp' => null,
            'email_verified_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', "Đã xác thực thủ công tài khoản {$user->username}!");
    }
}