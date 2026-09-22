<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'identity_type' => 'mahasiswa',
        'identity_number' => '32602100099',
        'phone_number' => '081234567899',
        'institution' => 'Teknik Mesin S1',
        'identity_card_file' => UploadedFile::fake()->create('kartu-mahasiswa.pdf', 200),
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('catalog.index', absolute: false));

    $user = User::query()->where('email', 'test@example.com')->sole();

    expect($user->role)->toBe('klien')
        ->and($user->identity_type)->toBe('mahasiswa')
        ->and($user->identity_number)->toBe('32602100099')
        ->and($user->phone_number)->toBe('081234567899')
        ->and($user->institution)->toBe('Teknik Mesin S1')
        ->and($user->identity_card_path)->toStartWith('identity_cards/');

    Storage::disk('public')->assertExists($user->identity_card_path);
});

test('registration is rejected without an identity card file', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test2@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'identity_type' => 'mahasiswa',
        'identity_number' => '32602100098',
        'phone_number' => '081234567898',
        'institution' => 'Teknik Mesin S1',
    ]);

    $response->assertSessionHasErrors('identity_card_file');
    $this->assertGuest();
});
