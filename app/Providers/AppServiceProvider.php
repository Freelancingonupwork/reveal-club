<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
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
        Schema::defaultStringLength(191);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        if (app()->environment('development')) {
            URL::forceScheme('https');
        }
        Blade::component('vendor.turnstile.components.turnstile-widget', 'turnstile');
        Validator::extend('turnstile', function ($attribute, $value, $parameters, $validator) {
            // Skip real validation in local/dev if needed
            if (app()->isLocal() && $value === 'fake-token') {
                return true;
            }
    
            $secretKey = config('turnstile.turnstile_secret_key');
    
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secretKey,
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);
    
            return $response->ok() && $response->json('success') === true;
        }, 'Failed to verify CAPTCHA. Please try again.');
    }
}
