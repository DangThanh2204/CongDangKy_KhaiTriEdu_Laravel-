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
        View::composer('*', function ($view) {
            $defaults = [
                'siteName' => 'Khai Tri Education',
                'siteTagline' => 'Nen tang hoc tap truc tuyen',
                'siteLogo' => null,
                'siteFavicon' => null,
                'footerText' => '',
                'contactEmail' => '',
                'contactPhone' => '',
                'contactAddress' => '',
                'facebookUrl' => '',
                'twitterUrl' => '',
                'instagramUrl' => '',
            ];

            try {
                $view->with([
                    'siteName' => Setting::get('site_name', $defaults['siteName']),
                    'siteTagline' => Setting::get('site_tagline', $defaults['siteTagline']),
                    'siteLogo' => Setting::get('site_logo', $defaults['siteLogo']),
                    'siteFavicon' => Setting::get('site_favicon', $defaults['siteFavicon']),
                    'footerText' => Setting::get('footer_text', $defaults['footerText']),
                    'contactEmail' => Setting::get('contact_email', $defaults['contactEmail']),
                    'contactPhone' => Setting::get('contact_phone', $defaults['contactPhone']),
                    'contactAddress' => Setting::get('contact_address', $defaults['contactAddress']),
                    'facebookUrl' => Setting::get('facebook_url', $defaults['facebookUrl']),
                    'twitterUrl' => Setting::get('twitter_url', $defaults['twitterUrl']),
                    'instagramUrl' => Setting::get('instagram_url', $defaults['instagramUrl']),
                ]);
            } catch (\Throwable $exception) {
                report($exception);
                $view->with($defaults);
            }
        });

        View::composer('layouts.app', AppLayoutComposer::class);
        View::composer('layouts.admin', AdminLayoutComposer::class);

        require_once app_path('Helpers/youtube.php');
    }
}