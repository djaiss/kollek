<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BlogTranslationState;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPostTranslation>
 */
class BlogPostTranslationFactory extends Factory
{
    protected $model = BlogPostTranslation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'blog_post_id' => BlogPost::factory(),
            'locale' => 'en',
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'title' => $title,
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'meta_title' => null,
            'meta_description' => null,
            'focus_keyword' => null,
            'og_image_path' => null,
            'state' => BlogTranslationState::Source,
        ];
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => BlogTranslationState::Live,
        ]);
    }

    public function inReview(): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => BlogTranslationState::InReview,
        ]);
    }

    public function outdated(): static
    {
        return $this->state(fn (array $attributes): array => [
            'state' => BlogTranslationState::Outdated,
        ]);
    }
}
