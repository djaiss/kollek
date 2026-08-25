<?php

declare(strict_types=1);

use App\Actions\CreateBlogPost;
use App\Enums\BlogPostStatus;
use App\Enums\BlogShelf;
use App\Enums\BlogTranslationState;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('starts a blog entry as a draft with its english text', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $post = new CreateBlogPost(
        user: $michael,
        title: 'Why Dunder Mifflin still uses paper',
        excerpt: 'A short defence of the ream.',
        body: '## The case for paper',
        shelf: BlogShelf::Collecting,
    )->execute();

    expect($post->status)->toBe(BlogPostStatus::Draft)
        ->and($post->published_at)->toBeNull()
        ->and($post->shelf)->toBe(BlogShelf::Collecting)
        ->and($post->author_id)->toBe($michael->id);

    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'why-dunder-mifflin-still-uses-paper',
        'title' => 'Why Dunder Mifflin still uses paper',
        'state' => BlogTranslationState::Source->value,
    ]);
});

it('slugs the title when no slug is given', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $post = new CreateBlogPost(
        user: $michael,
        title: 'Threat Level Midnight: a retrospective',
        excerpt: 'Eleven years in the making.',
        body: 'It was worth it.',
        shelf: BlogShelf::Releases,
    )->execute();

    expect($post->source()->slug)->toBe('threat-level-midnight-a-retrospective');
});

it('slugs a slug that was given', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $post = new CreateBlogPost(
        user: $michael,
        title: 'Threat Level Midnight',
        excerpt: 'Eleven years in the making.',
        body: 'It was worth it.',
        shelf: BlogShelf::Releases,
        slug: 'Golden Face Returns!',
    )->execute();

    expect($post->source()->slug)->toBe('golden-face-returns');
});

it('gives the first entry the reference number one', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $post = new CreateBlogPost(
        user: $michael,
        title: 'Hello',
        excerpt: 'The first one.',
        body: 'Hello.',
        shelf: BlogShelf::Releases,
    )->execute();

    expect($post->reference)->toBe(1)
        ->and($post->reference())->toBe('KLK-0001');
});

it('never reissues the reference of a deleted entry', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    BlogPost::factory()->create(['reference' => 30]);

    $post = new CreateBlogPost(
        user: $michael,
        title: 'The one after thirty',
        excerpt: 'Numbering is permanent.',
        body: 'It really is.',
        shelf: BlogShelf::Engineering,
    )->execute();

    expect($post->reference)->toBe(31)
        ->and($post->reference())->toBe('KLK-0031');
});

it('logs the creation', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    new CreateBlogPost(
        user: $michael,
        title: 'Hello',
        excerpt: 'The first one.',
        body: 'Hello.',
        shelf: BlogShelf::Releases,
    )->execute();

    Queue::assertPushedOn('low', LogUserAction::class, fn (LogUserAction $job): bool => $job->action === UserActionEnum::BlogPostCreation);
});

it('refuses to let somebody who is not an instance administrator write', function () {
    Queue::fake();
    $dwight = $this->createUser();

    new CreateBlogPost(
        user: $dwight,
        title: 'Beet farming quarterly',
        excerpt: 'Schrute Farms.',
        body: 'Beets.',
        shelf: BlogShelf::Collecting,
    )->execute();
})->throws(ModelNotFoundException::class);
