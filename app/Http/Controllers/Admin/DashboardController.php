<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Laboratory;
use App\Models\LoanRequest;
use App\Models\Tool;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with summary metrics scoped to the user's laboratory role.
     */
    public function index(): View
    {
        $user = auth()->user();

        $toolQuery = Tool::query()->where('is_active', true);
        $loanQuery = LoanRequest::query();

        if ($user->isLaboran()) {
            $toolQuery->where('laboratory_id', $user->laboratory_id);
            $loanQuery->where('laboratory_id', $user->laboratory_id);
        }

        $recentLoans = (clone $loanQuery)
            ->with(['user', 'items.tool'])
            ->latest()
            ->limit(5)
            ->get();

        $recentTools = (clone $toolQuery)
            ->with(['category', 'laboratory'])
            ->latest()
            ->limit(5)
            ->get();

        $categorySummary = Category::query()
            ->withCount(['tools' => fn ($query) => $query->when(
                $user->isLaboran(),
                fn ($inner) => $inner->where('laboratory_id', $user->laboratory_id),
            )])
            ->orderBy('name')
            ->get();

        $laboratoryBreakdown = null;

        if ($user->isKepalaLab()) {
            $laboratoryBreakdown = Laboratory::query()
                ->withCount([
                    'tools as active_tools_count' => fn ($query) => $query->where('is_active', true),
                    'loanRequests as pending_loans_count' => fn ($query) => $query->where('status', 'PENDING'),
                    'loanRequests as on_loan_loans_count' => fn ($query) => $query->where('status', 'ON_LOAN'),
                    'loanRequests as overdue_loans_count' => fn ($query) => $query->where('status', 'OVERDUE'),
                ])
                ->orderBy('name')
                ->get();
        }

        return view('admin.dashboard', [
            'totalTools' => (clone $toolQuery)->count(),
            'totalCategories' => Category::query()->count(),
            'totalStock' => (int) (clone $toolQuery)->sum('total_stock'),
            'pendingLoansCount' => (clone $loanQuery)->where('status', 'PENDING')->count(),
            'overdueLoansCount' => (clone $loanQuery)->where('status', 'OVERDUE')->count(),
            'activeLoansCount' => (clone $loanQuery)->where('status', 'ON_LOAN')->count(),
            'recentLoans' => $recentLoans,
            'recentTools' => $recentTools,
            'categorySummary' => $categorySummary,
            'managedLaboratory' => $user->isLaboran() ? $user->laboratory : null,
            'laboratoryBreakdown' => $laboratoryBreakdown,
        ]);
    }
}
