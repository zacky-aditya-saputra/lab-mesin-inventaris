<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tool_id', 'user_id', 'type', 'delta', 'before_available', 'after_available', 'before_damaged', 'after_damaged', 'reason', 'created_at'])]
class StockAdjustment extends Model
{
    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
