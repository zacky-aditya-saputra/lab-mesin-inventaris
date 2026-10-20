<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'laboratory_id', 'ticket_number', 'start_date', 'end_date', 'purpose', 'identity_card_path', 'loan_letter_path', 'status', 'admin_notes', 'approved_by', 'approved_at', 'handed_over_at', 'returned_at', 'cancelled_at', 'borrower_name', 'borrower_identity_number', 'borrower_identity_type', 'borrower_institution', 'borrower_phone'])]
class LoanRequest extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'approved_at' => 'datetime',
            'handed_over_at' => 'datetime',
            'returned_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LoanLog::class);
    }
}
