<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'code', 'slug', 'description'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * Get the category's code, falling back to the initials of its name
     * or, for a single word, its first consonants.
     */
    public function getCodeOrDefaultAttribute(): string
    {
        if (filled($this->code)) {
            return $this->code;
        }

        $words = collect(preg_split('/\s+/', trim((string) $this->name)) ?: [])
            ->map(fn (string $word) => (string) preg_replace('/[^A-Za-z]/', '', $word))
            ->filter()
            ->values();

        if ($words->isEmpty()) {
            return '';
        }

        if ($words->count() > 1) {
            return $words
                ->take(3)
                ->map(fn (string $word) => Str::upper(mb_substr($word, 0, 1)))
                ->implode('');
        }

        $letters = Str::upper((string) $words->first());
        $consonants = str_replace(['A', 'I', 'U', 'E', 'O'], '', $letters);

        return Str::substr($consonants === '' ? $letters : $consonants, 0, 3);
    }

    public function tools(): HasMany
    {
        return $this->hasMany(Tool::class);
    }

    /**
     * Determine whether the category still holds inventory tools.
     */
    public function hasTools(): bool
    {
        return $this->tools()->exists();
    }
}
