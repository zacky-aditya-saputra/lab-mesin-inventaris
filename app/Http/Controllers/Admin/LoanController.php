<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use App\Services\LoanStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoanController extends Controller
{
    /**
     * Display the loan ticket queue, optionally filtered by status.
     */
    public function index(Request $request): View
    {
        $loans = LoanRequest::query()
            ->with(['user', 'items.tool'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate(10);

        return view('admin.loans.index', compact('loans'));
    }

    /**
     * Display the review page of the specified loan ticket.
     */
    public function review(LoanRequest $loanRequest): View
    {
        $loanRequest->load(['user', 'items.tool', 'logs.user']);

        return view('admin.loans.review', ['loan' => $loanRequest]);
    }

    /**
     * Trigger a state machine transition (approve/reject/handover) on the loan ticket.
     */
    public function updateStatus(Request $request, LoanRequest $loanRequest, LoanStatusService $service): RedirectResponse
    {
        return match ($request->input('action')) {
            'approve' => $this->approveLoan($request, $loanRequest, $service),
            'reject' => $this->rejectLoan($request, $loanRequest, $service),
            'handover' => $this->handoverLoan($request, $loanRequest, $service),
            default => throw ValidationException::withMessages([
                'action' => 'Aksi status tiket tidak dikenali.',
            ]),
        };
    }

    /**
     * Record the physical return of the loaned items with their conditions.
     */
    public function returnLoan(Request $request, LoanRequest $loanRequest, LoanStatusService $service): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:loan_items,id'],
            'items.*.condition' => ['required', 'string', Rule::in(['Baik', 'Rusak', 'Hilang'])],
        ]);

        $service->returnItems($loanRequest, $validated['items'], $request->input('admin_notes'), $request->user());

        return back()->with('success', "Pengembalian alat untuk tiket {$loanRequest->ticket_number} berhasil dicatat.");
    }

    /**
     * Approve the ticket (fully or partially) and reserve the tool stock.
     */
    private function approveLoan(Request $request, LoanRequest $loanRequest, LoanStatusService $service): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:loan_items,id'],
            'items.*.approved_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $itemsApproval = collect($validated['items'])
            ->mapWithKeys(fn (array $item): array => [(int) $item['id'] => (int) $item['approved_quantity']])
            ->all();

        $service->approve($loanRequest, $itemsApproval, $request->input('admin_notes'), $request->user());

        return back()->with('success', "Tiket {$loanRequest->ticket_number} berhasil disetujui dan stok telah direservasi.");
    }

    /**
     * Reject the ticket without touching the tool stock.
     */
    private function rejectLoan(Request $request, LoanRequest $loanRequest, LoanStatusService $service): RedirectResponse
    {
        $service->reject($loanRequest, $request->input('admin_notes'), $request->user());

        return back()->with('success', "Tiket {$loanRequest->ticket_number} ditolak.");
    }

    /**
     * Confirm the physical handover of the tools to the student.
     */
    private function handoverLoan(Request $request, LoanRequest $loanRequest, LoanStatusService $service): RedirectResponse
    {
        $service->handover($loanRequest, $request->user());

        return back()->with('success', 'Serah terima fisik dikonfirmasi. Status tiket kini ON_LOAN.');
    }
}
