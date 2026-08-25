<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPostTranslation;
use Illuminate\Support\Facades\Cache;

/**
 * Measures one blog entry: how long it is, how long it takes to read, and how
 * hard the sentences are.
 *
 * Every number is derived from the Markdown source rather than stored, so it
 * can never disagree with the writing. Deriving it on every request would be
 * wasteful for a page that changes once and is read for months, so the result
 * is cached until the translation is next saved.
 *
 * The reading paces are the conventional ones: 200 words a minute for careful
 * reading, 250 for a skim, and 130 for reading aloud. The grade level is
 * Flesch-Kincaid, which assumes English; it is a rough signal for the writer,
 * not a claim about the other locales.
 */
class BlogPostMetrics
{
    private const int WORDS_PER_MINUTE = 200;

    private const int WORDS_PER_MINUTE_SKIMMING = 250;

    private const int WORDS_PER_MINUTE_ALOUD = 130;

    /**
     * @return array{words: int, sentences: int, paragraphs: int, characters: int, averageWordsPerSentence: float, longestSentence: int, headings: int, footnotes: int, minutesReading: int, minutesSkimming: int, minutesAloud: int, gradeLevel: int}
     */
    public function forTranslation(BlogPostTranslation $translation): array
    {
        $key = sprintf(
            'blog.metrics.%d.%d',
            $translation->id,
            (int) $translation->updated_at?->timestamp,
        );

        return Cache::remember($key, now()->addWeek(), fn (): array => $this->measure($translation->body));
    }

    /**
     * How many minutes the entry takes to read, which is the only measurement
     * the public index shows.
     */
    public function readingMinutes(BlogPostTranslation $translation): int
    {
        return $this->forTranslation($translation)['minutesReading'];
    }

    /**
     * @return array{words: int, sentences: int, paragraphs: int, characters: int, averageWordsPerSentence: float, longestSentence: int, headings: int, footnotes: int, minutesReading: int, minutesSkimming: int, minutesAloud: int, gradeLevel: int}
     */
    public function measure(string $body): array
    {
        $headings = preg_match_all('/^#{1,6}\s+\S/m', $body);
        $footnotes = preg_match_all('/^\[\^[^\]]+\]:/m', $body);
        $paragraphs = count(array_filter(preg_split('/\n\s*\n/', trim($body)) ?: [], $this->isProse(...)));

        $prose = $this->stripMarkup($body);

        $words = preg_split('/\s+/', $prose, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $wordCount = count($words);

        $sentences = $this->sentences($prose);
        $sentenceCount = max(1, count($sentences));

        $longest = 0;

        foreach ($sentences as $sentence) {
            $longest = max($longest, count(preg_split('/\s+/', $sentence, -1, PREG_SPLIT_NO_EMPTY) ?: []));
        }

        return [
            'words' => $wordCount,
            'sentences' => count($sentences),
            'paragraphs' => $paragraphs,
            'characters' => mb_strlen($prose),
            'averageWordsPerSentence' => round($wordCount / $sentenceCount, 1),
            'longestSentence' => $longest,
            'headings' => $headings,
            'footnotes' => $footnotes,
            'minutesReading' => $this->minutes($wordCount, self::WORDS_PER_MINUTE),
            'minutesSkimming' => $this->minutes($wordCount, self::WORDS_PER_MINUTE_SKIMMING),
            'minutesAloud' => $this->minutes($wordCount, self::WORDS_PER_MINUTE_ALOUD),
            'gradeLevel' => $this->gradeLevel($words, $sentenceCount),
        ];
    }

    /**
     * A block counts as a paragraph when it is prose, so the count is not
     * inflated by headings, fences, list items and images.
     */
    private function isProse(string $block): bool
    {
        $block = trim($block);

        if ($block === '') {
            return false;
        }

        return preg_match('/^(#{1,6}\s|```|>|\||\s*[-*+]\s|\s*\d+\.\s|!\[|\[\^)/', $block) === 0;
    }

    /**
     * The body with its Markdown scaffolding taken off, so the counts measure
     * the writing rather than the syntax around it.
     */
    private function stripMarkup(string $body): string
    {
        $patterns = [
            '/```.*?```/s' => ' ',
            '/`[^`]*`/' => ' ',
            '/^\[\^[^\]]+\]:.*$/m' => ' ',
            '/\[\^[^\]]+\]/' => ' ',
            '/!\[[^\]]*\]\([^)]*\)/' => ' ',
            '/\[([^\]]*)\]\([^)]*\)/' => '$1',
            '/^#{1,6}\s+/m' => '',
            '/^\s*>\s?/m' => '',
            '/^\s*[-*+]\s+/m' => '',
            '/^\s*\d+\.\s+/m' => '',
            '/[*_~]+/' => '',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $body = (string) preg_replace($pattern, $replacement, $body);
        }

        return trim((string) preg_replace('/\s+/', ' ', $body));
    }

    /**
     * @return array<int, string>
     */
    private function sentences(string $prose): array
    {
        $parts = preg_split('/(?<=[.!?])\s+/', $prose, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($parts, fn (string $part): bool => trim($part) !== ''));
    }

    private function minutes(int $words, int $wordsPerMinute): int
    {
        return max(1, (int) ceil($words / $wordsPerMinute));
    }

    /**
     * Flesch-Kincaid, rounded to a school year and floored at one so a very
     * short entry does not report a negative grade.
     *
     * @param  array<int, string>  $words
     */
    private function gradeLevel(array $words, int $sentenceCount): int
    {
        if ($words === []) {
            return 1;
        }

        $syllables = 0;

        foreach ($words as $word) {
            $syllables += $this->syllables($word);
        }

        $grade = 0.39 * (count($words) / $sentenceCount)
            + 11.8 * ($syllables / count($words))
            - 15.59;

        return max(1, (int) round($grade));
    }

    /**
     * A syllable count good enough for a readability score: vowel groups, minus
     * the silent trailing e, never less than one.
     */
    private function syllables(string $word): int
    {
        $word = mb_strtolower((string) preg_replace('/[^a-zA-Z]/', '', $word));

        if ($word === '') {
            return 0;
        }

        $count = preg_match_all('/[aeiouy]+/', $word);

        if (str_ends_with($word, 'e') && $count > 1) {
            $count--;
        }

        return max(1, $count);
    }
}
