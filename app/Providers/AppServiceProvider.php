<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Http\Livewire\LogoUploader;

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

        Livewire::component('logo-uploader', LogoUploader::class);
    }
}
