<?php

declare(strict_types=1);

use App\Actions\UpsertBlogPostTranslation;
use App\Enums\BlogTranslationState;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

/**
 * A published entry with its English text, which is what most of these are about.
 */
function publishedEntry(string $slug = 'the-dundies'): BlogPost
{
    $post = BlogPost::factory()->create();

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => $slug,
        'title' => 'The Dundies',
        'excerpt' => 'An awards ceremony.',
        'body' => 'The original body.',
        'state' => BlogTranslationState::Source,
    ]);

    return $post->refresh();
}

it('writes a language that did not exist yet', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = publishedEntry();

    $translation = new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post,
        locale: 'fr_FR',
        title: 'Les Dundies',
        excerpt: 'Une remise de prix.',
        body: 'Le corps.',
        slug: 'les-dundies',
    )->execute();

    expect($translation->locale)->toBe('fr_FR')
        ->and($translation->state)->toBe(BlogTranslationState::InReview);
});

it('keeps the state of a language it is editing', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = publishedEntry();
    BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
    ]);

    $translation = new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post->refresh(),
        locale: 'fr_FR',
        title: 'Les Dundies',
        excerpt: 'Une remise de prix.',
        body: 'Un corps corrigé.',
        slug: 'les-dundies',
    )->execute();

    expect($translation->state)->toBe(BlogTranslationState::Live);
});

it('keeps the old slug of a published entry as a redirect', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = publishedEntry('the-dundies');

    new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post,
        locale: 'en',
        title: 'The Dundies',
        excerpt: 'An awards ceremony.',
        body: 'The original body.',
        slug: 'the-dundie-awards',
    )->execute();

    $this->assertDatabaseHas('blog_post_redirects', [
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'the-dundies',
    ]);
});

it('leaves no redirect behind when a draft is renamed', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->draft()->create();
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'first-draft',
    ]);

    new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post->refresh(),
        locale: 'en',
        title: 'Second draft',
        excerpt: 'Still working on it.',
        body: 'Body.',
        slug: 'second-draft',
    )->execute();

    $this->assertDatabaseCount('blog_post_redirects', 0);
});

it('drops a redirect that would point at itself when a slug comes back around', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = publishedEntry('the-dundies');

    new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post,
        locale: 'en',
        title: 'The Dundies',
        excerpt: 'An awards ceremony.',
        body: 'The original body.',
        slug: 'the-dundie-awards',
    )->execute();

    new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post->refresh(),
        locale: 'en',
        title: 'The Dundies',
        excerpt: 'An awards ceremony.',
        body: 'The original body.',
        slug: 'the-dundies',
    )->execute();

    $this->assertDatabaseMissing('blog_post_redirects', [
        'blog_post_id' => $post->id,
        'slug' => 'the-dundies',
    ]);

    $this->assertDatabaseHas('blog_post_redirects', [
        'blog_post_id' => $post->id,
        'slug' => 'the-dundie-awards',
    ]);
});

it('flags every other language outdated when the english source changes', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = publishedEntry();
    BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
    ]);

    new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post->refresh(),
        locale: 'en',
        title: 'The Dundies',
        excerpt: 'An awards ceremony.',
        body: 'A rewritten body.',
        slug: 'the-dundies',
    )->execute();

    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'state' => BlogTranslationState::Outdated->value,
    ]);
});

it('leaves the other languages alone when the english edit changed nothing', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = publishedEntry();
    BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
    ]);

    new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post->refresh(),
        locale: 'en',
        title: 'The Dundies',
        excerpt: 'An awards ceremony.',
        body: 'The original body.',
        slug: 'the-dundies',
    )->execute();

    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'state' => BlogTranslationState::Live->value,
    ]);
});

it('does not flag anything outdated when a translation is edited', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = publishedEntry();
    BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'de_DE',
        'slug' => 'die-dundies',
    ]);

    new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post->refresh(),
        locale: 'fr_FR',
        title: 'Les Dundies',
        excerpt: 'Une remise de prix.',
        body: 'Le corps.',
        slug: 'les-dundies',
    )->execute();

    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'de_DE',
        'state' => BlogTranslationState::Live->value,
    ]);
});

it('logs the edit', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = publishedEntry();

    new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: $post,
        locale: 'en',
        title: 'The Dundies',
        excerpt: 'An awards ceremony.',
        body: 'A rewritten body.',
        slug: 'the-dundies',
    )->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostTranslationUpdate);
});

it('refuses a locale the instance does not offer', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new UpsertBlogPostTranslation(
        user: $michael,
        blogPost: publishedEntry(),
        locale: 'nl_NL',
        title: 'De Dundies',
        excerpt: 'Een prijsuitreiking.',
        body: 'Body.',
        slug: 'de-dundies',
    )->execute();
})->throws(ModelNotFoundException::class);

it('refuses to let somebody who is not an instance administrator write', function () {
    Queue::fake();
    $dwight = $this->createUser();

    new UpsertBlogPostTranslation(
        user: $dwight,
        blogPost: publishedEntry(),
        locale: 'en',
        title: 'Beets',
        excerpt: 'Beets.',
        body: 'Beets.',
        slug: 'beets',
    )->execute();
})->throws(ModelNotFoundException::class);
