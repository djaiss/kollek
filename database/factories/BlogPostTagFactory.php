<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\BlogPostTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogPostTag>
 */
class BlogPostTagFactory extends Factory
{
    protected $model = BlogPostTag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blog_post_id' => BlogPost::factory(),
            'name' => fake()->unique()->word(),
        ];
    }
}
