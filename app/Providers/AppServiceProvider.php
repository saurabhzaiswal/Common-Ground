<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use ProtoneMedia\LaravelXssProtection\Middleware\XssCleanInput;

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
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        RateLimiter::for('login', function (Request $request): array {
            $ipAddress = $request->ip() ?? 'unknown';
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by('login-ip:'.$ipAddress),
                Limit::perMinute(5)->by('login-email:'.$ipAddress.'|'.$email),
            ];
        });

        RateLimiter::for('registration', function (Request $request): array {
            $ipAddress = $request->ip() ?? 'unknown';

            return [
                Limit::perHour(5)->by('registration-hour:'.$ipAddress),
                Limit::perDay(15)->by('registration-day:'.$ipAddress),
            ];
        });

        XssCleanInput::skipKeyWhen(fn (string $key): bool => in_array($key, [
            'current_password',
            'new_password',
            'password',
            'password_confirmation',
            'cf-turnstile-response',
        ], true));
    }
}
