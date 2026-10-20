<?php

use App\Models\Laboratory;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('owner, kepala lab, and relevant laboran can view identity card but unauthorized users get 403', function () {
    Storage::fake('local');

    $labA = Laboratory::factory()->create(['name' => 'Lab A']);
    $labB = Laboratory::factory()->create(['name' => 'Lab B']);

    $laboranA = User::factory()->laboran()->create(['laboratory_id' => $labA->id]);
    $laboranB = User::factory()->laboran()->create(['laboratory_id' => $labB->id]);
    $kepalaLab = User::factory()->kepalaLab()->create();

    $student1 = User::factory()->create(['role' => 'klien']);
    $file1 = UploadedFile::fake()->create('ktp-student1.pdf', 200, 'application/pdf');
    $path1 = $file1->store('identity_cards', 'local');
    $student1->update(['identity_card_path' => $path1]);

    $student2 = User::factory()->create(['role' => 'klien']);

    // Student 1 has a loan in Lab A
    LoanRequest::query()->create([
        'user_id' => $student1->id,
        'laboratory_id' => $labA->id,
        'ticket_number' => 'LOAN-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'purpose' => 'Riset Mahasiswa',
        'identity_card_path' => $path1,
        'status' => 'PENDING',
    ]);

    // 1. Owner (student1) can view
    $this->actingAs($student1)
        ->get(route('documents.identity', $student1))
        ->assertOk();

    // 2. Kepala Lab can view
    $this->actingAs($kepalaLab)
        ->get(route('documents.identity', $student1))
        ->assertOk();

    // 3. Laboran A (relevant lab) can view
    $this->actingAs($laboranA)
        ->get(route('documents.identity', $student1))
        ->assertOk();

    // 4. Laboran B (different lab) gets 403
    $this->actingAs($laboranB)
        ->get(route('documents.identity', $student1))
        ->assertForbidden();

    // 5. Another student (student2) gets 403
    $this->actingAs($student2)
        ->get(route('documents.identity', $student1))
        ->assertForbidden();

    // 6. Guest gets redirected to login
    auth()->logout();
    $this->get(route('documents.identity', $student1))
        ->assertRedirect(route('login'));
});

test('borrower, assigned laboran, and kepala lab can access loan letter but unauthorized users get 403', function () {
    Storage::fake('local');

    $labA = Laboratory::factory()->create(['name' => 'Lab A']);
    $labB = Laboratory::factory()->create(['name' => 'Lab B']);

    $laboranA = User::factory()->laboran()->create(['laboratory_id' => $labA->id]);
    $laboranB = User::factory()->laboran()->create(['laboratory_id' => $labB->id]);
    $kepalaLab = User::factory()->kepalaLab()->create();

    $student = User::factory()->create(['role' => 'klien']);
    $otherStudent = User::factory()->create(['role' => 'klien']);

    $file = UploadedFile::fake()->create('surat-peminjaman.pdf', 300, 'application/pdf');
    $letterPath = $file->store('loan_letters', 'local');

    $loan = LoanRequest::query()->create([
        'user_id' => $student->id,
        'laboratory_id' => $labA->id,
        'ticket_number' => 'LOAN-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'purpose' => 'Riset Mahasiswa',
        'identity_card_path' => 'identity_cards/test.pdf',
        'loan_letter_path' => $letterPath,
        'status' => 'PENDING',
    ]);

    // Borrower can view
    $this->actingAs($student)
        ->get(route('documents.loan-letter', $loan))
        ->assertOk();

    // Assigned Laboran can view
    $this->actingAs($laboranA)
        ->get(route('documents.loan-letter', $loan))
        ->assertOk();

    // Kepala Lab can view
    $this->actingAs($kepalaLab)
        ->get(route('documents.loan-letter', $loan))
        ->assertOk();

    // Laboran from other lab gets 403
    $this->actingAs($laboranB)
        ->get(route('documents.loan-letter', $loan))
        ->assertForbidden();

    // Other student gets 403
    $this->actingAs($otherStudent)
        ->get(route('documents.loan-letter', $loan))
        ->assertForbidden();
});
