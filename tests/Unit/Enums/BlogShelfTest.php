<?php

declare(strict_types=1);

use App\Enums\BlogShelf;

it('labels and describes every shelf', function () {
    foreach (BlogShelf::cases() as $case) {
        expect($case->label())->not->toBeEmpty()
            ->and($case->description())->not->toBeEmpty();
    }
});

it('offers every shelf as a select option', function () {
    expect(BlogShelf::options())->toHaveCount(count(BlogShelf::cases()))
        ->and(BlogShelf::options())->toHaveKey('self_hosting');
});
