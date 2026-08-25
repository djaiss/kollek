<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Actions\UpdateBlogPost as UpdateBlogPostAction;
use App\Enums\BlogShelf;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Change what a blog entry is filed under and how crawlers should treat it. Only the fields given are changed; the rest are left as they are. It does not change the text, which write-blog-post-translation does.')]
class UpdateBlogPost extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'blog_post_id' => ['required', 'integer', 'exists:blog_posts,id'],
            'shelf' => ['nullable', 'string', Rule::in(array_column(BlogShelf::cases(), 'value'))],
            'is_featured' => ['nullable', 'boolean'],
            'robots' => ['nullable', 'string', Rule::in(['index,follow', 'noindex', 'nofollow'])],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],
        ]);

        $post = BlogPost::query()->with('tags')->findOrFail($validated['blog_post_id']);

        /** @var User $user */
        $user = $request->user();

        $post = new UpdateBlogPostAction(
            user: $user,
            blogPost: $post,
            shelf: isset($validated['shelf']) ? BlogShelf::from($validated['shelf']) : $post->shelf,
            isFeatured: isset($validated['is_featured']) ? (bool) $validated['is_featured'] : $post->is_featured,
            robots: $validated['robots'] ?? $post->robots,
            tags: $validated['tags'] ?? $post->tags->pluck('name')->all(),
        )->execute();

        return Response::structured([
            'id' => $post->id,
            'reference' => $post->reference(),
            'shelf' => $post->shelf->value,
            'is_featured' => $post->is_featured,
            'robots' => $post->robots,
            'tags' => $post->tags()->pluck('name')->all(),
            'message' => 'The entry has been updated.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'blog_post_id' => $schema->integer()->description('The id of the entry, as returned by list-blog-posts.')->required(),
            'shelf' => $schema->string()
                ->enum(array_column(BlogShelf::cases(), 'value'))
                ->description('The shelf the entry is filed on.'),
            'is_featured' => $schema->boolean()->description('Whether the entry is pulled to the top of the public index.'),
            'robots' => $schema->string()
                ->enum(['index,follow', 'noindex', 'nofollow'])
                ->description('What crawlers are told to do with the entry.'),
            'tags' => $schema->array()
                ->items($schema->string()->max(255))
                ->description('What the entry is filed under, replacing whatever it was filed under before.'),
        ];
    }
}
