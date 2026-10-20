<?php

use App\Models\Category;
use App\Models\LoanLog;
use App\Models\Tool;
use App\Models\User;
use App\Services\LoanStatusService;
use Illuminate\Validation\ValidationException;

test('atomic conditional decrement fails and leaves ticket and stock untouched when stock is insufficient', function () {
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
        expect($exception->errors()['items'][0])->toContain('tidak mencukupi.');
    }

    expect($loan->refresh()->status)->toBe('PENDING')
        ->and($loan->approved_by)->toBeNull()
        ->and($loan->approved_at)->toBeNull()
        ->and($loan->items->first()->refresh()->approved_quantity)->toBeNull()
        ->and($tool->refresh()->available_stock)->toBe(1)
        ->and($tool->total_stock)->toBe(3);

    $this->assertDatabaseMissing('loan_logs', [
        'loan_request_id' => $loan->id,
        'action' => 'APPROVAL',
    ]);
});

test('client can cancel their own pending ticket without mutating the tool stock', function () {
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 5]);
    $loan = createLoanRequestWithItems($student, 'PENDING', [
        ['tool' => $tool, 'requested' => 2],
    ]);

    $this->actingAs($student)
        ->patch(route('loans.cancel', $loan), ['reason' => 'Tidak jadi meminjam.'])
        ->assertRedirect(route('loans.history'))
        ->assertSessionHas('success');

    $loan->refresh();

    expect($loan->status)->toBe('CANCELLED')
        ->and($loan->cancelled_at)->not->toBeNull()
        ->and($tool->refresh()->available_stock)->toBe(5)
        ->and($tool->total_stock)->toBe(5);

    $this->assertDatabaseHas('loan_logs', [
        'loan_request_id' => $loan->id,
        'user_id' => $student->id,
        'action' => 'CANCELLATION',
        'previous_status' => 'PENDING',
        'new_status' => 'CANCELLED',
        'notes' => 'Tidak jadi meminjam.',
    ]);
});

test('client cannot cancel a ticket owned by another user', function () {
    $owner = User::factory()->student()->create();
    $intruder = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create();
    $loan = createLoanRequestWithItems($owner, 'PENDING', [
        ['tool' => $tool, 'requested' => 1],
    ]);

    $this->actingAs($intruder)
        ->patch(route('loans.cancel', $loan))
        ->assertForbidden();

    expect($loan->refresh()->status)->toBe('PENDING');
});

test('client cannot cancel a ticket that is no longer pending', function () {
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create();
    $loan = createLoanRequestWithItems($student, 'APPROVED', [
        ['tool' => $tool, 'requested' => 1, 'approved' => 1],
    ]);

    $this->actingAs($student)
        ->patch(route('loans.cancel', $loan))
        ->assertSessionHasErrors();

    expect($loan->refresh()->status)->toBe('APPROVED');
});

test('returning tools with a damaged condition increases the damaged stock without losing physical units', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 3, 'damaged_stock' => 0]);
    $loan = createLoanRequestWithItems($student, 'ON_LOAN', [
        ['tool' => $tool, 'requested' => 2, 'approved' => 2],
    ]);

    $service = new LoanStatusService;
    $itemId = $loan->items->first()->id;

    $service->returnItems($loan, [['id' => $itemId, 'condition' => 'Rusak']], 'Unit retak setelah jatuh.', $admin);

    $loan->refresh();

    expect($loan->status)->toBe('RETURNED')
        ->and($loan->returned_at)->not->toBeNull()
        ->and($tool->refresh()->damaged_stock)->toBe(2)
        ->and($tool->available_stock)->toBe(3)
        ->and($tool->total_stock)->toBe(5);
});

test('markAsRepaired moves quantity from damaged stock to available stock and records a stock adjustment', function () {
    $admin = User::factory()->admin()->create();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 3, 'damaged_stock' => 2]);

    $service = new LoanStatusService;

    $service->markAsRepaired($tool, 2, $admin, 'Kaca pembesar diganti baru.');

    $tool->refresh();

    expect($tool->damaged_stock)->toBe(0)
        ->and($tool->available_stock)->toBe(5)
        ->and($tool->total_stock)->toBe(5);

    $this->assertDatabaseHas('stock_adjustments', [
        'tool_id' => $tool->id,
        'user_id' => $admin->id,
        'type' => 'repair',
        'delta' => 2,
        'before_available' => 3,
        'after_available' => 5,
        'before_damaged' => 2,
        'after_damaged' => 0,
        'reason' => 'Kaca pembesar diganti baru.',
    ]);
});

