<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BlogTranslationState;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Start a language off from the English text, so a translator has the structure
 * in front of them rather than an empty page. The copy lands in review, and a
 * language somebody has already written is never overwritten.
 */
class CopyBlogPostSourceTranslation
{
    private BlogPostTranslation $translation;

    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
        private readonly string $locale,
    ) {}

    public function execute(): BlogPostTranslation
    {
        $this->validate();
        $this->copy();
        $this->log();

        return $this->translation;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }

        if ($this->locale === config('docs.default_locale')) {
            throw new ModelNotFoundException('Blog post not found');
        }

        if (! array_key_exists($this->locale, (array) config('docs.locales'))) {
            throw new ModelNotFoundException('Blog post not found');
        }

        if ($this->blogPost->translationFor($this->locale) !== null) {
            throw new ModelNotFoundException('Blog post not found');
        }
    }

    private function copy(): void
    {
        $source = $this->blogPost->source();

        if ($source === null) {
            throw new ModelNotFoundException('Blog post not found');
        }

        $this->translation = $this->blogPost->translations()->create([
            'locale' => $this->locale,
            'slug' => $source->slug,
            'title' => $source->title,
            'meta_description' => $source->meta_description,
            'body' => $source->body,
            'state' => BlogTranslationState::InReview,
        ]);
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::BlogPostTranslationUpdate,
            parameters: ['reference' => $this->blogPost->reference(), 'locale' => $this->locale],
        )->onQueue('low');
    }
}
