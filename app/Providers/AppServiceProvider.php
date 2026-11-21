<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
            $request = request();
            $settings = $request->attributes->get('synex_site_settings');

            if ($settings === null) {
                $settings = SiteSetting::query()->find(1)
                    ?? new SiteSetting(['name' => config('variables.templateName')]);
                $request->attributes->set('synex_site_settings', $settings);
            }

            $view->with('siteSettings', $settings);
        });
    }
}