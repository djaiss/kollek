<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Marketing site
    |--------------------------------------------------------------------------
    |
    | Whether the public marketing site (the homepage and the API reference) is
    | served. Self hosted instances rarely need it, so it stays off by default
    | and every marketing route redirects to the application instead.
    |
    */

    'show' => (bool) env('SHOW_MARKETING_SITE', false),

    /*
    |--------------------------------------------------------------------------
    | English only pages
    |--------------------------------------------------------------------------
    |
    | The public site is served at one URL per language prefix, but a handful of
    | pages carry the very same English text behind all of them. Left alone they
    | would compete with themselves in search, so they get one canonical URL
    | between them (the English one) and claim no hreflang alternates.
    |
    | Both App\ViewModels\MarketingSeo, which writes the tags in the head, and
    | App\Services\Sitemap, which lists the page once rather than seven times,
    | read the list from here. They have to agree: a sitemap that contradicts a
    | canonical is worse than no sitemap at all.
    |
    */

    'english_only_routes' => [
        'marketing.terms.index',
        'marketing.privacy.index',
        'marketing.mediaKit.index',
        'marketing.docs.api.index',
    ],

    /*
    |--------------------------------------------------------------------------
    | Repository
    |--------------------------------------------------------------------------
    |
    | Where every "View on GitHub" link on the marketing site points to.
    |
    */

    'github_url' => 'https://github.com/djaiss/kollek',

    /*
    |--------------------------------------------------------------------------
    | Press contact
    |--------------------------------------------------------------------------
    |
    | The address the media kit publishes for journalists. Set it to null on an
    | instance that does not want to advertise a mailbox: the page then sends
    | people to the GitHub discussions instead.
    |
    */

    'press_email' => env('PRESS_EMAIL', 'press@getkollek.com'),

    /*
    |--------------------------------------------------------------------------
    | Public page caching
    |--------------------------------------------------------------------------
    |
    | The public site is rendered once and then held by whatever cache sits in
    | front of it. The CDN keeps a page for a week and is emptied on demand
    | (see App\Services\CloudflareCache); the visitor's own browser keeps it for
    | five minutes, because no purge can reach that copy.
    |
    */

    'cache_public_pages' => (bool) env('CACHE_PUBLIC_PAGES', true),

    'browser_cache_seconds' => (int) env('PUBLIC_PAGE_BROWSER_CACHE', 300),

    'cdn_cache_seconds' => (int) env('PUBLIC_PAGE_CDN_CACHE', 60 * 60 * 24 * 7),

    /*
    |--------------------------------------------------------------------------
    | Blog
    |--------------------------------------------------------------------------
    |
    | The blog presents itself as a catalogue: every entry carries a permanent
    | reference number (KLK-0031) that is assigned once and never reused, so a
    | post can be corrected, retitled or moved to another shelf without the
    | number that people cite ever changing.
    |
    | The licence is printed on the catalogue record of every entry, which is
    | the only place the public site states the terms its writing is under.
    |
    */

    'blog' => [
        'reference_prefix' => env('BLOG_REFERENCE_PREFIX', 'KLK'),
        'licence' => 'CC BY 4.0',
    ],

];
