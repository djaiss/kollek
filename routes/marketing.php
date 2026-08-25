<?php

declare(strict_types=1);

use App\Http\Controllers\Marketing\AboutController;
use App\Http\Controllers\Marketing\BlogController;
use App\Http\Controllers\Marketing\BlogFeedController;
use App\Http\Controllers\Marketing\BlogOgImageController;
use App\Http\Controllers\Marketing\Docs\ApiDocsController;
use App\Http\Controllers\Marketing\Docs\ApiDocsMarkdownController;
use App\Http\Controllers\Marketing\Docs\DocsPortalController;
use App\Http\Controllers\Marketing\Docs\DocsPortalHomeController;
use App\Http\Controllers\Marketing\FaqController;
use App\Http\Controllers\Marketing\FeaturesController;
use App\Http\Controllers\Marketing\LlmsTxtController;
use App\Http\Controllers\Marketing\MarketingController;
use App\Http\Controllers\Marketing\MediaKitController;
use App\Http\Controllers\Marketing\PricingController;
use App\Http\Controllers\Marketing\PrivacyController;
use App\Http\Controllers\Marketing\RobotsController;
use App\Http\Controllers\Marketing\SitemapController;
use App\Http\Controllers\Marketing\TermsController;
use App\Http\Controllers\Marketing\TestimonialsController;
use Illuminate\Support\Facades\Route;

// The whole public site lives behind a language prefix (getkollek.com/en/...),
// so the URL reads naturally in the reader's language end to end. The prefix is
// the short form (en, fr), and is constrained to the locales that actually have
// content on disk, mirroring DocumentationPortal::availableLocales().
$portalPath = config('docs.portal_path');
$urlLocales = collect(config('docs.locales'))
    ->filter(fn (array $meta, string $locale): bool => is_dir($portalPath.DIRECTORY_SEPARATOR.$locale))
    ->pluck('url')
    ->implode('|');

// robots.txt sits outside the marketing gate: an instance that keeps the public
// site off still has something to tell a crawler, and the redirect to the login
// page the gate answers with says nothing at all. It carries no language prefix
// because it is one file for the whole host.
Route::get('robots.txt', [RobotsController::class, 'index'])
    ->middleware('marketing.cache')
    ->name('marketing.robots.index');

// The terms of use and the privacy policy sit outside the marketing gate below.
// The registration form links to them and refuses to sign anybody up until they
// have agreed, so they have to be readable on an instance that keeps the rest of
// the public site switched off. They carry the language prefix all the same, so
// the page around the text still reads in the visitor's language.
Route::prefix('{locale}')->where(['locale' => $urlLocales])->middleware(['marketing.locale', 'marketing.cache'])->group(function (): void {
    Route::get('terms', [TermsController::class, 'index'])->name('marketing.terms.index');
    Route::get('privacy', [PrivacyController::class, 'index'])->name('marketing.privacy.index');
});

Route::middleware(['marketing'])->group(function () use ($urlLocales): void {
    // The bare domain is the most linked URL on the site and its answer does not
    // change, so it is permanent rather than temporary, and it is held by the
    // same caches as the pages behind it instead of waking PHP every time.
    //
    // It sends everybody to the default language rather than reading
    // Accept-Language. The two cannot both be true here: the answer is kept by a
    // shared cache that does not vary on that header, so negotiating would hand
    // whichever language warmed the cache to everybody who came after. The
    // x-default alternate already points search engines at the same place, and a
    // visitor who wants another language is one click away in the footer.
    Route::get('/', fn () => redirect()->route('marketing.index', status: 301))
        ->middleware('marketing.cache')
        ->name('marketing.root');

    // One sitemap for the whole site rather than one per language: the hreflang
    // alternates inside it are what tie the translations of a page together, and
    // they only work when every language of that page is in the same file.
    Route::get('sitemap.xml', [SitemapController::class, 'index'])
        ->middleware('marketing.cache')
        ->name('marketing.sitemap.index');

    // llms.txt (https://llmstxt.org), one Markdown index for the whole host
    // rather than one per language prefix, the same as the sitemap above.
    Route::get('llms.txt', [LlmsTxtController::class, 'index'])
        ->middleware('marketing.cache')
        ->name('marketing.llms.index');

    // Every localized page is a public GET that changes only when the site is
    // redeployed, so the whole group carries the cache headers that let a CDN
    // hold it for a week (see config/marketing.php). Every visitor gets the same
    // page, signed in or not, which is what makes one shared copy correct.
    Route::prefix('{locale}')->where(['locale' => $urlLocales])->middleware(['marketing.locale', 'marketing.cache'])->group(function (): void {
        Route::get('/', [MarketingController::class, 'index'])->name('marketing.index');

        Route::get('features', [FeaturesController::class, 'index'])->name('marketing.features.index');
        Route::get('features/{slug}', [FeaturesController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('marketing.features.show');

        Route::get('pricing', [PricingController::class, 'index'])->name('marketing.pricing.index');

        // The blog. feed.xml and og.png are registered before blog/{slug} so
        // the slug pattern does not swallow them; the slug constraint would not
        // match either of them, but the order says the intent out loud.
        Route::get('blog', [BlogController::class, 'index'])->name('marketing.blog.index');
        Route::get('blog/feed.xml', [BlogFeedController::class, 'index'])->name('marketing.blog.feed.index');
        Route::get('blog/{slug}/og.png', [BlogOgImageController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('marketing.blog.ogImage.show');
        Route::get('blog/{slug}', [BlogController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('marketing.blog.show');

        Route::get('faq', [FaqController::class, 'index'])->name('marketing.faq.index');

        Route::get('about', [AboutController::class, 'index'])->name('marketing.about.index');

        Route::get('media-kit', [MediaKitController::class, 'index'])->name('marketing.mediaKit.index');

        Route::get('testimonials', [TestimonialsController::class, 'index'])->name('marketing.testimonials.index');

        Route::get('docs/api', [ApiDocsController::class, 'index'])->name('marketing.docs.api.index');
        Route::get('docs/api.md', [ApiDocsMarkdownController::class, 'index'])->name('marketing.docs.api.markdown.index');
        Route::get('docs/api/{section}.md', [ApiDocsMarkdownController::class, 'show'])->where('section', '[a-z0-9\-]+')->name('marketing.docs.api.markdown.show');

        Route::get('docs', [DocsPortalHomeController::class, 'show'])->name('marketing.docs.portal.home.show');
        Route::get('docs.md', [DocsPortalHomeController::class, 'markdown'])->name('marketing.docs.portal.home.markdown');
        Route::get('docs/{section}/{slug}', [DocsPortalController::class, 'show'])
            ->where('section', '[a-z0-9\-]+')
            ->where('slug', '[a-z0-9\-]+')
            ->name('marketing.docs.portal.show');
        Route::get('docs/{section}/{slug}.md', [DocsPortalController::class, 'markdown'])
            ->where('section', '[a-z0-9\-]+')
            ->where('slug', '[a-z0-9\-]+')
            ->name('marketing.docs.portal.markdown');
    });
});
