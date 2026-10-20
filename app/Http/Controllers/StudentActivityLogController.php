<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\StudentActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentActivityLogController extends Controller
{
    /**
     * Display a listing of the authenticated student's lab activity logs.
     */
    public function index(Request $request): View
    {
        $logs = $request->user()->activityLogs()
            ->with(['laboratory', 'verifier'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest('activity_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('activity_logs.index', compact('logs'));
    }

    /**
     * Show the form for creating a new activity log entry.
     */
    public function create(): View
    {
        $laboratories = Laboratory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('activity_logs.create', compact('laboratories'));
    }

    /**
     * Store a newly created activity log entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'laboratory_id' => ['required', 'integer', 'exists:laboratories,id'],
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i', 'after:time_in'],
            'activity_type' => ['required', 'string', Rule::in(['Praktikum', 'Penelitian', 'Tugas Akhir', 'Mandiri'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'activity_date.before_or_equal' => 'Tanggal kegiatan tidak boleh di masa mendatang.',
            'time_out.after' => 'Jam keluar harus setelah jam masuk.',
        ]);

        $request->user()->activityLogs()->create([
            'laboratory_id' => $validated['laboratory_id'],
            'activity_date' => $validated['activity_date'],
            'time_in' => $validated['time_in'],
            'time_out' => $validated['time_out'],
            'activity_type' => $validated['activity_type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'PENDING',
        ]);

        return redirect()->route('activity-logs.index')
            ->with('success', 'Catatan kegiatan laboratorium berhasil disimpan dan menunggu verifikasi laboran.');
    }

    /**
     * Remove the pending activity log entry.
     */
    public function destroy(Request $request, StudentActivityLog $activityLog): RedirectResponse
    {
        if ($activityLog->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki hak atas catatan ini.');
        }

        if ($activityLog->status !== 'PENDING') {
            return back()->with('error', 'Catatan yang sudah diverifikasi atau ditolak tidak dapat dihapus.');
        }

        $activityLog->delete();

        return redirect()->route('activity-logs.index')
            ->with('success', 'Catatan kegiatan laboratorium berhasil dihapus.');
    }
}
