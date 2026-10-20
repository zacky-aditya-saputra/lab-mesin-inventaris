<?php

use App\Models\User;

test('login attempts are rate limited after five consecutive failures', function () {
    $user = User::factory()->create();

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect();
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);

    $this->assertGuest();
});

test('login brute force with rotating email addresses from one IP is rate limited', function () {
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->post('/login', [
            'email' => "victim{$attempt}@example.com",
            'password' => 'wrong-password',
        ])->assertRedirect();
    }

    // Sixth attempt with yet another email still trips the per-IP limiter.
    $this->post('/login', [
        'email' => 'victim6@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

test('legitimate login attempts within the limit still succeed', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('catalog.index', absolute: false));

    $this->assertAuthenticated();
});

test('registration attempts are rate limited after five consecutive requests', function () {
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->post('/register', [
            'name' => 'Spam Bot',
        ])->assertRedirect();
    }

    $this->post('/register', [
        'name' => 'Spam Bot',
    ])->assertStatus(429);

    $this->assertDatabaseMissing('users', ['name' => 'Spam Bot']);
});
