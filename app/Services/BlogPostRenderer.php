<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BlogPostTranslation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Stevebauman\Purify\Facades\Purify;

/**
 * Turns one blog entry's Markdown body into the HTML the public site renders,
 * along with the contents list its sidebar is built from.
 *
 * This is deliberately not App\Services\DocumentationParser. That one speaks the
 * documentation portal's own dialect (@doc() references, admonitions, step
 * blocks) and is built around DocumentationPortal; a blog entry is plain
 * Markdown, and teaching it the portal's conventions would tie the two together
 * for no gain. What the two do share is the CommonMark setup below.
 *
 * Footnotes are on, because the writing style the blog is designed around uses
 * them, and the output is run through Purify: the body is written by an
 * instance administrator rather than by the public, but it ends up on a cached
 * page served to everybody, which is not the place to trust raw HTML.
 */
class BlogPostRenderer
{
    private MarkdownConverter $markdown;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new FootnoteExtension);

        $this->markdown = new MarkdownConverter($environment);
    }

    /**
     * Render a translation, keeping the result until the translation is next
     * saved. Rendering is the expensive half of drawing an entry, and a
     * published post changes far less often than it is read.
     *
     * @return array{html: string, toc: array<int, array{id: string, text: string, level: int}>}
     */
    public function render(BlogPostTranslation $translation): array
    {
        $key = sprintf(
            'blog.rendered.%d.%d',
            $translation->id,
            (int) $translation->updated_at?->timestamp,
        );

        return Cache::remember($key, now()->addWeek(), fn (): array => $this->convert($translation->body));
    }

    /**
     * @return array{html: string, toc: array<int, array{id: string, text: string, level: int}>}
     */
    public function convert(string $body): array
    {
        // Ids have to survive the sanitiser, which strips them by default:
        // footnotes are a pair of links pointing at each other's id, and
        // without them the note and the reference no longer connect.
        $html = Purify::config(['Attr.EnableID' => true])
            ->clean((string) $this->markdown->convert($body));

        return $this->extractTableOfContents($html);
    }

    /**
     * Give every h2 and h3 an id and collect them into the contents list. The
     * ids come from the heading text, and a repeated heading gets a numbered
     * suffix rather than a duplicate id.
     *
     * @return array{html: string, toc: array<int, array{id: string, text: string, level: int}>}
     */
    private function extractTableOfContents(string $html): array
    {
        $toc = [];
        $seen = [];

        $html = (string) preg_replace_callback('/<h([23])>(.*?)<\/h\1>/s', function (array $matches) use (&$toc, &$seen): string {
            $level = (int) $matches[1];
            $text = trim(strip_tags($matches[2]));
            $id = Str::slug($text) ?: 'section';

            $base = $id;
            $suffix = 2;

            while (isset($seen[$id])) {
                $id = $base.'-'.$suffix++;
            }

            $seen[$id] = true;
            $toc[] = ['id' => $id, 'text' => $text, 'level' => $level];

            return '<h'.$level.' id="'.$id.'" class="scroll-mt-24">'.$matches[2].'</h'.$level.'>';
        }, $html);

        return ['html' => $html, 'toc' => $toc];
    }
}
