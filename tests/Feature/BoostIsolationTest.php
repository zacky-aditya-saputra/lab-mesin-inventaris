<?php

test('laravel boost is force-disabled outside the local environment', function () {
    // The testing environment mirrors production hardening: the AppServiceProvider
    // override must keep Boost off so its routes and script injection can never
    // be served publicly, even when APP_DEBUG is accidentally left enabled.
    expect(app()->isLocal())->toBeFalse()
        ->and(config('boost.enabled'))->toBeFalse();
});

test('the boost browser logs endpoint is not served outside the local environment', function () {
    $this->post('/_boost/browser-logs', [
        'logs' => [
            ['type' => 'error', 'timestamp' => now()->toIso8601String(), 'data' => ['message' => 'x'], 'url' => '/x', 'userAgent' => 'test'],
        ],
    ])->assertNotFound();

    $this->get('/_boost/browser-logs')->assertNotFound();
});
