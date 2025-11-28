<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Hiển thị danh sách users với tìm kiếm và phân trang
    public function index(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role');
        $status = $request->get('status');

        $users = User::query()
            ->when($search, function ($query, $search) {
                return $query->where('username', 'like', "%{$search}%")
                           ->orWhere('fullname', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($role, function ($query, $role) {
                return $query->where('role', $role);
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

        return view('admin.users.index', compact('users', 'search', 'role', 'status'));
    }

    // Hiển thị form tạo user mới
    public function create()
    {
        return view('admin.users.create');
    }

    // Lưu user mới
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
            'is_verified' => true, // Admin tạo user thì auto verified
            'otp' => null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Tạo tài khoản thành công!');
    }

    // Hiển thị form chỉnh sửa user
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // Cập nhật user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => [
                'required',
                'min:4',
                Rule::unique('users')->ignore($user->id)
            ],
            'fullname' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
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

        // Chỉ cập nhật password nếu có
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
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

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật tài khoản thành công!');
    }

    // Xóa user
    public function destroy(User $user)
    {
        // Không cho xóa chính mình
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Bạn không thể xóa tài khoản của chính mình!');
        }

        // Xóa avatar nếu có
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Xóa tài khoản thành công!');
    }

    // Bật/tắt trạng thái verified
    public function toggleStatus(User $user)
    {
        $user->update([
            'is_verified' => !$user->is_verified
        ]);

        $status = $user->is_verified ? 'kích hoạt' : 'vô hiệu hóa';
        
        return redirect()->back()
            ->with('success', "Đã {$status} tài khoản {$user->username}!");
    }

    // Xác thực user thủ công
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