<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\ViewModels\MarketingFeatures;

/**
 * The plain Markdown index at /llms.txt (see https://llmstxt.org), so an
 * assistant reading the site finds the same pages a visitor would without
 * having to strip the HTML chrome from every one of them first.
 *
 * English only and unlocalized, the same as robots.txt and sitemap.xml: one
 * file for the whole host rather than one per language prefix, so its own
 * copy is plain English rather than going through __(). Every link points at
 * Markdown, not HTML, including the documentation pages, so an assistant
 * following it never has to render anything.
 */
class LlmsTxt
{
    /**
     * The blog is the one part of the site that grows without bound, so it is
     * capped here. The feed and the catalogue carry the rest.
     */
    private const int BLOG_ENTRIES = 30;

    public function __construct(
        private DocumentationPortal $portal,
        private MarketingFeatures $features,
    ) {}

    public function content(): string
    {
        $locale = $this->portal->defaultLocale();
        $urlLocale = $this->portal->urlLocaleFor($locale);

        $lines = [
            '# '.config('app.name'),
            '',
            '> '.config('app.description'),
            '',
            '## Product',
            '',
            '- [Home]('.route('marketing.index', ['locale' => $urlLocale]).')',
            '- [Pricing]('.route('marketing.pricing.index', ['locale' => $urlLocale]).')',
            '- [FAQ]('.route('marketing.faq.index', ['locale' => $urlLocale]).')',
            '- [About]('.route('marketing.about.index', ['locale' => $urlLocale]).')',
            '',
            '## Features',
            '',
        ];

        foreach ($this->features->all() as $feature) {
            $url = route('marketing.features.show', ['locale' => $urlLocale, 'slug' => $feature['slug']]);
            $lines[] = '- ['.$feature['title'].']('.$url.'): '.$feature['desc'];
        }

        $lines[] = '';
        $lines[] = '## Documentation';
        $lines[] = '';
        $lines[] = '- [Introduction]('.route('marketing.docs.portal.home.markdown', ['locale' => $urlLocale]).')';

        foreach ($this->portal->navigation($locale) as $section) {
            $lines[] = '';
            $lines[] = '### '.$section['title'];
            $lines[] = '';

            foreach ($section['items'] as $item) {
                $lines[] = '- ['.$item['title'].']('.$item['markdownUrl'].')';
            }
        }

        $blog = BlogPost::query()
            ->published()
            ->with('translations')
            ->limit(self::BLOG_ENTRIES)
            ->get();

        if ($blog->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '## Blog';
            $lines[] = '';

            foreach ($blog as $post) {
                $translation = $post->translation($locale);

                if ($translation === null) {
                    continue;
                }

                $url = route('marketing.blog.show', ['locale' => $urlLocale, 'slug' => $translation->slug]);
                $lines[] = '- ['.$translation->title.']('.$url.'): '.$translation->excerpt;
            }
        }

        $lines[] = '';
        $lines[] = '## Optional';
        $lines[] = '';
        $lines[] = '- [API reference]('.route('marketing.docs.api.markdown.index', ['locale' => $urlLocale]).')';

        return implode("\n", $lines)."\n";
    }
}
