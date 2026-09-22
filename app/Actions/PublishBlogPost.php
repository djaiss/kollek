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
 * Put a blog entry in the public catalogue. Only an instance administrator may
 * do this.
 */
class PublishBlogPost
{
    public function __construct(
        private readonly User $user,
        private readonly BlogPost $blogPost,
    ) {}

    public function execute(): BlogPost
    {
        $this->validate();
        $this->publish();
        $this->flushMarketingCache();
        $this->log();

        return $this->blogPost;
    }

    private function validate(): void
    {
        if (! $this->user->isInstanceAdministrator()) {
            throw new ModelNotFoundException('Blog post not found');
        }

        if ($this->blogPost->source() === null) {
            throw new ModelNotFoundException('Blog post not found');
        }
    }

    private function publish(): void
    {
        $this->blogPost->update([
            'status' => BlogPostStatus::Published,
            'published_at' => $this->blogPost->published_at ?? now(),
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
            action: UserActionEnum::BlogPostPublished,
            parameters: ['reference' => $this->blogPost->reference()],
        )->onQueue('low');
    }
}
