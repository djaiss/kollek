<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Services\BlogCatalogue;
use App\Services\BlogPostMetrics;
use App\ViewModels\MarketingBlog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    public function __construct(
        private MarketingBlog $blog,
        private BlogCatalogue $catalogue,
        private BlogPostMetrics $metrics,
    ) {}

    /**
     * The catalogue: every published entry, newest first, in whichever language
     * the reader has. The {locale} URL prefix is consumed and validated by the
     * marketing.locale middleware, so no locale argument is needed here.
     *
     * The whole catalogue is rendered and the shelf filter runs in the browser.
     * The page is held by the CDN as one document for everybody, so filtering on
     * the server would mean one cached copy per shelf and no gain to the reader.
     */
    public function index(): View
    {
        $locale = app()->getLocale();

        return view('marketing.blog.index', [
            'entries' => $this->catalogue->entries($locale),
            'shelves' => $this->blog->shelves(),
            'counts' => $this->catalogue->countsByShelf(),
        ]);
    }

    /**
     * One entry. The {locale} prefix is the first route parameter, so it is
     * absorbed here even though the locale itself comes from the app locale the
     * middleware already set.
     *
     * A slug is looked for in the reader's own language, then in English, and
     * finally among the slugs the entry used to answer on before it was renamed.
     */
    public function show(string $locale, string $slug): View|RedirectResponse
    {
        $appLocale = app()->getLocale();
        $translation = $this->catalogue->find($appLocale, $slug);

        if ($translation === null) {
            $current = $this->catalogue->currentSlugFor($appLocale, $slug);

            if ($current === null) {
                throw new NotFoundHttpException;
            }

            return redirect()->route('marketing.blog.show', $current, status: 301);
        }

        $post = $translation->blogPost;

        // The reader may have arrived under a slug from another language. What
        // they are served is whichever language they can actually read.
        $served = $post->translation($appLocale) ?? $translation;
        $rendered = $served->rendered();
        $metrics = $this->metrics->forTranslation($served);

        return view('marketing.blog.show', [
            'post' => $post,
            'translation' => $served,
            'body' => $rendered['html'],
            'toc' => $rendered['toc'],
            'readingMinutes' => $metrics['minutesReading'],
            'measurements' => $this->blog->measurements($metrics),
            'classics' => $this->blog->classics($metrics['words']),
            'previous' => $this->catalogue->adjacent($post, $appLocale, newer: false),
            'next' => $this->catalogue->adjacent($post, $appLocale, newer: true),
            'related' => $this->catalogue->related($post, $appLocale),
        ]);
    }
}
