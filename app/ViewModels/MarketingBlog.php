<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\BlogShelf;

/**
 * The copy and the reference tables the public blog is drawn from: the shelves a
 * reader can filter by, the books an entry is measured against, and the labels
 * on the reading pace list.
 *
 * Like the other marketing view models it asks the database nothing. The entries
 * themselves reach the views from the controller; what lives here is the writing
 * around them, resolved through __() at call time so it stays correct per locale.
 */
class MarketingBlog
{
    /**
     * The word counts every entry is measured against on its own page.
     *
     * Published editions differ, so these are the counts in common circulation
     * rather than an authority. They exist to give a reader a sense of scale,
     * which is a job an approximate number does perfectly well.
     *
     * @var array<int, array{title: string, words: int}>
     */
    private const array CLASSICS = [
        ['title' => 'Animal Farm', 'words' => 29966],
        ['title' => 'The Great Gatsby', 'words' => 47094],
        ['title' => "Harry Potter and the Philosopher's Stone", 'words' => 76944],
        ['title' => 'The Hobbit', 'words' => 95356],
        ['title' => 'Moby-Dick', 'words' => 206052],
        ['title' => 'War and Peace', 'words' => 587287],
    ];

    /**
     * The shelves the public index filters by, in display order, each with the
     * sentence that says what is on it.
     *
     * @return array<int, array{value: string, label: string, description: string}>
     */
    public function shelves(): array
    {
        return array_map(fn (BlogShelf $shelf): array => [
            'value' => $shelf->value,
            'label' => $shelf->label(),
            'description' => $shelf->description(),
        ], BlogShelf::cases());
    }

    /**
     * This entry as a percentage of each book, with a bar width to draw it at.
     *
     * The bar is scaled up so the shorter books produce something visible: at
     * true scale every entry would be a sliver against War and Peace and the
     * comparison would say nothing. The page says as much underneath it.
     *
     * @return array<int, array{title: string, words: int, percentage: float, width: float}>
     */
    public function classics(int $words): array
    {
        return array_map(function (array $book) use ($words): array {
            $percentage = ($words / $book['words']) * 100;

            return [
                'title' => $book['title'],
                'words' => $book['words'],
                'percentage' => round($percentage, $percentage >= 1 ? 1 : 2),
                'width' => round(max(2, min(100, $percentage * 11)), 1),
            ];
        }, self::CLASSICS);
    }

    /**
     * How long the entry takes at each pace, plus how hard it reads.
     *
     * @param  array{minutesReading: int, minutesSkimming: int, minutesAloud: int, gradeLevel: int}  $metrics
     * @return array<int, array{label: string, value: string}>
     */
    public function pace(array $metrics): array
    {
        return [
            ['label' => __('At 200 wpm'), 'value' => __(':count min', ['count' => $metrics['minutesReading']])],
            ['label' => __('At 250 wpm'), 'value' => __(':count min', ['count' => $metrics['minutesSkimming']])],
            ['label' => __('Read aloud'), 'value' => __(':count min', ['count' => $metrics['minutesAloud']])],
            ['label' => __('Grade level'), 'value' => __('Grade :level', ['level' => $metrics['gradeLevel']])],
        ];
    }

    /**
     * The measurements panel on an entry's page.
     *
     * @param  array{words: int, sentences: int, paragraphs: int, characters: int, averageWordsPerSentence: float, longestSentence: int, headings: int, footnotes: int}  $metrics
     * @return array<int, array{label: string, value: string}>
     */
    public function measurements(array $metrics): array
    {
        return [
            ['label' => __('Words'), 'value' => number_format($metrics['words'])],
            ['label' => __('Sentences'), 'value' => number_format($metrics['sentences'])],
            ['label' => __('Paragraphs'), 'value' => number_format($metrics['paragraphs'])],
            ['label' => __('Characters'), 'value' => number_format($metrics['characters'])],
            ['label' => __('Avg words / sentence'), 'value' => (string) $metrics['averageWordsPerSentence']],
            ['label' => __('Longest sentence'), 'value' => __(':count w', ['count' => $metrics['longestSentence']])],
            ['label' => __('Headings'), 'value' => (string) $metrics['headings']],
            ['label' => __('Footnotes'), 'value' => (string) $metrics['footnotes']],
        ];
    }
}
