<?php

namespace App\Providers;

use App\Models\Setting;
use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\AppLayoutComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
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
        // Site dùng Bootstrap nên ép paginator dùng template Bootstrap 5
        // — tránh icon SVG mặc định Tailwind (.w-5 .h-5) phồng to ra cả màn hình.
        Paginator::useBootstrapFive();

        $renderUrl = env('RENDER_EXTERNAL_URL');
        $appUrl = env('APP_URL');
        $shouldForceHttps = app()->environment('production') || filled($renderUrl) || str_contains((string) $appUrl, '.onrender.com');

        if (filled($renderUrl)) {
            config(['app.url' => $renderUrl]);
            URL::forceRootUrl($renderUrl);
        } elseif (filled($appUrl)) {
            URL::forceRootUrl($appUrl);
        }

        if ($shouldForceHttps) {
            URL::forceScheme('https');
        }

        View::share($this->resolveSharedSiteSettings());

        View::composer('layouts.app', AppLayoutComposer::class);
        View::composer('layouts.admin', AdminLayoutComposer::class);

        require_once app_path('Helpers/youtube.php');
    }

    private function resolveSharedSiteSettings(): array
    {
        $defaultMapsEmbed = 'https://maps.google.com/maps?q=Ung+Van+Khiem,+Long+Xuyen,+An+Giang&t=&z=15&ie=UTF8&iwloc=&output=embed';

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
            'youtubeUrl' => '',
            'tiktokUrl' => '',
            'zaloUrl' => '',
            'googleMapsEmbed' => $defaultMapsEmbed,
        ];

        try {
            $values = Setting::getMany([
                'site_name' => $defaults['siteName'],
                'site_tagline' => $defaults['siteTagline'],
                'site_logo' => $defaults['siteLogo'],
                'site_favicon' => $defaults['siteFavicon'],
                'footer_text' => $defaults['footerText'],
                'contact_email' => $defaults['contactEmail'],
                'contact_phone' => $defaults['contactPhone'],
                'contact_address' => $defaults['contactAddress'],
                'facebook_url' => $defaults['facebookUrl'],
                'youtube_url' => $defaults['youtubeUrl'],
                'tiktok_url' => $defaults['tiktokUrl'],
                'zalo_url' => $defaults['zaloUrl'],
                'google_maps_embed' => $defaults['googleMapsEmbed'],
            ]);

            return [
                'siteName' => $values['site_name'],
                'siteTagline' => $values['site_tagline'],
                'siteLogo' => $values['site_logo'],
                'siteFavicon' => $values['site_favicon'],
                'footerText' => $values['footer_text'],
                'contactEmail' => $values['contact_email'],
                'contactPhone' => $values['contact_phone'],
                'contactAddress' => $values['contact_address'],
                'facebookUrl' => $values['facebook_url'],
                'youtubeUrl' => $values['youtube_url'],
                'tiktokUrl' => $values['tiktok_url'],
                'zaloUrl' => $values['zalo_url'],
                'googleMapsEmbed' => $values['google_maps_embed'] ?: $defaultMapsEmbed,
            ];
        } catch (\Throwable $exception) {
            report($exception);

            return $defaults;
        }
    }
}
