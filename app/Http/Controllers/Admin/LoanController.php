<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
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
     * Display the loan ticket queue, optionally filtered by status and laboratory.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $loans = LoanRequest::query()
            ->with(['user', 'items.tool', 'laboratory'])
            ->when($user->isLaboran(), fn ($query) => $query->where('laboratory_id', $user->laboratory_id))
            ->when($user->isKepalaLab() && $request->filled('laboratory_id'), fn ($query) => $query->where('laboratory_id', $request->integer('laboratory_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when(blank($request->input('status')) && $request->input('view') !== 'reports', fn ($query) => $query->where('status', '!=', 'CANCELLED'))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to_date')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.loans.index', [
            'loans' => $loans,
            'laboratories' => Laboratory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Display the review page of the specified loan ticket.
     */
    public function review(LoanRequest $loanRequest): View
    {
        $this->authorizeLaboratoryScope($loanRequest);

        $loanRequest->load(['user', 'items.tool', 'logs.user', 'laboratory']);

        return view('admin.loans.review', ['loan' => $loanRequest]);
    }

    /**
     * Trigger a state machine transition (approve/reject/handover) on the loan ticket.
     */
    public function updateStatus(Request $request, LoanRequest $loanRequest, LoanStatusService $service): RedirectResponse
    {
        $this->authorizeLaboratoryScope($loanRequest);

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
        $this->authorizeLaboratoryScope($loanRequest);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'distinct', 'exists:loan_items,id'],
            'items.*.condition' => ['nullable', 'string', Rule::in(['Baik', 'Rusak', 'Hilang'])],
            'items.*.returned_good_qty' => ['nullable', 'integer', 'min:0'],
            'items.*.returned_damaged_qty' => ['nullable', 'integer', 'min:0'],
            'items.*.returned_missing_qty' => ['nullable', 'integer', 'min:0'],
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

    /**
     * Ensure laborans may only manage tickets belonging to their own laboratory.
     */
    private function authorizeLaboratoryScope(LoanRequest $loanRequest): void
    {
        $user = auth()->user();

        if ($user->isLaboran() && $loanRequest->laboratory_id !== $user->laboratory_id) {
            abort(403, 'Anda tidak memiliki wewenang atas tiket laboratorium ini.');
        }
    }
}
