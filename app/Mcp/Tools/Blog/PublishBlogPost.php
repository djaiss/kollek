<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Actions\PublishBlogPost as PublishBlogPostAction;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Put a blog entry in the public catalogue and purge the CDN cache. An entry with nothing written in English cannot be published, since English is what every other language falls back to.')]
class PublishBlogPost extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'blog_post_id' => ['required', 'integer', 'exists:blog_posts,id'],
        ]);

        $post = BlogPost::query()->with('translations')->findOrFail($validated['blog_post_id']);

        if ($post->source() === null) {
            return Response::error('This entry has no English text, so there is nothing for a reader to fall back to. Write it with write-blog-post-translation first.');
        }

        /** @var User $user */
        $user = $request->user();

        $post = new PublishBlogPostAction(user: $user, blogPost: $post)->execute();

        return Response::structured([
            'id' => $post->id,
            'reference' => $post->reference(),
            'status' => $post->status->value,
            'published_at' => $post->published_at?->toIso8601String(),
            'message' => 'The entry is live on the marketing site, and the CDN cache has been purged.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'blog_post_id' => $schema->integer()->description('The id of the entry, as returned by list-blog-posts.')->required(),
        ];
    }
}
