<?php

namespace App\Providers;

use App\Models\LoanRequest;
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
        View::creator('layouts.admin', function ($view): void {
            $user = auth()->user();

            $view->with('pendingCount', LoanRequest::query()
                ->where('status', 'PENDING')
                ->when($user?->isLaboran(), fn ($query) => $query->where('laboratory_id', $user->laboratory_id))
                ->count());
        });
    }
}
