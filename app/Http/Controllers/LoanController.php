<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Models\LoanRequest;
use App\Models\Tool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoanController extends Controller
{
    /**
     * Display the loan request form with all active tools.
     */
    public function create(): View
    {
        $toolsList = Tool::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Tool $tool): array => [
                'id' => $tool->id,
                'code' => $tool->code,
                'name' => $tool->name,
                'available_stock' => $tool->available_stock,
                'image' => $tool->image_path ? asset('storage/'.$tool->image_path) : null,
            ])
            ->all();

        return view('loans.create', compact('toolsList'));
    }

    /**
     * Store a newly submitted loan request from the authenticated client (mahasiswa or general public).
     */
    public function store(StoreLoanRequest $request): RedirectResponse
    {
        $path = $request->file('identity_card')->store('identity_cards', 'public');

        $loanRequest = DB::transaction(function () use ($request, $path): LoanRequest {
            $user = $request->user();

            if (blank($user->phone_number) && $request->filled('borrower_phone')) {
                $user->update(['phone_number' => $request->input('borrower_phone')]);
            }

            if (blank($user->identity_number) && $request->filled('borrower_identity')) {
                $user->update(['identity_number' => $request->input('borrower_identity')]);
            }

            $loanRequest = LoanRequest::query()->create([
                'user_id' => $user->id,
                'ticket_number' => $this->generateTicketNumber(),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'purpose' => $this->buildClientTag($request).' '.$request->input('purpose'),
                'identity_card_path' => $path,
                'status' => 'PENDING',
            ]);

            foreach ($request->input('items') as $item) {
                $loanRequest->items()->create([
                    'tool_id' => $item['tool_id'],
                    'requested_quantity' => $item['quantity'],
                    'approved_quantity' => null,
                    'return_condition' => null,
                ]);
            }

            return $loanRequest;
        });

        return redirect()
            ->route('loans.history')
            ->with('success', "Pengajuan peminjaman berhasil dikirim dengan nomor tiket {$loanRequest->ticket_number}. Silakan tunggu verifikasi admin.");
    }

    /**
     * Display the loan request history of the authenticated student.
     */
    public function history(): View
    {
        $loanRequests = LoanRequest::query()
            ->with(['items.tool.category'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('loans.history', compact('loanRequests'));
    }

    /**
     * Track the specified loan request ticket owned by the authenticated student.
     */
    public function show(string $ticket_number): RedirectResponse
    {
        LoanRequest::query()
            ->with(['items.tool.category', 'logs'])
            ->where('ticket_number', $ticket_number)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return redirect()->route('loans.history');
    }

    /**
     * Generate a unique ticket number for a new loan request.
     */
    private function generateTicketNumber(): string
    {
        do {
            $ticketNumber = 'LOAN-'.date('Ymd').'-'.strtoupper(Str::random(4));
        } while (LoanRequest::query()->where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    /**
     * Build the borrower category tag prefixed to the loan purpose.
     */
    private function buildClientTag(StoreLoanRequest $request): string
    {
        $institution = $request->input('institution');

        if ($request->input('client_type') === 'mahasiswa') {
            return "[Mahasiswa - {$institution}]";
        }

        if ($request->input('client_type') === 'umum' && $request->input('affiliation_type') === 'individu') {
            return "[Umum/Individu - {$institution}]";
        }

        return "[Umum/Instansi - {$institution}]";
    }
}
