<?php

declare(strict_types=1);

use App\Actions\ArchiveBlogPost;
use App\Enums\BlogPostStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('takes an entry out of the catalogue', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();

    new ArchiveBlogPost(user: $michael, blogPost: $post)->execute();

    expect($post->fresh()->status)->toBe(BlogPostStatus::Archived);
});

it('leaves the publication date alone so the url keeps answering', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();

    new ArchiveBlogPost(user: $michael, blogPost: $post)->execute();

    expect($post->fresh()->published_at)->not->toBeNull();
});

it('logs the archival', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new ArchiveBlogPost(user: $michael, blogPost: BlogPost::factory()->create())->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostArchived);
});

it('refuses to let somebody who is not an instance administrator archive', function () {
    Queue::fake();

    new ArchiveBlogPost(user: $this->createUser(), blogPost: BlogPost::factory()->create())->execute();
})->throws(ModelNotFoundException::class);
