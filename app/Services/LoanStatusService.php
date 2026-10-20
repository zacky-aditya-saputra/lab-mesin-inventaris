<?php

namespace App\Services;

use App\Models\LoanLog;
use App\Models\LoanRequest;
use App\Models\StockAdjustment;
use App\Models\Tool;
use App\Models\User;
use App\Notifications\LoanStatusChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanStatusService
{
    /**
     * Approve the pending loan request, reserve tool stock, and record the audit log.
     *
     * Uses atomic conditional updates instead of pessimistic locking so that
     * concurrent approvals can never oversell the tool stock (SQLite-safe).
     *
     * @param  array<int, int>  $itemsApproval  Map of loan item IDs to approved quantities.
     */
    public function approve(LoanRequest $loan, array $itemsApproval, ?string $adminNotes, User $admin): LoanRequest
    {
        $this->ensureStatus($loan, ['PENDING']);

        return DB::transaction(function () use ($loan, $itemsApproval, $adminNotes, $admin): LoanRequest {
            $loanItems = $loan->items()->get();

            // Guard 1: claim the ticket atomically from PENDING so two officers
            // can never process the same ticket at the same time.
            $affectedTicket = LoanRequest::query()
                ->whereKey($loan->id)
                ->where('status', 'PENDING')
                ->update([
                    'status' => 'APPROVED',
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                    'admin_notes' => $adminNotes,
                ]);

            if ($affectedTicket === 0) {
                throw ValidationException::withMessages([
                    'status' => 'Tiket sudah diproses oleh petugas lain.',
                ]);
            }

            $totalRequested = 0;
            $totalApproved = 0;

            foreach ($loanItems->sortBy('tool_id') as $loanItem) {
                $approvalData = $itemsApproval[$loanItem->id] ?? 0;
                $approvedQuantity = is_array($approvalData)
                    ? (int) ($approvalData['quantity'] ?? 0)
                    : (int) $approvalData;
                $approvedQuantity = min($approvedQuantity, $loanItem->requested_quantity);

                $reason = is_array($approvalData) ? ($approvalData['reason'] ?? null) : null;
                $itemStatus = $approvedQuantity > 0
                    ? ($approvedQuantity === $loanItem->requested_quantity ? 'APPROVED' : 'PARTIALLY_APPROVED')
                    : 'REJECTED';

                $loanItem->update([
                    'approved_quantity' => $approvedQuantity,
                    'status' => $itemStatus,
                    'rejection_reason' => $reason,
                ]);

                if ($approvedQuantity > 0) {
                    // Guard 2: conditional atomic stock decrement — only
                    // succeeds when the available stock is still sufficient.
                    $affected = DB::table('tools')
                        ->where('id', $loanItem->tool_id)
                        ->where('available_stock', '>=', $approvedQuantity)
                        ->decrement('available_stock', $approvedQuantity);

                    if ($affected === 0) {
                        $toolName = Tool::query()
                            ->whereKey($loanItem->tool_id)
                            ->value('name') ?? $loanItem->tool_id;

                        throw ValidationException::withMessages([
                            'items' => "Stok alat {$toolName} tidak mencukupi.",
                        ]);
                    }
                }

                $totalRequested += $loanItem->requested_quantity;
                $totalApproved += $approvedQuantity;
            }

            $newStatus = match (true) {
                $totalApproved === 0 => 'REJECTED',
                $totalApproved === $totalRequested => 'APPROVED',
                default => 'PARTIALLY_APPROVED',
            };

            $this->fillBorrowerSnapshot($loan);

            $loan->update(['status' => $newStatus]);

            $this->logTransition($loan, $admin, 'APPROVAL', 'PENDING', $newStatus, $adminNotes);

            $refreshed = $loan->refresh();

            $refreshed->user?->notify(new LoanStatusChangedNotification($refreshed));

            return $refreshed;
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

        $refreshed = $loan->refresh();

        $refreshed->user?->notify(new LoanStatusChangedNotification($refreshed));

        return $refreshed;
    }

    /**
     * Cancel the pending ticket on behalf of its owner without touching tool stock.
     */
    public function cancel(LoanRequest $loan, User $user, ?string $reason): LoanRequest
    {
        $this->ensureStatus($loan, ['PENDING']);

        $affected = LoanRequest::query()
            ->whereKey($loan->id)
            ->where('status', 'PENDING')
            ->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
            ]);

        if ($affected === 0) {
            throw ValidationException::withMessages([
                'status' => 'Tiket sudah diproses oleh petugas lain.',
            ]);
        }

        $this->logTransition($loan, $user, 'CANCELLATION', 'PENDING', 'CANCELLED', $reason);

        $refreshed = $loan->refresh();

        $refreshed->user?->notify(new LoanStatusChangedNotification($refreshed));

        return $refreshed;
    }

    /**
     * Confirm the physical handover of an approved loan request to the student.
     */
    public function handover(LoanRequest $loan, User $admin): LoanRequest
    {
        $this->ensureStatus($loan, ['APPROVED', 'PARTIALLY_APPROVED']);

        $previousStatus = $loan->status;

        $loan->update(['status' => 'ON_LOAN', 'handed_over_at' => now()]);

        $this->logTransition($loan, $admin, 'HANDOVER', $previousStatus, 'ON_LOAN', null);

        $refreshed = $loan->refresh();

        $refreshed->user?->notify(new LoanStatusChangedNotification($refreshed));

        return $refreshed;
    }

    /**
     * Record the return of loaned items, applying stock effects per item condition.
     * Supports both summary condition strings ('Baik', 'Rusak', 'Hilang') and split quantities.
     *
     * @param  array<int, array<string, mixed>>  $itemsCondition  Map of loan item IDs to return conditions or split quantities.
     */
    public function returnItems(LoanRequest $loan, array $itemsCondition, ?string $adminNotes, User $admin): LoanRequest
    {
        $this->ensureStatus($loan, ['ON_LOAN', 'OVERDUE']);

        $previousStatus = $loan->status;

        return DB::transaction(function () use ($loan, $itemsCondition, $adminNotes, $admin, $previousStatus): LoanRequest {
            $itemsMap = collect($itemsCondition)->keyBy(fn ($item) => (int) $item['id']);

            $loanItems = $loan->items()
                ->whereIn('id', $itemsMap->keys()->all())
                ->get();

            foreach ($loanItems as $loanItem) {
                $itemData = $itemsMap[$loanItem->id] ?? [];
                $approvedQty = (int) ($loanItem->approved_quantity ?? 0);

                if (isset($itemData['returned_good_qty']) || isset($itemData['returned_damaged_qty']) || isset($itemData['returned_missing_qty'])) {
                    $good = (int) ($itemData['returned_good_qty'] ?? 0);
                    $damaged = (int) ($itemData['returned_damaged_qty'] ?? 0);
                    $missing = (int) ($itemData['returned_missing_qty'] ?? 0);

                    if ($good + $damaged + $missing !== $approvedQty && $approvedQty > 0) {
                        $toolName = Tool::withTrashed()->whereKey($loanItem->tool_id)->value('name') ?? 'Alat';
                        throw ValidationException::withMessages([
                            'items' => "Total unit pengembalian untuk {$toolName} ({$good}+{$damaged}+{$missing}) harus sama dengan unit disetujui ({$approvedQty}).",
                        ]);
                    }

                    $conditionSummary = match (true) {
                        $good === $approvedQty => 'Baik',
                        $damaged === $approvedQty => 'Rusak',
                        $missing === $approvedQty => 'Hilang',
                        default => 'Parsial/Campuran',
                    };
                } else {
                    $conditionSummary = $itemData['condition'] ?? 'Baik';
                    $good = $conditionSummary === 'Baik' ? $approvedQty : 0;
                    $damaged = $conditionSummary === 'Rusak' ? $approvedQty : 0;
                    $missing = $conditionSummary === 'Hilang' ? $approvedQty : 0;
                }

                $loanItem->update([
                    'return_condition' => $conditionSummary,
                    'returned_good_qty' => $good,
                    'returned_damaged_qty' => $damaged,
                    'returned_missing_qty' => $missing,
                ]);

                if ($approvedQty < 1) {
                    continue;
                }

                $tool = Tool::withTrashed()->findOrFail($loanItem->tool_id);

                if ($good > 0) {
                    $tool->increment('available_stock', $good);
                }
                if ($damaged > 0) {
                    $tool->increment('damaged_stock', $damaged);
                }
                if ($missing > 0) {
                    $tool->decrement('total_stock', $missing);
                }
            }

            $loan->update(['status' => 'RETURNED', 'returned_at' => now()]);

            $this->logTransition($loan, $admin, 'RETURN', $previousStatus, 'RETURNED', $adminNotes);

            $refreshed = $loan->refresh();

            $refreshed->user?->notify(new LoanStatusChangedNotification($refreshed));

            return $refreshed;
        });
    }

    /**
     * Mark an ON_LOAN ticket as OVERDUE, triggered by the scheduled check command.
     */
    public function markOverdue(LoanRequest $loan): LoanRequest
    {
        $this->ensureStatus($loan, ['ON_LOAN']);

        $loan->update(['status' => 'OVERDUE']);

        LoanLog::query()->create([
            'loan_request_id' => $loan->id,
            'user_id' => null,
            'action' => 'OVERDUE_DETECTION',
            'previous_status' => 'ON_LOAN',
            'new_status' => 'OVERDUE',
            'notes' => 'Otomatis ditandai terlambat oleh scheduler sistem.',
        ]);

        $refreshed = $loan->refresh();

        $refreshed->user?->notify(new LoanStatusChangedNotification($refreshed));

        return $refreshed;
    }

    /**
     * Move the given quantity of a tool from damaged stock back to available stock
     * and record the manual stock adjustment for auditing.
     */
    public function markAsRepaired(Tool $tool, int $quantity, User $admin, string $notes): void
    {
        DB::transaction(function () use ($tool, $quantity, $admin, $notes): void {
            $tool->refresh();

            $beforeAvailable = (int) $tool->available_stock;
            $beforeDamaged = (int) $tool->damaged_stock;

            $tool->decrement('damaged_stock', $quantity);
            $tool->increment('available_stock', $quantity);

            StockAdjustment::query()->create([
                'tool_id' => $tool->id,
                'user_id' => $admin->id,
                'type' => 'repair',
                'delta' => $quantity,
                'before_available' => $beforeAvailable,
                'after_available' => $beforeAvailable + $quantity,
                'before_damaged' => $beforeDamaged,
                'after_damaged' => $beforeDamaged - $quantity,
                'reason' => $notes,
            ]);

            $tool->refresh();
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
     * Fill the borrower identity snapshot from the current user profile when empty.
     */
    private function fillBorrowerSnapshot(LoanRequest $loan): void
    {
        $user = $loan->user;

        if ($user === null) {
            return;
        }

        $snapshot = collect([
            'borrower_name' => $user->name,
            'borrower_identity_number' => $user->identity_number,
            'borrower_identity_type' => $user->identity_type,
            'borrower_institution' => $user->institution,
            'borrower_phone' => $user->phone_number,
        ])
            ->filter(fn ($value, string $column): bool => blank($loan->{$column}) && filled($value))
            ->all();

        if ($snapshot !== []) {
            $loan->forceFill($snapshot)->save();
        }
    }

    /**
     * Record a status transition in the loan audit log.
     */
    private function logTransition(LoanRequest $loan, User $actor, string $action, ?string $previousStatus, string $newStatus, ?string $notes): void
    {
        LoanLog::query()->create([
            'loan_request_id' => $loan->id,
            'user_id' => $actor->id,
            'action' => $action,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
        ]);
    }
}
