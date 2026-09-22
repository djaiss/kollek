<?php

declare(strict_types=1);

use App\Actions\AddBlogPostImage;
use App\Enums\BlogPostStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function base64Image(int $width = 400, int $height = 300, string $format = 'png'): string
{
    $image = imagecreatetruecolor($width, $height);

    ob_start();

    match ($format) {
        'jpg' => imagejpeg($image),
        'gif' => imagegif($image),
        'webp' => imagewebp($image),
        default => imagepng($image),
    };

    $bytes = (string) ob_get_clean();
    imagedestroy($image);

    return base64_encode($bytes);
}

it('stores the picture under the entry it belongs to', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->create();

    $path = new AddBlogPostImage(
        user: $michael,
        blogPost: $post,
        content: base64Image(),
    )->execute();

    expect($path)->toStartWith('blog/'.$post->id.'/body/');
    Storage::disk(config('filesystems.default'))->assertExists($path);
});

it('names the picture with no trace of what the caller sent', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $path = new AddBlogPostImage(
        user: $michael,
        blogPost: BlogPost::factory()->create(),
        content: base64Image(),
    )->execute();

    expect(basename($path))->toMatch('/^[0-9a-f-]{36}\.png$/');
});

it('scales a picture down to the cap', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $path = new AddBlogPostImage(
        user: $michael,
        blogPost: BlogPost::factory()->create(),
        content: base64Image(3000, 1500),
    )->execute();

    [$width, $height] = getimagesizefromstring((string) Storage::disk(config('filesystems.default'))->get($path));

    expect($width)->toBe(1600)->and($height)->toBe(800);
});

it('leaves a picture smaller than the cap at its own size', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $path = new AddBlogPostImage(
        user: $michael,
        blogPost: BlogPost::factory()->create(),
        content: base64Image(320, 240),
    )->execute();

    [$width, $height] = getimagesizefromstring((string) Storage::disk(config('filesystems.default'))->get($path));

    expect($width)->toBe(320)->and($height)->toBe(240);
});

it('refuses something that is not base64', function () {
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);

    expect(fn () => new AddBlogPostImage(
        user: $michael,
        blogPost: BlogPost::factory()->create(),
        content: 'this is not base64 at all !!!',
    )->execute())->toThrow(InvalidArgumentException::class);
});

it('refuses base64 that decodes to something other than an image', function () {
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);

    expect(fn () => new AddBlogPostImage(
        user: $michael,
        blogPost: BlogPost::factory()->create(),
        content: base64_encode('Dear Michael, this is a text file.'),
    )->execute())->toThrow(InvalidArgumentException::class);
});

it('refuses an image type that is not on the list', function () {
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);

    expect(fn () => new AddBlogPostImage(
        user: $michael,
        blogPost: BlogPost::factory()->create(),
        content: base64Image(100, 100, 'gif'),
    )->execute())->toThrow(InvalidArgumentException::class);
});

it('refuses somebody who does not administer the instance', function () {
    Storage::fake(config('filesystems.default'));
    $toby = $this->createUser();

    expect(fn () => new AddBlogPostImage(
        user: $toby,
        blogPost: BlogPost::factory()->create(),
        content: base64Image(),
    )->execute())->toThrow(ModelNotFoundException::class);
});

it('logs the upload', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new AddBlogPostImage(
        user: $michael,
        blogPost: BlogPost::factory()->create(['status' => BlogPostStatus::Draft]),
        content: base64Image(),
    )->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostImageUpload);
});
