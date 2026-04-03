<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name' => Setting::get('site_name', 'Khai Tri Education'),
            'site_tagline' => Setting::get('site_tagline', 'Nền tảng học tập trực tuyến'),
            'site_logo' => Setting::get('site_logo', ''),
            'site_favicon' => Setting::get('site_favicon', ''),
            'contact_email' => Setting::get('contact_email', ''),
            'contact_phone' => Setting::get('contact_phone', ''),
            'contact_address' => Setting::get('contact_address', ''),
            'facebook_url' => Setting::get('facebook_url', ''),
            'twitter_url' => Setting::get('twitter_url', ''),
            'instagram_url' => Setting::get('instagram_url', ''),
            'footer_text' => Setting::get('footer_text', ''),
            'allow_class_change' => Setting::get('allow_class_change', '0'),
            'class_change_deadline_days' => Setting::get('class_change_deadline_days', '0'),
            'ai_assistant_prompt' => Setting::get('ai_assistant_prompt', ''),
            'wallet_bank_name' => Setting::get('wallet_bank_name', ''),
            'wallet_bank_bin' => Setting::get('wallet_bank_bin', ''),
            'wallet_bank_account_name' => Setting::get('wallet_bank_account_name', ''),
            'wallet_bank_account_number' => Setting::get('wallet_bank_account_number', ''),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'site_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico|max:1024',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'footer_text' => 'nullable|string',
            'ai_assistant_prompt' => 'nullable|string|max:10000',
            'allow_class_change' => 'nullable|in:0,1',
            'class_change_deadline_days' => 'nullable|integer|min:0',
            'wallet_bank_name' => 'nullable|string|max:120',
            'wallet_bank_bin' => 'nullable|string|max:20',
            'wallet_bank_account_name' => 'nullable|string|max:120',
            'wallet_bank_account_number' => 'nullable|string|max:40',
        ]);

        if ($request->hasFile('site_logo')) {
            $oldLogo = Setting::get('site_logo');
            if ($oldLogo && Storage::exists('public/' . $oldLogo)) {
                Storage::delete('public/' . $oldLogo);
            }

            $path = $request->file('site_logo')->store('logos', 'public');
            Setting::set('site_logo', $path);
        }

        if ($request->hasFile('site_favicon')) {
            $oldFavicon = Setting::get('site_favicon');
            if ($oldFavicon && Storage::exists('public/' . $oldFavicon)) {
                Storage::delete('public/' . $oldFavicon);
            }

            $path = $request->file('site_favicon')->store('favicons', 'public');
            Setting::set('site_favicon', $path);
        }

        $filledSettings = [
            'site_name',
            'site_tagline',
            'contact_email',
            'contact_phone',
            'contact_address',
            'facebook_url',
            'twitter_url',
            'instagram_url',
            'footer_text',
            'ai_assistant_prompt',
            'allow_class_change',
            'class_change_deadline_days',
            'wallet_bank_name',
            'wallet_bank_bin',
            'wallet_bank_account_name',
            'wallet_bank_account_number',
        ];

        foreach ($filledSettings as $key) {
            if (array_key_exists($key, $validated)) {
                Setting::set($key, $validated[$key]);
            }
        }

        if (! array_key_exists('allow_class_change', $validated)) {
            Setting::set('allow_class_change', '0');
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Cập nhật cài đặt thành công.');
    }
}
