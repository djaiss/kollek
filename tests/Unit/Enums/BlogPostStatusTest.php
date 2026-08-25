<?php

declare(strict_types=1);

use App\Enums\BlogPostStatus;

it('labels every case', function () {
    foreach (BlogPostStatus::cases() as $case) {
        expect($case->label())->not->toBeEmpty();
    }
});

it('says which states answer on a url', function () {
    expect(BlogPostStatus::Published->isReadable())->toBeTrue()
        ->and(BlogPostStatus::Archived->isReadable())->toBeTrue()
        ->and(BlogPostStatus::Draft->isReadable())->toBeFalse();
});

it('gives the published and archived states a badge colour', function () {
    expect(BlogPostStatus::Published->color())->toBe('emerald')
        ->and(BlogPostStatus::Archived->color())->toBe('orange')
        ->and(BlogPostStatus::Draft->color())->toBeNull();
});

it('offers every case as a select option', function () {
    expect(BlogPostStatus::options())->toHaveCount(count(BlogPostStatus::cases()))
        ->and(BlogPostStatus::options())->toHaveKey('published');
});
