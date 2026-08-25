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

/**
 * Say how far along one language of a blog entry is: mark a translation
 * proofread so readers start seeing it, or take it back off the site.
 *
 * English cannot be moved. It is the source by definition, and the fallback
 * every other locale relies on, so demoting it would leave the entry with
 * nothing to show anybody.
 */
class UpdateBlogPostTranslationState
{
    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
        private readonly BlogPostTranslation $translation,
        private readonly BlogTranslationState $state,
    ) {}

    public function execute(): BlogPostTranslation
    {
        $this->validate();
        $this->update();
        $this->flushMarketingCache();
        $this->log();

        return $this->translation;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }

        if ($this->translation->blog_post_id !== $this->blogPost->id) {
            throw new ModelNotFoundException('Blog post not found');
        }

        if ($this->translation->state === BlogTranslationState::Source || $this->state === BlogTranslationState::Source) {
            throw new ModelNotFoundException('Blog post not found');
        }
    }

    private function update(): void
    {
        $this->translation->update([
            'state' => $this->state,
        ]);
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
            action: UserActionEnum::BlogPostTranslationStateUpdate,
            parameters: ['reference' => $this->blogPost->reference(), 'locale' => $this->translation->locale],
        )->onQueue('low');
    }
}
