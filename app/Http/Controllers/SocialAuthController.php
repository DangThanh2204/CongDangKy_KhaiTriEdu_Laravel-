<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google for authentication
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = $this->findOrCreateUser($googleUser, 'google');
            $user->forceFill([
                'remember_token' => null,
            ])->save();

            Auth::login($user, false);
            request()->session()->regenerate();
            request()->session()->put('browser_session_guard_skip_once', true);

            return redirect()->route('home')
                           ->with('success', 'Đăng nhập thành công bằng Google!');
        } catch (\Exception $e) {
            \Log::error('Google OAuth error: ' . $e->getMessage());

            $message = 'Đăng nhập Google thất bại. Vui lòng thử lại hoặc sử dụng phương thức khác.';
            if (config('app.debug')) {
                $message .= ' (' . $e->getMessage() . ')';
            }

            return redirect()->route('login')
                           ->with('error', $message);
        }
    }

    /**
     * Redirect to Facebook for authentication
     */
    public function redirectToFacebook()
    {
        // Validate that Facebook OAuth is configured (avoid confusing error messages)
        $clientId = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');

        if (empty($clientId) || empty($clientSecret) || str_contains($clientSecret, 'Khóa') || str_contains($clientSecret, 'abc123')) {
            return redirect()->route('login')
                ->with('error', 'Facebook OAuth chưa được cấu hình đúng. Vui lòng cập nhật FACEBOOK_CLIENT_SECRET với App Secret thật từ Facebook Developers.');
        }

        // Facebook requires the "public_profile" permission in addition to email.
        // Ensure your app is set to "Live" and has "Web OAuth Login" enabled.
        return Socialite::driver('facebook')
            ->scopes(['public_profile', 'email'])
            ->fields(['name', 'email', 'picture'])
            ->redirect();
    }

    /**
     * Handle Facebook callback
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            $user = $this->findOrCreateUser($facebookUser, 'facebook');
            $user->forceFill([
                'remember_token' => null,
            ])->save();

            Auth::login($user, false);
            request()->session()->regenerate();
            request()->session()->put('browser_session_guard_skip_once', true);

            return redirect()->route('home')
                           ->with('success', 'Đăng nhập thành công bằng Facebook!');
        } catch (\Exception $e) {
            \Log::error('Facebook OAuth error: ' . $e->getMessage());

            $message = 'Đăng nhập Facebook thất bại. Vui lòng thử lại hoặc sử dụng phương thức khác.';
            if (config('app.debug')) {
                $message .= ' (' . $e->getMessage() . ')';
            }

            return redirect()->route('login')
                           ->with('error', $message);
        }
    }

    /**
     * Find or create user based on OAuth provider
     */
    private function findOrCreateUser($providerUser, $provider)
    {
        $email = $providerUser->getEmail();

        // Some OAuth providers may not return an email (e.g., Facebook when not granted) 
        if (!$email) {
            throw new \Exception('Không thể lấy email từ ' . ucfirst($provider) . '. Vui lòng cấp quyền email hoặc sử dụng phương thức đăng nhập khác.');
        }

        // Look for existing user by provider ID
        $user = User::where($provider . '_id', $providerUser->getId())->first();

        if ($user) {
            return $user;
        }

        // Check if email already exists
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // Link the OAuth provider to existing account
            $existingUser->update([
                $provider . '_id' => $providerUser->getId(),
                'provider' => $provider,
                'provider_id' => $providerUser->getId(),
            ]);
            return $existingUser;
        }

        // Create new user
        $newUser = User::create([
            'username' => $this->generateUniqueUsername($providerUser->getName()),
            'fullname' => $providerUser->getName(),
            'email' => $email,
            'avatar' => $providerUser->getAvatar() ? $this->downloadAvatar($providerUser->getAvatar(), $providerUser->getId()) : null,
            'is_verified' => true, // OAuth users are pre-verified
            'role' => 'student',
            $provider . '_id' => $providerUser->getId(),
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
        ]);

        return $newUser;
    }

    /**
     * Generate a unique username from the provider user's name
     */
    private function generateUniqueUsername($name)
    {
        $baseUsername = str_replace(' ', '', strtolower($name));
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Download and save avatar from OAuth provider
     */
    private function downloadAvatar($avatarUrl, $providerId)
    {
        try {
            $response = Http::get($avatarUrl);
            if (! $response->successful()) {
                return null;
            }

            $filename = 'avatars/oauth_' . $providerId . '.jpg';
            Storage::disk('public')->put($filename, $response->body());
            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}
