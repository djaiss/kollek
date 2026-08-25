<?php

declare(strict_types=1);

use App\Actions\UpdateBlogPostOgImage;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('writes the card at the size social platforms read', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->create(['blog_post_id' => $post->id]);

    new UpdateBlogPostOgImage(
        user: $michael,
        blogPost: $post,
        translation: $translation,
        file: UploadedFile::fake()->image('card.png', 800, 800),
    )->execute();

    $path = (string) $translation->fresh()->og_image_path;
    Storage::disk(config('filesystems.default'))->assertExists($path);

    [$width, $height] = getimagesizefromstring((string) Storage::disk(config('filesystems.default'))->get($path));
    expect($width)->toBe(1200)->and($height)->toBe(630);
});

it('removes the earlier card only once the new one is saved', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $disk = Storage::disk(config('filesystems.default'));
    $disk->put('blog/old.png', 'the old card');

    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();
    $translation = BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'og_image_path' => 'blog/old.png',
    ]);

    new UpdateBlogPostOgImage(
        user: $michael,
        blogPost: $post,
        translation: $translation,
        file: UploadedFile::fake()->image('card.png', 1200, 630),
    )->execute();

    $disk->assertMissing('blog/old.png');
    $disk->assertExists((string) $translation->fresh()->og_image_path);
});

it('rejects a file that is not an image, whatever its extension claims', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();

    new UpdateBlogPostOgImage(
        user: $michael,
        blogPost: $post,
        translation: BlogPostTranslation::factory()->create(['blog_post_id' => $post->id]),
        file: UploadedFile::fake()->create('card.png', 10, 'application/pdf'),
    )->execute();
})->throws(InvalidArgumentException::class);

it('refuses a translation belonging to another entry', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new UpdateBlogPostOgImage(
        user: $michael,
        blogPost: BlogPost::factory()->create(),
        translation: BlogPostTranslation::factory()->create(),
        file: UploadedFile::fake()->image('card.png'),
    )->execute();
})->throws(ModelNotFoundException::class);

it('logs the replacement', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();

    new UpdateBlogPostOgImage(
        user: $michael,
        blogPost: $post,
        translation: BlogPostTranslation::factory()->create(['blog_post_id' => $post->id]),
        file: UploadedFile::fake()->image('card.png'),
    )->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostOgImageUpdate);
});

it('refuses to let somebody who is not an instance administrator upload', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $post = BlogPost::factory()->create();

    new UpdateBlogPostOgImage(
        user: $this->createUser(),
        blogPost: $post,
        translation: BlogPostTranslation::factory()->create(['blog_post_id' => $post->id]),
        file: UploadedFile::fake()->image('card.png'),
    )->execute();
})->throws(ModelNotFoundException::class);
