<?php

declare(strict_types=1);

use App\Enums\BlogShelf;
use App\ViewModels\MarketingBlog;

it('lists every shelf with its label and its description', function () {
    $shelves = app(MarketingBlog::class)->shelves();

    expect($shelves)->toHaveCount(count(BlogShelf::cases()))
        ->and($shelves[0])->toHaveKeys(['value', 'label', 'description']);
});

it('measures an entry against the classics', function () {
    $classics = app(MarketingBlog::class)->classics(2418);

    expect($classics)->toHaveCount(6)
        ->and($classics[0]['title'])->toBe('Animal Farm')
        ->and($classics[0]['percentage'])->toBe(8.1);
});

it('scales a bar so the shortest comparison is still visible', function () {
    $classics = app(MarketingBlog::class)->classics(10);

    foreach ($classics as $book) {
        expect($book['width'])->toBeGreaterThanOrEqual(2.0)
            ->and($book['width'])->toBeLessThanOrEqual(100.0);
    }
});

it('reports a percentage to two decimals when it is under one', function () {
    expect(app(MarketingBlog::class)->classics(100)[5]['percentage'])->toBe(0.02);
});

it('lists the reading paces and the grade level', function () {
    $pace = app(MarketingBlog::class)->pace([
        'minutesReading' => 12,
        'minutesSkimming' => 10,
        'minutesAloud' => 18,
        'gradeLevel' => 9,
    ]);

    expect($pace)->toHaveCount(4)
        ->and($pace[0]['value'])->toBe('12 min')
        ->and($pace[3]['value'])->toBe('Grade 9');
});

it('lists every measurement', function () {
    $measurements = app(MarketingBlog::class)->measurements([
        'words' => 2418,
        'sentences' => 138,
        'paragraphs' => 24,
        'characters' => 14902,
        'averageWordsPerSentence' => 17.5,
        'longestSentence' => 54,
        'headings' => 5,
        'footnotes' => 1,
    ]);

    expect($measurements)->toHaveCount(8)
        ->and($measurements[0]['value'])->toBe('2,418');
});
