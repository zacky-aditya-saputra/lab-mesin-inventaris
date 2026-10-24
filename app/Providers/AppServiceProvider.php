<?php

namespace App\Providers;

use App\Models\LoanRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Laravel Boost (browser logger script + /_boost/browser-logs route)
        // is a development-only tool. Force-disable it everywhere except the
        // local environment so it can never be served to the public, even when
        // APP_DEBUG is accidentally left on in a deployed environment.
        if (! $this->app->isLocal()) {
            $this->app['config']->set('boost.enabled', false);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $limit = $this->app->isLocal() ? 30 : 5;

            return Limit::perMinute($limit)->by($request->ip());
        });

        RateLimiter::for('register', function (Request $request): Limit {
            if ($this->app->isLocal()) {
                return Limit::perMinute(30)->by($request->ip());
            }

            return Limit::perMinutes(10, 5)->by($request->ip());
        });

        View::creator('layouts.admin', function ($view): void {
            $user = auth()->user();

            $view->with('pendingCount', LoanRequest::query()
                ->where('status', 'PENDING')
                ->when($user?->isLaboran(), fn ($query) => $query->where('laboratory_id', $user->laboratory_id))
                ->count());
        });
    }
}
