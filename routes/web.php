<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\ToolController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| A. Public Routes (Tanpa Auth)
|--------------------------------------------------------------------------
*/

Route::get('/', fn (): RedirectResponse => redirect()->route('catalog.index'));

Route::get('/katalog', [CatalogController::class, 'index'])
    ->name('catalog.index');

Route::get('/katalog/{slug}', [CatalogController::class, 'show'])
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
    Route::get('/peminjaman/baru', [LoanController::class, 'create'])
        ->name('loans.create');

    Route::post('/peminjaman', [LoanController::class, 'store'])
        ->name('loans.store');

    Route::get('/peminjaman/riwayat', [LoanController::class, 'history'])
        ->name('loans.history');

    Route::get('/peminjaman/{ticket_number}', [LoanController::class, 'show'])
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
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('tools', ToolController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::get('/loans', [AdminLoanController::class, 'index'])
            ->name('loans.index');

        Route::get('/loans/{loanRequest}', [AdminLoanController::class, 'review'])
            ->name('loans.review');

        Route::patch('/loans/{loanRequest}/status', [AdminLoanController::class, 'updateStatus'])
            ->name('loans.update-status');

        Route::post('/loans/{loanRequest}/return', [AdminLoanController::class, 'returnLoan'])
            ->name('loans.return');
    });
