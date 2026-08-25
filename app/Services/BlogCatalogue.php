<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogPostRedirect;
use App\Models\BlogPostTranslation;
use Carbon\Carbon;

/**
 * Reads the blog for the public site: what is in the catalogue, which entry a
 * URL points at, and what sits either side of it.
 *
 * This is the half of the blog that knows how to find things, kept out of the
 * controller (which must stay thin and hold no private methods) and out of
 * MarketingBlog (which is copy and asks the database nothing).
 */
class BlogCatalogue
{
    private const int RELATED_ENTRIES = 4;

    public function __construct(
        private BlogPostMetrics $metrics,
    ) {}

    /**
     * Every published entry, newest first, as the catalogue lists them.
     *
     * @return array<int, array{reference: string, title: string, slug: string, shelf: string, shelfLabel: string, publishedAt: Carbon, readingMinutes: int, isFeatured: bool, isNew: bool}>
     */
    public function entries(string $locale): array
    {
        return BlogPost::query()
            ->published()
            ->with('translations')
            ->get()
            ->map(fn (BlogPost $post): ?array => $this->summarize($post, $locale))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * The translation a URL points at: the reader's own language first, then
     * English, which every locale falls back to.
     */
    public function find(string $locale, string $slug): ?BlogPostTranslation
    {
        $default = (string) config('docs.default_locale');

        return $this->translationBySlug($locale, $slug)
            ?? ($locale === $default ? null : $this->translationBySlug($default, $slug));
    }

    /**
     * The current slug for an entry that used to answer on this one, so the old
     * URL can be permanently redirected rather than breaking.
     */
    public function currentSlugFor(string $locale, string $slug): ?string
    {
        $redirect = BlogPostRedirect::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->with('blogPost.translations')
            ->first();

        if ($redirect === null || ! $redirect->blogPost->isReadable()) {
            return null;
        }

        return $redirect->blogPost->translation($locale)?->slug;
    }

    /**
     * Every language one entry can be read in, as locale => slug.
     *
     * A locale with no live translation is absent rather than falling back: it
     * has no URL of its own, which is exactly what the language picker and the
     * hreflang tags need to know.
     *
     * @return array<string, string>
     */
    public function slugsByLocale(string $locale, string $slug): array
    {
        $translation = $this->find($locale, $slug);

        if ($translation === null) {
            return [];
        }

        return $translation->blogPost->translations
            ->filter(fn (BlogPostTranslation $candidate): bool => $candidate->isPublic())
            ->pluck('slug', 'locale')
            ->all();
    }

    /**
     * The entry either side of this one by reference number, which is the order
     * the catalogue was written in.
     *
     * @return array{reference: string, title: string, slug: string}|null
     */
    public function adjacent(BlogPost $post, string $locale, bool $newer): ?array
    {
        $adjacent = BlogPost::query()
            ->published()
            ->where('reference', $newer ? '>' : '<', $post->reference)
            ->reorder('reference', $newer ? 'asc' : 'desc')
            ->with('translations')
            ->first();

        return $adjacent === null ? null : $this->cite($adjacent, $locale);
    }

    /**
     * Up to four other entries on the same shelf, newest first.
     *
     * @return array<int, array{reference: string, title: string, slug: string, date: string}>
     */
    public function related(BlogPost $post, string $locale): array
    {
        return BlogPost::query()
            ->published()
            ->where('shelf', $post->shelf)
            ->whereKeyNot($post->id)
            ->with('translations')
            ->limit(self::RELATED_ENTRIES)
            ->get()
            ->map(fn (BlogPost $related): array => [
                ...$this->cite($related, $locale),
                'date' => $related->published_at->isoFormat('MMM YYYY'),
            ])
            ->all();
    }

    /**
     * How many entries sit on each shelf, for the counts on the index.
     *
     * @return array<string, int>
     */
    public function countsByShelf(): array
    {
        return BlogPost::query()
            ->published()
            ->reorder()
            ->selectRaw('shelf, count(*) as aggregate')
            ->groupBy('shelf')
            ->pluck('aggregate', 'shelf')
            ->all();
    }

    /**
     * A slug is unique per locale, so this can look the row up directly and
     * then ask the entry it belongs to whether it is readable at all. Asking
     * afterwards rather than in the query keeps the rule in one place, on the
     * model, instead of half in SQL.
     */
    private function translationBySlug(string $locale, string $slug): ?BlogPostTranslation
    {
        $translation = BlogPostTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->with('blogPost.translations', 'blogPost.tags')
            ->first();

        if ($translation === null || ! $translation->blogPost->isReadable()) {
            return null;
        }

        return $translation;
    }

    /**
     * @return array{reference: string, title: string, slug: string}
     */
    private function cite(BlogPost $post, string $locale): array
    {
        $translation = $post->translation($locale);

        return [
            'reference' => $post->reference(),
            'title' => (string) $translation?->title,
            'slug' => (string) $translation?->slug,
        ];
    }

    /**
     * @return array{reference: string, title: string, slug: string, shelf: string, shelfLabel: string, publishedAt: Carbon, readingMinutes: int, isFeatured: bool, isNew: bool}|null
     */
    private function summarize(BlogPost $post, string $locale): ?array
    {
        $translation = $post->translation($locale);

        if ($translation === null) {
            return null;
        }

        return [
            'reference' => $post->reference(),
            'title' => $translation->title,
            'slug' => $translation->slug,
            'shelf' => $post->shelf->value,
            'shelfLabel' => $post->shelf->label(),
            'publishedAt' => $post->published_at,
            'readingMinutes' => $this->metrics->readingMinutes($translation),
            'isFeatured' => $post->is_featured,
            // Newly published entries are worth pointing at, and a month is
            // long enough that a quiet blog still has something marked new.
            'isNew' => $post->published_at->greaterThan(now()->subMonth()),
        ];
    }
}
