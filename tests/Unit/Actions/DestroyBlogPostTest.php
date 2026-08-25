<?php

declare(strict_types=1);

use App\Actions\DestroyBlogPost;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\BlogPostRedirect;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('deletes the entry', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create(['blog_post_id' => $post->id]);
    BlogPostRedirect::factory()->create(['blog_post_id' => $post->id]);

    new DestroyBlogPost(user: $michael, blogPost: $post)->execute();

    $this->assertModelMissing($post);
});

it('deletes the social cards off the disk', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    Storage::disk(config('filesystems.default'))->put('blog/1/card.png', 'a card');

    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'og_image_path' => 'blog/1/card.png',
    ]);

    new DestroyBlogPost(user: $michael, blogPost: $post->refresh())->execute();

    Storage::disk(config('filesystems.default'))->assertMissing('blog/1/card.png');
});

it('logs the deletion', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new DestroyBlogPost(user: $michael, blogPost: BlogPost::factory()->create())->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostDeletion);
});

it('refuses to let somebody who is not an instance administrator delete', function () {
    Queue::fake();

    new DestroyBlogPost(user: $this->createUser(), blogPost: BlogPost::factory()->create())->execute();
})->throws(ModelNotFoundException::class);
