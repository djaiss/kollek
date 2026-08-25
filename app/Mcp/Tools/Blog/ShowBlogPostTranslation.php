<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Models\BlogPost;
use App\Services\BlogPostAdministration;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Read one language of a blog entry: its text, its URL, its metadata, and the advice on that metadata. Read this before rewriting a language, since write-blog-post-translation replaces what it is given.')]
class ShowBlogPostTranslation extends Tool
{
    public function __construct(
        private BlogPostAdministration $administration,
    ) {}

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'blog_post_id' => ['required', 'integer', 'exists:blog_posts,id'],
            'locale' => ['required', 'string', Rule::in(array_keys((array) config('docs.locales')))],
        ]);

        $post = BlogPost::query()->with('translations')->findOrFail($validated['blog_post_id']);
        $translation = $post->translationFor($validated['locale']);

        if ($translation === null) {
            return Response::error("This entry has not been written in {$validated['locale']} yet. Start it with copy-blog-post-source-translation, or write it from scratch with write-blog-post-translation.");
        }

        return Response::structured([
            'blog_post_id' => $post->id,
            'locale' => $translation->locale,
            'state' => $translation->state->value,
            'note' => $translation->state->note(),
            'title' => $translation->title,
            'excerpt' => $translation->excerpt,
            'body' => $translation->body,
            'slug' => $translation->slug,
            'meta_title' => $translation->meta_title,
            'meta_description' => $translation->meta_description,
            'focus_keyword' => $translation->focus_keyword,
            'checks' => $this->administration->checks($post, $translation),
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
                ->description('The language to read. English is the source every other language is written from.')
                ->required(),
        ];
    }
}
