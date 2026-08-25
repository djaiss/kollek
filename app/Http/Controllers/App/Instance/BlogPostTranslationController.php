<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Instance;

use App\Actions\CopyBlogPostSourceTranslation;
use App\Actions\UpdateBlogPostTranslationState;
use App\Actions\UpsertBlogPostTranslation;
use App\Enums\BlogShelf;
use App\Enums\BlogTranslationState;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\BlogPostAdministration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Writing one language of one blog entry.
 *
 * Editing is done a language at a time, so the locale is a path segment rather
 * than a tab the server cannot see: every language of every entry has its own
 * URL, and the browser's back button works the way the writer expects.
 *
 * The panel is English only and never translated, so its copy is plain strings.
 */
class BlogPostTranslationController extends Controller
{
    public function __construct(
        private BlogPostAdministration $administration,
    ) {}

    public function edit(Request $request, BlogPost $blogPost): View
    {
        $locale = (string) $request->route()?->parameter('locale');

        if (! array_key_exists($locale, (array) config('docs.locales'))) {
            throw new NotFoundHttpException;
        }

        $translation = $blogPost->translationFor($locale);

        return view('app.instance.marketing.blogPosts.edit', [
            'post' => $blogPost,
            'locale' => $locale,
            'translation' => $translation,
            'languages' => $this->administration->languages($blogPost),
            'checks' => $this->administration->checks($blogPost, $translation),
            'shelves' => BlogShelf::options(),
            'isSource' => $locale === config('docs.default_locale'),
        ]);
    }

    /**
     * Saving the text, copying the English source as a starting point, and
     * moving a translation on or off the public site are one update told apart
     * by the intent field, the way the testimonials screen tells approving from
     * rejecting.
     */
    public function update(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $locale = (string) $request->route()?->parameter('locale');

        $validated = $request->validate([
            'intent' => ['required', 'string', Rule::in(['save', 'copy_source', 'publish', 'withdraw'])],
            'title' => ['required_if:intent,save', 'string', 'max:255'],
            'excerpt' => ['required_if:intent,save', 'string', 'max:1000'],
            'body' => ['required_if:intent,save', 'string'],
            'slug' => ['required_if:intent,save', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'focus_keyword' => ['nullable', 'string', 'max:255'],
        ]);

        $back = to_route('instanceAdmin.marketing.blogPosts.translations.edit', [
            'blogPost' => $blogPost->id,
            'locale' => $locale,
        ]);

        if ($validated['intent'] === 'copy_source') {
            new CopyBlogPostSourceTranslation(
                user: $request->user(),
                blogPost: $blogPost,
                locale: $locale,
            )->execute();

            return $back
                ->with('status', 'English copied across')
                ->with('status_description', 'It sits in review until somebody has actually translated it.');
        }

        if ($validated['intent'] === 'publish' || $validated['intent'] === 'withdraw') {
            $translation = $blogPost->translationFor($locale);

            if ($translation === null) {
                throw new NotFoundHttpException;
            }

            new UpdateBlogPostTranslationState(
                user: $request->user(),
                blogPost: $blogPost,
                translation: $translation,
                state: $validated['intent'] === 'publish' ? BlogTranslationState::Live : BlogTranslationState::InReview,
            )->execute();

            return $back
                ->with('status', $validated['intent'] === 'publish' ? 'Translation marked proofread' : 'Translation withdrawn')
                ->with('status_description', $validated['intent'] === 'publish'
                    ? 'Readers in this language now see it instead of the English.'
                    : 'This language falls back to English again until it is marked proofread.');
        }

        new UpsertBlogPostTranslation(
            user: $request->user(),
            blogPost: $blogPost,
            locale: $locale,
            title: $validated['title'],
            excerpt: $validated['excerpt'],
            body: $validated['body'],
            slug: $validated['slug'],
            metaTitle: $validated['meta_title'] ?? null,
            metaDescription: $validated['meta_description'] ?? null,
            focusKeyword: $validated['focus_keyword'] ?? null,
        )->execute();

        return $back
            ->with('status', 'Saved')
            ->with('status_description', 'Changing a published slug leaves a permanent redirect behind, so old links keep working.');
    }
}
