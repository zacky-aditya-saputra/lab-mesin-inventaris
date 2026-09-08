<?php

use App\Models\Category;
use App\Models\LoanRequest;
use App\Models\Tool;
use App\Models\User;
use App\Services\LoanStatusService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

test('admin can fully approve a pending ticket, status becomes approved and available stock decreases', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 5]);
    $loan = createLoanRequestWithItems($student, 'PENDING', [
        ['tool' => $tool, 'requested' => 2],
    ]);

    $service = new LoanStatusService;
    $itemId = $loan->items->first()->id;

    $service->approve($loan, [$itemId => 2], 'Silakan ambil alat di ruang lab.', $admin);

    $loan->refresh();

    expect($loan->status)->toBe('APPROVED')
        ->and($loan->admin_notes)->toBe('Silakan ambil alat di ruang lab.')
        ->and($loan->items->first()->approved_quantity)->toBe(2)
        ->and($tool->refresh()->available_stock)->toBe(3)
        ->and($tool->total_stock)->toBe(5);

    $this->assertDatabaseHas('loan_logs', [
        'loan_request_id' => $loan->id,
        'user_id' => $admin->id,
        'previous_status' => 'PENDING',
        'new_status' => 'APPROVED',
        'notes' => 'Silakan ambil alat di ruang lab.',
    ]);
});

test('admin can partially approve a ticket and the status becomes partially approved', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 5]);
    $loan = createLoanRequestWithItems($student, 'PENDING', [
        ['tool' => $tool, 'requested' => 4],
    ]);

    $service = new LoanStatusService;
    $itemId = $loan->items->first()->id;

    $service->approve($loan, [$itemId => 2], null, $admin);

    $loan->refresh();

    expect($loan->status)->toBe('PARTIALLY_APPROVED')
        ->and($loan->items->first()->approved_quantity)->toBe(2)
        ->and($tool->refresh()->available_stock)->toBe(3);

    $this->assertDatabaseHas('loan_logs', [
        'loan_request_id' => $loan->id,
        'previous_status' => 'PENDING',
        'new_status' => 'PARTIALLY_APPROVED',
    ]);
});

test('approval fails with a validation error when the current tool stock is lower than the approved quantity', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 3, 'available_stock' => 1]);
    $loan = createLoanRequestWithItems($student, 'PENDING', [
        ['tool' => $tool, 'requested' => 2],
    ]);

    $service = new LoanStatusService;
    $itemId = $loan->items->first()->id;

    try {
        $service->approve($loan, [$itemId => 2], null, $admin);
        $this->fail('Approval should have failed because the tool stock is insufficient.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['items'][0])->toBe("Stok alat {$tool->name} tidak mencukupi.");
    }

    expect($loan->refresh()->status)->toBe('PENDING')
        ->and($loan->items->first()->approved_quantity)->toBeNull()
        ->and($tool->refresh()->available_stock)->toBe(1);
});

test('admin can reject a pending ticket without cutting the tool stock', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 5]);
    $loan = createLoanRequestWithItems($student, 'PENDING', [
        ['tool' => $tool, 'requested' => 2],
    ]);

    $service = new LoanStatusService;

    $service->reject($loan, 'Alat sedang dipakai untuk riset.', $admin);

    $loan->refresh();

    expect($loan->status)->toBe('REJECTED')
        ->and($loan->admin_notes)->toBe('Alat sedang dipakai untuk riset.')
        ->and($loan->items->first()->approved_quantity)->toBeNull()
        ->and($tool->refresh()->available_stock)->toBe(5)
        ->and($tool->total_stock)->toBe(5);

    $this->assertDatabaseHas('loan_logs', [
        'loan_request_id' => $loan->id,
        'user_id' => $admin->id,
        'previous_status' => 'PENDING',
        'new_status' => 'REJECTED',
    ]);
});

