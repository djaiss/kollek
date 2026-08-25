<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Http\Request;

/**
 * The JSON-LD graph in the head of every public page, telling a crawler what the
 * page is rather than leaving it to infer one from the markup.
 *
 * It is built the way App\ViewModels\MarketingSeo builds the meta tags, and for
 * the same reason: the answer depends on which route is being served, and the
 * copy that describes each one belongs in one place instead of in twenty views.
 * Both are written out by resources/views/partials/marketingMeta.blade.php.
 *
 * Every page carries the two entities that describe the site itself, and adds
 * whatever describes the page in front of it. They are one graph rather than one
 * script tag each, so the organisation is defined once and everything else
 * points at it by @id instead of repeating it.
 *
 * Three things are deliberately absent:
 *
 *  - no rating or review built out of the testimonials. Search engines do not
 *    honour reviews a business publishes about itself, and marking them up
 *    invites a manual action rather than a rich result;
 *  - no BreadcrumbList on the feature pages, which render no visible trail yet.
 *    Marking up what a reader cannot see is a guideline violation, so it lands
 *    with the visible breadcrumbs in issue #266;
 *  - no SearchAction on the website. The sitelinks searchbox it used to feed is
 *    gone, and the public site has no search for it to describe.
 */
class MarketingStructuredData
{
    public function __construct(
        private MarketingFeatures $features,
        private MarketingFaq $faq,
    ) {}

    /**
     * The graph for any public page other than a documentation page.
     *
     * @return array<string, mixed>
     */
    public function forRequest(Request $request): array
    {
        return $this->graph($this->pageEntities($request));
    }

    /**
     * The graph for one documentation page. The page is handed in rather than
     * resolved here: the view that renders it has already read it off disk, and
     * a second lookup would be the same answer at twice the price.
     *
     * @param  array<string, mixed>  $page
     * @return array<string, mixed>
     */
    public function forDocumentationPage(Request $request, array $page, string $excerpt): array
    {
        // The application travels with the article because the article points at
        // it: an @id nothing in the graph declares refers to nothing.
        $entities = [$this->application(), $this->documentationArticle($request, $page, $excerpt)];

        // The portal home is the top of the trail, so it has nothing to sit
        // under, and a breadcrumb of one item describes nothing.
        if ($page['is_home'] === false) {
            $entities[] = $this->documentationBreadcrumb($request, $page);
        }

        return $this->graph($entities);
    }

