<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // Hiển thị form đăng ký
    public function showRegister()
    {
        return view('auth.register');
    }

    // Xử lý đăng ký + gửi OTP
    public function register(Request $req)
    {
        $req->validate([
            'username' => 'required|min:4|unique:users',
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Lưu avatar
        $avatarPath = null;
        if ($req->hasFile('avatar')) {
            $avatarPath = $req->file('avatar')->store('avatars', 'public');
        }

        // Tạo OTP
        $otp = rand(100000, 999999);

        // Tạo user với role mặc định là student
        $user = User::create([
            'username' => $req->username,
            'fullname' => $req->fullname,
            'email' => $req->email,
            'password' => Hash::make($req->password),
            'avatar' => $avatarPath,
            'otp' => $otp,
            'is_verified' => false,
            'role' => 'student', // Mặc định là học viên
        ]);

        // Gửi mail OTP với template HTML
        try {
            Mail::send('emails.otp', ['otp' => $otp, 'user' => $user], function($message) use ($user) {
                $message->to($user->email)
                        ->subject('Mã OTP kích hoạt tài khoản - Khai Trí Edu');
            });
        } catch (\Exception $e) {
            // Log lỗi nhưng vẫn cho đăng ký thành công
            \Log::error('Gửi OTP thất bại: ' . $e->getMessage());
        }

        return redirect()->route('verify', ['email' => $user->email])
                        ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để nhận mã OTP kích hoạt tài khoản.');
    }

    // Hiển thị form verify OTP
    public function showVerify(Request $req)
    {
        $email = $req->query('email');
        
        if (!$email) {
            return redirect()->route('register')
                            ->with('error', 'Vui lòng đăng ký tài khoản trước.');
        }

        // Kiểm tra email có tồn tại không
        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('register')
                            ->with('error', 'Email không tồn tại. Vui lòng đăng ký lại.');
        }

        return view('auth.verify', compact('email'));
    }

    // Xử lý verify OTP
    public function verify(Request $req)
    {
        $req->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $req->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email không tồn tại!'])
                        ->withInput();
        }

        // Kiểm tra OTP
        if ($user->otp == $req->otp) {
            $user->update([
                'is_verified' => true,
                'otp' => null,
                'email_verified_at' => now(),
            ]);

            // Tự động đăng nhập sau khi verify
            Auth::login($user);

            // Redirect theo role
            return $this->redirectToRole($user);
        }

        return back()->withErrors(['otp' => 'Mã OTP không đúng! Vui lòng kiểm tra lại.'])
                    ->withInput();
    }

    // Redirect theo role sau khi đăng nhập/verify
    private function redirectToRole($user)
    {
        $message = 'Xác thực thành công! Chào mừng bạn đến với Khai Trí Edu.';

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', $message);
        } elseif ($user->isStaff()) {
            return redirect()->route('staff.dashboard')->with('success', $message);
        } else {
            return redirect()->route('home')->with('success', $message);
        }
    }

    // Resend OTP
    public function resendOtp(Request $req)
    {
        $req->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $req->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email không tồn tại!'
            ], 404);
        }

        // Tạo OTP mới
        $newOtp = rand(100000, 999999);
        
        $user->update([
            'otp' => $newOtp
        ]);

        // Gửi email OTP mới
        try {
            Mail::send('emails.otp', ['otp' => $newOtp, 'user' => $user], function($message) use ($user) {
                $message->to($user->email)
                        ->subject('Mã OTP mới - Khai Trí Edu');
            });

            return response()->json([
                'success' => true,
                'message' => 'Mã OTP mới đã được gửi đến email của bạn!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Resend OTP failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi OTP. Vui lòng thử lại!'
            ], 500);
        }
    }

    // Hiển thị form login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Xử lý login
    public function login(Request $req)
    {
        $credentials = $req->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Tìm user theo username
        $user = User::where('username', $req->username)->first();

        if (!$user) {
            return back()->withErrors(['username' => 'Tên đăng nhập không tồn tại!'])
                        ->withInput();
        }

        // Kiểm tra tài khoản đã kích hoạt chưa
        if (!$user->is_verified) {
            return back()->withErrors(['username' => 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email để xác thực.'])
                        ->with('resend_otp', $user->email)
                        ->withInput();
        }

        // Thử đăng nhập
        if (Auth::attempt($credentials, $req->remember)) {
            $req->session()->regenerate();
            
            // Redirect theo role
            return $this->redirectToRole($user);
        }

        return back()->withErrors(['password' => 'Mật khẩu không đúng!'])
                    ->withInput();
    }

    // Logout
    public function logout(Request $req)
    {
        Auth::logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();
        
        return redirect('/')
                ->with('success', 'Đăng xuất thành công!');
    }

    // Tạo tài khoản admin/staff (cho admin)
    public function createUser(Request $req)
    {
        // Middleware admin sẽ được thêm sau
        $req->validate([
            'username' => 'required|min:4|unique:users',
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,staff,student',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $avatarPath = null;
        if ($req->hasFile('avatar')) {
            $avatarPath = $req->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'username' => $req->username,
            'fullname' => $req->fullname,
            'email' => $req->email,
            'password' => Hash::make($req->password),
            'avatar' => $avatarPath,
            'role' => $req->role,
            'is_verified' => true, // Tài khoản admin/staff được tạo tự động verified
            'otp' => null,
        ]);

        return back()->with('success', "Tạo tài khoản {$req->role} thành công!");
    }
}