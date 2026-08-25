<?php

declare(strict_types=1);

namespace App\ViewModels;

use Illuminate\Http\Request;

/**
 * The head of every public page: the title, the description, the canonical URL,
 * the hreflang alternates and the Open Graph card.
 *
 * The whole public site is served at seven URLs, one per language prefix, and
 * three kinds of page live behind them:
 *
 *  - translated pages, where each prefix really is a different page;
 *  - English only pages (the terms, the privacy policy, the media kit, the API
 *    reference), where all seven prefixes serve the very same words;
 *  - documentation pages, which are translated but keep a different slug in each
 *    language, and fall back to English when a translation is missing.
 *
 * Left alone, the second and third kinds compete with themselves in search: seven
 * identical URLs, or an untranslated page shadowing the English one it copies.
 * So the canonical of an English only page always points at its English URL and
 * it claims no alternates at all, and a documentation page only ever claims the
 * languages that genuinely carry it.
 */
class MarketingSeo
{
    public function __construct(
        private MarketingLanguages $languages,
        private MarketingFeatures $features,
    ) {}

    /**
     * Everything the head needs for the page being served. A title, description
     * or social card handed in by the view wins over the map below, so a page
     * that already knows its own heading keeps it.
     *
     * @return array{title: string, ogTitle: string, description: string, canonical: string, alternates: array<int, array{hreflang: string, url: string}>, locale: string, alternateLocales: array<int, string>, image: string, type: string}
     */
    public function forRequest(Request $request, ?string $title = null, ?string $description = null, ?string $image = null): array
    {
        $copy = $this->copyFor($request);
        $links = $this->languages->links($request);

        $ogTitle = $title ?? $copy['title'];

        return [
            'title' => $ogTitle.' · '.config('app.name'),
            'ogTitle' => $ogTitle,
            'description' => $description ?? $copy['description'],
            'canonical' => $this->canonical($request, $links),
            'alternates' => $this->alternates($request, $links),
            'locale' => $this->openGraphLocale(app()->getLocale()),
            'alternateLocales' => $this->alternateLocales($request, $links),
            'image' => $image ?? asset('images/og/default.png'),
            'type' => 'website',
        ];
    }

    /**
     * The URL that owns this page. An English only page hands it to its English
     * URL, and so does a documentation page the current language does not carry:
     * both are serving English text under a prefix that promises otherwise.
     *
     * @param  array<int, array<string, mixed>>  $links
     */
    private function canonical(Request $request, array $links): string
    {
        $current = collect($links)->firstWhere('current', true);

        if ($this->isEnglishOnly($request) || $current === null || $current['translated'] === false) {
            return $this->englishUrl($links);
        }

        return (string) $current['url'];
    }

    /**
     * One entry per language that genuinely serves this page, plus x-default for
     * the crawler that recognises none of them. Nothing at all for an English
     * only page: seven identical URLs do not need telling apart.
     *
     * @param  array<int, array<string, mixed>>  $links
     * @return array<int, array{hreflang: string, url: string}>
     */
    private function alternates(Request $request, array $links): array
    {
        if ($this->isEnglishOnly($request)) {
            return [];
        }

        $alternates = collect($links)
            ->filter(fn (array $link): bool => $link['translated'] === true)
            ->map(fn (array $link): array => [
                'hreflang' => str_replace('_', '-', (string) $link['locale']),
                'url' => (string) $link['url'],
            ])
            ->values()
            ->all();

        if ($alternates === []) {
            return [];
        }

        $alternates[] = ['hreflang' => 'x-default', 'url' => $this->englishUrl($links)];

        return $alternates;
    }

