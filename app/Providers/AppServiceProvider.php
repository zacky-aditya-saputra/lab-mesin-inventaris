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
        //
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
