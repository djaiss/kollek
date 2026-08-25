<?php

declare(strict_types=1);

use App\Enums\BlogTranslationState;
use App\Models\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('belongs to an entry', function () {
    expect(BlogPostTranslation::factory()->create()->blogPost()->exists())->toBeTrue();
});

it('is public only as the source or once it is live', function () {
    expect(BlogPostTranslation::factory()->create()->isPublic())->toBeTrue()
        ->and(BlogPostTranslation::factory()->live()->create()->isPublic())->toBeTrue()
        ->and(BlogPostTranslation::factory()->inReview()->create()->isPublic())->toBeFalse()
        ->and(BlogPostTranslation::factory()->outdated()->create()->isPublic())->toBeFalse();
});

it('renders the body with a heading id to anchor to', function () {
    $translation = BlogPostTranslation::factory()->create([
        'body' => "Opening.\n\n## A heading\n\nMore.",
    ]);

    $rendered = $translation->rendered();

    expect($rendered['html'])->toContain('id="a-heading"')
        ->and($rendered['toc'])->toHaveCount(1)
        ->and($rendered['toc'][0]['text'])->toBe('A heading');
});

it('falls back to the headline when no meta title was written', function () {
    $translation = BlogPostTranslation::factory()->create([
        'title' => 'The Dundies',
        'meta_title' => null,
    ]);

    expect($translation->metaTitle())->toBe('The Dundies');
});

it('prefers the meta title when one was written', function () {
    $translation = BlogPostTranslation::factory()->create([
        'title' => 'The Dundies',
        'meta_title' => 'The Dundies, an awards ceremony',
    ]);

    expect($translation->metaTitle())->toBe('The Dundies, an awards ceremony');
});

it('falls back to the excerpt when no meta description was written', function () {
    $translation = BlogPostTranslation::factory()->create([
        'excerpt' => 'An awards ceremony.',
        'meta_description' => null,
    ]);

    expect($translation->metaDescription())->toBe('An awards ceremony.');
});

it('casts the state', function () {
    expect(BlogPostTranslation::factory()->create()->state)->toBeInstanceOf(BlogTranslationState::class);
});