test('approval fills the borrower identity snapshot from the current user profile', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create([
        'name' => 'Budi Santoso',
        'identity_number' => '1234567890',
        'identity_type' => 'mahasiswa',
        'institution' => 'Universitas Nusantara',
        'phone_number' => '081234567890',
    ]);
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 5]);
    $loan = createLoanRequestWithItems($student, 'PENDING', [
        ['tool' => $tool, 'requested' => 1],
    ]);

    $service = new LoanStatusService;
    $itemId = $loan->items->first()->id;

    $service->approve($loan, [$itemId => 1], null, $admin);

    $loan->refresh();

    expect($loan->borrower_name)->toBe('Budi Santoso')
        ->and($loan->borrower_identity_number)->toBe('1234567890')
        ->and($loan->borrower_identity_type)->toBe('mahasiswa')
        ->and($loan->borrower_institution)->toBe('Universitas Nusantara')
        ->and($loan->borrower_phone)->toBe('081234567890')
        ->and($loan->approved_by)->toBe($admin->id)
        ->and($loan->approved_at)->not->toBeNull();
});

test('client loan history displays item status, rejection reason, and split condition return details', function () {
    $student = User::factory()->student()->create();
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $toolA = Tool::factory()->for($category)->create(['name' => 'Mikrometer Sekrup']);
    $toolB = Tool::factory()->for($category)->create(['name' => 'Jangka Sorong Digital']);

    // Loan 1: partially approved with one rejected item
    $loan = createLoanRequestWithItems($student, 'PARTIALLY_APPROVED', [
        ['tool' => $toolA, 'requested' => 2],
        ['tool' => $toolB, 'requested' => 1],
    ]);

    $itemA = $loan->items()->where('tool_id', $toolA->id)->first();
    $itemA->update([
        'status' => 'APPROVED',
        'approved_quantity' => 2,
    ]);

    $itemB = $loan->items()->where('tool_id', $toolB->id)->first();
    $itemB->update([
        'status' => 'REJECTED',
        'approved_quantity' => 0,
        'rejection_reason' => 'Alat sedang dalam perbaikan rutin',
    ]);

    // Loan 2: returned with split condition
    $returnedLoan = createLoanRequestWithItems($student, 'RETURNED', [
        ['tool' => $toolA, 'requested' => 3],
    ]);
    $returnedItem = $returnedLoan->items->first();
    $returnedItem->update([
        'status' => 'APPROVED',
        'approved_quantity' => 3,
        'returned_good_qty' => 2,
        'returned_damaged_qty' => 1,
        'returned_missing_qty' => 0,
    ]);

    $response = $this->actingAs($student)->get(route('loans.history'));

    $response->assertOk();
    $response->assertSee('Mikrometer Sekrup');
    $response->assertSee('Jangka Sorong Digital');
    $response->assertSee('Disetujui');
    $response->assertSee('Ditolak');
    $response->assertSee('Alasan: Alat sedang dalam perbaikan rutin');
    $response->assertSee('2 Bagus');
    $response->assertSee('1 Rusak');
});

test('admin review view displays immutable borrower snapshot and audit logs', function () {
    $admin = User::factory()->admin()->create();
    $student = User::factory()->student()->create();
    $tool = Tool::factory()->for(Category::factory())->create();

    $loan = createLoanRequestWithItems($student, 'ON_LOAN', [
        ['tool' => $tool, 'requested' => 1],
    ]);

    $loan->update([
        'borrower_name' => 'Nama Snapshot Permanen',
        'borrower_identity_number' => 'NIM-998877',
        'borrower_institution' => 'Teknik Mesin Unwahas',
        'borrower_phone' => '089988776655',
        'approved_at' => now(),
        'handed_over_at' => now(),
    ]);

    LoanLog::create([
        'loan_request_id' => $loan->id,
        'user_id' => $admin->id,
        'action' => 'APPROVAL',
        'previous_status' => 'PENDING',
        'new_status' => 'APPROVED',
        'notes' => 'Disetujui untuk riset skripsi',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.loans.review', $loan));

    $response->assertOk();
    $response->assertSee('Nama Snapshot Permanen');
    $response->assertSee('NIM-998877');
    $response->assertSee('Snapshot Permanen');
    $response->assertSee('Jejak Audit Status Tiket');
    $response->assertSee('Disetujui untuk riset skripsi');
});
