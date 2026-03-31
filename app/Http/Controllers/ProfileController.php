<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Show separate change password form
     */
    public function showChangePasswordForm()
    {
        return view('profile.change-password');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $data = [
            'fullname' => $request->fullname,
            'email' => $request->email,
        ];

        // Xử lý avatar
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        $user->update($data);

        return back()->with('success', 'Thông tin cá nhân đã được cập nhật thành công!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng!']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // redirect back to profile edit page and jump to password section
        $redirectUrl = url()->previous() . '#change-password-card';
        return redirect()->to($redirectUrl)->with('success', 'Mật khẩu đã được thay đổi thành công!');
    }
}