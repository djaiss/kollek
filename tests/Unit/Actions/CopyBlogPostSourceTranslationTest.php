<?php

declare(strict_types=1);

use App\Actions\CopyBlogPostSourceTranslation;
use App\Enums\BlogTranslationState;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function entryWithSource(): BlogPost
{
    $post = BlogPost::factory()->create();

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'title' => 'The Dundies',
        'excerpt' => 'An awards ceremony.',
        'body' => 'The body.',
    ]);

    return $post->refresh();
}

it('starts a language off from the english text', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $translation = new CopyBlogPostSourceTranslation(
        user: $michael,
        blogPost: entryWithSource(),
        locale: 'fr_FR',
    )->execute();

    expect($translation->locale)->toBe('fr_FR')
        ->and($translation->title)->toBe('The Dundies')
        ->and($translation->body)->toBe('The body.');
});

it('lands the copy in review, because it is english sitting in a french row', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $translation = new CopyBlogPostSourceTranslation(
        user: $michael,
        blogPost: entryWithSource(),
        locale: 'fr_FR',
    )->execute();

    expect($translation->state)->toBe(BlogTranslationState::InReview);
});

it('refuses to overwrite a language somebody has already written', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = entryWithSource();
    BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
    ]);

    new CopyBlogPostSourceTranslation(
        user: $michael,
        blogPost: $post->refresh(),
        locale: 'fr_FR',
    )->execute();
})->throws(ModelNotFoundException::class);

it('refuses to copy english onto itself', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new CopyBlogPostSourceTranslation(
        user: $michael,
        blogPost: entryWithSource(),
        locale: 'en',
    )->execute();
})->throws(ModelNotFoundException::class);

it('refuses a locale the instance does not offer', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new CopyBlogPostSourceTranslation(
        user: $michael,
        blogPost: entryWithSource(),
        locale: 'nl_NL',
    )->execute();
})->throws(ModelNotFoundException::class);

it('refuses to let somebody who is not an instance administrator copy', function () {
    Queue::fake();

    new CopyBlogPostSourceTranslation(
        user: $this->createUser(),
        blogPost: entryWithSource(),
        locale: 'fr_FR',
    )->execute();
})->throws(ModelNotFoundException::class);
