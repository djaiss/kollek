<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogPostTranslation;

/**
 * The blog as an RSS document, one per language.
 *
 * Entries carry their whole body rather than a teaser. A reader who subscribed
 * to a feed has said where they would like to read; sending them back to the
 * site for the second half of a sentence ignores that, and it is the one
 * promise the blog makes about its feed.
 */
class BlogFeed
{
    private const int MAX_ENTRIES = 50;

    public function __construct(
        private BlogPostRenderer $renderer,
    ) {}

    public function forLocale(string $locale): string
    {
        $items = BlogPost::query()
            ->published()
            ->with('translations')
            ->limit(self::MAX_ENTRIES)
            ->get()
            ->map(fn (BlogPost $post): string => $this->item($post, $locale))
            ->implode('');

        $title = __(':name blog', ['name' => config('app.name')]);
        $description = __('Writing about collecting, building the app, and running your own instance.');

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'
            .'<channel>'
            .'<title>'.$this->escape($title).'</title>'
            .'<link>'.$this->escape(route('marketing.blog.index')).'</link>'
            .'<description>'.$this->escape($description).'</description>'
            .'<language>'.$this->escape(str_replace('_', '-', $locale)).'</language>'
            .'<atom:link href="'.$this->escape(route('marketing.blog.feed.index')).'" rel="self" type="application/rss+xml" />'
            .$items
            .'</channel>'
            .'</rss>';
    }

    private function item(BlogPost $post, string $locale): string
    {
        $translation = $post->translation($locale);

        if ($translation === null) {
            return '';
        }

        $url = route('marketing.blog.show', $translation->slug);

        return '<item>'
            .'<title>'.$this->escape($translation->title).'</title>'
            .'<link>'.$this->escape($url).'</link>'
            // The reference is stable where a URL is not: an entry can be
            // renamed, and a feed reader that keyed on the URL would then show
            // it again as though it were new.
            .'<guid isPermaLink="false">'.$this->escape($post->reference()).'</guid>'
            .'<pubDate>'.$post->published_at->toRfc2822String().'</pubDate>'
            .'<category>'.$this->escape($post->shelf->label()).'</category>'
            .'<description>'.$this->escape($translation->excerpt).'</description>'
            .'<content:encoded xmlns:content="http://purl.org/rss/1.0/modules/content/">'
            .'<![CDATA['.$this->body($translation).']]>'
            .'</content:encoded>'
            .'</item>';
    }

    /**
     * The rendered body, with any CDATA terminator in it broken up so it cannot
     * close the section it is wrapped in.
     */
    private function body(BlogPostTranslation $translation): string
    {
        return str_replace(']]>', ']]]]><![CDATA[>', $this->renderer->render($translation)['html']);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
