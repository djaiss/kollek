<?php

declare(strict_types=1);

use App\Actions\DestroyBlogPostOgImage;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('puts the entry back to the site wide card', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $disk = Storage::disk(config('filesystems.default'));
    $disk->put('blog/card.png', 'a card');

    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'og_image_path' => 'blog/card.png',
    ]);

    new DestroyBlogPostOgImage(user: $michael, blogPost: $post, translation: $translation)->execute();

    expect($translation->fresh()->og_image_path)->toBeNull();
    $disk->assertMissing('blog/card.png');
});

it('does nothing when there was no card to begin with', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->create(['blog_post_id' => $post->id]);

    new DestroyBlogPostOgImage(user: $michael, blogPost: $post, translation: $translation)->execute();

    expect($translation->fresh()->og_image_path)->toBeNull();
});

it('logs the removal', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();

    new DestroyBlogPostOgImage(
        user: $michael,
        blogPost: $post,
        translation: BlogPostTranslation::factory()->create(['blog_post_id' => $post->id]),
    )->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostOgImageDeletion);
});

it('refuses to let somebody who is not an instance administrator remove it', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $post = BlogPost::factory()->create();

    new DestroyBlogPostOgImage(
        user: $this->createUser(),
        blogPost: $post,
        translation: BlogPostTranslation::factory()->create(['blog_post_id' => $post->id]),
    )->execute();
})->throws(ModelNotFoundException::class);
