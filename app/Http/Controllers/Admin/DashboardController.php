<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LoanRequest;
use App\Models\Tool;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with summary metrics.
     */
    public function index(): View
    {
        $recentLoans = LoanRequest::query()
            ->with(['user', 'items.tool'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'totalTools' => Tool::query()->where('is_active', true)->count(),
            'totalCategories' => Category::query()->count(),
            'totalStock' => (int) Tool::query()->sum('total_stock'),
            'pendingLoansCount' => LoanRequest::query()->where('status', 'PENDING')->count(),
            'overdueLoansCount' => LoanRequest::query()->where('status', 'OVERDUE')->count(),
            'activeLoansCount' => LoanRequest::query()->where('status', 'ON_LOAN')->count(),
            'recentLoans' => $recentLoans,
        ]);
    }
}
