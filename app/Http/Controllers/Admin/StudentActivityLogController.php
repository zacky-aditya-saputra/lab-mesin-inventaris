<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\StudentActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentActivityLogController extends Controller
{
    /**
     * Display a listing of student activity logs scoped to staff role & laboratory.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $logs = StudentActivityLog::query()
            ->with(['user', 'laboratory', 'verifier'])
            ->when($user->isLaboran(), fn ($q) => $q->where('laboratory_id', $user->laboratory_id))
            ->when($user->isKepalaLab() && $request->filled('laboratory_id'), fn ($q) => $q->where('laboratory_id', $request->integer('laboratory_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('activity_type'), fn ($q) => $q->where('activity_type', $request->input('activity_type')))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('activity_date', '>=', $request->input('from_date')))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('activity_date', '<=', $request->input('to_date')))
            ->latest('activity_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $laboratories = Laboratory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('admin.activity_logs.index', compact('logs', 'laboratories'));
    }

    /**
     * Verify a student's activity log entry.
     */
    public function verify(Request $request, StudentActivityLog $activityLog): RedirectResponse
    {
        $this->authorizeStaffScope($activityLog);

        $activityLog->update([
            'status' => 'VERIFIED',
            'verified_by' => $request->user()->id,
        ]);

        return back()->with('success', "Catatan kegiatan {$activityLog->title} milik {$activityLog->user->name} berhasil diverifikasi.");
    }

    /**
     * Reject a student's activity log entry.
     */
    public function reject(Request $request, StudentActivityLog $activityLog): RedirectResponse
    {
        $this->authorizeStaffScope($activityLog);

        $activityLog->update([
            'status' => 'REJECTED',
            'verified_by' => $request->user()->id,
        ]);

        return back()->with('success', "Catatan kegiatan {$activityLog->title} telah ditolak.");
    }

    /**
     * Ensure laborans may only verify activity logs belonging to their own laboratory.
     */
    private function authorizeStaffScope(StudentActivityLog $activityLog): void
    {
        $user = auth()->user();

        if ($user->isLaboran() && $activityLog->laboratory_id !== $user->laboratory_id) {
            abort(403, 'Anda tidak memiliki wewenang untuk memverifikasi catatan kegiatan di luar laboratorium Anda.');
        }
    }
}
