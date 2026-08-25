<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Blog;

use App\Services\BlogPostAdministration;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List the blog entries, most recently touched first. Narrow the list to one status, or search the title and the slug of every language.')]
class ListBlogPosts extends Tool
{
    public function __construct(
        private BlogPostAdministration $administration,
    ) {}

    public function handle(Request $request): ResponseFactory
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['all', 'draft', 'published', 'archived'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $rows = $this->administration->rows(
            $validated['status'] ?? 'all',
            trim((string) ($validated['search'] ?? '')),
        );

        return Response::structured([
            'count' => count($rows),
            'posts' => array_map(fn (array $row): array => [
                'id' => $row['id'],
                'reference' => $row['reference'],
                'title' => $row['title'],
                'path' => $row['slug'],
                'shelf' => $row['shelfValue'],
                'status' => $row['status']->value,
                'locales_live' => $row['liveCount'],
                'locales_total' => $row['localeCount'],
                'updated' => $row['updatedAt'],
            ], $rows),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()
                ->enum(['all', 'draft', 'published', 'archived'])
                ->description('Which bucket to list. Defaults to every entry.'),
            'search' => $schema->string()
                ->max(255)
                ->description('Match a fragment of a title or a slug, in any language.'),
        ];
    }
}
