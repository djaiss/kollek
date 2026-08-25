<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Actions\CreateBlogPost as CreateBlogPostAction;
use App\Enums\BlogShelf;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Start a blog entry, with its English text. English is the source every other language is written from, so an entry is never created without it. The entry is a draft until publish-blog-post is called.')]
class CreateBlogPost extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:1000'],
            'body' => ['required', 'string'],
            'shelf' => ['required', 'string', Rule::in(array_column(BlogShelf::cases(), 'value'))],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $post = new CreateBlogPostAction(
            user: $user,
            title: $validated['title'],
            excerpt: $validated['excerpt'],
            body: $validated['body'],
            shelf: BlogShelf::from($validated['shelf']),
            slug: $validated['slug'] ?? null,
        )->execute();

        return Response::structured([
            'id' => $post->id,
            'reference' => $post->reference(),
            'status' => $post->status->value,
            'slug' => $post->source()?->slug,
            'message' => 'The entry has been created as a draft, in English. Write the other languages, then publish it.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->max(255)->description('The headline, in English.')->required(),
            'excerpt' => $schema->string()->max(1000)->description('The standfirst printed under the headline and in the catalogue, in English.')->required(),
            'body' => $schema->string()->description('The article itself, in English, written in Markdown.')->required(),
            'shelf' => $schema->string()
                ->enum(array_column(BlogShelf::cases(), 'value'))
                ->description('The shelf the entry is filed on. An entry belongs to exactly one.')
                ->required(),
            'slug' => $schema->string()->max(255)->description('The last segment of the English URL. Derived from the title when left out.'),
        ];
    }
}
