<?php

use App\Models\Laboratory;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('guests are redirected to login when accessing an identity card document', function () {
    $user = User::factory()->create([
        'identity_card_path' => 'identity_cards/guest-test.pdf',
    ]);

    $this->get(route('documents.identity', $user))
        ->assertRedirect(route('login'));
});

test('a user cannot view the identity card of another user', function () {
    Storage::fake('local');

    $owner = User::factory()->create();
    $path = UploadedFile::fake()->create('ktm.pdf', 100, 'application/pdf')->store('identity_cards', 'local');
    $owner->update(['identity_card_path' => $path]);

    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('documents.identity', $owner))
        ->assertForbidden();

    // Unsigned direct storage access to the private disk is denied for everyone.
    $this->actingAs($otherUser)
        ->get('/storage/'.$path)
        ->assertForbidden();
});

test('the document owner can view their own identity card', function () {
    Storage::fake('local');

    $owner = User::factory()->create();
    $path = UploadedFile::fake()->create('ktm.pdf', 100, 'application/pdf')->store('identity_cards', 'local');
    $owner->update(['identity_card_path' => $path]);

    $this->actingAs($owner)
        ->get(route('documents.identity', $owner))
        ->assertOk();
});

test('deleting a user account removes the identity card file from storage', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $path = UploadedFile::fake()->create('ktm.pdf', 100, 'application/pdf')->store('identity_cards', 'local');
    $user->update(['identity_card_path' => $path]);

    Storage::disk('local')->assertExists($path);

    $this->actingAs($user)
        ->delete('/profile', ['password' => 'password'])
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    Storage::disk('local')->assertMissing($path);
});

test('deleting a user account also removes their loan letter files from storage', function () {
    Storage::fake('local');

    $student = User::factory()->student()->create();
    $identityPath = UploadedFile::fake()->create('ktm.pdf', 100, 'application/pdf')->store('identity_cards', 'local');
    $student->update(['identity_card_path' => $identityPath]);

    $letterPath = UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf')->store('loan_letters', 'local');
    $laboratory = Laboratory::factory()->create();

    LoanRequest::query()->create([
        'user_id' => $student->id,
        'laboratory_id' => $laboratory->id,
        'ticket_number' => 'LOAN-'.$laboratory->code.'-'.strtoupper(Str::random(6)),
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(3)->toDateString(),
        'purpose' => 'Pengujian pembersihan berkas',
        'identity_card_path' => $identityPath,
        'loan_letter_path' => $letterPath,
        'status' => 'PENDING',
    ]);

    Storage::disk('local')->assertExists($letterPath);

    $this->actingAs($student)
        ->delete('/profile', ['password' => 'password'])
        ->assertRedirect('/');

    $this->assertDatabaseMissing('loan_requests', ['user_id' => $student->id]);
    Storage::disk('local')->assertMissing($identityPath);
    Storage::disk('local')->assertMissing($letterPath);
});

test('profile identity card upload is stored on the private local disk and replaces the old file', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $oldPath = UploadedFile::fake()->create('ktm-lama.pdf', 100, 'application/pdf')->store('identity_cards', 'local');
    $user->update(['identity_card_path' => $oldPath]);

    $this->actingAs($user)
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'identity_card_file' => UploadedFile::fake()->create('ktm-baru.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    Storage::disk('local')->assertMissing($oldPath);
    Storage::disk('local')->assertExists($user->identity_card_path);
    Storage::disk('public')->assertMissing($user->identity_card_path);
});