    /**
     * The graph for one blog entry. Like the documentation, the entry is handed
     * in rather than resolved here: the view has it already.
     *
     * There is no BreadcrumbList. The entry page shows a link back to the
     * catalogue, not a visible trail, and this class does not claim a breadcrumb
     * the reader cannot see.
     *
     * @return array<string, mixed>
     */
    public function forBlogPost(Request $request, BlogPost $post, BlogPostTranslation $translation): array
    {
        return $this->graph([$this->blogPosting($request, $post, $translation)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function blogPosting(Request $request, BlogPost $post, BlogPostTranslation $translation): array
    {
        return array_filter([
            '@type' => 'BlogPosting',
            '@id' => $request->url().'#article',
            'url' => $request->url(),
            'headline' => $translation->title,
            'description' => $translation->metaDescription(),
            'inLanguage' => $this->language($translation->locale),
            'datePublished' => $post->published_at?->toIso8601String(),
            // Only claimed when the entry has genuinely been edited since it
            // went out, rather than restating the publication date as a change.
            'dateModified' => $translation->updated_at?->greaterThan($post->published_at) === true
                ? $translation->updated_at->toIso8601String()
                : null,
            'author' => ['@type' => 'Person', 'name' => $post->author_name],
            'isPartOf' => ['@id' => $this->id('website')],
            'publisher' => ['@id' => $this->id('organization')],
        ], fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<int, array<string, mixed>>  $entities
     * @return array<string, mixed>
     */
    private function graph(array $entities): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $this->organization(),
                $this->website(),
                ...$entities,
            ],
        ];
    }

    /**
     * Whoever publishes all of this. Everything else in the graph points here.
     *
     * @return array<string, mixed>
     */
    private function organization(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => $this->id('organization'),
            'name' => config('app.name'),
            'url' => $this->root(),
            'description' => config('app.description'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('images/og/logo.png'),
                'width' => 512,
                'height' => 512,
            ],
            'sameAs' => [config('marketing.github_url')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => $this->id('website'),
            'name' => config('app.name'),
            'url' => $this->root(),
            'description' => config('app.description'),
            'publisher' => ['@id' => $this->id('organization')],
            'inLanguage' => $this->languages(),
        ];
    }

    /**
     * What the page in front of the visitor is, on top of the site itself.
     *
     * The pages that describe the product carry the application, and so do the
     * ones that point at it, because an @id nothing in the graph declares refers
     * to nothing. The pages that are about neither (the terms, the media kit,
     * the reviews) carry the site alone rather than a claim they do not make.
     *
     * @return array<int, array<string, mixed>>
     */
    private function pageEntities(Request $request): array
    {
        return match ($request->route()?->getName()) {
            'marketing.index', 'marketing.pricing.index',
            'marketing.features.index', 'marketing.features.show' => [$this->application()],
            'marketing.faq.index' => [$this->application(), $this->faqPage($request)],
            'marketing.about.index' => [$this->aboutPage($request)],
            default => [],
        };
    }

    /**
     * The product itself: what it is, what it runs on, what it costs and what it
     * is licensed under. The pages that describe the product all claim it, and
     * they claim the same one, which is what the shared @id is for.
     *
     * @return array<string, mixed>
     */
    private function application(): array
    {
        return [
            '@type' => 'SoftwareApplication',
            '@id' => $this->id('software'),
            'name' => config('app.name'),
            'url' => $this->root(),
            'description' => config('app.description'),
            'applicationCategory' => 'ProductivityApplication',
            'operatingSystem' => 'Web browser',
            'license' => config('marketing.github_url').'/blob/main/LICENSE',
            'isAccessibleForFree' => true,
            'inLanguage' => $this->languages(),
            'publisher' => ['@id' => $this->id('organization')],
            'softwareHelp' => [
                '@type' => 'CreativeWork',
                'name' => __('Documentation'),
                'url' => $this->url('marketing.docs.portal.home.show'),
            ],
            'featureList' => array_column($this->features->all(), 'title'),
            'offers' => $this->offers(),
        ];
    }

    /**
     * The two ways to have it, exactly as the pricing page words them. They are
     * not gated on whether this instance is the hosted one: the page prints the
     * price whatever the instance, and structured data has to describe what the
     * page visibly says.
     *
     * @return array<int, array<string, mixed>>
     */
    private function offers(): array
    {
        return [
            [
                '@type' => 'Offer',
                'name' => __('Self-host'),
                'description' => __('Free forever, not a trial or a tease'),
                'price' => '0',
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
                'url' => config('marketing.github_url'),
            ],
            [
                '@type' => 'Offer',
                'name' => __('Cloud'),
                'description' => __('No subscription'),
                'price' => (string) config('pricing.price'),
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
                'url' => $this->url('marketing.pricing.index'),
            ],
        ];
    }

    /**
     * Every question on the FAQ page, in the order it is read.
     *
     * The ten quick answers above the list are left out on purpose: they ask the
     * same things again in two words, and a page that answers one question twice
     * is a page a crawler is entitled to distrust.
     *
     * @return array<string, mixed>
     */
    private function faqPage(Request $request): array
    {
        $questions = [];

        foreach ($this->faq->sections() as $section) {
            foreach ($section['items'] as $item) {
                $questions[] = [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ];
            }
        }

        return [
            '@type' => 'FAQPage',
            '@id' => $request->url().'#faq',
            'url' => $request->url(),
            'name' => __('Frequently asked questions'),
            'inLanguage' => $this->language(app()->getLocale()),
            'isPartOf' => ['@id' => $this->id('website')],
            'about' => ['@id' => $this->id('software')],
            'mainEntity' => $questions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function aboutPage(Request $request): array
    {
        return [
            '@type' => 'AboutPage',
            '@id' => $request->url().'#about',
            'url' => $request->url(),
            'name' => __('About'),
            'inLanguage' => $this->language(app()->getLocale()),
            'isPartOf' => ['@id' => $this->id('website')],
            'about' => ['@id' => $this->id('organization')],
        ];
    }

    /**
     * A documentation page. TechArticle rather than Article: these are reference
     * and how-to pages about operating a piece of software, which is what the
     * type is for.
     *
     * There is no dateModified. The only date on disk is the mtime of the
     * Markdown, which a fresh clone or a Docker build resets, so every page would
     * claim to have changed at once (see issue #267).
     *
     * @param  array<string, mixed>  $page
     * @return array<string, mixed>
     */
    private function documentationArticle(Request $request, array $page, string $excerpt): array
    {
        return [
            '@type' => 'TechArticle',
            '@id' => $request->url().'#article',
            'url' => $request->url(),
            'headline' => $page['title'],
            'description' => $excerpt,
            'inLanguage' => $this->language(app()->getLocale()),
            'isPartOf' => ['@id' => $this->id('website')],
            'about' => ['@id' => $this->id('software')],
            'publisher' => ['@id' => $this->id('organization')],
        ];
    }

    /**
     * The trail a search result shows instead of the raw URL.
     *
     * It stops at the portal home and the page itself. The section between them
     * is a heading in the sidebar rather than a page of its own, so it has no URL
     * to give, and every item before the last one needs one.
     *
     * @param  array<string, mixed>  $page
     * @return array<string, mixed>
     */
    private function documentationBreadcrumb(Request $request, array $page): array
    {
        $home = $this->url('marketing.docs.portal.home.show');

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $request->url().'#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => __('Documentation'),
                    'item' => $home,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $page['title'],
                    'item' => $request->url(),
                ],
            ],
        ];
    }

    /**
     * A URL on the public site, in the language being read.
     */
    private function url(string $name): string
    {
        return route($name, ['locale' => $this->urlLocale()]);
    }

    private function urlLocale(): string
    {
        return config('docs.locales.'.app()->getLocale().'.url', 'en');
    }

    /**
     * An @id that is the same on every page of the instance, so the entities
     * that describe the site are recognised as one thing across all of them.
     */
    private function id(string $fragment): string
    {
        return $this->root().'/#'.$fragment;
    }

    /**
     * The root of the site, from the same generator that builds the canonical
     * and every other URL on the page. Reading config('app.url') directly would
     * disagree with them the moment the configured scheme and the served one
     * differ, and an @id that does not match the URL beside it ties nothing
     * together.
     */
    private function root(): string
    {
        return url('/');
    }

    /**
     * The languages the site is served in, as the tags a crawler expects.
     *
     * @return array<int, string>
     */
    private function languages(): array
    {
        return array_map($this->language(...), array_keys(config('docs.locales')));
    }

    private function language(string $locale): string
    {
        return str_replace('_', '-', $locale);
    }
}
