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

#[Description('Mark one language of a blog entry proofread, so readers in that language are served it instead of the English. English cannot be marked proofread: it is the source, and it is always live.')]
class PublishBlogPostTranslation extends Tool
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
            return Response::error('English is the source. It is always served, and there is nothing to mark proofread.');
        }

        /** @var User $user */
        $user = $request->user();

        $translation = new UpdateBlogPostTranslationState(
            user: $user,
            blogPost: $post,
            translation: $translation,
            state: BlogTranslationState::Live,
        )->execute();

        return Response::structured([
            'blog_post_id' => $post->id,
            'locale' => $translation->locale,
            'state' => $translation->state->value,
            'message' => 'Readers in this language now see it instead of the English.',
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
                ->description('The language to mark proofread. It cannot be English.')
                ->required(),
        ];
    }
}
