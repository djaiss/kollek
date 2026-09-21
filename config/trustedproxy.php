<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted proxies
    |--------------------------------------------------------------------------
    |
    | The proxies that sit in front of the instance, as a comma separated list
    | of IP addresses or CIDR ranges. Their X-Forwarded-* headers are the only
    | way the application learns that a visitor arrived over https, and which
    | address they came from.
    |
    | Left empty, every forwarded header is ignored: behind a proxy that
    | terminates TLS, links and assets then come out as http:// on an https://
    | page, and every visitor shares the proxy's address for rate limiting.
    |
    | Use "*" when the proxy has no fixed address, which is the usual case for
    | Traefik, Caddy or nginx running in a container next to this one. Only do
    | that when nothing but the proxy can reach the instance.
    |
    | The framework's TrustProxies middleware reads this at request time, so it
    | is set here rather than in bootstrap/app.php, where configuration has not
    | been loaded yet.
    |
    */

    'proxies' => env('TRUSTED_PROXIES'),

];
