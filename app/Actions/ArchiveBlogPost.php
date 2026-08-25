<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BlogPostStatus;
use App\Enums\UserActionEnum;
use App\Jobs\LogUserAction;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\CloudflareCache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Take a blog entry out of the catalogue without taking it off the web.
 *
 * An archived entry no longer appears on the index, in the feed or in the
 * sitemap, but its URL keeps answering. That is the whole point of archiving
 * rather than deleting: the links already pointing at it stay honest.
 */
class ArchiveBlogPost
{
    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
    ) {}

    public function execute(): BlogPost
    {
        $this->validate();
        $this->archive();
        $this->flushMarketingCache();
        $this->log();

        return $this->blogPost;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }
    }

    private function archive(): void
    {
        $this->blogPost->update([
            'status' => BlogPostStatus::Archived,
        ]);
    }

    private function flushMarketingCache(): void
    {
        CloudflareCache::purgeEverything();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::BlogPostArchived,
            parameters: ['reference' => $this->blogPost->reference()],
        )->onQueue('low');
    }
}
