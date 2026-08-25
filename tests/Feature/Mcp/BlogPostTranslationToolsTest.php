<?php

declare(strict_types=1);

use App\Enums\BlogTranslationState;
use App\Mcp\Servers\InstanceServer;
use App\Mcp\Tools\Blog\CopyBlogPostSourceTranslation;
use App\Mcp\Tools\Blog\PublishBlogPostTranslation;
use App\Mcp\Tools\Blog\ShowBlogPostTranslation;
use App\Mcp\Tools\Blog\WithdrawBlogPostTranslation;
use App\Mcp\Tools\Blog\WriteBlogPostTranslation;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function pilotedTranslatedEntry(): BlogPost
{
    $post = BlogPost::factory()->create();

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'the-dundies',
        'title' => 'The Dundies',
        'excerpt' => 'An awards night.',
        'body' => 'Held at Chili s.',
        'state' => BlogTranslationState::Source,
    ]);

    return $post->refresh();
}

it('reads one language of an entry', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(ShowBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'en',
    ]);

    $response->assertOk();
    $response->assertSee(['The Dundies', 'An awards night.', 'Meta title within 30 to 60 characters']);
});

it('says so when a language has not been written yet', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(ShowBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
    ]);

    $response->assertHasErrors(['has not been written in fr_FR yet']);
});

it('refuses a locale the instance does not speak', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(ShowBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'sv_SE',
    ]);

    $response->assertHasErrors();
});

it('writes a language that did not exist', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(WriteBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'title' => 'Les Dundies',
        'excerpt' => 'Une soiree de recompenses.',
        'body' => 'Chez Chili s.',
        'slug' => 'les-dundies',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'title' => 'Les Dundies',
        'state' => BlogTranslationState::InReview->value,
    ]);
});

it('refuses to write a language it has never seen without the text', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(WriteBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'title' => 'Les Dundies',
    ]);

    $response->assertHasErrors();
    $this->assertDatabaseMissing('blog_post_translations', ['blog_post_id' => $post->id, 'locale' => 'fr_FR']);
});

it('rewrites part of a language without being resent the body', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(WriteBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'meta_title' => 'The Dundies, an awards night at Chili s',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'body' => 'Held at Chili s.',
        'meta_title' => 'The Dundies, an awards night at Chili s',
    ]);
});

it('copies the english source across to start a language', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(CopyBlogPostSourceTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'de_DE',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'de_DE',
        'title' => 'The Dundies',
        'state' => BlogTranslationState::InReview->value,
    ]);
});

it('refuses to copy the english source onto english', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(CopyBlogPostSourceTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'en',
    ]);

    $response->assertHasErrors(['English is the source']);
});

it('refuses to copy over a language that already exists', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();
    BlogPostTranslation::factory()->inReview()->create(['blog_post_id' => $post->id, 'locale' => 'de_DE']);

    $response = InstanceServer::actingAs($michael)->tool(CopyBlogPostSourceTranslation::class, [
        'blog_post_id' => $post->refresh()->id,
        'locale' => 'de_DE',
    ]);

    $response->assertHasErrors(['already exists in de_DE']);
});

it('marks a language proofread', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();
    $translation = BlogPostTranslation::factory()->inReview()->create(['blog_post_id' => $post->id, 'locale' => 'fr_FR']);

    $response = InstanceServer::actingAs($michael)->tool(PublishBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
    ]);

    $response->assertOk();
    expect($translation->refresh()->state)->toBe(BlogTranslationState::Live);
});

it('refuses to mark english proofread', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(PublishBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'en',
    ]);

    $response->assertHasErrors(['English is the source']);
});

it('withdraws a language', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();
    $translation = BlogPostTranslation::factory()->live()->create(['blog_post_id' => $post->id, 'locale' => 'fr_FR']);

    $response = InstanceServer::actingAs($michael)->tool(WithdrawBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
    ]);

    $response->assertOk();
    expect($translation->refresh()->state)->toBe(BlogTranslationState::InReview);
});

it('refuses to withdraw english', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = pilotedTranslatedEntry();

    $response = InstanceServer::actingAs($michael)->tool(WithdrawBlogPostTranslation::class, [
        'blog_post_id' => $post->id,
        'locale' => 'en',
    ]);

    $response->assertHasErrors(['cannot be withdrawn']);
});
