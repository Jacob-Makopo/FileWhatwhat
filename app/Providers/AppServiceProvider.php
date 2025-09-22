<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Register the permission middleware
        $this->app->singleton('permission', function ($app) {
            return new \Spatie\Permission\Middleware\PermissionMiddleware($app->make(\Spatie\Permission\PermissionRegistrar::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5)->by(
                    ($request->input('employee_number') ?? $request->input('email') ?? 'guest')
                    .$request->ip()
                ),
            ];
        });
    }
}