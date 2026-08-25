<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BlogShelf;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\CloudflareCache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Change what a blog entry is: which shelf it sits on, whether it is featured,
 * what it tells crawlers, and what it is filed under.
 *
 * Not what it says, which belongs to a translation, and not whether it is
 * public, which is PublishBlogPost and ArchiveBlogPost. The reference is absent
 * on purpose: it is the one thing about an entry that never changes.
 */
class UpdateBlogPost
{
    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
        private readonly BlogShelf $shelf,
        private readonly bool $isFeatured,
        private readonly string $robots,
        /** @var array<int, string> */
        private readonly array $tags = [],
    ) {}

    public function execute(): BlogPost
    {
        $this->validate();
        $this->update();
        $this->syncTags();
        $this->flushMarketingCache();
        $this->log();

        return $this->blogPost->refresh();
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }
    }

    private function update(): void
    {
        $this->blogPost->update([
            'shelf' => $this->shelf,
            'is_featured' => $this->isFeatured,
            'robots' => $this->robots,
        ]);
    }

    /**
     * Tags are replaced wholesale rather than diffed: the panel edits them as
     * one list, and the table holds nothing worth preserving beyond the name.
     */
    private function syncTags(): void
    {
        $names = collect($this->tags)
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->values();

        $this->blogPost->tags()->delete();

        foreach ($names as $name) {
            $this->blogPost->tags()->create(['name' => $name]);
        }
    }

    /**
     * A published entry is held by the CDN for a week, so an edit that is not
     * purged would not be read until then. A draft has nothing cached to purge.
     */
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
            action: UserActionEnum::BlogPostUpdate,
            parameters: ['reference' => $this->blogPost->reference()],
        )->onQueue('low');
    }
}
