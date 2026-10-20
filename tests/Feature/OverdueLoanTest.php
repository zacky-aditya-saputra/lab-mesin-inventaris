<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\LoanItem;
use App\Models\LoanLog;
use App\Models\LoanRequest;
use App\Models\Tool;
use App\Models\User;
use App\Services\LoanStatusService;
use Illuminate\Support\Str;

test('artisan command loans:check-overdue marks past end_date ON_LOAN tickets as OVERDUE', function () {
    $lab = Laboratory::factory()->create();
    $student = User::factory()->create(['role' => 'klien']);

    $overdueLoan = LoanRequest::query()->create([
        'user_id' => $student->id,
        'laboratory_id' => $lab->id,
        'ticket_number' => 'LOAN-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
        'status' => 'ON_LOAN',
        'start_date' => now()->subDays(5)->toDateString(),
        'end_date' => now()->subDay()->toDateString(),
        'purpose' => 'Praktikum Pengelasan',
        'identity_card_path' => 'identity_cards/testing.pdf',
    ]);

    $activeLoan = LoanRequest::query()->create([
        'user_id' => $student->id,
        'laboratory_id' => $lab->id,
        'ticket_number' => 'LOAN-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
        'status' => 'ON_LOAN',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'purpose' => 'Praktikum Bubut',
        'identity_card_path' => 'identity_cards/testing.pdf',
    ]);

    $this->artisan('loans:check-overdue')
        ->expectsOutputToContain('Berhasil menandai 1 tiket peminjaman sebagai OVERDUE.')
        ->assertSuccessful();

    expect($overdueLoan->refresh()->status)->toBe('OVERDUE')
        ->and($activeLoan->refresh()->status)->toBe('ON_LOAN');

    $log = LoanLog::query()
        ->where('loan_request_id', $overdueLoan->id)
        ->where('action', 'OVERDUE_DETECTION')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBeNull()
        ->and($log->previous_status)->toBe('ON_LOAN')
        ->and($log->new_status)->toBe('OVERDUE');
});

test('returnItems succeeds when a tool was soft-deleted while on loan', function () {
    $lab = Laboratory::factory()->create();
    $admin = User::factory()->create(['role' => 'laboran', 'laboratory_id' => $lab->id]);
    $student = User::factory()->create(['role' => 'klien']);
    $category = Category::factory()->create();

    $tool = Tool::factory()->create([
        'laboratory_id' => $lab->id,
        'category_id' => $category->id,
        'total_stock' => 5,
        'available_stock' => 3,
        'damaged_stock' => 0,
    ]);

    $loan = LoanRequest::query()->create([
        'user_id' => $student->id,
        'laboratory_id' => $lab->id,
        'ticket_number' => 'LOAN-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
        'status' => 'ON_LOAN',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'purpose' => 'Uji Spesimen',
        'identity_card_path' => 'identity_cards/testing.pdf',
    ]);

    $item = LoanItem::query()->create([
        'loan_request_id' => $loan->id,
        'tool_id' => $tool->id,
        'requested_quantity' => 2,
        'approved_quantity' => 2,
    ]);

    // Soft delete tool while on loan
    $tool->delete();
    expect($tool->trashed())->toBeTrue();

    $service = app(LoanStatusService::class);
    $service->returnItems($loan, [['id' => $item->id, 'condition' => 'Baik']], 'Kembali tepat waktu', $admin);

    expect($loan->refresh()->status)->toBe('RETURNED');
    expect($tool->refresh()->available_stock)->toBe(5);
});

test('returnItems with split quantities updates available, damaged, and total stock accurately', function () {
    $lab = Laboratory::factory()->create();
    $admin = User::factory()->create(['role' => 'laboran', 'laboratory_id' => $lab->id]);
    $student = User::factory()->create(['role' => 'klien']);
    $category = Category::factory()->create();

    $tool = Tool::factory()->create([
        'laboratory_id' => $lab->id,
        'category_id' => $category->id,
        'total_stock' => 10,
        'available_stock' => 5, // 5 currently on loan
        'damaged_stock' => 0,
    ]);

    $loan = LoanRequest::query()->create([
        'user_id' => $student->id,
        'laboratory_id' => $lab->id,
        'ticket_number' => 'LOAN-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
        'status' => 'ON_LOAN',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'purpose' => 'Uji Tarik',
        'identity_card_path' => 'identity_cards/testing.pdf',
    ]);

    $item = LoanItem::query()->create([
        'loan_request_id' => $loan->id,
        'tool_id' => $tool->id,
        'requested_quantity' => 5,
        'approved_quantity' => 5,
    ]);

    // Return: 2 good, 2 damaged, 1 missing
    $service = app(LoanStatusService::class);
    $service->returnItems($loan, [
        [
            'id' => $item->id,
            'returned_good_qty' => 2,
            'returned_damaged_qty' => 2,
            'returned_missing_qty' => 1,
        ],
    ], 'Selesai praktikum dengan insiden', $admin);

    expect($loan->refresh()->status)->toBe('RETURNED');
    $item->refresh();
    expect($item->returned_good_qty)->toBe(2)
        ->and($item->returned_damaged_qty)->toBe(2)
        ->and($item->returned_missing_qty)->toBe(1)
        ->and($item->return_condition)->toBe('Parsial/Campuran');

    $tool->refresh();
    // Available was 5 + 2 good = 7
    expect($tool->available_stock)->toBe(7)
        // Damaged was 0 + 2 = 2
        ->and($tool->damaged_stock)->toBe(2)
        // Total was 10 - 1 missing = 9
        ->and($tool->total_stock)->toBe(9);
});
