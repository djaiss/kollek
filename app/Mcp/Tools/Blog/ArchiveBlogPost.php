<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Actions\ArchiveBlogPost as ArchiveBlogPostAction;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Take a blog entry out of the public catalogue and the feed, while its URL keeps answering so the links pointing at it do not break. This is how an entry is retired; there is no tool that deletes one.')]
class ArchiveBlogPost extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'blog_post_id' => ['required', 'integer', 'exists:blog_posts,id'],
        ]);

        $post = BlogPost::query()->findOrFail($validated['blog_post_id']);

        /** @var User $user */
        $user = $request->user();

        $post = new ArchiveBlogPostAction(user: $user, blogPost: $post)->execute();

        return Response::structured([
            'id' => $post->id,
            'reference' => $post->reference(),
            'status' => $post->status->value,
            'message' => 'The entry has left the catalogue, and its URL keeps answering.',
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
