<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'loan_request_id',
    'tool_id',
    'requested_quantity',
    'approved_quantity',
    'status',
    'rejection_reason',
    'return_condition',
    'returned_good_qty',
    'returned_damaged_qty',
    'returned_missing_qty',
])]
class LoanItem extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requested_quantity' => 'integer',
            'approved_quantity' => 'integer',
            'returned_good_qty' => 'integer',
            'returned_damaged_qty' => 'integer',
            'returned_missing_qty' => 'integer',
        ];
    }

    public function loanRequest(): BelongsTo
    {
        return $this->belongsTo(LoanRequest::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }
}
