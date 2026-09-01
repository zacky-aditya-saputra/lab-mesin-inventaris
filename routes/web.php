<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| A. Public Routes (Tanpa Auth)
|--------------------------------------------------------------------------
*/

Route::get('/', fn (): RedirectResponse => redirect()->route('catalog.index'));

Route::get('/katalog', fn (): string => 'Placeholder: halaman e-catalog publik.')
    ->name('catalog.index');

Route::get('/katalog/{slug}', fn (string $slug): string => "Placeholder: detail spesifikasi alat ({$slug}).")
    ->name('catalog.show');

/*
|--------------------------------------------------------------------------
| B. Authenticated Common Routes (Breeze)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| C. Student Protected Routes (middleware: auth + role:student)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:student'])->group(function (): void {
    Route::get('/peminjaman/baru', fn (): string => 'Placeholder: form pengajuan pinjam.')
        ->name('loans.create');

    Route::post('/peminjaman', fn (): string => 'Placeholder: simpan tiket pengajuan.')
        ->name('loans.store');

    Route::get('/peminjaman/riwayat', fn (): string => 'Placeholder: riwayat pengajuan mahasiswa.')
        ->name('loans.history');

    Route::get('/peminjaman/{ticket_number}', fn (string $ticket_number): string => "Placeholder: detail & pelacakan status tiket ({$ticket_number}).")
        ->name('loans.show');
});

/*
|--------------------------------------------------------------------------
| D. Admin Protected Routes (middleware: auth + role:admin, prefix: admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', fn (): string => 'Placeholder: admin dashboard.')
            ->name('dashboard');

        Route::prefix('categories')->name('categories.')->group(function (): void {
            Route::get('/', fn (): string => 'Placeholder: daftar kategori alat.')
                ->name('index');

            Route::post('/', fn (): string => 'Placeholder: simpan kategori alat.')
                ->name('store');

            Route::put('/{category}', fn (string $category): string => "Placeholder: perbarui kategori ({$category}).")
                ->name('update');

            Route::delete('/{category}', fn (string $category): string => "Placeholder: hapus kategori ({$category}).")
                ->name('destroy');
        });

        Route::prefix('tools')->name('tools.')->group(function (): void {
            Route::get('/', fn (): string => 'Placeholder: daftar alat.')
                ->name('index');

            Route::get('/create', fn (): string => 'Placeholder: form tambah alat.')
                ->name('create');

            Route::post('/', fn (): string => 'Placeholder: simpan alat.')
                ->name('store');

            Route::get('/{tool}/edit', fn (string $tool): string => "Placeholder: form ubah alat ({$tool}).")
                ->name('edit');

            Route::put('/{tool}', fn (string $tool): string => "Placeholder: perbarui alat ({$tool}).")
                ->name('update');

            Route::delete('/{tool}', fn (string $tool): string => "Placeholder: hapus alat ({$tool}).")
                ->name('destroy');
        });
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        Route::get('/loans', fn (): string => 'Placeholder: antrean tiket peminjaman.')
            ->name('loans.index');

        Route::get('/loans/{loanRequest}', fn (string $loanRequest): string => "Placeholder: review & partial approval tiket ({$loanRequest}).")
            ->name('loans.review');

        Route::patch('/loans/{loanRequest}/status', fn (string $loanRequest): string => "Placeholder: state machine trigger tiket ({$loanRequest}).")
            ->name('loans.update-status');

        Route::post('/loans/{loanRequest}/return', fn (string $loanRequest): string => "Placeholder: konfirmasi pengembalian kondisi fisik tiket ({$loanRequest}).")
            ->name('loans.return');
    });
