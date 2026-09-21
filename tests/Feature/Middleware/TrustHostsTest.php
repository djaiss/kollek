<?php

declare(strict_types=1);
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Middleware\TrustHosts;

it('trusts only the application url and its subdomains', function () {
    $middleware = app(TrustHosts::class);
    $host = parse_url(config('app.url'), PHP_URL_HOST);

    expect($middleware->hosts())->toBe(['^(.+\.)?'.preg_quote($host).'$']);
});

it('runs the trusted host check on every request', function () {
    expect(app(Kernel::class)->getGlobalMiddleware())->toContain(TrustHosts::class);
});
