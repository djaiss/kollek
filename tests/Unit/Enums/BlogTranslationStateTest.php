<?php

declare(strict_types=1);

use App\Enums\BlogTranslationState;

it('labels and annotates every state', function () {
    foreach (BlogTranslationState::cases() as $case) {
        expect($case->label())->not->toBeEmpty()
            ->and($case->note())->not->toBeEmpty();
    }
});

it('serves readers only the source and the live translations', function () {
    expect(BlogTranslationState::Source->isPublic())->toBeTrue()
        ->and(BlogTranslationState::Live->isPublic())->toBeTrue()
        ->and(BlogTranslationState::InReview->isPublic())->toBeFalse()
        ->and(BlogTranslationState::Outdated->isPublic())->toBeFalse();
});

it('gives every state a badge colour', function () {
    foreach (BlogTranslationState::cases() as $case) {
        expect($case->color())->not->toBeEmpty();
    }
});

it('offers every state as a select option', function () {
    expect(BlogTranslationState::options())->toHaveCount(count(BlogTranslationState::cases()));
});
