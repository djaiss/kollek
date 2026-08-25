<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Instance;

use App\Actions\ArchiveBlogPost;
use App\Actions\CreateBlogPost;
use App\Actions\DestroyBlogPost;
use App\Actions\PublishBlogPost;
use App\Actions\UpdateBlogPost;
use App\Enums\BlogPostStatus;
use App\Enums\BlogShelf;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\BlogPostAdministration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The blog, from the instance administration.
 *
 * The panel is English only and never translated, so its copy is plain strings
 * rather than __() calls.
 */
class BlogPostController extends Controller
{
    public function __construct(
        private BlogPostAdministration $administration,
    ) {}

    public function index(Request $request): View
    {
        $status = (string) ($request->route()?->parameter('status') ?? 'all');
        $search = trim((string) $request->query('search'));

        return view('app.instance.marketing.blogPosts.index', [
            'status' => $status,
            'search' => $search,
            'rows' => $this->administration->rows($status, $search),
            'counts' => $this->administration->counts(),
            'summary' => $this->administration->summary(),
        ]);
    }

    public function new(Request $request): View
    {
        return view('app.instance.marketing.blogPosts.new', [
            'shelves' => BlogShelf::options(),
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:1000'],
            'body' => ['required', 'string'],
            'shelf' => ['required', 'string', Rule::in(array_keys(BlogShelf::options()))],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        $post = new CreateBlogPost(
            user: $request->user(),
            title: $validated['title'],
            excerpt: $validated['excerpt'],
            body: $validated['body'],
            shelf: BlogShelf::from($validated['shelf']),
            slug: $validated['slug'] ?? null,
        )->execute();

        return to_route('instanceAdmin.marketing.blogPosts.translations.edit', [
            'blogPost' => $post->id,
            'locale' => config('docs.default_locale'),
        ])
            ->with('status', 'Blog post created')
            ->with('status_description', 'It is a draft until you publish it.');
    }

    /**
     * What the entry is, and whether it is public. Publishing and archiving are
     * the same update, told apart by the intent field, the way the testimonials
     * screen tells approving from rejecting.
     */
    public function update(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $validated = $request->validate([
            'intent' => ['required', 'string', Rule::in(['save', 'publish', 'archive'])],
            'shelf' => ['required_if:intent,save', 'string', Rule::in(array_keys(BlogShelf::options()))],
            'is_featured' => ['nullable', 'boolean'],
            'robots' => ['nullable', 'string', Rule::in(['index,follow', 'noindex', 'nofollow'])],
            'tags' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['intent'] === 'publish') {
            new PublishBlogPost(user: $request->user(), blogPost: $blogPost)->execute();

            return to_route('instanceAdmin.marketing.blogPosts.translations.edit', [
                'blogPost' => $blogPost->id,
                'locale' => config('docs.default_locale'),
            ])
                ->with('status', 'Blog post published')
                ->with('status_description', 'It is live on the marketing site, and the CDN cache has been purged.');
        }

        if ($validated['intent'] === 'archive') {
            new ArchiveBlogPost(user: $request->user(), blogPost: $blogPost)->execute();

            return to_route('instanceAdmin.marketing.blogPosts.translations.edit', [
                'blogPost' => $blogPost->id,
                'locale' => config('docs.default_locale'),
            ])
                ->with('status', 'Blog post archived')
                ->with('status_description', 'It has left the catalogue, and its URL keeps answering.');
        }

        new UpdateBlogPost(
            user: $request->user(),
            blogPost: $blogPost,
            shelf: BlogShelf::from($validated['shelf']),
            isFeatured: (bool) ($validated['is_featured'] ?? false),
            robots: $validated['robots'] ?? 'index,follow',
            tags: array_filter(array_map(trim(...), explode(',', (string) ($validated['tags'] ?? '')))),
        )->execute();

        return to_route('instanceAdmin.marketing.blogPosts.translations.edit', [
            'blogPost' => $blogPost->id,
            'locale' => config('docs.default_locale'),
        ])
            ->with('status', 'Blog post updated')
            ->with('status_description', 'The shelf, the tags and the crawler settings have been saved.');
    }

    public function destroy(Request $request, BlogPost $blogPost): RedirectResponse
    {
        new DestroyBlogPost(user: $request->user(), blogPost: $blogPost)->execute();

        return to_route('instanceAdmin.marketing.blogPosts.index', ['status' => BlogPostStatus::Draft->value])
            ->with('status', 'Blog post deleted')
            ->with('status_description', 'Every language of it is gone, and its reference will not be reused.');
    }
}
