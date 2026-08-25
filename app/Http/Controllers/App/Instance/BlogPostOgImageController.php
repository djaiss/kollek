<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\Instance;

use App\Actions\DestroyBlogPostOgImage;
use App\Actions\UpdateBlogPostOgImage;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The social card one language of a blog entry shares as.
 *
 * The panel is English only and never translated, so its copy is plain strings.
 */
class BlogPostOgImageController extends Controller
{
    public function create(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $locale = (string) $request->route()?->parameter('locale');

        $request->validate([
            'og_image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $translation = $blogPost->translationFor($locale);

        if ($translation === null) {
            throw new NotFoundHttpException;
        }

        new UpdateBlogPostOgImage(
            user: $request->user(),
            blogPost: $blogPost,
            translation: $translation,
            file: $request->file('og_image'),
        )->execute();

        return to_route('instanceAdmin.marketing.blogPosts.translations.edit', [
            'blogPost' => $blogPost->id,
            'locale' => $locale,
        ])
            ->with('status', 'Social card replaced')
            ->with('status_description', 'It has been cropped to 1200 by 630, the size social platforms read.');
    }

    public function destroy(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $locale = (string) $request->route()?->parameter('locale');

        $translation = $blogPost->translationFor($locale);

        if ($translation === null) {
            throw new NotFoundHttpException;
        }

        new DestroyBlogPostOgImage(
            user: $request->user(),
            blogPost: $blogPost,
            translation: $translation,
        )->execute();

        return to_route('instanceAdmin.marketing.blogPosts.translations.edit', [
            'blogPost' => $blogPost->id,
            'locale' => $locale,
        ])
            ->with('status', 'Social card removed')
            ->with('status_description', 'This language shares under the site wide card again.');
    }
}
