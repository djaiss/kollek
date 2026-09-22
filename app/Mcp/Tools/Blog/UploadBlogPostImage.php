<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Actions\AddBlogPostImage;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Put a picture on the instance for a blog entry to use, and get back the Markdown that shows it. This only stores the picture: it appears to a reader once that Markdown is written into a language with write-blog-post-translation. The picture belongs to the entry rather than to one language of it, so the same one can be shown by every translation. It does not set the social card an entry shares as, which is done from the instance administration.')]
class UploadBlogPostImage extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'blog_post_id' => ['required', 'integer'],
            'content' => ['required', 'string'],
            'alt' => ['required', 'string', 'max:255'],
            'sha256' => ['sometimes', 'string', 'size:64'],
        ]);

        $post = BlogPost::query()->findOrFail($validated['blog_post_id']);

        /** @var User $user */
        $user = $request->user();

        $path = new AddBlogPostImage(
            user: $user,
            blogPost: $post,
            content: $validated['content'],
            sha256: $validated['sha256'] ?? null,
        )->execute();

        $url = route('marketing.blog.image.show', [
            'blogPost' => $post->id,
            'image' => basename($path),
        ], absolute: false);

        return Response::structured([
            'url' => $url,
            'markdown' => '!['.$validated['alt'].']('.$url.')',
            'message' => 'The picture is stored but nobody can see it yet. Put the markdown above into the body of a language with write-blog-post-translation, reading the body first so the rest of it survives.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'blog_post_id' => $schema->integer()->description('The id of the entry the picture belongs to, as returned by list-blog-posts.')->required(),
            'content' => $schema->string()->description('The picture itself, base64 encoded. A jpeg, png or webp, of no more than 5 MB once decoded. Anything wider or taller than 1600 pixels is scaled down to it. Send it whole: a string rebuilt from several pieces of command output is usually cut short, and only part of a picture is refused.')->required(),
            'sha256' => $schema->string()->description('The sha256 of the picture before it was base64 encoded, lowercase hex. Optional, but send it whenever you can: it is what catches a picture that arrived cut short, and the answer then says so instead of storing something broken.'),
            'alt' => $schema->string()->max(255)->description('What the picture shows, in a few words. It goes in the returned Markdown, and it is what somebody reading with a screen reader gets instead of the picture.')->required(),
        ];
    }
}
