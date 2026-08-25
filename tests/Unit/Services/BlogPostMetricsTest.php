<?php

declare(strict_types=1);

use App\Services\BlogPostMetrics;

it('counts the words in the prose rather than the markdown around it', function () {
    $metrics = app(BlogPostMetrics::class)->measure("## A heading\n\nOne two three four five.");

    expect($metrics['words'])->toBe(7)
        ->and($metrics['headings'])->toBe(1);
});

it('counts sentences and paragraphs', function () {
    $metrics = app(BlogPostMetrics::class)->measure("One sentence. Two sentences.\n\nA second paragraph here.");

    expect($metrics['sentences'])->toBe(3)
        ->and($metrics['paragraphs'])->toBe(2);
});

it('counts footnotes', function () {
    $metrics = app(BlogPostMetrics::class)->measure("A claim.[^1]\n\n[^1]: The source.");

    expect($metrics['footnotes'])->toBe(1);
});

it('measures the longest sentence', function () {
    $metrics = app(BlogPostMetrics::class)->measure('Short one. This particular sentence has rather more words in it than the other.');

    expect($metrics['longestSentence'])->toBe(12);
});

it('reports a reading time of at least one minute', function () {
    $metrics = app(BlogPostMetrics::class)->measure('Three words only.');

    expect($metrics['minutesReading'])->toBe(1);
});

it('reads faster when skimming and slower aloud', function () {
    $metrics = app(BlogPostMetrics::class)->measure(str_repeat('word ', 2000));

    expect($metrics['minutesSkimming'])->toBeLessThan($metrics['minutesReading'])
        ->and($metrics['minutesAloud'])->toBeGreaterThan($metrics['minutesReading']);
});

it('never reports a grade level below one', function () {
    expect(app(BlogPostMetrics::class)->measure('Hi.')['gradeLevel'])->toBeGreaterThanOrEqual(1);
});

it('handles an empty body without dividing by zero', function () {
    $metrics = app(BlogPostMetrics::class)->measure('');

    expect($metrics['words'])->toBe(0)
        ->and($metrics['sentences'])->toBe(0)
        ->and($metrics['gradeLevel'])->toBe(1);
});

it('ignores a fenced code block when counting the prose', function () {
    $withCode = app(BlogPostMetrics::class)->measure("One two three.\n\n```\nlots of code words here\n```");
    $without = app(BlogPostMetrics::class)->measure('One two three.');

    expect($withCode['words'])->toBe($without['words']);
});
