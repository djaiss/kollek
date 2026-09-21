<?php

declare(strict_types=1);

use App\Exceptions\ItemLimitReached;
use App\Http\Middleware\CacheMarketingResponse;
use App\Http\Middleware\CheckCatalog;
use App\Http\Middleware\CheckCopy;
use App\Http\Middleware\CheckItem;
use App\Http\Middleware\CheckMarketing;
use App\Http\Middleware\EnsureAccountOwner;
use App\Http\Middleware\EnsureEditorAccess;
use App\Http\Middleware\EnsureHostedInstance;
use App\Http\Middleware\EnsureInstanceAdministrator;
use App\Http\Middleware\EnsureSupportEnabled;
use App\Http\Middleware\HandleOversizedUpload;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetMarketingLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // The public site is registered outside the web group on purpose. It
            // is served through a CDN, and a response that starts a session
            // carries a Set-Cookie header, which is enough for a cache in front
            // of us to refuse to store it. So no session is started here, no
            // cookie is queued, and every visitor gets the same page. Nothing on
            // these pages needs either: the language comes from the url, the
            // theme from the browser, and there is no form to protect.
            Route::middleware([SubstituteBindings::class])
                ->group(__DIR__.'/../routes/marketing.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Which proxies are trusted is set in config/trustedproxy.php, because
        // the framework's TrustProxies middleware reads it per request and
        // configuration is not loaded yet at this point.
        //
        // A trusted proxy also gets to say which host was asked for, and an
        // invitation or magic link email is built from that host, so the host
        // has to be one of ours. Without this, a forged X-Forwarded-Host would
        // send someone's login link to an attacker. The framework matches
        // APP_URL and its subdomains, and skips the check locally and in tests.
        $middleware->trustHosts();

        // Runs before CSRF verification: a body over post_max_size arrives without
        // its token, so this must catch it before the token mismatch fires.
        $middleware->web(prepend: [HandleOversizedUpload::class]);

        $middleware->alias([
            'set.locale' => SetLocale::class,
            // the collection domain resolves what the url names, outermost first
            'catalog' => CheckCatalog::class,
            'item' => CheckItem::class,
            'copy' => CheckCopy::class,
            'owner' => EnsureAccountOwner::class,
            'editor' => EnsureEditorAccess::class,
            'instance.admin' => EnsureInstanceAdministrator::class,
            'support.enabled' => EnsureSupportEnabled::class,
            'hosted' => EnsureHostedInstance::class,
            'marketing' => CheckMarketing::class,
            'marketing.locale' => SetMarketingLocale::class,
            'marketing.cache' => CacheMarketingResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // An account that has used up its free allowance gets 402 rather than
        // the 404 an authorization failure answers with, so an API client can
        // tell "you may not" apart from "you have run out". In a browser the
        // same refusal lands on the screen that explains it.
        $exceptions->render(function (ItemLimitReached $e, Request $request): Response {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 402);
            }

            return redirect()->route('upgrade.index');
        });
    })->create();
