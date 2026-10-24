<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user cannot update their email to an email already used by another account', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $originalEmailA = $userA->email;
    $originalEmailB = $userB->email;

    $response = $this
        ->actingAs($userB)
        ->from('/profile')
        ->patch('/profile', [
            'name' => $userB->name,
            'email' => $userA->email,
        ]);

    $response
        ->assertSessionHasErrors('email')
        ->assertRedirect('/profile');

    expect($userB->refresh()->email)->toBe($originalEmailB)
        ->and($userA->refresh()->email)->toBe($originalEmailA);
});

test('user can keep their own email when updating the profile', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Nama Baru',
            'email' => $user->email,
        ]);

    $response->assertSessionHasNoErrors();

    expect($user->refresh()->name)->toBe('Nama Baru');
});

test('user cannot update their identity_number to an identity_number already used by another account', function () {
    $userA = User::factory()->create(['identity_number' => '24103041001']);
    $userB = User::factory()->create(['identity_number' => '24103041002']);

    $response = $this
        ->actingAs($userB)
        ->from('/profile')
        ->patch('/profile', [
            'name' => $userB->name,
            'email' => $userB->email,
            'identity_number' => '24103041001',
        ]);

    $response
        ->assertSessionHasErrors([
            'identity_number' => 'Nomor identitas (NIM/NIK) ini sudah digunakan oleh akun lain.',
        ])
        ->assertRedirect('/profile');

    expect($userB->refresh()->identity_number)->toBe('24103041002');
});

test('user can keep their own identity_number when updating the profile', function () {
    $user = User::factory()->create(['identity_number' => '24103041001']);

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Nama Update',
            'email' => $user->email,
            'identity_number' => '24103041001',
        ]);

    $response->assertSessionHasNoErrors();

    expect($user->refresh()->identity_number)->toBe('24103041001');
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', [
            'password' => 'Kata sandi yang Anda masukkan salah.',
        ])
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
