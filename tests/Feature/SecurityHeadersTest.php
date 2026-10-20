<?php

use App\Models\User;

test('public web responses include the defensive security headers', function () {
    $response = $this->get(route('catalog.index'));

    $response->assertOk();

    expect($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->headers->get('Permissions-Policy'))->toBe('camera=(), microphone=(), geolocation=()')
        ->and($response->headers->get('X-Powered-By'))->toBeNull();
});

test('authenticated web responses include the defensive security headers', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('profile.edit'));

    $response->assertOk();

    expect($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->headers->get('Permissions-Policy'))->toBe('camera=(), microphone=(), geolocation=()')
        ->and($response->headers->get('X-Powered-By'))->toBeNull();
});

test('the strict transport security header is only sent over https', function () {
    $insecureResponse = $this->get(route('catalog.index'));

    $insecureResponse->assertOk();
    expect($insecureResponse->headers->get('Strict-Transport-Security'))->toBeNull();

    $secureResponse = $this->get(route('catalog.index'), ['X-Forwarded-Proto' => 'https']);

    $secureResponse->assertOk();
    expect($secureResponse->headers->get('Strict-Transport-Security'))
        ->toBe('max-age=31536000; includeSubDomains');
});

test('redirect responses carry the security headers as well', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('catalog.index'));

    expect($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
});
