<?php

declare(strict_types=1);

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function storedImage(BlogPost $post): string
{
    $name = Str::uuid()->toString().'.png';

    Storage::disk(config('filesystems.default'))->put('blog/'.$post->id.'/body/'.$name, 'not really a png');

    return $name;
}

it('serves a picture to a reader with no session', function () {
    Storage::fake(config('filesystems.default'));
    $post = BlogPost::factory()->create();
    $name = storedImage($post);

    $response = $this->get('blog-images/'.$post->id.'/'.$name);

    $response->assertOk();
});

it('answers not found for a picture nobody uploaded', function () {
    Storage::fake(config('filesystems.default'));
    $post = BlogPost::factory()->create();

    $this->get('blog-images/'.$post->id.'/'.Str::uuid()->toString().'.png')->assertNotFound();
});

it('answers not found for an entry that does not exist', function () {
    Storage::fake(config('filesystems.default'));

    $this->get('blog-images/9999/'.Str::uuid()->toString().'.png')->assertNotFound();
});

it('refuses a name that is not a uuid, so no other path can be asked for', function () {
    Storage::fake(config('filesystems.default'));
    $post = BlogPost::factory()->create();

    $this->get('blog-images/'.$post->id.'/card.png')->assertNotFound();
    $this->get('blog-images/'.$post->id.'/'.Str::uuid()->toString().'.svg')->assertNotFound();
});

it('keeps a picture of one entry out of another entry', function () {
    Storage::fake(config('filesystems.default'));
    $dundies = BlogPost::factory()->create();
    $other = BlogPost::factory()->create();
    $name = storedImage($dundies);

    $this->get('blog-images/'.$other->id.'/'.$name)->assertNotFound();
});
