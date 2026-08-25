<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Models\BlogPost;
use App\Services\BlogPostAdministration;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Read one blog entry: what it is filed under, whether it is public, and how far along every language of it is. It does not return the text, which show-blog-post-translation does, one language at a time.')]
class ShowBlogPost extends Tool
{
    public function __construct(
        private BlogPostAdministration $administration,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'blog_post_id' => ['required', 'integer', 'exists:blog_posts,id'],
        ]);

        $post = BlogPost::query()->with(['translations', 'tags'])->findOrFail($validated['blog_post_id']);
        $source = $post->source();

        return Response::structured([
            'id' => $post->id,
            'reference' => $post->reference(),
            'title' => $source?->title,
            'path' => '/blog/'.($source->slug ?? ''),
            'status' => $post->status->value,
            'shelf' => $post->shelf->value,
            'is_featured' => $post->is_featured,
            'robots' => $post->robots,
            'tags' => $post->tags->pluck('name')->all(),
            'author' => $post->author_name,
            'published_at' => $post->published_at?->toIso8601String(),
            'updated_at' => $post->updated_at?->toIso8601String(),
            'languages' => array_map(fn (array $language): array => [
                'locale' => $language['locale'],
                'label' => $language['label'],
                'state' => $language['state']?->value,
                'note' => $language['note'],
                'slug' => $language['slug'],
            ], $this->administration->languages($post)),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'blog_post_id' => $schema->integer()
                ->description('The id of the entry, as returned by list-blog-posts.')
                ->required(),
        ];
    }
}
