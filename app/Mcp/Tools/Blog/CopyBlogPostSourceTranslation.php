<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Actions\CopyBlogPostSourceTranslation as CopyBlogPostSourceTranslationAction;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Start a language from the English source, as a starting point to translate over. It sits in review, so readers keep seeing the English until somebody has actually translated it and marked it proofread.')]
class CopyBlogPostSourceTranslation extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'blog_post_id' => ['required', 'integer', 'exists:blog_posts,id'],
            'locale' => ['required', 'string', Rule::in(array_keys((array) config('docs.locales')))],
        ]);

        $post = BlogPost::query()->with('translations')->findOrFail($validated['blog_post_id']);
        $locale = $validated['locale'];

        if ($locale === config('docs.default_locale')) {
            return Response::error('English is the source, so there is nothing to copy it from.');
        }

        if ($post->translationFor($locale) !== null) {
            return Response::error("This entry already exists in {$locale}. Rewrite it with write-blog-post-translation instead.");
        }

        if ($post->source() === null) {
            return Response::error('This entry has no English text to copy. Write it with write-blog-post-translation first.');
        }

        /** @var User $user */
        $user = $request->user();

        $translation = new CopyBlogPostSourceTranslationAction(
            user: $user,
            blogPost: $post,
            locale: $locale,
        )->execute();

        return Response::structured([
            'blog_post_id' => $post->id,
            'locale' => $translation->locale,
            'state' => $translation->state->value,
            'slug' => $translation->slug,
            'message' => 'The English has been copied across. It sits in review until it is translated and marked proofread.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'blog_post_id' => $schema->integer()->description('The id of the entry, as returned by list-blog-posts.')->required(),
            'locale' => $schema->string()
                ->enum(array_keys((array) config('docs.locales')))
                ->description('The language to start. It cannot be English, which is the source.')
                ->required(),
        ];
    }
}
