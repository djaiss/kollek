<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\BlogPostRedirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogPostRedirect>
 */
class BlogPostRedirectFactory extends Factory
{
    protected $model = BlogPostRedirect::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blog_post_id' => BlogPost::factory(),
            'locale' => 'en',
            'slug' => fake()->unique()->slug(),
        ];
    }
}
