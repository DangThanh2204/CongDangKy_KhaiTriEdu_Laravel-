<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google for authentication.
     */
    public function redirectToGoogle()
    {
        if ($response = $this->ensureProviderConfigured('google')) {
            return $response;
        }

        return $this->socialiteDriver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle Google callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = $this->socialiteDriver('google')->user();
            $user = $this->findOrCreateUser($googleUser, 'google');
            $user->forceFill([
                'remember_token' => null,
            ])->save();

            Auth::login($user, false);
            request()->session()->regenerate();
            request()->session()->put('browser_session_guard_skip_once', true);

            return redirect()->route('home')
                ->with('success', 'Đăng nhập thành công bằng Google!');
        } catch (Throwable $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage(), [
                'redirect' => config('services.google.redirect'),
            ]);

            return redirect()->route('login')
                ->with('error', $this->socialLoginErrorMessage('google', $e));
        }
    }

    /**
     * Redirect to Facebook for authentication.
     */
    public function redirectToFacebook()
    {
        if ($response = $this->ensureProviderConfigured('facebook')) {
            return $response;
        }

        return $this->socialiteDriver('facebook')
            ->scopes(['public_profile', 'email'])
            ->fields(['name', 'email', 'picture'])
            ->redirect();
    }

    /**
     * Handle Facebook callback.
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = $this->socialiteDriver('facebook')->user();
            $user = $this->findOrCreateUser($facebookUser, 'facebook');
            $user->forceFill([
                'remember_token' => null,
            ])->save();

            Auth::login($user, false);
            request()->session()->regenerate();
            request()->session()->put('browser_session_guard_skip_once', true);

            return redirect()->route('home')
                ->with('success', 'Đăng nhập thành công bằng Facebook!');
        } catch (Throwable $e) {
            \Log::error('Facebook OAuth error: ' . $e->getMessage(), [
                'redirect' => config('services.facebook.redirect'),
            ]);

            return redirect()->route('login')
                ->with('error', $this->socialLoginErrorMessage('facebook', $e));
        }
    }

    /**
     * Find or create user based on OAuth provider.
     */
    private function findOrCreateUser($providerUser, string $provider): User
    {
        $email = $providerUser->getEmail();

        if (! $email) {
            throw new \RuntimeException(
                'Không thể lấy email từ ' . $this->providerLabel($provider) . '. Vui lòng cấp quyền email hoặc dùng phương thức đăng nhập khác.'
            );
        }

        $user = User::where($provider . '_id', $providerUser->getId())->first();

        if ($user) {
            return $user;
        }

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $existingUser->update([
                $provider . '_id' => $providerUser->getId(),
                'provider' => $provider,
                'provider_id' => $providerUser->getId(),
            ]);

            return $existingUser;
        }

        return User::create([
            'username' => $this->generateUniqueUsername($providerUser->getName()),
            'fullname' => $providerUser->getName(),
            'email' => $email,
            'avatar' => $providerUser->getAvatar() ? $this->downloadAvatar($providerUser->getAvatar(), $providerUser->getId()) : null,
            'is_verified' => true,
            'role' => 'student',
            $provider . '_id' => $providerUser->getId(),
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
        ]);
    }

    /**
     * Generate a unique username from the provider user's name.
     */
    private function generateUniqueUsername(?string $name): string
    {
        $baseUsername = preg_replace('/[^a-z0-9]/', '', strtolower((string) $name));
        $baseUsername = $baseUsername !== '' ? $baseUsername : 'user';
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Download and save avatar from OAuth provider.
     */
    private function downloadAvatar(string $avatarUrl, string $providerId): ?string
    {
        try {
            $response = Http::get($avatarUrl);
            if (! $response->successful()) {
                return null;
            }

            $filename = 'avatars/oauth_' . $providerId . '.jpg';
            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function socialiteDriver(string $provider)
    {
        $driver = Socialite::driver($provider);

        if (app()->environment('production') || request()->isSecure()) {
            return $driver->stateless();
        }

        return $driver;
    }

    private function ensureProviderConfigured(string $provider)
    {
        $issues = $this->socialConfigIssues($provider);

        if ($issues === []) {
            return null;
        }

        return redirect()->route('login')
            ->with('error', $this->providerConfigErrorMessage($provider, $issues));
    }

    private function socialConfigIssues(string $provider): array
    {
        $prefix = strtoupper($provider);
        $clientId = trim((string) config("services.{$provider}.client_id"));
        $clientSecret = trim((string) config("services.{$provider}.client_secret"));
        $redirect = trim((string) config("services.{$provider}.redirect"));
        $issues = [];

        if ($clientId === '' || $this->looksLikePlaceholder($clientId)) {
            $issues[] = "Thiếu hoặc sai {$prefix}_CLIENT_ID.";
        }

        if ($clientSecret === '' || $this->looksLikePlaceholder($clientSecret)) {
            $issues[] = "Thiếu hoặc sai {$prefix}_CLIENT_SECRET.";
        }

        if ($redirect === '' || ! filter_var($redirect, FILTER_VALIDATE_URL)) {
            $issues[] = "{$prefix}_REDIRECT_URI chưa hợp lệ.";
        }

        return $issues;
    }

    private function providerConfigErrorMessage(string $provider, array $issues): string
    {
        $label = $this->providerLabel($provider);
        $redirect = (string) config("services.{$provider}.redirect");

        return sprintf(
            '%s OAuth chưa được cấu hình đúng. %s Callback hiện tại: %s',
            $label,
            implode(' ', $issues),
            $redirect !== '' ? $redirect : '(chưa có)'
        );
    }

    private function socialLoginErrorMessage(string $provider, Throwable $exception): string
    {
        $label = $this->providerLabel($provider);
        $message = strtolower($exception->getMessage());
        $redirect = (string) config("services.{$provider}.redirect");
        $prefix = strtoupper($provider);

        if (str_contains($message, 'access_denied')) {
            return "Bạn đã hủy đăng nhập bằng {$label}.";
        }

        if (
            str_contains($message, 'redirect_uri_mismatch') ||
            str_contains($message, 'redirect uri mismatch') ||
            str_contains($message, 'url blocked') ||
            str_contains($message, 'invalid redirect') ||
            str_contains($message, 'redirect_uri')
        ) {
            return "{$label} đang từ chối callback. Hãy cập nhật Redirect URI trong {$label} Developers thành: {$redirect}";
        }

        if (
            str_contains($message, 'invalid_client') ||
            str_contains($message, 'client secret') ||
            str_contains($message, 'app secret') ||
            str_contains($message, 'invalid appsecret_proof')
        ) {
            return "{$label} OAuth chưa đúng Client ID hoặc Client Secret. Hãy kiểm tra lại {$prefix}_CLIENT_ID và {$prefix}_CLIENT_SECRET.";
        }

        if (
            str_contains($message, 'app is in development mode') ||
            str_contains($message, 'authorization error') ||
            str_contains($message, 'access blocked') ||
            str_contains($message, 'unauthorized_client')
        ) {
            return "{$label} đang chặn quyền truy cập. Hãy kiểm tra trạng thái ứng dụng, tài khoản test và Redirect URI trong {$label} Developers.";
        }

        $fallback = "Đăng nhập {$label} thất bại. Vui lòng thử lại hoặc kiểm tra cấu hình OAuth.";

        if (config('app.debug')) {
            return $fallback . ' (' . $exception->getMessage() . ')';
        }

        return $fallback;
    }

    private function providerLabel(string $provider): string
    {
        return match ($provider) {
            'google' => 'Google',
            'facebook' => 'Facebook',
            default => ucfirst($provider),
        };
    }

    private function looksLikePlaceholder(string $value): bool
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return true;
        }

        foreach (['your_', 'your-', 'example', 'abc123', 'client_secret', 'app_secret', 'replace_me', 'changeme'] as $marker) {
            if (str_contains($normalized, $marker)) {
                return true;
            }
        }

        return false;
    }
}