test('admin can confirm the physical handover of an approved ticket to on loan without changing stock', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 3]);
    $loan = createLoanRequestWithItems($student, 'APPROVED', [
        ['tool' => $tool, 'requested' => 2, 'approved' => 2],
    ]);

    $service = new LoanStatusService;

    $service->handover($loan, $admin);

    $loan->refresh();

    expect($loan->status)->toBe('ON_LOAN')
        ->and($tool->refresh()->available_stock)->toBe(3)
        ->and($tool->total_stock)->toBe(5);

    $this->assertDatabaseHas('loan_logs', [
        'loan_request_id' => $loan->id,
        'user_id' => $admin->id,
        'previous_status' => 'APPROVED',
        'new_status' => 'ON_LOAN',
    ]);
});

test('returning tools with a good condition restores the available stock', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 3]);
    $loan = createLoanRequestWithItems($student, 'ON_LOAN', [
        ['tool' => $tool, 'requested' => 2, 'approved' => 2],
    ]);

    $service = new LoanStatusService;
    $itemId = $loan->items->first()->id;

    $service->returnItems($loan, [['id' => $itemId, 'condition' => 'Baik']], 'Kondisi tetap terawat.', $admin);

    $loan->refresh();

    expect($loan->status)->toBe('RETURNED')
        ->and($loan->items->first()->return_condition)->toBe('Baik')
        ->and($tool->refresh()->available_stock)->toBe(5)
        ->and($tool->total_stock)->toBe(5);

    $this->assertDatabaseHas('loan_logs', [
        'loan_request_id' => $loan->id,
        'user_id' => $admin->id,
        'previous_status' => 'ON_LOAN',
        'new_status' => 'RETURNED',
    ]);
});

test('returning tools with a lost condition permanently decreases the total stock', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 3]);
    $loan = createLoanRequestWithItems($student, 'ON_LOAN', [
        ['tool' => $tool, 'requested' => 2, 'approved' => 2],
    ]);

    $service = new LoanStatusService;
    $itemId = $loan->items->first()->id;

    $service->returnItems($loan, [['id' => $itemId, 'condition' => 'Hilang']], null, $admin);

    $loan->refresh();

    expect($loan->status)->toBe('RETURNED')
        ->and($loan->items->first()->return_condition)->toBe('Hilang')
        ->and($tool->refresh()->total_stock)->toBe(3)
        ->and($tool->available_stock)->toBe(3);

    $this->assertDatabaseHas('loan_logs', [
        'loan_request_id' => $loan->id,
        'previous_status' => 'ON_LOAN',
        'new_status' => 'RETURNED',
    ]);
});

test('students are forbidden from accessing the admin loan approval routes', function () {
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create();
    $loan = createLoanRequestWithItems($student, 'PENDING', [
        ['tool' => $tool, 'requested' => 1],
    ]);

    $this->actingAs($student)->get(route('admin.loans.index'))->assertForbidden();
    $this->actingAs($student)->get(route('admin.loans.review', $loan))->assertForbidden();
    $this->actingAs($student)->patch(route('admin.loans.update-status', $loan), [
        'action' => 'approve',
        'items' => [['id' => $loan->items->first()->id, 'approved_quantity' => 1]],
    ])->assertForbidden();
    $this->actingAs($student)->post(route('admin.loans.return', $loan), [
        'items' => [['id' => $loan->items->first()->id, 'condition' => 'Baik']],
    ])->assertForbidden();
});

/**
 * Create a loan request with the given status and item definitions for testing.
 *
 * @param  array<int, array{tool: Tool, requested: int, approved?: int, condition?: string}>  $items
 */
function createLoanRequestWithItems(User $student, string $status, array $items): LoanRequest
{
    $loanRequest = LoanRequest::query()->create([
        'user_id' => $student->id,
        'ticket_number' => 'LOAN-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'purpose' => 'Pengujian state machine tiket peminjaman',
        'identity_card_path' => 'identity_cards/testing.pdf',
        'status' => $status,
    ]);

    foreach ($items as $item) {
        $loanRequest->items()->create([
            'tool_id' => $item['tool']->id,
            'requested_quantity' => $item['requested'],
            'approved_quantity' => $item['approved'] ?? null,
            'return_condition' => $item['condition'] ?? null,
        ]);
    }

    return $loanRequest->refresh();
}
