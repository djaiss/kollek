<?php

declare(strict_types=1);

use App\Actions\UpdateBlogPostTranslationState;
use App\Enums\BlogTranslationState;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('marks a translation proofread so readers start seeing it', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->inReview()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
    ]);

    new UpdateBlogPostTranslationState(
        user: $michael,
        blogPost: $post,
        translation: $translation,
        state: BlogTranslationState::Live,
    )->execute();

    expect($translation->fresh()->state)->toBe(BlogTranslationState::Live);
});

it('takes a live translation back off the site', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
    ]);

    new UpdateBlogPostTranslationState(
        user: $michael,
        blogPost: $post,
        translation: $translation,
        state: BlogTranslationState::InReview,
    )->execute();

    expect($translation->fresh()->state)->toBe(BlogTranslationState::InReview);
});

it('refuses to move the english source, which every locale falls back to', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
    ]);

    new UpdateBlogPostTranslationState(
        user: $michael,
        blogPost: $post,
        translation: $translation,
        state: BlogTranslationState::InReview,
    )->execute();
})->throws(ModelNotFoundException::class);

it('refuses to promote a translation to the source', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
    ]);

    new UpdateBlogPostTranslationState(
        user: $michael,
        blogPost: $post,
        translation: $translation,
        state: BlogTranslationState::Source,
    )->execute();
})->throws(ModelNotFoundException::class);

it('refuses a translation belonging to another entry', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $translation = BlogPostTranslation::factory()->live()->create(['locale' => 'fr_FR']);

    new UpdateBlogPostTranslationState(
        user: $michael,
        blogPost: BlogPost::factory()->create(),
        translation: $translation,
        state: BlogTranslationState::InReview,
    )->execute();
})->throws(ModelNotFoundException::class);

it('logs the change', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->inReview()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
    ]);

    new UpdateBlogPostTranslationState(
        user: $michael,
        blogPost: $post,
        translation: $translation,
        state: BlogTranslationState::Live,
    )->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostTranslationStateUpdate);
});

it('refuses to let somebody who is not an instance administrator change it', function () {
    Queue::fake();
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->inReview()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
    ]);

    new UpdateBlogPostTranslationState(
        user: $this->createUser(),
        blogPost: $post,
        translation: $translation,
        state: BlogTranslationState::Live,
    )->execute();
})->throws(ModelNotFoundException::class);
