<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BlogPostStatus;
use App\Enums\BlogShelf;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => fake()->unique()->numberBetween(1, 9999),
            'shelf' => BlogShelf::Collecting,
            'status' => BlogPostStatus::Published,
            'published_at' => now()->subDay(),
            'is_featured' => false,
            'robots' => 'index,follow',
            'author_id' => null,
            'author_name' => fake()->name(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BlogPostStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BlogPostStatus::Archived,
        ]);
    }
}
