<?php

declare(strict_types=1);

use App\Enums\BlogPostStatus;
use App\Enums\BlogTranslationState;
use App\Models\BlogPost;
use App\Models\BlogPostRedirect;
use App\Models\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has translations', function () {
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create(['blog_post_id' => $post->id]);

    expect($post->translations()->exists())->toBeTrue();
});

it('has redirects', function () {
    $post = BlogPost::factory()->create();
    BlogPostRedirect::factory()->create(['blog_post_id' => $post->id]);

    expect($post->redirects()->exists())->toBeTrue();
});

it('has tags', function () {
    $post = BlogPost::factory()->create();
    $post->tags()->create(['name' => 'Paper']);

    expect($post->tags()->exists())->toBeTrue();
});

it('has an author', function () {
    $michael = $this->createUser();
    $post = BlogPost::factory()->create(['author_id' => $michael->id]);

    expect($post->author()->exists())->toBeTrue();
});

it('prints the reference the way people cite it', function () {
    expect(BlogPost::factory()->create(['reference' => 31])->reference())->toBe('KLK-0031')
        ->and(BlogPost::factory()->create(['reference' => 7])->reference())->toBe('KLK-0007')
        ->and(BlogPost::factory()->create(['reference' => 1234])->reference())->toBe('KLK-1234');
});

it('lists only the published entries, newest first', function () {
    $older = BlogPost::factory()->create(['published_at' => now()->subMonth()]);
    $newer = BlogPost::factory()->create(['published_at' => now()->subDay()]);
    BlogPost::factory()->draft()->create();
    BlogPost::factory()->archived()->create();

    $published = BlogPost::query()->published()->get();

    expect($published->pluck('id')->all())->toBe([$newer->id, $older->id]);
});

it('leaves an entry dated in the future out of the catalogue', function () {
    BlogPost::factory()->create(['published_at' => now()->addWeek()]);

    expect(BlogPost::query()->published()->count())->toBe(0);
});

it('answers on a url for a published or archived entry but not a draft', function () {
    expect(BlogPost::factory()->create()->isReadable())->toBeTrue()
        ->and(BlogPost::factory()->archived()->create()->isReadable())->toBeTrue()
        ->and(BlogPost::factory()->draft()->create()->isReadable())->toBeFalse();
});

it('finds the english source', function () {
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create(['blog_post_id' => $post->id, 'locale' => 'en']);
    BlogPostTranslation::factory()->live()->create(['blog_post_id' => $post->id, 'locale' => 'fr_FR']);

    expect($post->refresh()->source()->locale)->toBe('en');
});

it('serves a live translation in its own language', function () {
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create(['blog_post_id' => $post->id, 'locale' => 'en']);
    BlogPostTranslation::factory()->live()->create(['blog_post_id' => $post->id, 'locale' => 'fr_FR']);

    expect($post->refresh()->translation('fr_FR')->locale)->toBe('fr_FR');
});

it('falls back to english for a translation that is not public', function () {
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create(['blog_post_id' => $post->id, 'locale' => 'en']);
    BlogPostTranslation::factory()->inReview()->create(['blog_post_id' => $post->id, 'locale' => 'fr_FR']);

    expect($post->refresh()->translation('fr_FR')->locale)->toBe('en');
});

it('falls back to english for a locale nobody has written', function () {
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create(['blog_post_id' => $post->id, 'locale' => 'en']);

    expect($post->refresh()->translation('de_DE')->locale)->toBe('en');
});

it('hands the administration a translation whether or not it is public', function () {
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->inReview()->create(['blog_post_id' => $post->id, 'locale' => 'fr_FR']);

    expect($post->refresh()->translationFor('fr_FR')->state)->toBe(BlogTranslationState::InReview)
        ->and($post->translationFor('de_DE'))->toBeNull();
});

it('lists the locales a reader can actually read it in', function () {
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create(['blog_post_id' => $post->id, 'locale' => 'en']);
    BlogPostTranslation::factory()->live()->create(['blog_post_id' => $post->id, 'locale' => 'fr_FR']);
    BlogPostTranslation::factory()->inReview()->create(['blog_post_id' => $post->id, 'locale' => 'de_DE']);
    BlogPostTranslation::factory()->outdated()->create(['blog_post_id' => $post->id, 'locale' => 'es_ES']);

    expect($post->refresh()->liveLocales())->toBe(['en', 'fr_FR']);
});

it('casts the status and the shelf', function () {
    $post = BlogPost::factory()->create();

    expect($post->status)->toBeInstanceOf(BlogPostStatus::class)
        ->and($post->is_featured)->toBeBool();
});
