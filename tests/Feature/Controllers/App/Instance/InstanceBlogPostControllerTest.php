<?php

declare(strict_types=1);

use App\Enums\BlogPostStatus;
use App\Enums\BlogShelf;
use App\Enums\BlogTranslationState;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function entryFor(string $slug = 'the-dundies', BlogPostStatus $status = BlogPostStatus::Published): BlogPost
{
    $post = BlogPost::factory()->create([
        'status' => $status,
        'published_at' => $status === BlogPostStatus::Draft ? null : now()->subDay(),
    ]);

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => $slug,
        'title' => 'The Dundies',
        'state' => BlogTranslationState::Source,
    ]);

    return $post->refresh();
}

it('lists the entries to an instance administrator', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    entryFor();

    $response = $this->actingAs($michael)->get('instance-admin/marketing/blog-posts');

    $response->assertOk();
    $response->assertViewIs('app.instance.marketing.blogPosts.index');
    $response->assertSee('The Dundies');
});

it('filters the list down to one bucket', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    entryFor('published-one');
    entryFor('drafted-one', BlogPostStatus::Draft);

    $response = $this->actingAs($michael)->get('instance-admin/marketing/blog-posts/draft');

    $response->assertOk();
    $response->assertSee('drafted-one');
    $response->assertDontSee('published-one');
});

it('searches by title and by slug', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    entryFor('threat-level-midnight');
    $other = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $other->id,
        'locale' => 'en',
        'slug' => 'beet-farming',
        'title' => 'Beet farming',
    ]);

    $response = $this->actingAs($michael)->get('instance-admin/marketing/blog-posts?search=beet');

    $response->assertOk();
    $response->assertSee('Beet farming');
    $response->assertDontSee('threat-level-midnight');
});

it('searches without regard to case, which plain LIKE does not give on postgres', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    entryFor('threat-level-midnight');
    $other = BlogPost::factory()->create();
    BlogPostTranslation::factory()->create([
        'blog_post_id' => $other->id,
        'locale' => 'en',
        'slug' => 'beet-farming',
        'title' => 'Beet Farming Quarterly',
    ]);

    $response = $this->actingAs($michael)->get('instance-admin/marketing/blog-posts?search=BEET');

    $response->assertOk();
    $response->assertSee('Beet Farming Quarterly');
});

it('treats a wildcard in the search term literally', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    entryFor('threat-level-midnight');

    $response = $this->actingAs($michael)->get('instance-admin/marketing/blog-posts?search=%25');

    $response->assertOk();
    $response->assertSee('No posts match this filter.');
});

it('shows the form for a new entry', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $response = $this->actingAs($michael)->get('instance-admin/marketing/blog-posts/new');

    $response->assertOk();
    $response->assertViewIs('app.instance.marketing.blogPosts.new');
});

it('creates an entry and lands on its english text', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $response = $this->actingAs($michael)->post('instance-admin/marketing/blog-posts', [
        'title' => 'Why Dunder Mifflin still uses paper',
        'excerpt' => 'A short defence of the ream.',
        'body' => '## The case for paper',
        'shelf' => BlogShelf::Collecting->value,
    ]);

    $post = BlogPost::query()->firstOrFail();

    $response->assertRedirect('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en');
    $response->assertSessionHas('status', 'Blog post created');
    $this->assertDatabaseHas('blog_posts', ['status' => BlogPostStatus::Draft->value]);
});

it('rejects an entry with no title', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $response = $this->actingAs($michael)->post('instance-admin/marketing/blog-posts', [
        'excerpt' => 'A short defence of the ream.',
        'body' => 'Body.',
        'shelf' => BlogShelf::Collecting->value,
    ]);

    $response->assertSessionHasErrors('title');
});

it('publishes an entry', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = entryFor('the-dundies', BlogPostStatus::Draft);

    $response = $this->actingAs($michael)->put('instance-admin/marketing/blog-posts/'.$post->id, [
        'intent' => 'publish',
    ]);

    $response->assertSessionHas('status', 'Blog post published');
    expect($post->fresh()->status)->toBe(BlogPostStatus::Published);
});

it('archives an entry', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = entryFor();

    $response = $this->actingAs($michael)->put('instance-admin/marketing/blog-posts/'.$post->id, [
        'intent' => 'archive',
    ]);

    $response->assertSessionHas('status', 'Blog post archived');
    expect($post->fresh()->status)->toBe(BlogPostStatus::Archived);
});

it('saves the shelf, the tags and the crawler settings', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = entryFor();

    $response = $this->actingAs($michael)->put('instance-admin/marketing/blog-posts/'.$post->id, [
        'intent' => 'save',
        'shelf' => BlogShelf::Engineering->value,
        'is_featured' => '1',
        'robots' => 'noindex',
        'tags' => 'Paper, Scranton',
    ]);

    $response->assertSessionHas('status', 'Blog post updated');

    $post->refresh();
    expect($post->shelf)->toBe(BlogShelf::Engineering)
        ->and($post->is_featured)->toBeTrue()
        ->and($post->robots)->toBe('noindex')
        ->and($post->tags->pluck('name')->all())->toBe(['Paper', 'Scranton']);
});

it('deletes an entry', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = entryFor();

    $response = $this->actingAs($michael)->delete('instance-admin/marketing/blog-posts/'.$post->id);

    $response->assertSessionHas('status', 'Blog post deleted');
    $this->assertModelMissing($post);
});

it('hides the whole section from somebody who is not an instance administrator', function () {
    $dwight = $this->createUser();
    $post = entryFor();

    $this->actingAs($dwight)->get('instance-admin/marketing/blog-posts')->assertNotFound();
    $this->actingAs($dwight)->get('instance-admin/marketing/blog-posts/new')->assertNotFound();
    $this->actingAs($dwight)->post('instance-admin/marketing/blog-posts', [])->assertNotFound();
    $this->actingAs($dwight)->put('instance-admin/marketing/blog-posts/'.$post->id, ['intent' => 'publish'])->assertNotFound();
    $this->actingAs($dwight)->delete('instance-admin/marketing/blog-posts/'.$post->id)->assertNotFound();
});
