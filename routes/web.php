<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LaboranController;
use App\Http\Controllers\Admin\LaboratoryController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\StudentActivityLogController as AdminStudentActivityLogController;
use App\Http\Controllers\Admin\ToolController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentActivityLogController;
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

    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('notifications.mark-all-read');

    Route::post('notifications/{id}/mark-read', [NotificationController::class, 'markRead'])
        ->name('notifications.mark-read');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| C. Student Protected Routes (middleware: auth + role:klien,student)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:klien,student'])->group(function (): void {
    Route::get('/peminjaman/baru', [LoanController::class, 'create'])
        ->name('loans.create');

    Route::post('/peminjaman', [LoanController::class, 'store'])
        ->name('loans.store');

    Route::get('/peminjaman/riwayat', [LoanController::class, 'history'])
        ->name('loans.history');

    Route::patch('/peminjaman/{loan}/batal', [LoanController::class, 'cancel'])
        ->name('loans.cancel');

    Route::get('/peminjaman/{ticket_number}', [LoanController::class, 'show'])
        ->name('loans.show');

    Route::get('/logbook', [StudentActivityLogController::class, 'index'])
        ->name('activity-logs.index');
    Route::get('/logbook/tambah', [StudentActivityLogController::class, 'create'])
        ->name('activity-logs.create');
    Route::post('/logbook', [StudentActivityLogController::class, 'store'])
        ->name('activity-logs.store');
    Route::delete('/logbook/{activityLog}', [StudentActivityLogController::class, 'destroy'])
        ->name('activity-logs.destroy');
});

/*
|--------------------------------------------------------------------------
| Document Protected Routes (middleware: auth - protected streaming)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::get('/documents/identity/{user}', [DocumentController::class, 'showIdentityCard'])
        ->name('documents.identity');

    Route::get('/documents/loan-letter/{loan}', [DocumentController::class, 'showLoanLetter'])
        ->name('documents.loan-letter');
});

/*
|--------------------------------------------------------------------------
| D. Admin Protected Routes (middleware: auth + role:laboran,kepala_lab,admin, prefix: admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:laboran,kepala_lab,admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/tools/generate-code', [ToolController::class, 'generateCode'])
            ->name('tools.generate-code');

        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('/tools/adjustments', [StockAdjustmentController::class, 'index'])
            ->name('tools.adjustments');

        Route::resource('tools', ToolController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::post('tools/{tool}/repair', [ToolController::class, 'repair'])
            ->name('tools.repair');

        Route::patch('laborans/{laboran}/toggle-active', [LaboranController::class, 'toggleActive'])
            ->name('laborans.toggle-active')
            ->middleware('role:kepala_lab');

        Route::resource('laborans', LaboranController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('role:kepala_lab');

        Route::patch('laboratories/{laboratory}/toggle-active', [LaboratoryController::class, 'toggleActive'])
            ->name('laboratories.toggle-active')
            ->middleware('role:kepala_lab');

        Route::resource('laboratories', LaboratoryController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('role:kepala_lab');

        Route::get('/clients', [ClientController::class, 'index'])
            ->name('clients.index');

        Route::get('/loans', [AdminLoanController::class, 'index'])
            ->name('loans.index');

        Route::get('/loans/{loanRequest}', [AdminLoanController::class, 'review'])
            ->name('loans.review');

        Route::patch('/loans/{loanRequest}/status', [AdminLoanController::class, 'updateStatus'])
            ->name('loans.update-status');

        Route::post('/loans/{loanRequest}/return', [AdminLoanController::class, 'returnLoan'])
            ->name('loans.return');

        Route::get('/activity-logs', [AdminStudentActivityLogController::class, 'index'])
            ->name('activity-logs.index');
        Route::patch('/activity-logs/{activityLog}/verify', [AdminStudentActivityLogController::class, 'verify'])
            ->name('activity-logs.verify');
        Route::patch('/activity-logs/{activityLog}/reject', [AdminStudentActivityLogController::class, 'reject'])
            ->name('activity-logs.reject');
    });
