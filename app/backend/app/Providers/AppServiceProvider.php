<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Rate limiters para perfiles críticos
        RateLimiter::for('auth', function (Request $request) {
            // Límite estricto para login, registro, password
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('public-read', function (Request $request) {
            // Límite medio para endpoints públicos de solo lectura
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('public-write', function (Request $request) {
            // Límite estricto para endpoints públicos de escritura
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('authenticated', function (Request $request) {
            // Límite medio para autenticados (usuario+IP)
            $user = $request->user();

            return Limit::perMinute(100)->by(optional($user)->id.'|'.$request->ip());
        });

        RateLimiter::for('heavy', function (Request $request) {
            // Límite estricto para operaciones pesadas autenticadas
            $user = $request->user();

            return Limit::perMinute(20)->by(optional($user)->id.'|'.$request->ip());
        });
    }
}
