<?php

declare(strict_types=1);

use App\Enums\BlogPostStatus;
use App\Enums\BlogTranslationState;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function writtenEntry(BlogPostStatus $status = BlogPostStatus::Published): BlogPost
{
    $post = BlogPost::factory()->create([
        'status' => $status,
        'published_at' => $status === BlogPostStatus::Draft ? null : now()->subDay(),
    ]);

    BlogPostTranslation::factory()->create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'the-dundies',
        'title' => 'The Dundies',
        'excerpt' => 'An awards ceremony.',
        'body' => 'The original body.',
        'state' => BlogTranslationState::Source,
    ]);

    return $post->refresh();
}

it('shows the english text of an entry', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();

    $response = $this->actingAs($michael)->get('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en');

    $response->assertOk();
    $response->assertViewIs('app.instance.marketing.blogPosts.edit');
    $response->assertSee('The Dundies');
});

it('shows an empty form for a language nobody has written yet', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();

    $response = $this->actingAs($michael)->get('instance-admin/marketing/blog-posts/'.$post->id.'/translations/fr_FR');

    $response->assertOk();
    $response->assertSee('falls back to English');
});

it('answers not found for a locale the instance does not offer', function () {
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();

    $this->actingAs($michael)
        ->get('instance-admin/marketing/blog-posts/'.$post->id.'/translations/nl_NL')
        ->assertNotFound();
});

it('saves a language', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();

    $response = $this->actingAs($michael)->put('instance-admin/marketing/blog-posts/'.$post->id.'/translations/fr_FR', [
        'intent' => 'save',
        'title' => 'Les Dundies',
        'excerpt' => 'Une remise de prix.',
        'body' => 'Le corps.',
        'slug' => 'les-dundies',
        'meta_title' => 'Les Dundies, la cérémonie',
        'meta_description' => 'Ce qui se passe quand une soirée de remise de prix se tient dans un restaurant.',
    ]);

    $response->assertSessionHas('status', 'Saved');
    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
        'state' => BlogTranslationState::InReview->value,
    ]);
});

it('leaves a redirect behind when a published slug changes', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();

    $this->actingAs($michael)->put('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en', [
        'intent' => 'save',
        'title' => 'The Dundies',
        'excerpt' => 'An awards ceremony.',
        'body' => 'The original body.',
        'slug' => 'the-dundie-awards',
    ]);

    $this->assertDatabaseHas('blog_post_redirects', [
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'the-dundies',
    ]);
});

it('copies the english text as a base for a new language', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();

    $response = $this->actingAs($michael)->put('instance-admin/marketing/blog-posts/'.$post->id.'/translations/de_DE', [
        'intent' => 'copy_source',
    ]);

    $response->assertSessionHas('status', 'English copied across');
    $this->assertDatabaseHas('blog_post_translations', [
        'blog_post_id' => $post->id,
        'locale' => 'de_DE',
        'title' => 'The Dundies',
        'state' => BlogTranslationState::InReview->value,
    ]);
});

it('marks a translation proofread so readers start seeing it', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();
    BlogPostTranslation::factory()->inReview()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
    ]);

    $response = $this->actingAs($michael)->put('instance-admin/marketing/blog-posts/'.$post->id.'/translations/fr_FR', [
        'intent' => 'publish',
    ]);

    $response->assertSessionHas('status', 'Translation marked proofread');
    $this->assertDatabaseHas('blog_post_translations', [
        'locale' => 'fr_FR',
        'state' => BlogTranslationState::Live->value,
    ]);
});

it('withdraws a live translation back to review', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();
    BlogPostTranslation::factory()->live()->create([
        'blog_post_id' => $post->id,
        'locale' => 'fr_FR',
        'slug' => 'les-dundies',
    ]);

    $response = $this->actingAs($michael)->put('instance-admin/marketing/blog-posts/'.$post->id.'/translations/fr_FR', [
        'intent' => 'withdraw',
    ]);

    $response->assertSessionHas('status', 'Translation withdrawn');
    $this->assertDatabaseHas('blog_post_translations', [
        'locale' => 'fr_FR',
        'state' => BlogTranslationState::InReview->value,
    ]);
});

it('rejects a save with no body', function () {
    Queue::fake();
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();

    $response = $this->actingAs($michael)->put('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en', [
        'intent' => 'save',
        'title' => 'The Dundies',
        'excerpt' => 'An awards ceremony.',
        'slug' => 'the-dundies',
    ]);

    $response->assertSessionHasErrors('body');
});

it('uploads a social card and takes it away again', function () {
    Queue::fake();
    Storage::fake(config('filesystems.default'));
    $michael = $this->createUser(['is_instance_administrator' => true]);
    $post = writtenEntry();

    $this->actingAs($michael)->post('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en/og-image', [
        'og_image' => UploadedFile::fake()->image('card.png', 1200, 630),
    ])->assertSessionHas('status', 'Social card replaced');

    expect($post->refresh()->source()->og_image_path)->not->toBeNull();

    $this->actingAs($michael)->delete('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en/og-image')
        ->assertSessionHas('status', 'Social card removed');

    expect($post->refresh()->source()->og_image_path)->toBeNull();
});

it('hides every translation route from somebody who is not an instance administrator', function () {
    $dwight = $this->createUser();
    $post = writtenEntry();

    $this->actingAs($dwight)->get('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en')->assertNotFound();
    $this->actingAs($dwight)->put('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en', ['intent' => 'copy_source'])->assertNotFound();
    $this->actingAs($dwight)->post('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en/og-image', [])->assertNotFound();
    $this->actingAs($dwight)->delete('instance-admin/marketing/blog-posts/'.$post->id.'/translations/en/og-image')->assertNotFound();
});
