<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('catalog.index', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('inactive users cannot authenticate and receive specific message', function () {
    $inactiveUser = User::factory()->create([
        'is_active' => false,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => $inactiveUser->email,
        'password' => 'password123',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors(['email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Kepala Laboratorium.']);
});

test('laboran user is redirected to admin dashboard upon login', function () {
    $laboran = User::factory()->create([
        'role' => 'laboran',
        'is_active' => true,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => $laboran->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($laboran);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('kepala lab user is redirected to admin dashboard upon login', function () {
    $kepalaLab = User::factory()->create([
        'role' => 'kepala_lab',
        'is_active' => true,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => $kepalaLab->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($kepalaLab);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});
