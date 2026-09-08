<?php

namespace App\Services;

use App\Models\LoanLog;
use App\Models\LoanRequest;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanStatusService
{
    /**
     * Approve the pending loan request, reserve tool stock, and record the audit log.
     *
     * @param  array<int, int>  $itemsApproval  Map of loan item IDs to approved quantities.
     */
    public function approve(LoanRequest $loan, array $itemsApproval, ?string $adminNotes, User $admin): LoanRequest
    {
        $this->ensureStatus($loan, ['PENDING']);

        return DB::transaction(function () use ($loan, $itemsApproval, $adminNotes, $admin): LoanRequest {
            $loanItems = $loan->items()->get();

            $tools = Tool::query()
                ->whereIn('id', $loanItems->pluck('tool_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalRequested = 0;
            $totalApproved = 0;

            foreach ($loanItems as $loanItem) {
                $approvedQuantity = min(
                    (int) ($itemsApproval[$loanItem->id] ?? 0),
                    $loanItem->requested_quantity,
                );

                $tool = $tools->get($loanItem->tool_id);

                if ($tool === null) {
                    throw ValidationException::withMessages([
                        'items' => "Alat untuk item tiket {$loan->ticket_number} tidak ditemukan.",
                    ]);
                }

                if ($approvedQuantity > $tool->available_stock) {
                    throw ValidationException::withMessages([
                        'items' => "Stok alat {$tool->name} tidak mencukupi.",
                    ]);
                }

                $loanItem->update(['approved_quantity' => $approvedQuantity]);

                if ($approvedQuantity > 0) {
                    $tool->decrement('available_stock', $approvedQuantity);
                }

                $totalRequested += $loanItem->requested_quantity;
                $totalApproved += $approvedQuantity;
            }

            $newStatus = match (true) {
                $totalApproved === 0 => 'REJECTED',
                $totalApproved === $totalRequested => 'APPROVED',
                default => 'PARTIALLY_APPROVED',
            };

            $loan->update(['status' => $newStatus, 'admin_notes' => $adminNotes]);

            $this->logTransition($loan, $admin, 'APPROVAL', 'PENDING', $newStatus, $adminNotes);

            return $loan->refresh();
        });
    }

    /**
     * Reject the pending loan request without touching tool stock.
     */
    public function reject(LoanRequest $loan, ?string $adminNotes, User $admin): LoanRequest
    {
        $this->ensureStatus($loan, ['PENDING']);

        $loan->update(['status' => 'REJECTED', 'admin_notes' => $adminNotes]);

        $this->logTransition($loan, $admin, 'REJECTION', 'PENDING', 'REJECTED', $adminNotes);

        return $loan->refresh();
    }

    /**
     * Confirm the physical handover of an approved loan request to the student.
     */
    public function handover(LoanRequest $loan, User $admin): LoanRequest
    {
        $this->ensureStatus($loan, ['APPROVED', 'PARTIALLY_APPROVED']);

        $previousStatus = $loan->status;

        $loan->update(['status' => 'ON_LOAN']);

        $this->logTransition($loan, $admin, 'HANDOVER', $previousStatus, 'ON_LOAN', null);

        return $loan->refresh();
    }

    /**
     * Record the return of loaned items, applying stock effects per item condition.
     *
     * @param  array<int, array{id: int|string, condition: string}>  $itemsCondition  Map of loan item IDs to return conditions.
     */
    public function returnItems(LoanRequest $loan, array $itemsCondition, ?string $adminNotes, User $admin): LoanRequest
    {
        $this->ensureStatus($loan, ['ON_LOAN', 'OVERDUE']);

        $previousStatus = $loan->status;

        return DB::transaction(function () use ($loan, $itemsCondition, $adminNotes, $admin, $previousStatus): LoanRequest {
            $conditions = collect($itemsCondition)
                ->mapWithKeys(fn (array $item): array => [(int) $item['id'] => $item['condition']]);

            $loanItems = $loan->items()
                ->whereIn('id', $conditions->keys()->all())
                ->lockForUpdate()
                ->get();

            foreach ($loanItems as $loanItem) {
                $condition = $conditions[$loanItem->id];
                $quantity = (int) ($loanItem->approved_quantity ?? 0);

                $loanItem->update(['return_condition' => $condition]);

                if ($quantity < 1) {
                    continue;
                }

                $tool = Tool::query()
                    ->whereKey($loanItem->tool_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                match ($condition) {
                    'Baik' => $tool->increment('available_stock', $quantity),
                    'Rusak' => null,
                    'Hilang' => $tool->decrement('total_stock', $quantity),
                };
            }

            $loan->update(['status' => 'RETURNED']);

            $this->logTransition($loan, $admin, 'RETURN', $previousStatus, 'RETURNED', $adminNotes);

            return $loan->refresh();
        });
    }

    /**
     * Ensure the loan request is currently in one of the allowed statuses.
     *
     * @param  array<int, string>  $allowedStatuses
     */
    private function ensureStatus(LoanRequest $loan, array $allowedStatuses): void
    {
        if (! in_array($loan->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => "Aksi tidak dapat dilakukan karena status tiket saat ini adalah {$loan->status}.",
            ]);
        }
    }

    /**
     * Record a status transition in the loan audit log.
     */
    private function logTransition(LoanRequest $loan, User $admin, string $action, ?string $previousStatus, string $newStatus, ?string $notes): void
    {
        LoanLog::query()->create([
            'loan_request_id' => $loan->id,
            'user_id' => $admin->id,
            'action' => $action,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
        ]);
    }
}
