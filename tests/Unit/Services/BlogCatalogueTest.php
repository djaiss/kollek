<?php

declare(strict_types=1);

use App\Enums\BlogShelf;
use App\Models\BlogPost;
use App\Models\BlogPostRedirect;
use App\Models\BlogPostTranslation;
use App\Services\BlogCatalogue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function catalogued(int $reference, string $slug, BlogShelf $shelf = BlogShelf::Collecting): BlogPost
{
    $post = BlogPost::factory()->create([
        'reference' => $reference,
        'shelf' => $shelf,
        'published_at' => now()->subDays($reference),
    ]);

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => $slug,
        'title' => 'Entry '.$reference,
    ]);

    return $post->refresh();
}

it('summarises every published entry', function () {
    catalogued(1, 'first');
    catalogued(2, 'second');
    BlogPost::factory()->draft()->create();

    $entries = app(BlogCatalogue::class)->entries('en');

    expect($entries)->toHaveCount(2)
        ->and($entries[0])->toHaveKeys(['reference', 'title', 'slug', 'shelf', 'readingMinutes', 'isNew']);
});

it('marks a recent entry as new and an old one as not', function () {
    $recent = BlogPost::factory()->create(['reference' => 1, 'published_at' => now()->subDay()]);
    BlogPostTranslation::factory()->create(['blog_post_id' => $recent->id, 'locale' => 'en', 'slug' => 'recent']);
    $old = BlogPost::factory()->create(['reference' => 2, 'published_at' => now()->subYear()]);
    BlogPostTranslation::factory()->create(['blog_post_id' => $old->id, 'locale' => 'en', 'slug' => 'old']);

    $entries = collect(app(BlogCatalogue::class)->entries('en'))->keyBy('slug');

    expect($entries['recent']['isNew'])->toBeTrue()
        ->and($entries['old']['isNew'])->toBeFalse();
});

it('finds an entry by its slug', function () {
    catalogued(1, 'the-dundies');

    expect(app(BlogCatalogue::class)->find('en', 'the-dundies')?->title)->toBe('Entry 1');
});

it('finds an entry under its english slug from another locale', function () {
    catalogued(1, 'the-dundies');

    expect(app(BlogCatalogue::class)->find('fr_FR', 'the-dundies')?->locale)->toBe('en');
});

it('finds nothing for a draft', function () {
    $post = BlogPost::factory()->draft()->create();
    BlogPostTranslation::factory()->create(['blog_post_id' => $post->id, 'locale' => 'en', 'slug' => 'a-draft']);

    expect(app(BlogCatalogue::class)->find('en', 'a-draft'))->toBeNull();
});

it('resolves an old slug to the current one', function () {
    $post = catalogued(1, 'the-dundie-awards');
    BlogPostRedirect::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'the-dundies',
    ]);

    expect(app(BlogCatalogue::class)->currentSlugFor('en', 'the-dundies'))->toBe('the-dundie-awards');
});

it('resolves nothing for a slug that was never used', function () {
    expect(app(BlogCatalogue::class)->currentSlugFor('en', 'never-used'))->toBeNull();
});

it('walks the catalogue by reference number', function () {
    $first = catalogued(1, 'first');
    $second = catalogued(2, 'second');
    $third = catalogued(3, 'third');

    $catalogue = app(BlogCatalogue::class);

    expect($catalogue->adjacent($second, 'en', newer: false)['reference'])->toBe($first->reference())
        ->and($catalogue->adjacent($second, 'en', newer: true)['reference'])->toBe($third->reference())
        ->and($catalogue->adjacent($first, 'en', newer: false))->toBeNull()
        ->and($catalogue->adjacent($third, 'en', newer: true))->toBeNull();
});

it('gathers up to four other entries from the same shelf', function () {
    $post = catalogued(1, 'first', BlogShelf::Engineering);
    foreach (range(2, 7) as $reference) {
        catalogued($reference, 'entry-'.$reference, BlogShelf::Engineering);
    }
    catalogued(8, 'elsewhere', BlogShelf::Releases);

    $related = app(BlogCatalogue::class)->related($post, 'en');

    expect($related)->toHaveCount(4)
        ->and(collect($related)->pluck('slug'))->not->toContain('elsewhere', 'first');
});

it('counts the entries on each shelf', function () {
    catalogued(1, 'first', BlogShelf::Engineering);
    catalogued(2, 'second', BlogShelf::Engineering);
    catalogued(3, 'third', BlogShelf::Releases);

    $counts = app(BlogCatalogue::class)->countsByShelf();

    expect($counts[BlogShelf::Engineering->value])->toBe(2)
        ->and($counts[BlogShelf::Releases->value])->toBe(1);
});

it('lists only the languages an entry can be read in', function () {
    $post = catalogued(1, 'the-dundies');
    BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
    ]);
    BlogPostTranslation::factory()->inReview()->create([
        'blog_post_id' => $post->id,
        'locale' => 'de_DE',
        'slug' => 'die-dundies',
    ]);

    $slugs = app(BlogCatalogue::class)->slugsByLocale('en', 'the-dundies');

    expect($slugs)->toBe(['en' => 'the-dundies', 'fr_FR' => 'les-dundies']);
});
