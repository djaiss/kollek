<?php

declare(strict_types=1);

use App\Enums\BlogPostStatus;
use App\Enums\BlogShelf;
use App\Enums\BlogTranslationState;
use App\Mcp\Servers\InstanceServer;
use App\Mcp\Tools\Blog\ArchiveBlogPost;
use App\Mcp\Tools\Blog\CreateBlogPost;
use App\Mcp\Tools\Blog\ListBlogPosts;
use App\Mcp\Tools\Blog\PublishBlogPost;
use App\Mcp\Tools\Blog\ShowBlogPost;
use App\Mcp\Tools\Blog\UpdateBlogPost;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function pilotedEntry(string $slug = 'the-dundies', string $title = 'The Dundies', BlogPostStatus $status = BlogPostStatus::Published): BlogPost
{
    $post = BlogPost::factory()->create([
        'status' => $status,
        'published_at' => $status === BlogPostStatus::Draft ? null : now()->subDay(),
    ]);

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => $slug,
        'title' => $title,
        'state' => BlogTranslationState::Source,
    ]);

    return $post->refresh();
}

it('lists the entries', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    pilotedEntry();

    $response = InstanceServer::actingAs($michael)->tool(ListBlogPosts::class);

    $response->assertOk();
    $response->assertSee('The Dundies');
});

it('narrows the list down to one bucket', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    pilotedEntry('published-one', 'Published one');
    pilotedEntry('drafted-one', 'Drafted one', BlogPostStatus::Draft);

    $response = InstanceServer::actingAs($michael)->tool(ListBlogPosts::class, ['status' => 'draft']);

    $response->assertOk();
    $response->assertSee('drafted-one');
    $response->assertDontSee('published-one');
});

it('searches the list by title', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    pilotedEntry('threat-level-midnight', 'Threat Level Midnight');
    pilotedEntry('the-dundies', 'The Dundies');

    $response = InstanceServer::actingAs($michael)->tool(ListBlogPosts::class, ['search' => 'midnight']);

    $response->assertOk();
    $response->assertSee('threat-level-midnight');
    $response->assertDontSee('the-dundies');
});

it('refuses a bucket that is not one of the four', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $response = InstanceServer::actingAs($michael)->tool(ListBlogPosts::class, ['status' => 'somewhere-else']);

    $response->assertHasErrors();
});

it('shows one entry with every language of it', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedEntry();

    $response = InstanceServer::actingAs($michael)->tool(ShowBlogPost::class, ['blog_post_id' => $post->id]);

    $response->assertOk();
    $response->assertSee(['The Dundies', 'fr_FR', 'Not translated yet']);
});

it('creates an entry as a draft, in english', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $response = InstanceServer::actingAs($michael)->tool(CreateBlogPost::class, [
        'title' => 'Grading a Dundie',
        'excerpt' => 'What the award is worth.',
        'body' => 'It is a paper plate.',
        'shelf' => BlogShelf::Collecting->value,
    ]);

    $response->assertOk();
    $response->assertSee(['grading-a-dundie', 'draft']);
    $this->assertDatabaseHas('blog_posts', ['status' => BlogPostStatus::Draft->value]);
    $this->assertDatabaseHas('blog_post_translations', [
        'locale' => 'en',
        'title' => 'Grading a Dundie',
        'slug' => 'grading-a-dundie',
        'state' => BlogTranslationState::Source->value,
    ]);
});

it('refuses to create an entry without a shelf', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $response = InstanceServer::actingAs($michael)->tool(CreateBlogPost::class, [
        'title' => 'Grading a Dundie',
        'excerpt' => 'What the award is worth.',
        'body' => 'It is a paper plate.',
    ]);

    $response->assertHasErrors();
    $this->assertDatabaseCount('blog_posts', 0);
});

it('leaves alone the fields an update is not given', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedEntry();
    $post->update(['is_featured' => true, 'robots' => 'noindex']);

    $response = InstanceServer::actingAs($michael)->tool(UpdateBlogPost::class, [
        'blog_post_id' => $post->id,
        'shelf' => BlogShelf::Releases->value,
    ]);

    $response->assertOk();
    $post->refresh();
    expect($post->shelf)->toBe(BlogShelf::Releases);
    expect($post->is_featured)->toBeTrue();
    expect($post->robots)->toBe('noindex');
});

it('files an entry under the tags it is given', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedEntry();

    $response = InstanceServer::actingAs($michael)->tool(UpdateBlogPost::class, [
        'blog_post_id' => $post->id,
        'tags' => ['dundies', 'awards'],
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('blog_post_tags', ['blog_post_id' => $post->id, 'name' => 'dundies']);
    $this->assertDatabaseHas('blog_post_tags', ['blog_post_id' => $post->id, 'name' => 'awards']);
});

it('publishes an entry', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedEntry('the-dundies', 'The Dundies', BlogPostStatus::Draft);

    $response = InstanceServer::actingAs($michael)->tool(PublishBlogPost::class, ['blog_post_id' => $post->id]);

    $response->assertOk();
    expect($post->refresh()->status)->toBe(BlogPostStatus::Published);
});

it('refuses to publish an entry that has no english text', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = BlogPost::factory()->draft()->create();

    $response = InstanceServer::actingAs($michael)->tool(PublishBlogPost::class, ['blog_post_id' => $post->id]);

    $response->assertHasErrors(['no English text']);
    expect($post->refresh()->status)->toBe(BlogPostStatus::Draft);
});

it('archives an entry', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedEntry();

    $response = InstanceServer::actingAs($michael)->tool(ArchiveBlogPost::class, ['blog_post_id' => $post->id]);

    $response->assertOk();
    expect($post->refresh()->status)->toBe(BlogPostStatus::Archived);
});

it('refuses an entry that does not exist', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);

    $response = InstanceServer::actingAs($michael)->tool(ShowBlogPost::class, ['blog_post_id' => 404]);

    $response->assertHasErrors();
});

it('refuses a user who does not administer the instance, even inside the tool', function () {
    $toby = $this->createUser();

    $response = InstanceServer::actingAs($toby)->tool(CreateBlogPost::class, [
        'title' => 'Grading a Dundie',
        'excerpt' => 'What the award is worth.',
        'body' => 'It is a paper plate.',
        'shelf' => BlogShelf::Collecting->value,
    ]);

    $response->assertHasErrors();
    $this->assertDatabaseCount('blog_posts', 0);
});
