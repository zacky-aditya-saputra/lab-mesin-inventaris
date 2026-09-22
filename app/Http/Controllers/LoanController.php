<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Models\Laboratory;
use App\Models\LoanRequest;
use App\Models\Tool;
use App\Models\User;
use App\Notifications\NewLoanRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoanController extends Controller
{
    /**
     * Display the loan request form with laboratories and all active tools.
     */
    public function create(): View
    {
        $laboratories = Laboratory::query()
            ->where('is_active', true)
            ->whereHas('tools', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();

        $toolsList = Tool::query()
            ->with(['category', 'laboratory'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Tool $tool): array => [
                'id' => $tool->id,
                'code' => $tool->code,
                'name' => $tool->name,
                'laboratory_id' => $tool->laboratory_id,
                'available_stock' => $tool->available_stock,
                'image' => $tool->image_path ? asset('storage/'.$tool->image_path) : null,
            ])
            ->all();

        $user = auth()->user();

        $isProfileComplete = filled($user->identity_number)
            && filled($user->phone_number)
            && filled($user->identity_card_path);

        return view('loans.create', compact('toolsList', 'laboratories', 'user', 'isProfileComplete'));
    }

    /**
     * Store a newly submitted loan request from the authenticated client (mahasiswa or general public).
     */
    public function store(StoreLoanRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->filled('phone_number')) {
            $user->phone_number = $request->input('phone_number');
        }

        if ($request->hasFile('identity_card_file')) {
            if (filled($user->identity_card_path)) {
                Storage::disk('public')->delete($user->identity_card_path);
            }

            $user->identity_card_path = $request->file('identity_card_file')->store('identity_cards', 'public');
        }

        if ($user->isDirty()) {
            $user->save();
        }

        if (blank($user->identity_card_path)) {
            return back()
                ->withInput()
                ->with('error', 'Dokumen identitas (KTM/KTP) belum terunggah di profil Anda. Silakan lengkapi profil terlebih dahulu.');
        }

        $loanLetterPath = $request->file('loan_letter')->store('loan_letters', 'public');

        $loanRequest = DB::transaction(function () use ($request, $user, $loanLetterPath): LoanRequest {
            $loanRequest = LoanRequest::query()->create([
                'user_id' => $user->id,
                'laboratory_id' => (int) $request->input('laboratory_id'),
                'ticket_number' => $this->generateTicketNumber(),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'purpose' => $request->input('purpose'),
                'identity_card_path' => $user->identity_card_path,
                'loan_letter_path' => $loanLetterPath,
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

        $laborans = User::query()
            ->where('role', 'laboran')
            ->where('laboratory_id', $loanRequest->laboratory_id)
            ->get();

        Notification::send($laborans, new NewLoanRequestNotification($loanRequest));

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
}
