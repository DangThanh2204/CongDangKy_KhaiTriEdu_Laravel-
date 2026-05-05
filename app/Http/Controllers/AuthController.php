<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AuthController extends Controller
{
    public function showRegister()
    {
        $this->purgeExpiredUnverifiedUsers();

        return view('auth.register');
    }

    public function register(Request $req)
    {
        $this->purgeExpiredUnverifiedUsers();

        $remaining = $this->reusableUnverifiedCooldown($req->input('email'));
        if ($remaining > 0) {
            return back()
                ->withErrors(['email' => "Email này vừa được dùng để đăng ký. Vui lòng kiểm tra hộp thư hoặc đợi {$remaining} giây trước khi gửi lại OTP."])
                ->withInput($req->except(['password', 'password_confirmation']));
        }

        $this->purgeReusableUnverifiedUser($req->input('email'), $req->input('username'));

        $req->validate([
            'username' => 'required|min:4|unique:users',
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $avatarPath = null;
        if ($req->hasFile('avatar')) {
            $avatarPath = $req->file('avatar')->store('avatars', 'public');
        }

        $otp = random_int(100000, 999999);

        $user = User::create([
            'username' => $req->username,
            'fullname' => $req->fullname,
            'email' => $req->email,
            'password' => Hash::make($req->password),
            'avatar' => $avatarPath,
            'otp' => $otp,
            'otp_sent_at' => now(),
            'is_verified' => false,
            'role' => 'student',
        ]);

        try {
            $this->sendOtpMail(
                $user,
                $otp,
                'Kích hoạt tài khoản',
                'Mã OTP kích hoạt tài khoản - Khai Trí Edu'
            );
        } catch (Throwable $exception) {
            $this->cleanupUnverifiedUser($user);

            return back()
                ->withErrors(['email' => $this->friendlyOtpMailErrorMessage($exception)])
                ->withInput($req->except(['password', 'password_confirmation']));
        }

        return redirect()->route('verify', ['email' => $user->email])
            ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để nhận mã OTP kích hoạt tài khoản.');
    }

    public function showVerify(Request $req)
    {
        $this->purgeExpiredUnverifiedUsers();

        $email = (string) $req->query('email');

        if ($email === '') {
            return redirect()->route('register')
                ->with('error', 'Vui lòng đăng ký tài khoản trước.');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('register')
                ->with('error', 'Tài khoản chưa xác thực đã hết hạn hoặc không tồn tại. Vui lòng đăng ký lại.');
        }

        if ($user->is_verified) {
            return redirect()->route('login')
                ->with('success', 'Tài khoản này đã được xác thực. Bạn có thể đăng nhập ngay.');
        }

        if ($this->isOtpExpired($user)) {
            $this->cleanupUnverifiedUser($user);

            return redirect()->route('register')
                ->with('error', 'Mã OTP đã hết hạn. Vui lòng đăng ký lại để nhận mã mới.');
        }

        return view('auth.verify', compact('email'));
    }

    public function verify(Request $req)
    {
        $this->purgeExpiredUnverifiedUsers();

        $req->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $req->email)->first();

        if (! $user) {
            return redirect()->route('register')
                ->with('error', 'Tài khoản chưa xác thực đã hết hạn hoặc không tồn tại. Vui lòng đăng ký lại.');
        }

        if ($user->is_verified) {
            return redirect()->route('login')
                ->with('success', 'Tài khoản này đã được xác thực. Bạn có thể đăng nhập ngay.');
        }

        if ($this->isOtpExpired($user)) {
            $this->cleanupUnverifiedUser($user);

            return redirect()->route('register')
                ->with('error', 'Mã OTP đã hết hạn. Vui lòng đăng ký lại để nhận mã mới.');
        }

        if ((string) $user->otp === (string) $req->otp) {
            $user->update([
                'is_verified' => true,
                'otp' => null,
                'otp_sent_at' => null,
                'email_verified_at' => now(),
            ]);

            Auth::login($user, false);
            $req->session()->regenerate();
            $this->markBrowserSessionGuardBypass($req);

            return $this->redirectToRole($user);
        }

        return back()->withErrors(['otp' => 'Mã OTP không đúng! Vui lòng kiểm tra lại.'])
            ->withInput();
    }

    private function redirectToRole($user)
    {
        $message = 'Xác thực thành công! Chào mừng bạn đến với Khai Trí Edu.';

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', $message);
        }

        if ($user->isInstructor()) {
            return redirect()->route('instructor.dashboard')->with('success', $message);
        }

        return redirect()->route('home')->with('success', $message);
    }

    private function recentLoginFailureCount(string $ip): int
    {
        try {
            return \App\Models\SystemLog::where('action', 'login_failure')
                ->where('ip', $ip)
                ->where('created_at', '>=', now()->subMinutes(15))
                ->count();
        } catch (Throwable $exception) {
            report($exception);

            return 0;
        }
    }

    public function resendOtp(Request $req)
    {
        $this->purgeExpiredUnverifiedUsers();

        $req->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $req->email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản chưa xác thực đã hết hạn hoặc không tồn tại. Vui lòng đăng ký lại.',
            ], 404);
        }

        if ($user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản này đã được xác thực. Bạn có thể đăng nhập ngay.',
            ], 422);
        }

        if ($this->isOtpExpired($user)) {
            $this->cleanupUnverifiedUser($user);

            return response()->json([
                'success' => false,
                'message' => 'Mã OTP đã hết hạn. Vui lòng đăng ký lại để nhận mã mới.',
            ], 410);
        }

        $cooldown = $this->resendCooldownRemaining($user);
        if ($cooldown > 0) {
            return response()->json([
                'success' => false,
                'message' => "Vui lòng đợi {$cooldown} giây trước khi gửi lại OTP.",
                'retry_after' => $cooldown,
            ], 429);
        }

        $oldOtp = $user->otp;
        $oldOtpSentAt = $user->otp_sent_at;
        $newOtp = random_int(100000, 999999);
        $user->update([
            'otp' => $newOtp,
            'otp_sent_at' => now(),
        ]);

        try {
            $this->sendOtpMail(
                $user,
                $newOtp,
                'Kích hoạt tài khoản',
                'Mã OTP mới - Khai Trí Edu'
            );

            return response()->json([
                'success' => true,
                'message' => 'Mã OTP mới đã được gửi đến email của bạn.',
            ]);
        } catch (Throwable $exception) {
            $user->update([
                'otp' => $oldOtp,
                'otp_sent_at' => $oldOtpSentAt,
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->friendlyOtpMailErrorMessage($exception),
            ], 500);
        }
    }

    public function showLogin()
    {
        $this->purgeExpiredUnverifiedUsers();

        return view('auth.login');
    }

    public function showForgotPasswordForm()
    {
        $this->purgeExpiredUnverifiedUsers();

        return view('auth.forgot-password');
    }

    public function sendPasswordOtp(Request $req)
    {
        $this->purgeExpiredUnverifiedUsers();

        $req->validate([
            'username' => 'required',
            'email' => 'required|email',
        ]);

        $user = User::where('username', $req->username)
            ->where('email', $req->email)
            ->first();

        if (! $user) {
            return back()->withErrors([
                'username' => 'Tên đăng nhập và email không khớp hoặc không tồn tại.',
            ])->withInput();
        }

        $cooldown = $this->resendCooldownRemaining($user);
        if ($cooldown > 0) {
            return back()->withErrors([
                'email' => "Vui lòng đợi {$cooldown} giây trước khi gửi lại mã OTP đặt lại mật khẩu.",
            ])->withInput();
        }

        $oldOtp = $user->otp;
        $oldOtpSentAt = $user->otp_sent_at;
        $otp = random_int(100000, 999999);
        $user->update([
            'otp' => $otp,
            'otp_sent_at' => now(),
        ]);

        \App\Services\SystemLogService::record('security', 'password_reset_requested', ['user_id' => $user->id]);

        try {
            $this->sendOtpMail(
                $user,
                $otp,
                'Đặt lại mật khẩu',
                'Mã OTP đặt lại mật khẩu - Khai Trí Edu'
            );
        } catch (Throwable $exception) {
            $user->update([
                'otp' => $oldOtp,
                'otp_sent_at' => $oldOtpSentAt,
            ]);

            return back()->withErrors([
                'email' => $this->friendlyOtpMailErrorMessage($exception),
            ])->withInput();
        }

        return redirect()->route('password.reset.form', ['username' => $user->username, 'email' => $user->email])
            ->with('success', 'Mã OTP đã được gửi đến email của bạn.');
    }

    public function showResetPasswordForm(Request $req)
    {
        $username = $req->query('username');
        $email = $req->query('email');

        if (! $username || ! $email) {
            return redirect()->route('password.forgot')
                ->with('error', 'Vui lòng cung cấp thông tin để đặt lại mật khẩu.');
        }

        $user = User::where('username', $username)->where('email', $email)->first();
        if (! $user) {
            return redirect()->route('password.forgot')
                ->with('error', 'Thông tin không hợp lệ.');
        }

        return view('auth.reset-password', compact('username', 'email'));
    }

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

        if (! $user) {
            return back()->withErrors(['username' => 'Thông tin người dùng không hợp lệ!'])->withInput();
        }

        if ((string) $user->otp !== (string) $req->otp) {
            return back()->withErrors(['otp' => 'Mã OTP không đúng!'])->withInput();
        }

        $user->update([
            'password' => Hash::make($req->password),
            'otp' => null,
            'otp_sent_at' => null,
        ]);

        \App\Services\SystemLogService::record('security', 'password_changed', ['user_id' => $user->id]);

        return redirect()->route('login')->with('success', 'Mật khẩu của bạn đã được đặt lại. Vui lòng đăng nhập lại.');
    }

    public function login(Request $req)
    {
        $this->purgeExpiredUnverifiedUsers();

        $req->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $req->username)->first();

        if (! $user) {
            \App\Services\SystemLogService::record('security', 'login_failure', ['username' => $req->username, 'reason' => 'not_found']);

            $recent = $this->recentLoginFailureCount($req->ip());
            if ($recent >= 5) {
                \App\Services\SystemLogService::record('security', 'security_alert', ['type' => 'many_failed_logins', 'ip' => $req->ip(), 'count' => $recent]);
            }

            return back()->withErrors(['username' => 'Tên đăng nhập không tồn tại!'])
                ->withInput();
        }

        if (! $user->is_verified) {
            if ($this->isOtpExpired($user)) {
                $this->cleanupUnverifiedUser($user);

                return back()->withErrors(['username' => 'Tài khoản chưa xác thực đã hết hạn. Vui lòng đăng ký lại để nhận mã OTP mới.'])
                    ->withInput();
            }

            return back()->withErrors(['username' => 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email để xác thực.'])
                ->with('resend_otp', $user->email)
                ->withInput();
        }

        try {
            $passwordMatches = Hash::check($req->password, $user->password);
        } catch (Throwable $exception) {
            $passwordMatches = false;

            if ($user->password === $req->password) {
                $passwordMatches = true;
            } elseif (md5($req->password) === $user->password) {
                $passwordMatches = true;
            } elseif (sha1($req->password) === $user->password) {
                $passwordMatches = true;
            }

            if ($passwordMatches) {
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

            return $this->redirectToRole($user);
        }

        \App\Services\SystemLogService::record('security', 'login_failure', ['user_id' => $user->id, 'reason' => 'wrong_password']);

        $recent = $this->recentLoginFailureCount($req->ip());
        if ($recent >= 5) {
            \App\Services\SystemLogService::record('security', 'security_alert', ['type' => 'many_failed_logins', 'ip' => $req->ip(), 'count' => $recent, 'user_id' => $user->id]);
        }

        return back()->withErrors(['password' => 'Mật khẩu không đúng!'])
            ->withInput();
    }

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

    public function createUser(Request $req)
    {
        $req->validate([
            'username' => 'required|min:4|unique:users',
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,instructor,student',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $avatarPath = null;
        if ($req->hasFile('avatar')) {
            $avatarPath = $req->file('avatar')->store('avatars', 'public');
        }

        User::create([
            'username' => $req->username,
            'fullname' => $req->fullname,
            'email' => $req->email,
            'password' => Hash::make($req->password),
            'avatar' => $avatarPath,
            'role' => $req->role,
            'is_verified' => true,
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

    private function otpLifetimeMinutes(): int
    {
        return 10;
    }

    private function otpResendCooldownSeconds(): int
    {
        return 60;
    }

    private function resendCooldownRemaining(User $user): int
    {
        if (! $user->otp_sent_at) {
            return 0;
        }

        $elapsed = now()->getTimestamp() - $user->otp_sent_at->getTimestamp();
        $remaining = $this->otpResendCooldownSeconds() - $elapsed;

        return max(0, (int) $remaining);
    }

    private function reusableUnverifiedCooldown(?string $email): int
    {
        $email = trim((string) $email);
        if ($email === '') {
            return 0;
        }

        $user = User::where('email', $email)
            ->where('is_verified', false)
            ->first();

        if (! $user) {
            return 0;
        }

        return $this->resendCooldownRemaining($user);
    }

    private function purgeExpiredUnverifiedUsers(): void
    {
        $cutoff = now()->subMinutes($this->otpLifetimeMinutes());

        User::query()
            ->where('is_verified', false)
            ->where(function ($query) use ($cutoff) {
                $query->where(function ($subQuery) use ($cutoff) {
                    $subQuery->whereNull('updated_at')
                        ->where('created_at', '<=', $cutoff);
                })->orWhere('updated_at', '<=', $cutoff);
            })
            ->orderBy('id')
            ->get()
            ->each(function (User $user): void {
                $this->cleanupUnverifiedUser($user);
            });
    }

    private function purgeReusableUnverifiedUser(?string $email, ?string $username): void
    {
        $email = trim((string) $email);
        $username = trim((string) $username);

        if ($email === '' && $username === '') {
            return;
        }

        User::query()
            ->where('is_verified', false)
            ->where(function ($query) use ($email, $username) {
                if ($email !== '') {
                    $query->orWhere('email', $email);
                }

                if ($username !== '') {
                    $query->orWhere('username', $username);
                }
            })
            ->get()
            ->each(function (User $user): void {
                $this->cleanupUnverifiedUser($user);
            });
    }

    private function cleanupUnverifiedUser(User $user): void
    {
        if ($user->is_verified) {
            return;
        }

        try {
            if (! empty($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        $user->delete();
    }

    private function isOtpExpired(User $user): bool
    {
        $reference = $user->updated_at ?? $user->created_at;

        if (! $reference) {
            return true;
        }

        return $reference->copy()->addMinutes($this->otpLifetimeMinutes())->isPast();
    }

    private function sendOtpMail(User $user, int $otp, string $purpose, string $subject): void
    {
        $issues = $this->mailConfigurationIssues();

        if ($issues !== []) {
            throw new \RuntimeException(implode(' ', $issues));
        }

        try {
            Mail::send('emails.otp', [
                'otp' => $otp,
                'user' => $user,
                'purpose' => $purpose,
            ], function ($message) use ($user, $subject) {
                $message->to($user->email)
                    ->subject($subject);
            });
        } catch (Throwable $exception) {
            Log::error('OTP mail failed', [
                'email' => $user->email,
                'purpose' => $purpose,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'mail_scheme' => config('mail.mailers.smtp.scheme'),
            ]);

            throw new \RuntimeException('Không thể gửi email OTP. Vui lòng kiểm tra cấu hình SMTP hoặc App Password Gmail trên Render.', 0, $exception);
        }
    }

    private function mailConfigurationIssues(): array
    {
        $issues = [];
        $mailer = (string) config('mail.default');

        if ($mailer !== 'smtp') {
            $issues[] = 'MAIL_MAILER phải là smtp để gửi OTP thật.';
        }

        foreach ([
            'mail.mailers.smtp.host' => 'MAIL_HOST',
            'mail.mailers.smtp.port' => 'MAIL_PORT',
            'mail.mailers.smtp.username' => 'MAIL_USERNAME',
            'mail.mailers.smtp.password' => 'MAIL_PASSWORD',
            'mail.from.address' => 'MAIL_FROM_ADDRESS',
        ] as $configKey => $label) {
            $value = config($configKey);

            if ($value === null || trim((string) $value) === '') {
                $issues[] = "Thiếu {$label}.";
            }
        }

        return $issues;
    }

    private function friendlyOtpMailErrorMessage(Throwable $exception): string
    {
        $message = (string) $exception->getMessage();
        $lower = strtolower($message);
        $port = (int) config('mail.mailers.smtp.port');
        $scheme = (string) config('mail.mailers.smtp.scheme');

        if (str_contains($message, 'MAIL_MAILER') || str_contains($message, 'MAIL_')) {
            return 'Hệ thống email chưa cấu hình đầy đủ trên Render. Vui lòng kiểm tra lại MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD và MAIL_FROM_ADDRESS.';
        }

        if (str_contains($lower, 'authentication') || str_contains($lower, '535') || str_contains($lower, 'username and password not accepted')) {
            return 'Sai App Password Gmail. Vào Google Account → Security → App Passwords tạo mật khẩu mới (16 ký tự, không khoảng trắng) và cập nhật MAIL_PASSWORD trên Render.';
        }

        if (str_contains($lower, 'starttls') || str_contains($lower, 'tls') || str_contains($lower, 'ssl') || str_contains($lower, 'stream_socket_enable_crypto')) {
            if ($port === 465 && $scheme !== 'smtps') {
                return 'Port 465 cần MAIL_SCHEME=smtps trên Render. Hãy đặt MAIL_SCHEME=smtps rồi redeploy.';
            }
            if ($port === 587 && $scheme === 'smtps') {
                return 'Port 587 không dùng smtps. Hãy bỏ trống MAIL_SCHEME (Symfony tự dùng STARTTLS) rồi redeploy.';
            }
            return 'Lỗi TLS/SSL khi kết nối SMTP. Với port 465 đặt MAIL_SCHEME=smtps; với port 587 để trống MAIL_SCHEME.';
        }

        if (str_contains($lower, 'connection') || str_contains($lower, 'timed out') || str_contains($lower, 'timeout') || str_contains($lower, 'could not open') || str_contains($lower, 'getaddrinfo')) {
            return 'Không kết nối được tới SMTP server. Kiểm tra MAIL_HOST=smtp.gmail.com và MAIL_PORT (465 hoặc 587), Render có thể đang block port hoặc DNS chưa resolve.';
        }

        if (str_contains($lower, 'quota') || str_contains($lower, 'rate limit') || str_contains($lower, 'too many')) {
            return 'Gmail đang giới hạn số mail gửi từ tài khoản này. Đợi 1-2 giờ hoặc dùng tài khoản Gmail khác.';
        }

        if (app()->environment('local') || config('app.debug')) {
            return 'Không thể gửi email OTP: ' . $message;
        }

        return 'Không thể gửi email OTP lúc này. Vui lòng kiểm tra lại cấu hình SMTP/App Password Gmail trên Render rồi thử lại.';
    }
}
