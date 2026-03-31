<?php

namespace App\Providers;

use App\Models\Setting;
use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\AppLayoutComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Chia se settings voi tat ca views
        View::composer('*', function ($view) {
            $view->with([
                'siteName' => Setting::get('site_name', 'Khai Tri Education'),
                'siteTagline' => Setting::get('site_tagline', 'Nen tang hoc tap truc tuyen'),
                'siteLogo' => Setting::get('site_logo', null),
                'siteFavicon' => Setting::get('site_favicon', null),
                'footerText' => Setting::get('footer_text', ''),
                'contactEmail' => Setting::get('contact_email', ''),
                'contactPhone' => Setting::get('contact_phone', ''),
                'contactAddress' => Setting::get('contact_address', ''),
                'facebookUrl' => Setting::get('facebook_url', ''),
                'twitterUrl' => Setting::get('twitter_url', ''),
                'instagramUrl' => Setting::get('instagram_url', ''),
            ]);
        });

        View::composer('layouts.app', AppLayoutComposer::class);
        View::composer('layouts.admin', AdminLayoutComposer::class);

        // Dang ky helper function cho YouTube embed
        require_once app_path('Helpers/youtube.php');
    }
}
