<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            'site_name' => 'Khai Trí Education',
            'site_tagline' => 'Nền tảng học tập trực tuyến hiện đại',
            'contact_email' => 'contact@khatriedu.com',
            'contact_phone' => '+84 (0) 123 456 789',
            'contact_address' => 'Hà Nội, Việt Nam',
            'facebook_url' => 'https://facebook.com/khatriedu',
            'twitter_url' => 'https://twitter.com/khatriedu',
            'instagram_url' => 'https://instagram.com/khatriedu',
            'footer_text' => '&copy; 2024 Khai Trí Education. All rights reserved.',
        ];

        foreach ($defaultSettings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
