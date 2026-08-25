<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Actions\UpdateBlogPostTranslationState;
use App\Enums\BlogTranslationState;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Take one language of a blog entry back off the public site, so that language falls back to English again until it is marked proofread. English cannot be withdrawn: it is the source everything else falls back to.')]
class WithdrawBlogPostTranslation extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'blog_post_id' => ['required', 'integer', 'exists:blog_posts,id'],
            'locale' => ['required', 'string', Rule::in(array_keys((array) config('docs.locales')))],
        ]);

        $post = BlogPost::query()->with('translations')->findOrFail($validated['blog_post_id']);
        $translation = $post->translationFor($validated['locale']);

        if ($translation === null) {
            return Response::error("This entry has not been written in {$validated['locale']} yet.");
        }

        if ($translation->state === BlogTranslationState::Source) {
            return Response::error('English is the source every other language falls back to, so it cannot be withdrawn.');
        }

        /** @var User $user */
        $user = $request->user();

        $translation = new UpdateBlogPostTranslationState(
            user: $user,
            blogPost: $post,
            translation: $translation,
            state: BlogTranslationState::InReview,
        )->execute();

        return Response::structured([
            'blog_post_id' => $post->id,
            'locale' => $translation->locale,
            'state' => $translation->state->value,
            'message' => 'This language falls back to English again until it is marked proofread.',
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
                ->description('The language to withdraw. It cannot be English.')
                ->required(),
        ];
    }
}
