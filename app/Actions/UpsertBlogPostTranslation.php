<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BlogTranslationState;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use App\Models\User;
use App\Services\CloudflareCache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * Write one language of a blog entry, creating it if this is the first time
 * anybody has written that language.
 */
class UpsertBlogPostTranslation
{
    private BlogPostTranslation $translation;

    private bool $sourceChanged = false;

    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
        private readonly string $locale,
        private readonly string $title,
        private readonly string $metaDescription,
        private readonly string $body,
        private readonly string $slug,
        private readonly ?string $metaTitle = null,
        private readonly ?string $focusKeyword = null,
    ) {}

    public function execute(): BlogPostTranslation
    {
        $this->validate();
        $this->recordOldSlug();
        $this->write();
        $this->flagTranslationsOutdated();
        $this->flushMarketingCache();
        $this->log();

        return $this->translation;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }

        if (! array_key_exists($this->locale, (array) config('docs.locales'))) {
            throw new ModelNotFoundException('Blog post not found');
        }
    }

    private function recordOldSlug(): void
    {
        $existing = $this->blogPost->translationFor($this->locale);

        if ($existing === null || ! $this->blogPost->status->isReadable()) {
            return;
        }

        if ($existing->slug === $this->newSlug()) {
            return;
        }

        $this->blogPost->redirects()->updateOrCreate(
            ['locale' => $this->locale, 'slug' => $existing->slug],
            [],
        );
    }

    private function write(): void
    {
        $existing = $this->blogPost->translationFor($this->locale);

        $this->sourceChanged = $this->isSource()
            && $existing !== null
            && ($existing->title !== $this->title || $existing->body !== $this->body);

        $this->translation = $this->blogPost->translations()->updateOrCreate(
            ['locale' => $this->locale],
            [
                'slug' => $this->newSlug(),
                'title' => $this->title,
                'body' => $this->body,
                'meta_title' => $this->metaTitle,
                'meta_description' => $this->metaDescription,
                'focus_keyword' => $this->focusKeyword,
                'state' => $existing !== null ? $existing->state : $this->initialState(),
            ],
        );

        // The entry may have been renamed back to a slug it used before, in
        // which case the redirect now points at itself.
        $this->blogPost->redirects()
            ->where('locale', $this->locale)
            ->where('slug', $this->translation->slug)
            ->delete();
    }

    private function initialState(): BlogTranslationState
    {
        return $this->isSource() ? BlogTranslationState::Source : BlogTranslationState::InReview;
    }

    private function flagTranslationsOutdated(): void
    {
        if (! $this->sourceChanged) {
            return;
        }

        $this->blogPost->translations()
            ->where('locale', '!=', $this->locale)
            ->where('state', '!=', BlogTranslationState::Outdated)
            ->update(['state' => BlogTranslationState::Outdated]);
    }

    private function isSource(): bool
    {
        return $this->locale === config('docs.default_locale');
    }

    private function newSlug(): string
    {
        return Str::slug($this->slug) ?: Str::slug($this->title);
    }

    private function flushMarketingCache(): void
    {
        if (! $this->blogPost->status->isReadable()) {
            return;
        }

        CloudflareCache::purgeEverything();
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
