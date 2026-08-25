<?php

declare(strict_types=1);

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Services\BlogFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function feedEntry(): BlogPost
{
    $post = BlogPost::factory()->create(['reference' => 31]);

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'the-dundies',
        'title' => 'The Dundies',
        'excerpt' => 'An awards ceremony.',
        'body' => "Opening.\n\n## A heading\n\nMore.",
    ]);

    return $post->refresh();
}

it('builds a well formed rss document', function () {
    feedEntry();

    $xml = app(BlogFeed::class)->forLocale('en');

    expect(simplexml_load_string($xml))->not->toBeFalse()
        ->and($xml)->toContain('<title>The Dundies</title>');
});

it('carries the whole entry rather than a teaser', function () {
    feedEntry();

    expect(app(BlogFeed::class)->forLocale('en'))->toContain('A heading');
});

it('keys an item on the reference, which a rename cannot change', function () {
    feedEntry();

    expect(app(BlogFeed::class)->forLocale('en'))->toContain('<guid isPermaLink="false">KLK-0031</guid>');
});

it('leaves a draft out', function () {
    $post = BlogPost::factory()->draft()->create();
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'A draft',
    ]);

    expect(app(BlogFeed::class)->forLocale('en'))->not->toContain('A draft');
});

it('escapes a title that would otherwise break the xml', function () {
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'Paper & "ream" <tags>',
    ]);

    $xml = app(BlogFeed::class)->forLocale('en');

    expect(simplexml_load_string($xml))->not->toBeFalse()
        ->and($xml)->toContain('&amp;');
});

it('survives a body carrying a cdata terminator', function () {
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'Tricky',
        'body' => 'A body with ]]> in it.',
    ]);

    expect(simplexml_load_string(app(BlogFeed::class)->forLocale('en')))->not->toBeFalse();
});
