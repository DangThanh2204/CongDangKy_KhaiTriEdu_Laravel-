<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $teachers = User::query()
            ->where('role', 'instructor')
            ->when($search, function ($query, $search) {
                return $query->where('username', 'like', "%{$search}%")
                             ->orWhere('fullname', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($status !== null, function ($query) use ($status) {
                if ($status === 'verified') {
                    return $query->where('is_verified', true);
                } elseif ($status === 'unverified') {
                    return $query->where('is_verified', false);
                }
                return $query;
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'totalTeachers' => User::where('role', 'instructor')->count(),
            'verifiedTeachers' => User::where('role', 'instructor')->where('is_verified', true)->count(),
            'unverifiedTeachers' => User::where('role', 'instructor')->where('is_verified', false)->count(),
        ];

        return view('admin.teachers.index', compact('teachers', 'search', 'status', 'stats'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|min:4|unique:users',
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
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
            'role' => 'instructor',
            'is_verified' => true,
            'otp' => null,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Tạo giảng viên thành công!');
    }

    public function edit(User $user)
    {
        // Ensure we only allow editing instructor accounts
        if ($user->role !== 'instructor') {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Không tìm thấy giảng viên.');
        }

        return view('admin.teachers.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'instructor') {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Không tìm thấy giảng viên.');
        }

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
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $data = [
            'username' => $request->username,
            'fullname' => $request->fullname,
            'email' => $request->email,
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

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Cập nhật giảng viên thành công!');
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'instructor') {
            return redirect()->route('admin.teachers.index')
                ->with('error', 'Không tìm thấy giảng viên.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Bạn không thể xóa tài khoản của chính mình!');
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Xóa giảng viên thành công!');
    }
}
