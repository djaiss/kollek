<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Testimonial;
use App\ViewModels\MarketingFeatures;

/**
 * Every public URL worth crawling, each carrying the languages the same page is
 * offered in.
 *
 * The public site is around nine hundred URLs: seven language prefixes over the
 * homepage, the features hub, twelve feature pages, the pricing, FAQ, about and
 * reviews pages, the documentation home and a hundred and ten documentation
 * pages, plus the four English only pages. Almost all of them are reachable only
 * by crawling three or four links deep, in a language the crawler has to guess
 * to follow, which is the shape of site that gets half indexed.
 *
 * Two things decide what goes in, and both are about agreeing with what the
 * pages themselves already say in their head (see App\ViewModels\MarketingSeo):
 *
 *  - an English only page is listed once, at its English URL, because that is
 *    the URL its canonical points at from behind all seven prefixes;
 *  - a documentation page keeps a different section and slug in every language,
 *    so its translations are found through its page id rather than by swapping
 *    the prefix the way every other route allows. A blog entry works the same
 *    way, and claims only the languages actually written for it.
 *
 * There is deliberately no lastmod. The only date available is the mtime of the
 * Markdown on disk, which a fresh clone or a Docker build sets to the moment it
 * checked out, so every page would claim to have changed at once. A date we
 * cannot stand behind is worse than none: see issue #267 for giving the pages a
 * real one.
 */
class Sitemap
{
    public function __construct(
        private DocumentationPortal $portal,
        private MarketingFeatures $features,
    ) {}

    /**
     * @return array<int, array{loc: string, alternates: array<int, array{hreflang: string, url: string}>}>
     */
    public function entries(): array
    {
        return [
            ...$this->localizedPages(),
            ...$this->documentationPages(),
            ...$this->blogEntries(),
            ...$this->englishOnlyPages(),
        ];
    }

    /**
     * The pages that are the same route under every prefix. Each one is listed
     * once per language, and every copy claims the whole set as its alternates.
     *
     * @return array<int, array{loc: string, alternates: array<int, array{hreflang: string, url: string}>}>
     */
    private function localizedPages(): array
    {
        $entries = [];

        foreach ($this->localizedRoutes() as [$name, $parameters]) {
            $urls = [];

            foreach ($this->locales() as $locale => $urlLocale) {
                $urls[$locale] = route($name, [...$parameters, 'locale' => $urlLocale]);
            }

            $alternates = $this->alternates($urls);

            foreach ($urls as $url) {
                $entries[] = ['loc' => $url, 'alternates' => $alternates];
            }
        }

        return $entries;
    }

    /**
     * @return array<int, array{0: string, 1: array<string, string>}>
     */
    private function localizedRoutes(): array
    {
        $routes = [
            ['marketing.index', []],
            ['marketing.features.index', []],
        ];

        foreach ($this->features->all() as $feature) {
            $routes[] = ['marketing.features.show', ['slug' => $feature['slug']]];
        }

        $routes[] = ['marketing.pricing.index', []];
        $routes[] = ['marketing.faq.index', []];
        $routes[] = ['marketing.about.index', []];

        // The site itself only links the reviews once there is something
        // published to read, and a sitemap that promises an empty page is worse
        // than one that leaves it out until it is worth reading.
        if (Testimonial::query()->published()->exists()) {
            $routes[] = ['marketing.testimonials.index', []];
        }

        $routes[] = ['marketing.docs.portal.home.show', []];

        // As with the reviews, the catalogue is only worth crawling once there
        // is something in it.
        if (BlogPost::query()->published()->exists()) {
            $routes[] = ['marketing.blog.index', []];
        }

        return $routes;
    }

    /**
     * The blog, one entry at a time. A slug is translated per language like a
     * documentation page, so the translations of an entry are gathered from the
     * entry itself rather than by swapping the prefix.
     *
     * Only the languages actually written for an entry are listed. A locale that
     * falls back to English has no URL of its own, and claiming one would put a
     * duplicate of the English page in the sitemap under seven prefixes.
     *
     * @return array<int, array{loc: string, alternates: array<int, array{hreflang: string, url: string}>}>
     */
    private function blogEntries(): array
    {
        $entries = [];
        $locales = $this->locales();

        $posts = BlogPost::query()
            ->published()
            ->with('translations')
            ->get();

        foreach ($posts as $post) {
            $urls = [];

            foreach ($post->translations as $translation) {
                if (! $translation->isPublic() || ! isset($locales[$translation->locale])) {
                    continue;
                }

                $urls[$translation->locale] = route('marketing.blog.show', [
                    'locale' => $locales[$translation->locale],
                    'slug' => $translation->slug,
                ]);
            }

            $alternates = $this->alternates($urls);

            foreach ($urls as $url) {
                $entries[] = ['loc' => $url, 'alternates' => $alternates];
            }
        }

        return $entries;
    }

    /**
     * The documentation portal, gathered by page id so a page finds its own
     * translations. A page that only exists in some languages claims only those.
     *
     * @return array<int, array{loc: string, alternates: array<int, array{hreflang: string, url: string}>}>
     */
    private function documentationPages(): array
    {
        $urls = [];

        foreach (array_keys($this->locales()) as $locale) {
            foreach ($this->portal->pagesFor($locale) as $page) {
                // The introduction is the portal home, which is listed already.
                if ($page['is_home']) {
                    continue;
                }

                $urls[$page['id']][$locale] = $this->portal->urlFor($locale, $page);
            }
        }

        $entries = [];

        foreach ($urls as $pageUrls) {
            $alternates = $this->alternates($pageUrls);

            foreach ($pageUrls as $url) {
                $entries[] = ['loc' => $url, 'alternates' => $alternates];
            }
        }

        return $entries;
    }

    /**
     * The pages whose seven prefixes serve the very same English words. Listing
     * all seven would contradict the canonical those pages print.
     *
     * @return array<int, array{loc: string, alternates: array<int, array{hreflang: string, url: string}>}>
     */
    private function englishOnlyPages(): array
    {
        $english = $this->portal->urlLocaleFor(config('docs.default_locale'));

        return array_map(
            fn (string $name): array => [
                'loc' => route($name, ['locale' => $english]),
                'alternates' => [],
            ],
            array_values(config('marketing.english_only_routes')),
        );
    }

    /**
     * One entry per language that genuinely serves the page, plus x-default for
     * the crawler that recognises none of them, exactly as the head does.
     *
     * @param  array<string, string>  $urls
     * @return array<int, array{hreflang: string, url: string}>
     */
    private function alternates(array $urls): array
    {
        $alternates = [];

        foreach ($urls as $locale => $url) {
            $alternates[] = ['hreflang' => str_replace('_', '-', $locale), 'url' => $url];
        }

        $english = $urls[config('docs.default_locale')] ?? null;

        if ($english !== null) {
            $alternates[] = ['hreflang' => 'x-default', 'url' => $english];
        }

        return $alternates;
    }

    /**
     * The languages the site is served in, mapped to the prefix their URLs
     * carry. A language counts once its documentation folder exists, which is
     * the same test routes/marketing.php constrains the {locale} prefix with.
     *
     * @return array<string, string>
     */
    private function locales(): array
    {
        $locales = [];

        foreach (config('docs.locales') as $locale => $meta) {
            if (! $this->portal->hasLocale($locale)) {
                continue;
            }

            $locales[$locale] = $meta['url'];
        }

        return $locales;
    }
}
