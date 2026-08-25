<?php

declare(strict_types=1);

use App\Actions\PublishBlogPost;
use App\Enums\BlogPostStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function draftEntry(): BlogPost
{
    $post = BlogPost::factory()->draft()->create();

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
    ]);

    return $post->refresh();
}

it('publishes an entry and stamps the publication time', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = draftEntry();

    new PublishBlogPost(user: $michael, blogPost: $post)->execute();

    expect($post->fresh()->status)->toBe(BlogPostStatus::Published)
        ->and($post->fresh()->published_at)->not->toBeNull();
});

it('keeps the original publication date when an archived entry comes back', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $publishedAt = now()->subMonth()->startOfSecond();
    $post = BlogPost::factory()->archived()->create(['published_at' => $publishedAt]);
    BlogPostTranslation::factory()->create(['blog_post_id' => $post->id, 'locale' => 'en']);

    new PublishBlogPost(user: $michael, blogPost: $post->refresh())->execute();

    expect($post->fresh()->status)->toBe(BlogPostStatus::Published)
        ->and($post->fresh()->published_at->timestamp)->toBe($publishedAt->timestamp);
});

it('refuses to publish an entry with nothing written in english', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new PublishBlogPost(user: $michael, blogPost: BlogPost::factory()->draft()->create())->execute();
})->throws(ModelNotFoundException::class);

it('logs the publication', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new PublishBlogPost(user: $michael, blogPost: draftEntry())->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostPublished);
});

it('refuses to let somebody who is not an instance administrator publish', function () {
    Queue::fake();

    new PublishBlogPost(user: $this->createUser(), blogPost: draftEntry())->execute();
})->throws(ModelNotFoundException::class);
