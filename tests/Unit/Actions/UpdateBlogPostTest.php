<?php

declare(strict_types=1);

use App\Actions\UpdateBlogPost;
use App\Enums\BlogShelf;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('moves an entry to another shelf', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create(['shelf' => BlogShelf::Collecting]);

    new UpdateBlogPost(
        user: $michael,
        blogPost: $post,
        shelf: BlogShelf::Engineering,
        isFeatured: true,
        robots: 'noindex',
    )->execute();

    expect($post->fresh()->shelf)->toBe(BlogShelf::Engineering)
        ->and($post->fresh()->is_featured)->toBeTrue()
        ->and($post->fresh()->robots)->toBe('noindex');
});

it('replaces the tags wholesale', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $post->tags()->create(['name' => 'Beets']);

    new UpdateBlogPost(
        user: $michael,
        blogPost: $post,
        shelf: BlogShelf::Collecting,
        isFeatured: false,
        robots: 'index,follow',
        tags: ['Paper', 'Scranton'],
    )->execute();

    expect($post->fresh()->tags->pluck('name')->all())->toBe(['Paper', 'Scranton']);
});

it('ignores blank and repeated tags', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();

    new UpdateBlogPost(
        user: $michael,
        blogPost: $post,
        shelf: BlogShelf::Collecting,
        isFeatured: false,
        robots: 'index,follow',
        tags: ['Paper', '  ', 'Paper', ' Scranton '],
    )->execute();

    expect($post->fresh()->tags->pluck('name')->all())->toBe(['Paper', 'Scranton']);
});

it('logs the update', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new UpdateBlogPost(
        user: $michael,
        blogPost: BlogPost::factory()->create(),
        shelf: BlogShelf::Releases,
        isFeatured: false,
        robots: 'index,follow',
    )->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostUpdate);
});

it('refuses to let somebody who is not an instance administrator update', function () {
    Queue::fake();

    new UpdateBlogPost(
        user: $this->createUser(),
        blogPost: BlogPost::factory()->create(),
        shelf: BlogShelf::Releases,
        isFeatured: false,
        robots: 'index,follow',
    )->execute();
})->throws(ModelNotFoundException::class);
