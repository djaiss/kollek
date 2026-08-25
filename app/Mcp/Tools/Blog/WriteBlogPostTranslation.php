<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Actions\UpsertBlogPostTranslation;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Write one language of a blog entry, creating it if it does not exist yet. The text is required the first time; afterwards only the fields given are replaced. Changing a published slug leaves a permanent redirect behind, so old links keep working. Writing English marks every other language outdated, since they were translated from what it used to say.')]
class WriteBlogPostTranslation extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $identity = $request->validate([
            'blog_post_id' => ['required', 'integer', 'exists:blog_posts,id'],
            'locale' => ['required', 'string', Rule::in(array_keys((array) config('docs.locales')))],
        ]);

        $post = BlogPost::query()->with('translations')->findOrFail($identity['blog_post_id']);
        $existing = $post->translationFor($identity['locale']);
        $required = Rule::requiredIf($existing === null);

        $validated = $request->validate([
            'title' => [$required, 'string', 'max:255'],
            'excerpt' => [$required, 'string', 'max:1000'],
            'body' => [$required, 'string'],
            'slug' => [$required, 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'focus_keyword' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $translation = new UpsertBlogPostTranslation(
            user: $user,
            blogPost: $post,
            locale: $identity['locale'],
            title: (string) ($validated['title'] ?? $existing?->title),
            excerpt: (string) ($validated['excerpt'] ?? $existing?->excerpt),
            body: (string) ($validated['body'] ?? $existing?->body),
            slug: (string) ($validated['slug'] ?? $existing?->slug),
            metaTitle: $validated['meta_title'] ?? $existing?->meta_title,
            metaDescription: $validated['meta_description'] ?? $existing?->meta_description,
            focusKeyword: $validated['focus_keyword'] ?? $existing?->focus_keyword,
        )->execute();

        return Response::structured([
            'blog_post_id' => $post->id,
            'locale' => $translation->locale,
            'state' => $translation->state->value,
            'slug' => $translation->slug,
            'message' => 'Saved. Changing a published slug leaves a permanent redirect behind, so old links keep working.',
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
                ->description('The language being written.')
                ->required(),
            'title' => $schema->string()->max(255)->description('The headline in this language.'),
            'excerpt' => $schema->string()->max(1000)->description('The standfirst in this language.'),
            'body' => $schema->string()->description('The article in this language, written in Markdown.'),
            'slug' => $schema->string()->max(255)->description('The last segment of the URL in this language. Slugs are unique per language, not across the blog.'),
            'meta_title' => $schema->string()->max(255)->description('The title tag. Falls back to the headline when left out. Aim for 30 to 60 characters.'),
            'meta_description' => $schema->string()->max(500)->description('The description tag. Falls back to the standfirst when left out. Aim for 70 to 160 characters.'),
            'focus_keyword' => $schema->string()->max(255)->description('The phrase this entry is meant to be found on.'),
        ];
    }
}
