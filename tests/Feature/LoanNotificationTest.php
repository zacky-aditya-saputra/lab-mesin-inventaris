<?php

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\LoanRequest;
use App\Models\Tool;
use App\Models\User;
use App\Notifications\LoanStatusChangedNotification;
use App\Notifications\NewLoanRequestNotification;
use App\Services\LoanStatusService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * Create a klien account with a complete borrowing profile for notification tests.
 */
function notifTestClient(array $attributes = []): User
{
    return User::factory()->student()->create(array_merge([
        'identity_number' => '32602100001',
        'phone_number' => '081234567890',
        'institution' => 'Teknik Mesin S1',
        'identity_card_path' => 'identity_cards/notif-test.pdf',
    ], $attributes));
}

/**
 * Create a loan request with one item for the given client and tool.
 */
function notifTestLoan(User $client, string $status, Tool $tool, ?Laboratory $laboratory = null, ?int $approved = null): LoanRequest
{
    $loan = LoanRequest::query()->create([
        'user_id' => $client->id,
        'laboratory_id' => $laboratory?->id ?? $tool->laboratory_id,
        'ticket_number' => 'LOAN-NOTIF-'.uniqid(),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(7)->toDateString(),
        'purpose' => 'Pengujian notifikasi peminjaman',
        'identity_card_path' => 'identity_cards/notif-test.pdf',
        'status' => $status,
    ]);

    $loan->items()->create([
        'tool_id' => $tool->id,
        'requested_quantity' => 2,
        'approved_quantity' => $approved,
    ]);

    return $loan->refresh();
}

test('submitting a loan request notifies every laboran of the target laboratory only', function () {
    Notification::fake();
    Storage::fake('public');

    $student = notifTestClient();
    $targetLab = Laboratory::factory()->create();
    $otherLab = Laboratory::factory()->create();

    $laboranTarget = User::factory()->laboran()->create(['laboratory_id' => $targetLab->id]);
    $laboranOther = User::factory()->laboran()->create(['laboratory_id' => $otherLab->id]);

    $tool = Tool::factory()->for(Category::factory())->for($targetLab)->create(['available_stock' => 5]);

    $this
        ->actingAs($student)
        ->post(route('loans.store'), [
            'laboratory_id' => $targetLab->id,
            'loan_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'purpose' => 'Pengujian notifikasi tiket baru',
            'items' => [
                ['tool_id' => $tool->id, 'quantity' => 1],
            ],
        ])
        ->assertRedirect(route('loans.history'))
        ->assertSessionHas('success');

    Notification::assertSentTo($laboranTarget, NewLoanRequestNotification::class);
    Notification::assertNotSentTo($laboranOther, NewLoanRequestNotification::class);
    Notification::assertSentToTimes($laboranTarget, NewLoanRequestNotification::class, 1);
});

test('the new loan request notification carries the ticket and review url', function () {
    $student = notifTestClient();
    $laboratory = Laboratory::factory()->create();
    $tool = Tool::factory()->for(Category::factory())->for($laboratory)->create();
    $loan = notifTestLoan($student, 'PENDING', $tool, $laboratory);

    $notification = new NewLoanRequestNotification($loan);
    $payload = $notification->toArray($student);

    expect($payload['ticket_number'])->toBe($loan->ticket_number)
        ->and($payload['type'])->toBe('new_loan_request')
        ->and($payload['url'])->toBe(route('admin.loans.review', $loan));
});

test('approving a ticket sends a status notification to the borrower', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $student = notifTestClient();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 5]);
    $loan = notifTestLoan($student, 'PENDING', $tool);

    (new LoanStatusService)->approve($loan, [$loan->items->first()->id => 2], null, $admin);

    Notification::assertSentTo($student, LoanStatusChangedNotification::class, function (LoanStatusChangedNotification $notification) {
        return $notification->loanRequest->status === 'APPROVED';
    });
});

test('rejecting a ticket sends a status notification to the borrower', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $student = notifTestClient();
    $tool = Tool::factory()->for(Category::factory())->create();
    $loan = notifTestLoan($student, 'PENDING', $tool);

    (new LoanStatusService)->reject($loan, 'Stok dipakai praktikum.', $admin);

    Notification::assertSentTo($student, LoanStatusChangedNotification::class, function (LoanStatusChangedNotification $notification) {
        return $notification->loanRequest->status === 'REJECTED';
    });
});

test('handover and return transitions each notify the borrower once', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $student = notifTestClient();
    $tool = Tool::factory()->for(Category::factory())->create(['total_stock' => 5, 'available_stock' => 3]);
    $loan = notifTestLoan($student, 'APPROVED', $tool, approved: 2);

    $service = new LoanStatusService;

    $service->handover($loan, $admin);

    Notification::assertSentTo($student, LoanStatusChangedNotification::class, function (LoanStatusChangedNotification $notification) {
        return $notification->loanRequest->status === 'ON_LOAN';
    });

    $service->returnItems($loan->refresh(), [['id' => $loan->items->first()->id, 'condition' => 'Baik']], null, $admin);

    Notification::assertSentTo($student, LoanStatusChangedNotification::class, function (LoanStatusChangedNotification $notification) {
        return $notification->loanRequest->status === 'RETURNED';
    });

    Notification::assertSentToTimes($student, LoanStatusChangedNotification::class, 2);
});

test('users can mark their notifications as read', function () {
    $student = notifTestClient();
    $laboratory = Laboratory::factory()->create();
    $tool = Tool::factory()->for(Category::factory())->for($laboratory)->create();
    $loan = notifTestLoan($student, 'PENDING', $tool, $laboratory);

    $student->notify(new LoanStatusChangedNotification($loan));

    $notification = $student->notifications()->firstOrFail();

    expect($student->refresh()->unreadNotifications()->count())->toBe(1);

    $this
        ->actingAs($student)
        ->post(route('notifications.mark-read', $notification->id))
        ->assertRedirect();

    expect($student->refresh()->unreadNotifications()->count())->toBe(0);

    $student->notify(new LoanStatusChangedNotification($loan));
    $student->notify(new LoanStatusChangedNotification($loan));

    expect($student->refresh()->unreadNotifications()->count())->toBe(2);

    $this
        ->actingAs($student)
        ->post(route('notifications.mark-all-read'))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($student->refresh()->unreadNotifications()->count())->toBe(0);
});

test('users cannot mark a notification that belongs to another user', function () {
    $owner = notifTestClient();
    $intruder = notifTestClient(['identity_number' => '32602100099']);
    $laboratory = Laboratory::factory()->create();
    $tool = Tool::factory()->for(Category::factory())->for($laboratory)->create();
    $loan = notifTestLoan($owner, 'PENDING', $tool, $laboratory);

    $owner->notify(new LoanStatusChangedNotification($loan));
    $notification = $owner->notifications()->firstOrFail();

    $this
        ->actingAs($intruder)
        ->post(route('notifications.mark-read', $notification->id))
        ->assertNotFound();

    expect($owner->refresh()->unreadNotifications()->count())->toBe(1);
});
