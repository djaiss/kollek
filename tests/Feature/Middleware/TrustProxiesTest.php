<?php

declare(strict_types=1);
use Illuminate\Support\Facades\Route;

/*
 * The request is built from an absolute http:// url rather than a path, so the
 * scheme under test is the one the proxy forwards and not the one APP_URL
 * happens to carry. CI serves an https APP_URL, which would otherwise make
 * every request secure before a forwarded header is read at all.
 */
beforeEach(function () {
    $this->host = parse_url(config('app.url'), PHP_URL_HOST);
    $this->probe = "http://{$this->host}/trusted-proxy-probe";

    Route::get('trusted-proxy-probe', fn () => response()->json([
        'secure' => request()->isSecure(),
        'url' => url('assets/app.css'),
        'ip' => request()->ip(),
    ]));
});

it('ignores forwarded headers when no proxy is trusted', function () {
    config(['trustedproxy.proxies' => null]);

    $this->get($this->probe, [
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-For' => '80.90.100.110',
    ])
        ->assertJsonPath('secure', false)
        ->assertJsonPath('url', "http://{$this->host}/assets/app.css")
        ->assertJsonPath('ip', '127.0.0.1');
});

it('builds https urls behind a trusted proxy that terminates tls', function () {
    config(['trustedproxy.proxies' => '*']);

    $this->get($this->probe, ['X-Forwarded-Proto' => 'https'])
        ->assertJsonPath('secure', true)
        ->assertJsonPath('url', "https://{$this->host}/assets/app.css");
});

it('reads the visitor address from a trusted proxy', function () {
    config(['trustedproxy.proxies' => '*']);

    $this->get($this->probe, ['X-Forwarded-For' => '80.90.100.110'])
        ->assertJsonPath('ip', '80.90.100.110');
});

it('only trusts the proxies it is given', function () {
    config(['trustedproxy.proxies' => '10.0.0.1,10.0.0.2']);

    $this->get($this->probe, ['X-Forwarded-Proto' => 'https'])
        ->assertJsonPath('secure', false);
});

it('takes the proxies it trusts from the environment', function () {
    $_ENV['TRUSTED_PROXIES'] = '10.0.0.1,10.0.0.2';

    expect(require config_path('trustedproxy.php'))
        ->toBe(['proxies' => '10.0.0.1,10.0.0.2']);

    unset($_ENV['TRUSTED_PROXIES']);
});
