<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'identity_number', 'phone_number', 'role', 'is_active', 'laboratory_id', 'identity_type', 'institution', 'identity_card_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Clean up privately stored documents when a user account is deleted.
     *
     * Loan requests are cascade-deleted at the database level, so their
     * Eloquent events never fire and the letter cleanup must happen here.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->identity_card_path) {
                if (Storage::disk('local')->exists($user->identity_card_path)) {
                    Storage::disk('local')->delete($user->identity_card_path);
                }

                if (Storage::disk('public')->exists($user->identity_card_path)) {
                    Storage::disk('public')->delete($user->identity_card_path);
                }
            }

            $user->loanRequests()
                ->pluck('loan_letter_path')
                ->filter()
                ->unique()
                ->each(function (string $path): void {
                    if (Storage::disk('local')->exists($path)) {
                        Storage::disk('local')->delete($path);
                    }

                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                });
        });
    }

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function loanRequests(): HasMany
    {
        return $this->hasMany(LoanRequest::class);
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(StudentActivityLog::class);
    }

    public function isKlien(): bool
    {
        return in_array($this->role, ['klien', 'student'], true);
    }

    public function isLaboran(): bool
    {
        return $this->role === 'laboran';
    }

    public function isKepalaLab(): bool
    {
        return in_array($this->role, ['kepala_lab', 'admin'], true);
    }

    public function roleLabel(): string
    {
        if ($this->isKepalaLab()) {
            return 'Kepala Lab';
        }

        if ($this->isLaboran()) {
            $labName = $this->laboratory?->name;

            return $labName
                ? 'Laboran '.str_replace('Laboratorium', 'Lab', $labName)
                : 'Laboran';
        }

        return match ($this->identity_type) {
            'umum' => 'Umum',
            'dosen' => 'Dosen',
            'pegawai' => 'Staf/Pegawai',
            default => 'Mahasiswa',
        };
    }
}
