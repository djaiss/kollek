<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BlogPostStatus;
use App\Enums\BlogTranslationState;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\Builder;

/**
 * What the instance administration shows about the blog: the list of entries,
 * the counts on the filter tabs, and how far along every language of one entry
 * is.
 *
 * Kept out of the controller, which must stay thin and hold no private methods.
 * The panel is English only and never translated, so the strings here are plain
 * rather than going through __().
 */
class BlogPostAdministration
{
    /**
     * The entries in one bucket, most recently touched first.
     *
     * Blog content is not encrypted, unlike almost everything else in this
     * schema, which is what lets the search box match a title or a slug in SQL
     * rather than having to read every row into memory first.
     *
     * The search is case insensitive on every engine (whereLike writes ILIKE on
     * Postgres, where plain LIKE is case sensitive), and the wildcards are
     * escaped so a title containing % or _ is searched for literally.
     *
     * @return array<int, array{id: int, reference: string, title: string, slug: string, shelf: string, shelfValue: string, status: BlogPostStatus, languages: array<int, array{code: string, label: string, state: ?BlogTranslationState}>, liveCount: int, localeCount: int, updatedAt: string}>
     */
    public function rows(string $status, string $search): array
    {
        return BlogPost::query()
            ->when($status !== 'all', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $term = '%'.addcslashes($search, '%_\\').'%';

                $query->whereHas('translations', function (Builder $translations) use ($term): void {
                    $translations->whereLike('title', $term, caseSensitive: false)
                        ->orWhereLike('slug', $term, caseSensitive: false);
                });
            })
            ->with('translations')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (BlogPost $post): array => $this->row($post))
            ->all();
    }

    /**
     * How many entries sit in each bucket, for the filter tabs.
     *
     * @return array<string, int>
     */
    public function counts(): array
    {
        $counts = BlogPost::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $buckets = ['all' => array_sum($counts)];

        foreach (BlogPostStatus::cases() as $case) {
            $buckets[$case->value] = (int) ($counts[$case->value] ?? 0);
        }

        return $buckets;
    }

    /**
     * The figures across the top of the list.
     *
     * @return array<int, array{label: string, value: string, note: string}>
     */
    public function summary(): array
    {
        $counts = $this->counts();
        $locales = count((array) config('docs.locales'));

        $states = BlogPostTranslation::query()
            ->selectRaw('state, count(*) as aggregate')
            ->groupBy('state')
            ->pluck('aggregate', 'state')
            ->all();

        $live = (int) ($states[BlogTranslationState::Source->value] ?? 0) + (int) ($states[BlogTranslationState::Live->value] ?? 0);
        $waiting = (int) ($states[BlogTranslationState::InReview->value] ?? 0) + (int) ($states[BlogTranslationState::Outdated->value] ?? 0);
        $possible = $counts['all'] * $locales;

        return [
            [
                'label' => 'Posts',
                'value' => (string) $counts['all'],
                'note' => $counts[BlogPostStatus::Published->value].' published · '.$counts[BlogPostStatus::Draft->value].' draft',
            ],
            [
                'label' => 'Locales',
                'value' => (string) $locales,
                'note' => strtoupper((string) config('docs.default_locale')).' source · '.($locales - 1).' translations',
            ],
            [
                'label' => 'Translations live',
                'value' => $live.' / '.$possible,
                'note' => $waiting.' waiting · '.max(0, $possible - $live - $waiting).' not written',
            ],
            [
                'label' => 'Featured',
                'value' => (string) BlogPost::query()->where('is_featured', true)->count(),
                'note' => 'Pulled to the top of the public index',
            ],
        ];
    }

    /**
     * Every language of one entry, written or not, for the language strip on the
     * edit screen. A locale with no row is the mockup's "missing": a null state
     * rather than a case of its own.
     *
     * @return array<int, array{locale: string, code: string, label: string, state: ?BlogTranslationState, note: string, slug: ?string}>
     */
    public function languages(BlogPost $post): array
    {
        $languages = [];

        foreach ((array) config('docs.locales') as $locale => $meta) {
            $translation = $post->translationFor($locale);

            $languages[] = [
                'locale' => $locale,
                'code' => $meta['code'],
                'label' => $meta['label'],
                'state' => $translation?->state,
                'note' => $translation?->state->note() ?? 'Not translated yet, the site falls back to English',
                'slug' => $translation?->slug,
            ];
        }

        return $languages;
    }

    /**
     * The metadata checks shown next to the SEO fields. They are advice, not a
     * gate: nothing here stops an entry being published.
     *
     * @return array<int, array{passes: bool, text: string}>
     */
    public function checks(BlogPost $post, ?BlogPostTranslation $translation): array
    {
        $metaTitle = (string) $translation?->metaTitle();
        $metaDescription = (string) $translation?->metaDescription();
        $slug = (string) $translation?->slug;
        $keyword = trim((string) $translation?->focus_keyword);

        return [
            [
                'passes' => mb_strlen($metaTitle) >= 30 && mb_strlen($metaTitle) <= 60,
                'text' => 'Meta title within 30 to 60 characters',
            ],
            [
                'passes' => mb_strlen($metaDescription) >= 70 && mb_strlen($metaDescription) <= 160,
                'text' => 'Meta description within 70 to 160 characters',
            ],
            [
                'passes' => $slug !== '' && mb_strlen($slug) <= 60,
                'text' => 'Slug is present and under 60 characters',
            ],
            [
                'passes' => $keyword !== '' && str_contains(mb_strtolower($metaTitle.' '.$metaDescription), mb_strtolower($keyword)),
                'text' => 'Focus keyword appears in the title or the description',
            ],
            [
                'passes' => count($post->liveLocales()) >= 3,
                'text' => 'At least three locales live, for hreflang coverage',
            ],
        ];
    }

    /**
     * @return array{id: int, reference: string, title: string, slug: string, shelf: string, shelfValue: string, status: BlogPostStatus, languages: array<int, array{code: string, label: string, state: ?BlogTranslationState}>, liveCount: int, localeCount: int, updatedAt: string}
     */
    private function row(BlogPost $post): array
    {
        $source = $post->source();
        $locales = (array) config('docs.locales');

        $languages = [];

        foreach ($locales as $locale => $meta) {
            $languages[] = [
                'code' => $meta['code'],
                'label' => $meta['label'],
                'state' => $post->translationFor($locale)?->state,
            ];
        }

        return [
            'id' => $post->id,
            'reference' => $post->reference(),
            'title' => $source !== null ? $source->title : 'Untitled',
            'slug' => '/blog/'.($source !== null ? $source->slug : ''),
            'shelf' => $post->shelf->label(),
            'shelfValue' => $post->shelf->value,
            'status' => $post->status,
            'languages' => $languages,
            'liveCount' => count($post->liveLocales()),
            'localeCount' => count($locales),
            'updatedAt' => $post->updated_at?->diffForHumans() ?? '',
        ];
    }
}
