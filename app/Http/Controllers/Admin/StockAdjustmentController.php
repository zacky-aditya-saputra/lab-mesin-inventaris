<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    /**
     * Display the audit trail of stock adjustments and repairs, scoped by role and laboratory.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $adjustments = StockAdjustment::query()
            ->with(['tool.laboratory', 'tool.category', 'user'])
            ->when($user->isLaboran(), function ($query) use ($user): void {
                $query->whereHas('tool', fn ($q) => $q->where('laboratory_id', $user->laboratory_id));
            })
            ->when($user->isKepalaLab() && $request->filled('laboratory_id'), function ($query) use ($request): void {
                $query->whereHas('tool', fn ($q) => $q->where('laboratory_id', $request->integer('laboratory_id')));
            })
            ->when($request->filled('type'), function ($query) use ($request): void {
                $query->where('type', $request->input('type'));
            })
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->input('search');
                $query->whereHas('tool', function ($q) use ($search): void {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('code', 'LIKE', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.tools.adjustments', [
            'adjustments' => $adjustments,
            'laboratories' => Laboratory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