    /**
     * The other languages this page is offered in, for og:locale:alternate.
     *
     * @param  array<int, array<string, mixed>>  $links
     * @return array<int, string>
     */
    private function alternateLocales(Request $request, array $links): array
    {
        if ($this->isEnglishOnly($request)) {
            return [];
        }

        return collect($links)
            ->filter(fn (array $link): bool => $link['translated'] === true && $link['current'] === false)
            ->map(fn (array $link): string => $this->openGraphLocale((string) $link['locale']))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $links
     */
    private function englishUrl(array $links): string
    {
        $english = collect($links)->firstWhere('locale', config('docs.default_locale'));

        return (string) ($english['url'] ?? route('marketing.index', ['locale' => 'en']));
    }

    /**
     * Whether this page carries the same English text whatever the prefix says.
     * The list is in config/marketing.php, because the sitemap has to reach the
     * same verdict about the same page.
     */
    private function isEnglishOnly(Request $request): bool
    {
        return in_array($request->route()?->getName(), config('marketing.english_only_routes'), true);
    }

    /**
     * Open Graph wants a language and a territory. Our locale keys already read
     * that way apart from plain English.
     */
    private function openGraphLocale(string $locale): string
    {
        return $locale === 'en' ? 'en_US' : $locale;
    }

    /**
     * The title and the description of the page being served. Feature pages come
     * from the catalogue that already describes them; everything else is listed
     * here, so the copy sits in one place instead of in twenty views.
     *
     * @return array{title: string, description: string}
     */
    private function copyFor(Request $request): array
    {
        $name = $request->route()?->getName();

        if ($name === 'marketing.features.show') {
            $feature = $this->features->find((string) $request->route('slug'));

            if ($feature !== null) {
                return ['title' => $feature['title'], 'description' => $feature['desc']];
            }
        }

        return $this->pages()[$name] ?? [
            'title' => config('app.name'),
            'description' => (string) config('app.description'),
        ];
    }

    /**
     * @return array<string, array{title: string, description: string}>
     */
    private function pages(): array
    {
        return [
            'marketing.index' => [
                'title' => __('The open source home for everything you collect'),
                'description' => __('Catalogue books, records, comics, watches, wine and anything else you collect. Track every physical copy you own, what it cost, what it is worth and where it lives. Self-host it for free.'),
            ],
            'marketing.blog.index' => [
                'title' => __('Blog'),
                'description' => __('Writing about collecting, about how KolleK is built, and about running your own instance. Every entry catalogued, numbered and kept.'),
            ],
            'marketing.features.index' => [
                'title' => __('Features'),
                'description' => __('Copy tracking, custom collection types, photos, loans, valuations, provenance, an API and self-hosting. Everything KolleK does, area by area.'),
            ],
            'marketing.pricing.index' => [
                'title' => __('Pricing'),
                'description' => __('Self-host KolleK for free, forever, under the MIT licence. No subscription, no per-seat charge, and no feature held back from the free version.'),
            ],
            'marketing.faq.index' => [
                'title' => __('Frequently asked questions'),
                'description' => __('A hundred answers about ownership, privacy, pricing, self-hosting and the honest limits of what KolleK does today.'),
            ],
            'marketing.about.index' => [
                'title' => __('About'),
                'description' => __('KolleK is an independent open source project built by one developer and a set of AI tools. Who is behind it, why it exists, and what it will never build.'),
            ],
            'marketing.testimonials.index' => [
                'title' => __('Reviews'),
                'description' => __('What collectors say about cataloguing their collection with KolleK, in their own words.'),
            ],
            'marketing.mediaKit.index' => [
                'title' => 'Media kit',
                'description' => 'Boilerplate, key facts, logos and screenshots for journalists writing about KolleK. No form, no embargo, no approval loop.',
            ],
            'marketing.terms.index' => [
                'title' => 'Terms of Use',
                'description' => 'The terms that govern the use of the hosted KolleK service.',
            ],
            'marketing.privacy.index' => [
                'title' => 'Privacy Policy',
                'description' => 'What the hosted KolleK service collects, how it is stored and encrypted, and who it is shared with.',
            ],
            'marketing.docs.portal.home.show' => [
                'title' => __('Documentation'),
                'description' => __('Guides, tutorials and reference for cataloguing a collection with KolleK, from the first item to running your own instance.'),
            ],
            'marketing.docs.api.index' => [
                'title' => 'API reference',
                'description' => 'Every endpoint of the KolleK JSON API, with request and response examples, authentication and rate limits.',
            ],
        ];
    }
}
