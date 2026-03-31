<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BlockchainAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct(protected BlockchainAuditService $blockchainAudit)
    {
    }

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
            Mail::send('emails.otp', ['otp' => $otp, 'user' => $user, 'purpose' => 'Kích Hoạt Tài Khoản'], function($message) use ($user) {
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
            $req->session()->regenerate();
            $this->markBrowserSessionGuardBypass($req);

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

    private function recentLoginFailureCount(string $ip): int
    {
        try {
            if (! Schema::hasTable((new \App\Models\SystemLog())->getTable())) {
                return 0;
            }

            return \App\Models\SystemLog::where('action', 'login_failure')
                ->where('ip', $ip)
                ->where('created_at', '>=', now()->subMinutes(15))
                ->count();
        } catch (\Throwable $exception) {
            report($exception);

            return 0;
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
            Mail::send('emails.otp', ['otp' => $newOtp, 'user' => $user, 'purpose' => 'Kích Hoạt Tài Khoản'], function($message) use ($user) {
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

    /**
     * Forgot password: form to enter username + email
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle send OTP for password reset
     */
    public function sendPasswordOtp(Request $req)
    {
        $req->validate([
            'username' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::where('username', $req->username)
                    ->where('email', $req->email)
                    ->first();
        if (!$user) {
            return back()->withErrors(['username' => 'Tên đăng nhập và email không khớp hoặc không tồn tại.'])
                        ->withInput();
        }

        // generate OTP and save
        $otp = rand(100000, 999999);
        $user->update(['otp' => $otp]);

        \App\Services\SystemLogService::record('security', 'password_reset_requested', ['user_id' => $user->id]);

        try {
            Mail::send('emails.otp', ['otp' => $otp, 'user' => $user, 'purpose' => 'Đặt lại mật khẩu'], function($message) use ($user) {
                $message->to($user->email)
                        ->subject('Mã OTP đặt lại mật khẩu - Khai Trí Edu');
            });
        } catch (\Exception $e) {
            \Log::error('Gửi OTP forgot password thất bại: ' . $e->getMessage());
        }

        return redirect()->route('password.reset.form', ['username' => $user->username, 'email' => $user->email])
                         ->with('success', 'Mã OTP đã được gửi đến email của bạn.');
    }

    /**
     * Show page where user enters otp and new password
     */
    public function showResetPasswordForm(Request $req)
    {
        $username = $req->query('username');
        $email = $req->query('email');

        if (!$username || !$email) {
            return redirect()->route('password.forgot')
                             ->with('error', 'Vui lòng cung cấp thông tin để đặt lại mật khẩu.');
        }

        $user = User::where('username', $username)->where('email', $email)->first();
        if (!$user) {
            return redirect()->route('password.forgot')
                             ->with('error', 'Thông tin không hợp lệ.');
        }

        return view('auth.reset-password', compact('username','email'));
    }

    /**
     * Process actual password reset
     */
    public function resetPassword(Request $req)
    {
        $req->validate([
            'username' => 'required',
            'email' => 'required|email',
            'otp' => 'required|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('username', $req->username)
                    ->where('email', $req->email)
                    ->first();
        if (!$user) {
            return back()->withErrors(['username' => 'Thông tin người dùng không hợp lệ!'])->withInput();
        }

        if ($user->otp != $req->otp) {
            return back()->withErrors(['otp' => 'Mã OTP không đúng!'])->withInput();
        }

        $user->update([
            'password' => Hash::make($req->password),
            'otp' => null,
        ]);

        \App\Services\SystemLogService::record('security', 'password_changed', ['user_id' => $user->id]);

        return redirect()->route('login')->with('success', 'Mật khẩu của bạn đã được đặt lại. Vui lòng đăng nhập lại.');
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
            \App\Services\SystemLogService::record('security', 'login_failure', ['username' => $req->username, 'reason' => 'not_found']);

            // detect multiple failures from same IP
            $recent = $this->recentLoginFailureCount($req->ip());
            if ($recent >= 5) {
                \App\Services\SystemLogService::record('security', 'security_alert', ['type' => 'many_failed_logins', 'ip' => $req->ip(), 'count' => $recent]);
            }

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
        try {
            $passwordMatches = Hash::check($req->password, $user->password);
        } catch (\Exception $e) {
            // Nếu password trong DB không phải hash bcrypt, kiểm tra một vài dạng phổ biến
            $passwordMatches = false;
            if ($user->password === $req->password) {
                $passwordMatches = true; // plaintext
            } elseif (md5($req->password) === $user->password) {
                $passwordMatches = true; // MD5
            } elseif (sha1($req->password) === $user->password) {
                $passwordMatches = true; // SHA1
            }

            if ($passwordMatches) {
                // Migrate lên bcrypt
                $user->password = Hash::make($req->password);
                $user->save();
            }
        }

        if ($passwordMatches) {
            $user->forceFill([
                'remember_token' => null,
            ])->save();

            Auth::login($user, false);
            $req->session()->regenerate();
            $this->markBrowserSessionGuardBypass($req);

            \App\Services\SystemLogService::record('security', 'login_success', ['user_id' => $user->id]);

            try {
                $this->blockchainAudit->record('security.login_success', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'session_id' => $req->session()->getId(),
                ], [
                    'reference' => 'LOGIN-' . $user->id . '-' . now()->format('YmdHis'),
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'role' => $user->role,
                    'ip' => $req->ip(),
                ]);
            } catch (\Throwable $exception) {
                report($exception);
            }

            // Redirect theo role
            return $this->redirectToRole($user);
        }

        \App\Services\SystemLogService::record('security', 'login_failure', ['user_id' => $user->id, 'reason' => 'wrong_password']);

        // detect multiple failures from same IP
        $recent = $this->recentLoginFailureCount($req->ip());
        if ($recent >= 5) {
            \App\Services\SystemLogService::record('security', 'security_alert', ['type' => 'many_failed_logins', 'ip' => $req->ip(), 'count' => $recent, 'user_id' => $user->id]);
        }

        return back()->withErrors(['password' => 'Mật khẩu không đúng!'])
                    ->withInput();
    }

    // Logout
    public function logout(Request $req)
    {
        $this->invalidateAuthenticatedSession($req);

        return redirect('/')
                ->with('success', 'Đăng xuất thành công!');
    }

    public function logoutOnBrowserClose(Request $req)
    {
        $this->invalidateAuthenticatedSession($req);

        return response()->noContent();
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

    private function invalidateAuthenticatedSession(Request $req): void
    {
        Auth::logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();
    }

    private function markBrowserSessionGuardBypass(Request $req): void
    {
        $req->session()->put('browser_session_guard_skip_once', true);
    }

}
