<?php

declare(strict_types=1);

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Services\LlmsTxt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('opens with the app name and its tagline', function () {
    $content = app(LlmsTxt::class)->content();

    expect($content)
        ->toStartWith('# '.config('app.name')."\n\n> ".config('app.description'));
});

it('links the product, features, and documentation sections', function () {
    $content = app(LlmsTxt::class)->content();

    expect($content)
        ->toContain('## Product')
        ->toContain('## Features')
        ->toContain('## Documentation')
        ->toContain('## Optional');
});

it('points every documentation link at its markdown route rather than the rendered page', function () {
    $content = app(LlmsTxt::class)->content();

    $markdownUrl = route('marketing.docs.portal.markdown', ['locale' => 'en', 'section' => 'getting-started', 'slug' => 'create-your-account']);
    $htmlUrl = route('marketing.docs.portal.show', ['locale' => 'en', 'section' => 'getting-started', 'slug' => 'create-your-account']);

    expect($content)
        ->toContain(route('marketing.docs.portal.home.markdown', ['locale' => 'en']))
        ->toContain('('.$markdownUrl.')')
        ->not->toContain('('.$htmlUrl.')');
});

it('links the api reference as markdown', function () {
    $content = app(LlmsTxt::class)->content();

    expect($content)->toContain(route('marketing.docs.api.markdown.index', ['locale' => 'en']));
});

it('stays in english regardless of the current app locale', function () {
    app()->setLocale('fr_FR');

    $content = app(LlmsTxt::class)->content();

    app()->setLocale('en');

    expect($content)
        ->toContain('Getting Started')
        ->not->toContain('Démarrage');
});

it('describes a blog entry with its meta description', function () {
    $post = BlogPost::factory()->create();

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'the-dundies',
        'title' => 'The Dundies',
        'meta_description' => 'An awards ceremony.',
    ]);

    expect(app(LlmsTxt::class)->content())
        ->toContain('[The Dundies]')
        ->toContain('): An awards ceremony.');
});
