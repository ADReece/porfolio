<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);

        // Share currency symbol with all views
        view()->composer('*', function ($view) {
            $currencySymbol = \App\Models\Setting::where('key', 'currency_symbol')->value('value') ?? '£';
            $view->with('currencySymbol', $currencySymbol);
        });
    }
}
