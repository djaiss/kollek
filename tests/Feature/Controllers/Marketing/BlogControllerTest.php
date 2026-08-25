<?php

declare(strict_types=1);

use App\Enums\BlogPostStatus;
use App\Enums\BlogShelf;
use App\Enums\BlogTranslationState;
use App\Models\BlogPost;
use App\Models\BlogPostRedirect;
use App\Models\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('marketing.show', true);
});

/**
 * An entry with English text, which every locale falls back to.
 */
function publicEntry(string $slug = 'the-dundies', BlogPostStatus $status = BlogPostStatus::Published): BlogPost
{
    $post = BlogPost::factory()->create([
        'status' => $status,
        'published_at' => $status === BlogPostStatus::Draft ? null : now()->subDay(),
        'shelf' => BlogShelf::Collecting,
        'author_name' => 'Michael Scott',
    ]);

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => $slug,
        'title' => 'The Dundies',
        'excerpt' => 'An awards ceremony.',
        'body' => "The opening.\n\n## An object is not a row\n\nA row is flat.",
        'state' => BlogTranslationState::Source,
    ]);

    return $post->refresh();
}

it('shows the catalogue', function () {
    publicEntry();

    $response = $this->get('en/blog');

    $response->assertOk();
    $response->assertViewIs('marketing.blog.index');
    $response->assertSee('The Dundies');
});

it('leaves a draft out of the catalogue', function () {
    publicEntry('a-draft', BlogPostStatus::Draft);

    $response = $this->get('en/blog');

    $response->assertOk();
    $response->assertDontSee('The Dundies');
});

it('leaves an archived entry out of the catalogue', function () {
    publicEntry('archived-one', BlogPostStatus::Archived);

    $response = $this->get('en/blog');

    $response->assertOk();
    $response->assertDontSee('The Dundies');
});

it('shows one entry', function () {
    publicEntry();

    $response = $this->get('en/blog/the-dundies');

    $response->assertOk();
    $response->assertViewIs('marketing.blog.show');
    $response->assertSee('The Dundies');
    $response->assertSee('Michael Scott');
});

it('answers not found for a draft', function () {
    publicEntry('a-draft', BlogPostStatus::Draft);

    $this->get('en/blog/a-draft')->assertNotFound();
});

it('keeps answering on an archived entry, so old links do not break', function () {
    publicEntry('archived-one', BlogPostStatus::Archived);

    $this->get('en/blog/archived-one')->assertOk();
});

it('answers not found for a slug nobody has used', function () {
    $this->get('en/blog/never-written')->assertNotFound();
});

it('redirects an old slug permanently to the current one', function () {
    $post = publicEntry('the-dundie-awards');
    BlogPostRedirect::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'the-dundies',
    ]);

    $response = $this->get('en/blog/the-dundies');

    $response->assertStatus(301);
    $response->assertRedirect(route('marketing.blog.show', ['locale' => 'en', 'slug' => 'the-dundie-awards']));
});

it('serves a live translation in its own language', function () {
    $post = publicEntry();
    BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
        'title' => 'Les Dundies',
        'excerpt' => 'Une remise de prix.',
        'body' => 'Le corps.',
    ]);

    $response = $this->get('fr/blog/les-dundies');

    $response->assertOk();
    $response->assertSee('Les Dundies');
});

it('falls back to english when a locale has no translation', function () {
    publicEntry();

    $response = $this->get('fr/blog/the-dundies');

    $response->assertOk();
    $response->assertSee('The Dundies');
});

it('falls back to english while a translation is still in review', function () {
    $post = publicEntry();
    BlogPostTranslation::factory()->inReview()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
        'title' => 'Les Dundies',
    ]);

    $response = $this->get('fr/blog/les-dundies');

    $response->assertOk();
    $response->assertSee('The Dundies');
    $response->assertDontSee('Les Dundies');
});

it('falls back to english once a translation has gone outdated', function () {
    $post = publicEntry();
    BlogPostTranslation::factory()->outdated()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
        'title' => 'Les Dundies',
    ]);

    $response = $this->get('fr/blog/les-dundies');

    $response->assertOk();
    $response->assertSee('The Dundies');
});

it('lets two languages share a slug', function () {
    $first = publicEntry('the-dundies');
    $second = BlogPost::factory()->create(['published_at' => now()->subDays(2)]);
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $second->id,
        'locale' => 'en',
        'slug' => 'another-entry',
        'title' => 'Another entry',
    ]);
    BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $second->id,
        'locale' => 'fr_FR',
        'slug' => 'the-dundies',
        'title' => 'Une autre entrée',
    ]);

    $this->get('en/blog/the-dundies')->assertSee('The Dundies');
    $this->get('fr/blog/the-dundies')->assertSee('Une autre entrée');

    expect($first->id)->not->toBe($second->id);
});

it('renders the body, the contents list and the measurements', function () {
    publicEntry();

    $response = $this->get('en/blog/the-dundies');

    $response->assertOk();
    $response->assertSee('An object is not a row');
    $response->assertSee('Contents');
    $response->assertSee('Measurements');
    $response->assertSee('Shelved against the classics');
    $response->assertDontSee('Reading pace');
});

it('serves the feed', function () {
    publicEntry();

    $response = $this->get('en/blog/feed.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
    $response->assertSee('The Dundies', escape: false);
});

it('carries the whole entry in the feed rather than a teaser', function () {
    publicEntry();

    $response = $this->get('en/blog/feed.xml');

    $response->assertSee('An object is not a row', escape: false);
});

it('leaves a draft out of the feed', function () {
    publicEntry('a-draft', BlogPostStatus::Draft);

    $this->get('en/blog/feed.xml')->assertDontSee('The Dundies', escape: false);
});

it('links the blog from the header and the footer', function () {
    $response = $this->get('en');

    $response->assertOk();
    $response->assertSee(route('marketing.blog.index', ['locale' => 'en']), escape: false);
});

it('pluralises the count on each shelf card', function () {
    publicEntry();

    $response = $this->get('en/blog');

    $response->assertSee('1 entry');
    $response->assertSee('0 entries');
});

it('answers not found for a social card that was never uploaded', function () {
    publicEntry();

    $this->get('en/blog/the-dundies/og.png')->assertNotFound();
});
