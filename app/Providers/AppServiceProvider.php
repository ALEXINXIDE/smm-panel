<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Disable Cashier auto-migrations — we manage them ourselves
        Cashier::$runsMigrations = false;

        // Register ProviderSyncService as singleton for DI
        $this->app->singleton(\App\Services\ProviderSyncService::class);
    }

    public function boot(): void
    {
        // Force HTTPS in production (Railway terminates SSL at proxy level)
        if ($this->app->environment('production', 'staging')) {
            URL::forceScheme('https');
        }

        // Catch N+1 query bugs in development
        if (! $this->app->environment('production')) {
            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
        }

        // Use Tailwind CSS pagination views
        Paginator::useTailwind();

        // Schema string length for MySQL utf8mb4 compatibility (PostgreSQL doesn't need this)
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\Schema::defaultStringLength(191);
        }
    }
}
